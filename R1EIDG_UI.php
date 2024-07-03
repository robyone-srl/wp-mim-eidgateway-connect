<?php

class R1EIDG_UI
{
    const LOGIN_ERROR_TRANSIENT_NAME = 'eid-gateway-login-errors';

    public static function init()
    {
        add_action('login_form', [get_class(), 'draw_login_button']);
        add_action('cmb2_admin_init', [get_class(), 'register_user_fields']);
        add_filter('wp_login_errors', [get_class(), 'print_login_messages']);
    }

    public static function register_user_fields()
    {
        $cmb_user = new_cmb2_box([
            'id'               => 'R1EIDG_title',
            'title'            => "eID-Gateway", // Doesn't output for user boxes
            'object_types'     => array('user'), // Tells CMB2 to use user_meta vs post_meta
            'show_names'       => true,
            'new_user_section' => 'add-new-user', // where form will show on new user page. 'add-existing-user' is only other valid option.
        ]);

        $cmb_user->add_field([
            'name'     => 'Codice Fiscale',
            'desc'     => "Il codice fiscale dell'utente, per permettere l'accesso con SPID, CIE o eIDAS tramite eID-Gateway.",
            'id'       => 'codice_fiscale',
            'type'     => 'text',
        ]);
    }

    public static function print_login_messages($errors)
    {
        $login_error = get_transient(R1EIDG_UI::LOGIN_ERROR_TRANSIENT_NAME);
        delete_transient(R1EIDG_UI::LOGIN_ERROR_TRANSIENT_NAME);

        if ($login_error)
            $errors->add('access', $login_error);

        return $errors;
    }

    public static function draw_login_button()
    {
        $start_login_url = get_site_url() . '/wp-json/' . R1EIDG_ROUTE_NAMESPACE . '/' . R1EIDG_ROUTE_START_LOGIN;

        wp_enqueue_style(
            'R1EIDG_spid_button',
            plugins_url('public/css/spid-sp-access-button.min.css', __FILE__),
            [],
            R1EIDG_VERSION
        );

        wp_enqueue_script(
            'R1EIDG_login_ui',
            plugins_url('public/js/login-ui.js', __FILE__),
            [],
            R1EIDG_VERSION
        );

        wp_enqueue_style(
            'R1EIDG_login_ui',
            plugins_url('public/css/login-ui.css', __FILE__),
            [],
            R1EIDG_VERSION
        );
?>
        <div class="R1EIDG-wrapper">
            <a href="<?= $start_login_url ?>" class="italia-it-button italia-it-button-size-m button-spid" spid-idp-button="#spid-idp-button-medium-get">
                <span class="italia-it-button-icon"><img alt="" src="<?= plugins_url('public/img/spid-ico-circle-bb.svg', __FILE__) ?>" /></span>
                <span class="italia-it-button-text">Entra con SPID</span>
            </a>
            <a href="<?= $start_login_url ?>" class="italia-it-button italia-it-button-size-m button-spid button-cie" spid-idp-button="#spid-idp-button-medium-get">
                <span class="italia-it-button-icon"><img alt="" src="<?= plugins_url('public/img/Logo_CIE_ID.svg', __FILE__) ?>" /></span>
                <span class="italia-it-button-text">Entra con CIE</span>
            </a>
        </div>
<?php
    }
}
