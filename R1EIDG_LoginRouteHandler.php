<?php

class R1EIDG_LoginRouteHandler
{
    static function start_login($request){
        wp_redirect(R1EIDG_GatewayURLs::authenticate_url());
        exit();
    }

    static function login($request)
    {
        $token = $request['token'];

        // Verify the token
        $token_verification_response = wp_remote_get(R1EIDG_GatewayURLs::verify_url($token));

        if(is_wp_error($token_verification_response))
            R1EIDG_LoginRouteHandler::set_login_error_and_die("Errore nella verifica del token");

        if ($token_verification_response['response']['code'] != 204)
            R1EIDG_LoginRouteHandler::set_login_error_and_die("Token non valido");

        // Extract the fiscal number from the token
        $payload = R1EIDG_LoginRouteHandler::decode_jwt($token);
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
            R1EIDG_LoginRouteHandler::set_login_error_and_die("L'utente non è registrato.");

        // Log in the found user
        nocache_headers();
        wp_clear_auth_cookie();
        wp_set_auth_cookie($users[0]->ID);

        wp_redirect('/wp-admin');
        exit();
    }

    static function set_login_error_and_die($message)
    {
        set_transient(R1EIDG_UI::LOGIN_ERROR_TRANSIENT_NAME, $message);
        wp_redirect('/wp-login.php');
        exit();
    }

    static function decode_jwt($token)
    {
        list(, $base64UrlPayload,) = explode('.', $token);
        $payload = base64_decode($base64UrlPayload);
        return json_decode($payload, true);
    }
}
