<?php

/**
 * Functions to display additional fields in the user settings
 */
class R1EIDG_Profile
{
    static function init()
    {
        add_action('edit_user_profile', [get_class(), 'add_user_fields']);
        add_action('edit_user_profile_update', [get_class(), 'save_user_fields']);
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
     * Callback for edit_user_profile_update to save the user field. Inspired by https://usersinsights.com/wordpress-add-custom-field-to-user-profile/
     */
    static function save_user_fields($user_id)
    {
        if (empty($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'update-user_' . $user_id)) {
            return;
        }

        if (!current_user_can('edit_user', $user_id)) {
            return;
        }

        update_user_meta($user_id, 'codice_fiscale', $_POST['codice_fiscale']);
    }
}
