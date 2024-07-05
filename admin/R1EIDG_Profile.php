<?php

/**
 * Functions to display additional fields in the user settings. Inspired by https://usersinsights.com/wordpress-add-custom-field-to-user-profile/
 */
class R1EIDG_Profile
{
    const TRANSIENT_NAME = 'r1eidg_profile_messages';

    static function init()
    {
        add_action('show_user_profile', [get_class(), 'add_user_fields']);
        add_action('edit_user_profile', [get_class(), 'add_user_fields']);

        add_action('edit_user_profile_update', [get_class(), 'save_user_fields']);
        add_action('personal_options_update', [get_class(), 'save_user_fields']);

        add_action('admin_notices', [get_class(), 'print_errors']);
    }

    /**
     * Registers a "codice_fiscale" field in the users setting page,
     * because the plugin looks for user with a "codice_fiscale" meta field that corresponds to the one received from eID-Gateway to log in a user
     * (the meta field is the same as the <a href="https://wordpress.org/plugins/wp-spid-italia/">WP SPID Italia</a> plugin, for compatibility).
     * 
     * @link https://wordpress.org/plugins/wp-spid-italia/
     */
    static function add_user_fields($user)
    {
        if (!R1EIDG_Profile::can_edit_eid_user_fields($user->ID)) {
            return;
        }

        $fiscal_number = get_user_meta($user->ID, 'codice_fiscale', true);
?>
        <table class="form-table">
            <tr>
                <th><label for="codice_fiscale"><?= esc_html__('Codice fiscale'); ?></label></th>
                <td>
                    <input type="text" name="codice_fiscale" id="codice_fiscale" value="<?= esc_attr($fiscal_number); ?>" />
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Callback for edit_user_profile_update to save the user field.
     */
    static function save_user_fields($user_id)
    {
        if (empty($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'update-user_' . $user_id)) {
            return;
        }

        if (!R1EIDG_Profile::can_edit_eid_user_fields($user_id)) {
            return;
        }

        $new_fiscal_number = strtoupper(trim($_POST['codice_fiscale']));

        //check if other users already have this fiscal number
        $users = get_users(
            [
                'meta_key' => 'codice_fiscale',
                'meta_value' => $new_fiscal_number,
                'number' => 1
            ]
        );

        if (!empty($users) && $users[0]->ID != $user_id) {
            R1EIDG_Profile::add_error(esc_html__("Un altro utente ha già questo codice fiscale. Il codice fiscale non è stato modificato."));
            return;
        }

        update_user_meta($user_id, 'codice_fiscale', $new_fiscal_number);
    }

    /**
     * Check if the logged user should be able to modify user data related to eID-Gateway (the fiscal number).
     * @param mixed $user_id the user id of the user that is being modified
     */
    static function can_edit_eid_user_fields($user_id): bool
    {
        return is_super_admin() && current_user_can('edit_user', $user_id);
    }

    static function add_error($message)
    {
        $messages = get_transient(R1EIDG_Profile::TRANSIENT_NAME) ?: [];
        $messages[] = $message;
        set_transient(R1EIDG_Profile::TRANSIENT_NAME, $messages);
    }

    static function print_errors()
    {
        foreach (get_transient(R1EIDG_Profile::TRANSIENT_NAME) ?: [] as $message) {
        ?>
            <div class="error">
                <p><?= $message ?></p>
            </div>
<?php
        }

        delete_transient(R1EIDG_Profile::TRANSIENT_NAME);
    }
}
