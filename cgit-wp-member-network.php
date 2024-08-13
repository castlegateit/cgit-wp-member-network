<?php

/*

Plugin Name: Castlegate IT WP Member Network
Plugin URI: https://github.com/castlegateit/cgit-wp-member-network
Description: Basic member network plugin.
Version: 1.0.4
Author: Castlegate IT
Author URI: https://www.castlegateit.co.uk/
Network: true

Copyright (c) 2019 Castlegate IT. All rights reserved.

*/

if (!defined('ABSPATH')) {
    wp_die('Access denied');
}

require_once __DIR__ . '/lib/recaptcha/src/autoload.php';
require_once __DIR__ . '/classes/autoload.php';

$plugin = new \Cgit\MemberNetwork\Plugin(__FILE__);

do_action('cgit_member_network_plugin', $plugin);
do_action('cgit_member_network_loaded');
