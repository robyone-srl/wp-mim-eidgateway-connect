<?php

/**
 * Functions and constants that regard the settings.
 */
class R1EIDG_Settings
{
    const PAGE = 'R1EIDG';
    const OPTION_NAME = R1EIDG_Settings::PAGE . '_options';

    const SETTING_EID_ENABLED = R1EIDG_Settings::PAGE . '_eid_enabled';
    const SETTING_SCHOOL_THEME_SHOW_IN_PUBLIC = R1EIDG_Settings::PAGE . '_school_theme_show_in_public';
    const SETTING_SCHOOL_THEME_HIDE_LOGIN_FORM = R1EIDG_Settings::PAGE . '_school_theme_hide_login_form';
    const SETTING_EID_TEST = R1EIDG_Settings::PAGE . '_eid_test';

    const SETTING_SCHOOL_CLIENT_ID = R1EIDG_Settings::PAGE . '_school_client_id';
    const SETTING_SCHOOL_MECHANOGRAPHIC_CODE = R1EIDG_Settings::PAGE . '_school_mechanographic_code';

    /**
     * Initializes the actions for the admin page.
     */
    static function init()
    {
        add_action('admin_init', [get_class(), 'init_settings_callback']);
        add_action('admin_menu', [get_class(), 'init_options_page_callback']);
        add_filter('plugin_action_links_wp-mim-eidgateway-connect/wp-mim-eidgateway-connect.php', [get_class(), 'init_options_link_callback']);
    }

    /**
     * Callback for admin_menu to add the menu page.
     */
    static function init_options_page_callback()
    {

        add_submenu_page(
            'options-general.php',
            esc_html__("eID-Gateway settings", 'wp-mim-eidgateway-connect'),
            esc_html__("eID-Gateway", 'wp-mim-eidgateway-connect') . R1EIDG_Settings::configuration_complete_badge(),
            'manage_options',
            R1EIDG_Settings::PAGE,
            [get_class(), 'options_page_html_callback']
        );
    }

    /**
     * Callback for plugin_action_links_... to add the settings link to the plugin entry in plugins page.
     */
    static function init_options_link_callback($links)
    {
        $settings_link = '<a href="' . menu_page_url(R1EIDG_Settings::PAGE, false) . '">' . __('Settings') . '</a>';
        array_push($links, $settings_link);
        return $links;
    }

