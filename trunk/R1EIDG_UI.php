<?php

/**
 * Contains functions that handle the drawing of UI elements.
 */
class R1EIDG_UI
{
    const LOGIN_ERROR_TRANSIENT_NAME = 'eid-gateway-login-errors';

    const BUTTON_SIZE_S = 's';
    const BUTTON_SIZE_M = 'm';
    const BUTTON_SIZE_L = 'l';
    const BUTTON_SIZE_XL = 'xl';

    /**
     * Configures actions and filters so that the plugin can draw UI elements where needed.
     */
    static function init()
    {
        add_shortcode('eid_gateway_buttons', [get_class(), 'draw_login_button_from_shortcode_callback']);

        $eid_enabled = R1EIDG_Settings::is_setting_enabled(R1EIDG_Settings::SETTING_EID_ENABLED);
        if (!$eid_enabled)
            return;

        if(is_user_logged_in())
            return;

        add_action('login_form', [get_class(), 'draw_login_button_from_login_form_callback']);
        add_filter('wp_login_errors', [get_class(), 'print_login_errors_callback']);

        if (R1EIDG_Settings::is_setting_enabled(R1EIDG_Settings::SETTING_SCHOOL_THEME_SHOW_IN_PUBLIC)) {
            add_action('wp_footer', [get_class(), 'wp_footer_callback']);
        }

        wp_enqueue_script(
            'R1EIDG_login_ui',
            plugins_url('public/js/login-ui.js', __FILE__),
            ['jquery'],
            R1EIDG_VERSION
        );
    }

    /**
     * Callback for wp_footer, to draw the buttons in the page body.
     */
    static function wp_footer_callback(){
        if (!is_admin() && $GLOBALS['pagenow'] !== 'wp-login.php') {
            // A JavaScript script will position the buttons in the right place
            R1EIDG_UI::draw_login_button(originates_from_plugin: true);
        }
    }

    /**
     * Callback for wp_login_errors, that adds to the login errors those errors that have been set by the plugin.
     */
    static function print_login_errors_callback($errors)
    {
        $login_error = $_COOKIE[R1EIDG_UI::LOGIN_ERROR_TRANSIENT_NAME] ?? false; // read the error message from the cookie

        if ($login_error) {
            setcookie(R1EIDG_UI::LOGIN_ERROR_TRANSIENT_NAME, ''); // delete the cookie
            $errors->add('access', $login_error);
        }

        return $errors;
    }

    /**
     * Callback for the "eid_gateway_buttons" shortcode.
     * 
     * @param array $atts Array of attributes. Supports a "size" optional attribute, which can be any of the constants that start with "R1EIDG_UI::BUTTON_SIZE_",
     * and a "redirect_to" optional attribute, which specifies where the user will be redirected after the login.
     */
    static function draw_login_button_from_shortcode_callback($atts)
    {
        $eid_enabled = R1EIDG_Settings::is_setting_enabled(R1EIDG_Settings::SETTING_EID_ENABLED);
        if (!$eid_enabled)
            return;

        if(is_user_logged_in())
            return;

        $atts = shortcode_atts([
            'size' => R1EIDG_UI::BUTTON_SIZE_M,
            'redirect_to' => null,
        ], $atts);

        R1EIDG_UI::draw_login_button($atts['size'], $atts['redirect_to']);
    }

    /**
     * Callback for the "login_form" action.
     */
    static function draw_login_button_from_login_form_callback()
    {
        R1EIDG_UI::draw_login_button(originates_from_plugin: true);
    }

    /**
     * Draws the "Login with SPID" and "Login with CIE" buttons. Checks if the option to login with eID-Gateway is enabled and if the user is not already logged.
     * 
     * @param string $size Buttons size. Can be any of R1EIDG_UI::BUTTON_SIZE_*
     * @param string $redirect_to Where to redirect the user after login. If not set, user will be redirected to the admin page.
     * @param bool $originates_from_plugin Indicates if the buttons were drawn by the plugin or from a shortcode.
     */
    static function draw_login_button($size = R1EIDG_UI::BUTTON_SIZE_M, $redirect_to = null, $originates_from_plugin = false)
    {
        wp_enqueue_style(
            'R1EIDG_spid_button',
            plugins_url('public/css/spid-sp-access-button.min.css', __FILE__),
            [],
            R1EIDG_VERSION
        );

        wp_enqueue_style(
            'R1EIDG_login_ui',
            plugins_url('public/css/login-ui.css', __FILE__),
            [],
            R1EIDG_VERSION
        );

        $size = $size ?: R1EIDG_UI::BUTTON_SIZE_M;
        $size = trim(strtolower($size));
        $sizes = [
            R1EIDG_UI::BUTTON_SIZE_S,
            R1EIDG_UI::BUTTON_SIZE_M,
            R1EIDG_UI::BUTTON_SIZE_L,
            R1EIDG_UI::BUTTON_SIZE_XL,
        ];
        if (!in_array($size, $sizes, true))
            $size = R1EIDG_UI::BUTTON_SIZE_M;

        $start_login_url = get_rest_url(path: R1EIDG_ROUTE_NAMESPACE . '/' . R1EIDG_ROUTE_START_LOGIN);

        if ($redirect_to_after_login = $redirect_to ?? $_GET['redirect_to'] ?? false) {
            $query = http_build_query([
                'redirect_to' => $redirect_to_after_login
            ]);
            $start_login_url .= '?' . $query;
        }
?>
        <div class="R1EIDG-wrapper<?= $originates_from_plugin ? ' from-R1EIDG' : '' ?>" style="<?= $originates_from_plugin ? 'display: none;' : '' ?>" data-hide-login="<?= (bool)R1EIDG_Settings::is_setting_enabled(R1EIDG_Settings::SETTING_SCHOOL_THEME_HIDE_LOGIN_FORM) ?>">

            <a href="<?= $start_login_url ?>" class="italia-it-button italia-it-button-size-<?= $size ?> button-spid" spid-idp-button="#spid-idp-button-medium-get">
                <span class="italia-it-button-icon"><img alt="" src="<?= plugins_url('public/img/spid-ico-circle-bb.svg', __FILE__) ?>" /></span>
                <span class="italia-it-button-text"><?= esc_html__("Login with SPID", 'wp-mim-eidgateway-connect') ?></span>
            </a>
            <a href="<?= $start_login_url ?>" class="italia-it-button italia-it-button-size-<?= $size ?> button-spid button-cie" spid-idp-button="#spid-idp-button-medium-get">
                <span class="italia-it-button-icon"><img alt="" src="<?= plugins_url('public/img/Logo_CIE_ID.svg', __FILE__) ?>" /></span>
                <span class="italia-it-button-text"><?= esc_html__("Login with CIE", 'wp-mim-eidgateway-connect') ?></span>
            </a>
        </div>
<?php
    }
}
