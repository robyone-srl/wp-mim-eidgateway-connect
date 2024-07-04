<?php

/**
 * Functions and constants that regard the settings.
 */
class R1EIDG_Settings
{
    const PAGE = 'R1EIDG';
    const OPTION_NAME = R1EIDG_Settings::PAGE . '_options';

    const SETTING_EID_ENABLED = R1EIDG_Settings::PAGE . '_eid_enabled';
    const SETTING_EID_TEST = R1EIDG_Settings::PAGE . '_eid_test';

    const SETTING_SCHOOL_CLIENT_ID = R1EIDG_Settings::PAGE . '_school_client_id';
    const SETTING_SCHOOL_SECRET = R1EIDG_Settings::PAGE . '_school_secret';
    const SETTING_SCHOOL_MECHANOGRAPHIC_CODE = R1EIDG_Settings::PAGE . '_school_mechanographic_code';

    /**
     * Initializes the actions for the admin page (checks if we are in admin).
     */
    static function init()
    {
        if (!is_admin())
            return;

        add_action('admin_init', [get_class(), 'init_settings']);
        add_action('admin_menu', [get_class(), 'init_options_page']);
        add_filter('plugin_action_links_wp-mim-eidgateway-connect/wp-mim-eidgateway-connect.php', [get_class(), 'init_options_link']);
    }

    /**
     * Callback for admin_menu to add the menu page.
     */
    static function init_options_page()
    {
        add_submenu_page(
            'options-general.php',
            "Impostazioni di eID-Gateway",
            "eID-Gateway",
            'manage_options',
            R1EIDG_Settings::PAGE,
            [get_class(), 'options_page_html']
        );
    }

    /**
     * Callback for plugin_action_links_... to add the settings link to the plugin entry in plugins page.
     */
    static function init_options_link($links)
    {
        $settings_link = '<a href="' . menu_page_url(R1EIDG_Settings::PAGE, false) . '">' . __('Settings') . '</a>';
        array_push($links, $settings_link);
        return $links;
    }

    /**
     * Callback to draw the html page
     */
    static function options_page_html()
    {
        // check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }

        if (!R1EIDG_Settings::is_configuration_complete()) {
            add_settings_error(
                R1EIDG_Settings::PAGE . '_messages',
                R1EIDG_Settings::PAGE . '_incomplete_configuration',
                "La configurazione è incompleta. Client ID e codice meccanografico sono obbligatori."
            );
        } else if (!R1EIDG_Settings::is_setting_enabled(R1EIDG_Settings::SETTING_EID_ENABLED)) {
            add_settings_error(
                R1EIDG_Settings::PAGE . '_messages',
                R1EIDG_Settings::PAGE . '_eid_disabled',
                "L'accesso con eID-Gateway è disabilitato. Puoi attivarlo con le impostazioni qui sotto.",
                'warning'
            );
        }

        // show error/update messages
        settings_errors(R1EIDG_Settings::PAGE . '_messages');
?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                // output security fields for the registered setting
                settings_fields(R1EIDG_Settings::PAGE);
                // output setting sections and their fields
                do_settings_sections(R1EIDG_Settings::PAGE);
                // output save settings button
                submit_button('Salva');
                ?>
            </form>
        </div>
    <?php
    }

    /**
     * Callback for admin_init for initializing settings.
     */
    static function init_settings()
    {
        register_setting(
            R1EIDG_Settings::PAGE,
            R1EIDG_Settings::OPTION_NAME
        );

        $eid_section_id = R1EIDG_Settings::PAGE . '_eid_section';
        $school_section_id = R1EIDG_Settings::PAGE . '_school_section';

        // eID-Gateway settings

        add_settings_section(
            $eid_section_id,
            "Funzionamento di eID-Gateway",
            [get_class(), 'eid_section_callback'],
            R1EIDG_Settings::PAGE
        );

        R1EIDG_Settings::add_field(
            R1EIDG_Settings::SETTING_EID_ENABLED,
            "Abilita login con eID-Gateway",
            'checkbox',
            $eid_section_id,
        );

        R1EIDG_Settings::add_field(
            R1EIDG_Settings::SETTING_EID_TEST,
            "Modalità di test di eID-Gateway",
            'checkbox',
            $eid_section_id,
        );

        // School settings

        add_settings_section(
            $school_section_id,
            "Dati della scuola",
            [get_class(), 'school_section_callback'],
            R1EIDG_Settings::PAGE
        );

        R1EIDG_Settings::add_field(
            R1EIDG_Settings::SETTING_SCHOOL_CLIENT_ID,
            "Client ID fornito dal SIDI",
            'text',
            $school_section_id,
        );

        R1EIDG_Settings::add_field(
            R1EIDG_Settings::SETTING_SCHOOL_SECRET,
            "Secret key fornita dal SIDI",
            'text',
            $school_section_id,
        );

        R1EIDG_Settings::add_field(
            R1EIDG_Settings::SETTING_SCHOOL_MECHANOGRAPHIC_CODE,
            "Codice meccanografico della scuola",
            'text',
            $school_section_id,
        );
    }

