<?php

class R1EIDG_UI
{
    const LOGIN_ERROR_TRANSIENT_NAME = 'eid-gateway-login-errors';

    const BUTTON_SIZE_S = 's';
    const BUTTON_SIZE_M = 'm';
    const BUTTON_SIZE_L = 'l';
    const BUTTON_SIZE_XL = 'xl';

    static function init()
    {
        add_action('login_form', [get_class(), 'draw_login_button']);
        add_shortcode('spid_login_button', [get_class(), 'draw_login_button_from_shortcode']);
        add_action('cmb2_admin_init', [get_class(), 'register_user_fields']);
        add_filter('wp_login_errors', [get_class(), 'print_login_messages']);
    }

    static function register_user_fields()
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

    static function print_login_messages($errors)
    {
        $login_error = get_transient(R1EIDG_UI::LOGIN_ERROR_TRANSIENT_NAME);
        delete_transient(R1EIDG_UI::LOGIN_ERROR_TRANSIENT_NAME);

        if ($login_error)
            $errors->add('access', $login_error);

        return $errors;
    }

    static function draw_login_button_from_shortcode($atts)
    {
        $atts = shortcode_atts([
            'size' => R1EIDG_UI::BUTTON_SIZE_M,
        ], $atts);

        R1EIDG_UI::draw_login_button($atts['size']);
    }
    
    static function draw_login_button($size = R1EIDG_UI::BUTTON_SIZE_M)
    {
        $size = $size ?: R1EIDG_UI::BUTTON_SIZE_M;
        $size = trim(strtolower($size));
        $sizes = [
            R1EIDG_UI::BUTTON_SIZE_S, 
            R1EIDG_UI::BUTTON_SIZE_M,
            R1EIDG_UI::BUTTON_SIZE_L,
            R1EIDG_UI::BUTTON_SIZE_XL,
        ];
        if(!in_array($size, $sizes, true))
            $size = R1EIDG_UI::BUTTON_SIZE_M;

        $start_login_url = get_site_url(path: '/wp-json/' . R1EIDG_ROUTE_NAMESPACE . '/' . R1EIDG_ROUTE_START_LOGIN);

        if($redirect_to_after_login = $_GET['redirect_to'] ?? false)
        {
            $query = http_build_query([
                'redirect_to' => $redirect_to_after_login
            ]);
            $start_login_url .= '?'.$query;
        }

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
            <a href="<?= $start_login_url ?>" class="italia-it-button italia-it-button-size-<?= $size ?> button-spid" spid-idp-button="#spid-idp-button-medium-get">
                <span class="italia-it-button-icon"><img alt="" src="<?= plugins_url('public/img/spid-ico-circle-bb.svg', __FILE__) ?>" /></span>
                <span class="italia-it-button-text">Entra con SPID</span>
            </a>
            <a href="<?= $start_login_url ?>" class="italia-it-button italia-it-button-size-<?= $size ?> button-spid button-cie" spid-idp-button="#spid-idp-button-medium-get">
                <span class="italia-it-button-icon"><img alt="" src="<?= plugins_url('public/img/Logo_CIE_ID.svg', __FILE__) ?>" /></span>
                <span class="italia-it-button-text">Entra con CIE</span>
            </a>
        </div>
<?php
    }
}
