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
        add_action('login_form', [get_class(), 'draw_login_button_callback']);
        add_shortcode('spid_login_button', [get_class(), 'draw_login_button_from_shortcode_callback']);
        add_filter('wp_login_errors', [get_class(), 'print_login_errors_callback']);

        // TODO: option to show login buttons
        if (!is_admin() && $GLOBALS['pagenow'] !== 'wp-login.php') {
            // A JavaScript script will position the buttons in the rigt place
            echo do_shortcode('[spid_login_button]');
        }
    }

    /**
     * Callback for wp_login_errors, that prints then deletes transient messages that have been set by the plugin.
     */
    static function print_login_errors_callback($errors)
    {
        $login_error = get_transient(R1EIDG_UI::LOGIN_ERROR_TRANSIENT_NAME);
        delete_transient(R1EIDG_UI::LOGIN_ERROR_TRANSIENT_NAME);

        if ($login_error)
            $errors->add('access', $login_error);

        return $errors;
    }

    /**
     * Callback for the "spid_login_button" shortcode.
     * 
     * @param array $atts Array of attributes. Supports a "size" optional attribute, which can be any of the constants that start with "R1EIDG_UI::BUTTON_SIZE_",
     * and a "redirect_to" optional attribute, which specifies where the user will be redirected after the login.
     */
    static function draw_login_button_from_shortcode_callback($atts)
    {
        $atts = shortcode_atts([
            'size' => R1EIDG_UI::BUTTON_SIZE_M,
            'redirect_to' => null,
        ], $atts);

        R1EIDG_UI::draw_login_button_callback($atts['size'], $atts['redirect_to']);
    }

    /**
     * Draws the "Entra con SPID" and "Entra con CIE" buttons. Checks if the option to login with eID-Gateway is enabled.
     * 
     * @param string $size Buttons size. Can be any of R1EIDG_UI::BUTTON_SIZE_*
     * @param string $redirect_to Where to redirect the user after login. If not set, user will be redirected to the admin page.
     */
    static function draw_login_button_callback($size = R1EIDG_UI::BUTTON_SIZE_M, $redirect_to = null)
    {
        $eid_enabled = R1EIDG_Settings::is_setting_enabled(R1EIDG_Settings::SETTING_EID_ENABLED);
        if (!$eid_enabled)
            return;

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

        wp_enqueue_style(
            'R1EIDG_spid_button',
            plugins_url('public/css/spid-sp-access-button.min.css', __FILE__),
            [],
            R1EIDG_VERSION
        );

        wp_enqueue_script(
            'R1EIDG_login_ui',
            plugins_url('public/js/login-ui.js', __FILE__),
            ['jquery'],
            R1EIDG_VERSION
        );

        wp_enqueue_style(
            'R1EIDG_login_ui',
            plugins_url('public/css/login-ui.css', __FILE__),
            [],
            R1EIDG_VERSION
        );
?>
        <div class="R1EIDG-wrapper" style="display: none;">
            <a href="<?= $start_login_url ?>" class="italia-it-button italia-it-button-size-<?= $size ?> button-spid" spid-idp-button="#spid-idp-button-medium-get">
                <span class="italia-it-button-icon"><img alt="" src="<?= plugins_url('public/img/spid-ico-circle-bb.svg', __FILE__) ?>" /></span>
                <span class="italia-it-button-text"><?= esc_html__("Entra con SPID", 'wp-mim-eidgateway-connect') ?></span>
            </a>
            <a href="<?= $start_login_url ?>" class="italia-it-button italia-it-button-size-<?= $size ?> button-spid button-cie" spid-idp-button="#spid-idp-button-medium-get">
                <span class="italia-it-button-icon"><img alt="" src="<?= plugins_url('public/img/Logo_CIE_ID.svg', __FILE__) ?>" /></span>
                <span class="italia-it-button-text"><?= esc_html__("Entra con CIE", 'wp-mim-eidgateway-connect') ?></span>
            </a>
        </div>
<?php
    }
}
