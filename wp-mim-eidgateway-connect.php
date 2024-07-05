<?php

/**
 * Login con eID-Gateway
 *
 * @author            Robyone S.r.l.
 * @copyright         2024 Robyone S.r.l.
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Login con eID-Gateway
 * Description:       Questo plugin non ufficiale facilita il processo di integrazione del sito web scolastico realizzato con WordPress al componente eID-Gateway messo a disposizione dal MIM.
 * Version:           1.0.0
 * Requires at least: 5.0
 * Requires PHP:      8.0
 * Author:            Robyone S.r.l.
 * Author URI:        https://robyone.net/
 * Text Domain:       wp-mim-eidgateway-connect
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

const R1EIDG_VERSION = "1.0.0"; // for assets cache busting

require_once __DIR__ . '/R1EIDG_UI.php';
require_once __DIR__ . '/R1EIDG_LoginController.php';
require_once __DIR__ . '/R1EIDG_GatewayURLs.php';
require_once __DIR__ . '/admin/R1EIDG_Settings.php';
require_once __DIR__ . '/admin/R1EIDG_Profile.php';

add_action('init', 'R1EIDG_init');
function R1EIDG_init()
{
    // Initialize UI (login buttons), settings and additional profile fields
    R1EIDG_UI::init();

    if (is_admin()) {
        R1EIDG_Settings::init();
        R1EIDG_Profile::init();
    }
}

// Register routes
const R1EIDG_ROUTE_NAMESPACE = 'eid-gateway';

const R1EIDG_ROUTE_LOGIN = 'login';
const R1EIDG_ROUTE_START_LOGIN = 'start-login';

add_action('rest_api_init', 'R1EIDG_register_controllers');

function R1EIDG_register_controllers()
{
    (new R1EIDG_LoginController())->register_routes();
}

// Load translations
function R1EIDG_load_translations()
{
    $plugin_rel_path = basename(dirname(__FILE__)) . '/languages';
    load_plugin_textdomain('wp-mim-eidgateway-connect', false, $plugin_rel_path);
}
add_action('plugins_loaded', 'R1EIDG_load_translations');
