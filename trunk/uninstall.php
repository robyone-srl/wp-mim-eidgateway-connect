<?php

// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

require_once __DIR__ . '/admin/R1EIDG_Settings.php';

delete_option(R1EIDG_Settings::OPTION_NAME);
