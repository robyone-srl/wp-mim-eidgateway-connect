<?php

class R1EIDG_Settings
{
    const PAGE = 'R1EIDG';
    const OPTIONS = R1EIDG_Settings::PAGE . '_options';

    const OPTION_CLIENT_ID = R1EIDG_Settings::PAGE . '_school_client_id';
    const OPTION_SECRET = R1EIDG_Settings::PAGE . '_school_secret';
    const OPTION_MECHANOGRAPHIC_CODE = R1EIDG_Settings::PAGE . '_school_mechanographic_code';

    static function init()
    {
        if (is_admin()) {
            add_action('admin_init', [get_class(), 'init_settings']);
            add_action('admin_menu', [get_class(), 'init_options_page']);
        }
    }

    static function init_options_page()
    {
        add_menu_page(
            "Impostazioni di eID-Gateway",
            "eID-Gateway",
            'manage_options',
            R1EIDG_Settings::PAGE,
            [get_class(), 'options_page_html']
        );
    }

    static function options_page_html()
    {
        // check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }

        // add error/update messages

        // check if the user have submitted the settings
        // WordPress will add the "settings-updated" $_GET parameter to the url
        if (isset($_GET['settings-updated'])) {
            // add settings saved message with the class of "updated"
            add_settings_error(
                R1EIDG_Settings::PAGE . '_messages',
                R1EIDG_Settings::PAGE . '_message',
                "Impostazioni salvate",
                'updated'
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

    static function init_settings()
    {
        register_setting(R1EIDG_Settings::PAGE, R1EIDG_Settings::OPTIONS);

        $school_section_id = R1EIDG_Settings::PAGE . '_school_section';

        add_settings_section(
            $school_section_id,
            "Dati della scuola",
            [get_class(), 'school_section_callback'],
            R1EIDG_Settings::PAGE
        );

        R1EIDG_Settings::add_text_field(
            R1EIDG_Settings::OPTION_CLIENT_ID,
            "Client ID fornito dal SIDI",
            $school_section_id,
        );

        R1EIDG_Settings::add_text_field(
            R1EIDG_Settings::OPTION_SECRET,
            "Secret key fornita dal SIDI",
            $school_section_id,
        );

        R1EIDG_Settings::add_text_field(
            R1EIDG_Settings::OPTION_MECHANOGRAPHIC_CODE,
            "Codice meccanografico della scuola",
            $school_section_id,
        );
    }

    private static function add_text_field($field_id, $field_title, $section_id)
    {
        add_settings_field(
            $field_id,
            $field_title,
            [get_class(), 'create_text_field_callback'],
            R1EIDG_Settings::PAGE,
            $section_id,
            array(
                'label_for' => $field_id,
            )
        );
    }

    static function school_section_callback($args)
    {
    ?>
        <p id="<?= $args['id'] ?>">Dopo aver effettuato l'aggregazione della scuola nel portale SIDI, inserisci qui i dati richiesti.</p>
    <?php
    }

    static function create_text_field_callback($args)
    {
        $options = get_option(R1EIDG_Settings::OPTIONS);
        $current_value = $options[$args['label_for']] ?? '';

    ?>
        <input type="text" name="<?= R1EIDG_Settings::OPTIONS . '[' . $args['label_for'] . ']' ?>" value="<?= esc_html($current_value) ?>" />
<?php
    }
}