    /**
     * Callback to draw the html page
     */
    static function options_page_html_callback()
    {
        // check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }

        if (!R1EIDG_Settings::is_configuration_complete()) {
            add_settings_error(
                R1EIDG_Settings::PAGE . '_messages',
                R1EIDG_Settings::PAGE . '_incomplete_configuration',
                esc_html__("The configuration is incomplete. Client ID and mechanographic code are mandatory.", 'wp-mim-eidgateway-connect')
            );
        } else if (!R1EIDG_Settings::is_setting_enabled(R1EIDG_Settings::SETTING_EID_ENABLED)) {
            add_settings_error(
                R1EIDG_Settings::PAGE . '_messages',
                R1EIDG_Settings::PAGE . '_eid_disabled',
                esc_html__("Login with eID-Gateway is disabled. You can enable it with the settings below.", 'wp-mim-eidgateway-connect'),
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
                submit_button(__("Save"));
                ?>
            </form>
        </div>
    <?php
    }

    /**
     * Callback for admin_init for initializing settings.
     */
    static function init_settings_callback()
    {
        register_setting(
            R1EIDG_Settings::PAGE,
            R1EIDG_Settings::OPTION_NAME
        );

        $eid_section_id = R1EIDG_Settings::PAGE . '_eid_section';
        $school_section_id = R1EIDG_Settings::PAGE . '_school_section';
        $school_theme_section_id = R1EIDG_Settings::PAGE . '_school_theme_section';

        // eID-Gateway settings

        add_settings_section(
            $eid_section_id,
            esc_html__("eID-Gateway operation settings", 'wp-mim-eidgateway-connect'),
            [get_class(), 'eid_section_callback'],
            R1EIDG_Settings::PAGE
        );

        R1EIDG_Settings::add_field(
            $eid_section_id,
            R1EIDG_Settings::SETTING_EID_ENABLED,
            esc_html__("Enable login with eID-Gateway", 'wp-mim-eidgateway-connect'),
            'checkbox',
        );

        R1EIDG_Settings::add_field(
            $eid_section_id,
            R1EIDG_Settings::SETTING_EID_TEST,
            esc_html__("Enable eID-Gateway test mode", 'wp-mim-eidgateway-connect'),
            'checkbox',
        );

        // School settings

        add_settings_section(
            $school_section_id,
            esc_html__("School data", 'wp-mim-eidgateway-connect'),
            [get_class(), 'school_section_callback'],
            R1EIDG_Settings::PAGE
        );

        R1EIDG_Settings::add_field(
            $school_section_id,
            R1EIDG_Settings::SETTING_SCHOOL_CLIENT_ID,
            esc_html__("Client ID provided by SIDI", 'wp-mim-eidgateway-connect'),
            'text',
        );

        R1EIDG_Settings::add_field(
            $school_section_id,
            R1EIDG_Settings::SETTING_SCHOOL_MECHANOGRAPHIC_CODE,
            esc_html__("School mechanographic code", 'wp-mim-eidgateway-connect'),
            'text',
        );

        // School theme settings

        add_settings_section(
            $school_theme_section_id,
            esc_html__("Integration with Design Scuole Italia theme", 'wp-mim-eidgateway-connect'),
            [get_class(), 'school_theme_section_callback'],
            R1EIDG_Settings::PAGE
        );

        R1EIDG_Settings::add_field(
            $school_theme_section_id,
            R1EIDG_Settings::SETTING_SCHOOL_THEME_SHOW_IN_PUBLIC,
            esc_html__("Show login buttons in public part too", 'wp-mim-eidgateway-connect'),
            'checkbox',
            esc_html__("Normally, the SPID and CIE login buttons are displayed only on the WordPress login page. If you use the Design Scuole Italia theme, it is advisable to enable this option, so that the login buttons are also displayed in the public part, in the panel that appears by clicking on \"Login\" in the top bar.", 'wp-mim-eidgateway-connect'),
        );

        R1EIDG_Settings::add_field(
            $school_theme_section_id,
            R1EIDG_Settings::SETTING_SCHOOL_THEME_HIDE_LOGIN_FORM,
            esc_html__("Hide theme login form", 'wp-mim-eidgateway-connect'),
            'checkbox',
            sprintf(
                /* translators: %s: Name of the cited option */
                esc_html__("It only works if the \"%s\" option is enabled. By enabling this option, the Design Scuole Italia theme login form is hidden, allowing access only with SPID or CIE.", 'wp-mim-eidgateway-connect'),
                esc_html__("Show login buttons in public part too", 'wp-mim-eidgateway-connect')
            ),
        );
    }

    /**
     * Adds a text field
     * @param string $section_id Id of the section where the field must appear.
     * @param string $field_id The id of the field, should be prefixed to be unique.
     * @param string $field_title Field title that will be shown to the user.
     * @param string $type Used as type attribute in the input element.
     * @param string|null $description Shown near the field.
     */
    private static function add_field($section_id, $field_id, $field_title, $type, $description = null)
    {
        add_settings_field(
            $field_id,
            $field_title,
            [get_class(), 'create_field_callback'],
            R1EIDG_Settings::PAGE,
            $section_id,
            [
                'label_for' => $field_id,
                'type' => $type ?? 'text',
                'description' => $description
            ]
        );
    }

    /**
     * Callback for drawing the school settings section header
     */
    static function school_section_callback($args)
    {
    ?>
        <p id="<?= $args['id'] ?>">
            <?= esc_html__("After aggregating the school in the SIDI portal, enter the required data here.", 'wp-mim-eidgateway-connect') ?>
        </p>
    <?php
    }

    /**
     * Callback for drawing the school theme settings section header
     */
    static function school_theme_section_callback($args)
    {
    ?>
        <p id="<?= $args['id'] ?>">
            <?= esc_html__("If you use the Design Scuole Italia theme, you can use these settings to better integrate the login with SPID and CIE to the theme. If you do not use this theme, do not activate the following settings because they may cause unwanted effects.", 'wp-mim-eidgateway-connect') ?>
        </p>
    <?php
    }

    /**
     * Callback for drawing the school settings section header
     */
    static function eid_section_callback($args)
    {
    ?>
        <p id="<?= $args['id'] ?>">
            <?= esc_html__("Manage general settings", 'wp-mim-eidgateway-connect') ?>
        </p>
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
                $attributes = 'value="' . esc_attr($current_value) . '"';
                break;
            case 'checkbox':
                $attributes = 'value="true" ' . checked('true', $current_value, false);
                break;
            default:
                $attributes = 'value="' . esc_attr($current_value) . '"';
                break;
        }

    ?>
        <input type="<?= $type ?>" name="<?= R1EIDG_Settings::OPTION_NAME . '[' . $args['label_for'] . ']' ?>" <?= $attributes ?> />
        <?php
        if ($description = $args['description']) {
        ?>
            <p class="description"><?= $description ?></p>
<?php
        }
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
     * Callback for admin_menu to add the menu page.
     */
    static function configuration_complete_badge(): string
    {
        $configuration_complete = R1EIDG_Settings::is_configuration_complete();
        return $configuration_complete ?  '' : ' <span class="awaiting-mod">!</span>';
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