    /**
     * Adds a text field
     * @param mixed $field_id The id of the field, should be prefixed to be unique.
     * @param mixed $field_title Field title that will be shown to the user.
     * @param mixed $type Will be used as type attribute in the input element.
     * @param mixed $section_id Id of the section where the field must appear.
     */
    private static function add_field($field_id, $field_title, $type, $section_id)
    {
        add_settings_field(
            $field_id,
            $field_title,
            [get_class(), 'create_field_callback'],
            R1EIDG_Settings::PAGE,
            $section_id,
            [
                'label_for' => $field_id,
                'type' => $type
            ]
        );
    }

    /**
     * Callback for drawing the school settings section header
     */
    static function school_section_callback($args)
    {
    ?>
        <p id="<?= $args['id'] ?>">Dopo aver effettuato l'aggregazione della scuola nel portale SIDI, inserisci qui i dati richiesti.</p>
    <?php
    }

    /**
     * Callback for drawing the school settings section header
     */
    static function eid_section_callback($args)
    {
    ?>
        <p id="<?= $args['id'] ?>">Gestisci le impostazioni generali</p>
    <?php
    }

    /**
     * Callback that draws a field
     * @param array $args array that sould contain a 'label_for' key with the value that corresponds to the setting id, and a 'type' key that will be used in the type attribute of the input element. 
     */
    static function create_field_callback($args)
    {
        $current_value = R1EIDG_Settings::get_setting($args['label_for']) ?? '';

        $type = $args['type'] ?? 'text';

        $attributes = '';

        switch ($type) {
            case 'text':
                $attributes = 'value="' . esc_html($current_value) . '"';
                break;
            case 'checkbox':
                $attributes = 'value="true" ' . checked('true', $current_value, false);
                break;
            default:
                $attributes = 'value="' . esc_html($current_value) . '"';
                break;
        }

    ?>
        <input type="<?= $type ?>" name="<?= R1EIDG_Settings::OPTION_NAME . '[' . $args['label_for'] . ']' ?>" <?= $attributes ?> />
<?php
    }

    /**
     * Checks if the minimum configuration for the plugin to work is present.
     * @return bool Wheter the plugin has the necessary data to work.
     */
    static function is_configuration_complete(): bool
    {
        return R1EIDG_Settings::is_setting_enabled(R1EIDG_Settings::SETTING_SCHOOL_CLIENT_ID)
            && R1EIDG_Settings::is_setting_enabled(R1EIDG_Settings::SETTING_SCHOOL_MECHANOGRAPHIC_CODE);
    }

    /**
     * Checks if a checkbox setting has a value
     * @param string $setting_id Setting id (they are defined as constants and start with R1EIDG_Settings::SETTING_*)
     * @return bool Wheter the checkbox setting has a truthy value
     */
    static function is_setting_enabled($setting_id): bool
    {
        return R1EIDG_Settings::get_setting($setting_id) ?? false;
    }

    /**
     * Gets the value of a setting.
     * @param string $setting_id Setting id (they are defined as constants and start with R1EIDG_Settings::SETTING_*)
     * @return string|null The setting value (null if not set)
     */
    static function get_setting($setting_id): string|null
    {
        $settings = get_option(R1EIDG_Settings::OPTION_NAME);
        return $settings[$setting_id] ?? null;
    }
}
