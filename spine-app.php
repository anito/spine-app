<?php
/*
Plugin Name: Spine App
Plugin URI: https://webpremiere.de
Description: Extend Wordpress by SpineJS
Version: 1.2.6
Author: Webpremiere
Author URI: https://webpremiere.de
Text Domain: spine-app
Domain Path: /languages
*/
// Exit if accessed directly
defined('ABSPATH') or die("you do not have access to this page!");

function SPINEJS()
{
    return SpineApp_Public::instance();
}

if ( is_admin() ) {
	// require_once dirname( __FILE__ ) . '/class-spineapp-public.php';
} else {
	//	do nothing
}

require_once dirname( __FILE__ ) . '/class-spineapp-public.php';
add_action( 'plugins_loaded', 'SPINEJS', 8 );