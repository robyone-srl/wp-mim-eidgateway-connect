<?php

/**
 * Functions to generate the URLs that point to eID-Gateway.
 */
class R1EIDG_GatewayURLs
{
    /**
     * @return string The base URL of the eID-Gateway endpoints.
     */
    static function base_url()
    {
        $eid_test = R1EIDG_Settings::is_setting_enabled(R1EIDG_Settings::SETTING_EID_TEST);
        
        return "http://robylogin.localhost"; //temporary

        if ($eid_test)
            return "https://eid-test.istruzione.it/eid-gateway";

        return "https://eid.istruzione.it/eid-gateway";
    }

    /**
     * Returns the URL to eID-Gateway, where the user can log in.
     * @param string $redirect_to_after_login where the user will be redirected to after logging in. If not set, the user is redirected to the admin page.
     * @return string
     */
    static function authenticate_url($redirect_to_after_login = null)
    {
        if(!R1EIDG_Settings::is_configuration_complete())
            throw new Exception("Configurazione accesso eID-Gateway incompleta");

        $client_id = R1EIDG_Settings::get_setting(R1EIDG_Settings::SETTING_SCHOOL_CLIENT_ID);
        $mechanographic_code = R1EIDG_Settings::get_setting(R1EIDG_Settings::SETTING_SCHOOL_MECHANOGRAPHIC_CODE);

        $redirect_uri = get_rest_url(path: R1EIDG_ROUTE_NAMESPACE . '/' . R1EIDG_ROUTE_LOGIN);

        if ($redirect_to_after_login ?? false) {
            $redirect_uri .= '?' . http_build_query([
                'redirect_to' => $redirect_to_after_login
            ]);
        }

        $query = http_build_query([
            'client_id' => $client_id,
            'redirect_uri' => $redirect_uri,
            'aggregate_ref_type' => 'MECHANOGRAPHIC_CODE',
            'aggregate_ref_value' => $mechanographic_code
        ]);

        return R1EIDG_GatewayURLs::base_url() . "/sp/authenticate.php?$query";
    }

    /**
     * Gets the URL where the token can be verified.
     * @param string $token The token to verify.
     */
    static function verify_url($token)
    {
        return R1EIDG_GatewayURLs::base_url() . "/sp/token/verify.php?token=$token";
    }
    
    /**
     * Gets the URL to download the JWK Set to verify the JWT signature.
     */
    static function certificate_url()
    {
        return R1EIDG_GatewayURLs::base_url() . "/oauth2/certs";
    }
}
