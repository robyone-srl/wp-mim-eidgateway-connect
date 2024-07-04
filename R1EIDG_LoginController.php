<?php

/**
 * Controller for login routes. Instantiate and call register_routes() to register the routes
 * * [rest-url]/eid-gateway/start_login: redirects to eID-Gateway;
 * * [rest-url]/eid-gateway/login: eID-Gateway redirects to this route.
 */
class R1EIDG_LoginController
{
    /**
     * Registers routes.
     */
    function register_routes()
    {
        register_rest_route(R1EIDG_ROUTE_NAMESPACE, '/' . R1EIDG_ROUTE_START_LOGIN, [
            'methods'  => WP_REST_Server::READABLE,
            'callback' => [$this, "handle_start_login"],
        ]);

        register_rest_route(R1EIDG_ROUTE_NAMESPACE, '/' . R1EIDG_ROUTE_LOGIN . '(?P<token>)', [
            'methods'  => WP_REST_Server::READABLE,
            'callback' => [$this, "handle_login"],
        ]);
    }

    /**
     * Handles the request to start the login, by redirecting to eID-Gateway. The request can have a
     * redirect_to parameter, that specifies where the user will be redirected after the login.
     */
    function handle_start_login($request)
    {
        $redirect_to_after_login = $request['redirect_to'] ?? null;
        wp_redirect(R1EIDG_GatewayURLs::authenticate_url($redirect_to_after_login));
        exit();
    }

    /**
     * Handles the login request, after being redirected from eID-Gateway, logging in the user.
     */
    function handle_login($request)
    {
        $token = $request['token'];

        // Verify the token
        $token_verification_response = wp_remote_get(R1EIDG_GatewayURLs::verify_url($token));

        if (is_wp_error($token_verification_response))
            R1EIDG_LoginController::set_login_error_and_die(esc_html__("Errore nella verifica del token", R1EIDG_TEXT_DOMAIN));

        if ($token_verification_response['response']['code'] != 204)
            R1EIDG_LoginController::set_login_error_and_die(esc_html__("Token non valido", R1EIDG_TEXT_DOMAIN));

        // Extract the fiscal number from the token
        $payload = R1EIDG_LoginController::decode_jwt($token);
        $fiscal_number = $payload['fiscal_number'];

        // Search for users that have the received fiscal number
        $users = get_users(
            array(
                'meta_key' => 'codice_fiscale',
                'meta_value' => $fiscal_number,
                'number' => 1,
                'count_total' => false,
            )
        );

        if (empty($users))
            R1EIDG_LoginController::set_login_error_and_die(esc_html__("L'utente non è registrato.", R1EIDG_TEXT_DOMAIN));

        // Log in the found user
        nocache_headers();
        wp_clear_auth_cookie();
        wp_set_auth_cookie($users[0]->ID);

        wp_redirect($request['redirect_to'] ?? '/wp-admin');
        exit();
    }

    /**
     * Sets a transient message and redirects to the login screen, where the transient message will be printed.
     */
    static function set_login_error_and_die($message)
    {
        set_transient(R1EIDG_UI::LOGIN_ERROR_TRANSIENT_NAME, $message);
        wp_redirect('/wp-login.php');
        exit();
    }

    /**
     * Decodes the JWT token.
     * @return array Deserialized JWT payload
     */
    static function decode_jwt($token)
    {
        list(, $base64UrlPayload,) = explode('.', $token);
        $payload = base64_decode($base64UrlPayload);
        return json_decode($payload, true);
    }
}
