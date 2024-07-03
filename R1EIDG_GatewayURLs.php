<?php
class R1EIDG_GatewayURLs
{
    static function base_url()
    {
        return "http://robylogin.localhost";
    }

    static function authenticate_url($redirect_to_after_login = null)
    {
        $options = get_option(R1EIDG_Settings::OPTIONS);

        $client_id = $options[R1EIDG_Settings::OPTION_CLIENT_ID] ?? false;
        $mechanographic_code = $options[R1EIDG_Settings::OPTION_MECHANOGRAPHIC_CODE] ?? false;

        if (!($client_id && $mechanographic_code))
            return '';

        $redirect_uri = get_site_url(path: '/wp-json/' . R1EIDG_ROUTE_NAMESPACE . '/' . R1EIDG_ROUTE_LOGIN);

        if($redirect_to_after_login ?? false)
        {
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

        return R1EIDG_GatewayURLs::base_url() . "/authenticate.php?$query";
    }

    static function verify_url($token)
    {
        return R1EIDG_GatewayURLs::base_url() . "/token/verify.php?token=$token";
    }
}
