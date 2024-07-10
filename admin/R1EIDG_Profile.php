<?php

/**
 * Functions to display additional fields in the user settings. Inspired by https://usersinsights.com/wordpress-add-custom-field-to-user-profile/
 */
class R1EIDG_Profile
{
    const TRANSIENT_NAME = 'r1eidg_profile_messages';

    static function init()
    {
        if (!current_user_can('edit_users'))
            return;

        // register actions to show and save the fiscal number field in user profile
        add_action('show_user_profile', [get_class(), 'add_user_fields_callback']);
        add_action('edit_user_profile', [get_class(), 'add_user_fields_callback']);

        add_action('edit_user_profile_update', [get_class(), 'save_user_fields_callback']);
        add_action('personal_options_update', [get_class(), 'save_user_fields_callback']);

        // register action to show the errors
        add_action('admin_notices', [get_class(), 'admin_notices_callback']);

        // register fiscal number column in user list
        add_filter('manage_users_columns', [get_class(), 'add_fiscal_number_column_callback']);
        add_action('manage_users_custom_column', [get_class(), 'manage_fiscal_number_column_callback'], 10, 3);
    }

    /**
     * Registers a "codice_fiscale" field in the users setting page,
     * because the plugin looks for user with a "codice_fiscale" meta field that corresponds to the one received from eID-Gateway to log in a user
     * (the meta field is the same as the <a href="https://wordpress.org/plugins/wp-spid-italia/">WP SPID Italia</a> plugin, for compatibility).
     * 
     * @link https://wordpress.org/plugins/wp-spid-italia/
     */
    static function add_user_fields_callback($user)
    {
        if($user)
            $fiscal_number = get_user_meta($user->ID, 'codice_fiscale', true);
?>
        <table class="form-table">
            <tr>
                <th><label for="codice_fiscale"><?= esc_html__('Codice fiscale', 'wp-mim-eidgateway-connect') ?></label></th>
                <td>
                    <input type="text" name="codice_fiscale" id="codice_fiscale" value="<?= esc_attr($fiscal_number ?? ''); ?>" />
                    <p class="description"><?= esc_html__("Il codice fiscale viene usato per l'accesso con SPID e CIE tramite eID-Gateway.", 'wp-mim-eidgateway-connect') ?></p>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Callback for edit_user_profile_update to save the user field.
     */
    static function save_user_fields_callback($user_id)
    {
        if (empty($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'update-user_' . $user_id)) {
            return;
        }

        $new_fiscal_number = strtoupper(trim($_POST['codice_fiscale']));

        if ($new_fiscal_number) {
            //check if other users already have this fiscal number
            $users = get_users(
                [
                    'meta_key' => 'codice_fiscale',
                    'meta_value' => $new_fiscal_number,
                    'number' => 1
                ]
            );

            if (!empty($users) && $users[0]->ID != $user_id) {
                R1EIDG_Profile::add_error(esc_html__("Un altro utente ha già questo codice fiscale. Il codice fiscale non è stato modificato.", 'wp-mim-eidgateway-connect'));
                return;
            }

            if (!preg_match('/^[a-zA-Z]{6}[0-9]{2}[abcdehlmprstABCDEHLMPRST]{1}[0-9]{2}[a-zA-Z]{1}[0-9]{3}[a-zA-Z]{1}$/i', $new_fiscal_number)) {
                R1EIDG_Profile::add_error(esc_html__("Il formato del codice fiscale non è valido. Il codice fiscale non è stato modificato.", 'wp-mim-eidgateway-connect'));
                return;
            }
        }

        update_user_meta($user_id, 'codice_fiscale', $new_fiscal_number);
    }

    /**
     * Callback for manage_users_columns to add fiscal number column
     */
    static function add_fiscal_number_column_callback($columns)
    {
        if (!is_super_admin())
            return;

        return array_merge($columns, ['codice_fiscale' => esc_html__('Codice fiscale', 'wp-mim-eidgateway-connect')]);
    }

    /**
     * Callback for manage_users_custom_column to fill fiscal number column
     */
    static function manage_fiscal_number_column_callback($val, $column_name, $user_id)
    {
        if ($column_name != 'codice_fiscale')
            return;

        return '<span style="font-family:monospace">' . esc_html__(get_user_meta($user_id, 'codice_fiscale', true)) . '</span>';
    }

    /**
     * Callback for admin_notice to display the stored errors.
     */
    static function admin_notices_callback()
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

    /**
     * Adds an error that will be displayed the next time the page loads (namely, after saving the user)
     * @param string $message the message to display
     */
    private static function add_error($message)
    {
        $messages = get_transient(R1EIDG_Profile::TRANSIENT_NAME) ?: [];
        $messages[] = $message;
        set_transient(R1EIDG_Profile::TRANSIENT_NAME, $messages);
    }
}
