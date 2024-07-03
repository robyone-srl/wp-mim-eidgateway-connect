<?php

/**
 * Integrazione del login di WordPress con eID-Gateway
 *
 * @package           MIMEIdGatewayUnofficial
 * @author            Robyone S.r.l.
 * @copyright         2024 Robyone S.r.l.
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Integrazione del login di WordPress con eID-Gateway
 * Description:       Questo plugin non ufficiale facilita il processo di integrazione del sito web scolastico realizzato con WordPress al componente eID-Gateway messo a disposizione dal MIM.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      8.0
 * Author:            Robyone S.r.l.
 * Author URI:        https://robyone.net/
 * Text Domain:       mim-eid-gateway-unofficial
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

const R1EIDG_VERSION = "1.0.0"; // for assets cache busting

require_once __DIR__ . '/R1EIDG_UI.php';
require_once __DIR__ . '/R1EIDG_LoginRouteHandler.php';
require_once __DIR__ . '/R1EIDG_GatewayURLs.php';
require_once __DIR__ . '/admin/R1EIDG_Settings.php';

R1EIDG_UI::init();
R1EIDG_Settings::init();

add_action('rest_api_init', 'R1EIDG_register_routes');

const R1EIDG_ROUTE_NAMESPACE = 'eid-gateway';

const R1EIDG_ROUTE_LOGIN = 'login';
const R1EIDG_ROUTE_START_LOGIN = 'start-login';

function R1EIDG_register_routes()
{
    register_rest_route(R1EIDG_ROUTE_NAMESPACE, '/' . R1EIDG_ROUTE_LOGIN . '(?P<token>)', [
        'methods'  => WP_REST_Server::READABLE,
        'callback' => "R1EIDG_LoginRouteHandler::login",
    ]);

    register_rest_route(R1EIDG_ROUTE_NAMESPACE, '/' . R1EIDG_ROUTE_START_LOGIN, [
        'methods'  => WP_REST_Server::READABLE,
        'callback' => "R1EIDG_LoginRouteHandler::start_login",
    ]);
}
