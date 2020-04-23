<?php
/*
 * Add some JS to the footer
 * @since 2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') or die("you do not have access to this page!");

class SpineApp_Public {

	private static $instance;

	public $plugin_filename = "spine-app.php";
	public $spine_js_help;

	private function __construct() {
		//
	}

	public static function instance() {

		if(!isset(self::$instance) && !(self::$instance instanceof SpineApp_Public)) {
			self::$instance = new SpineApp_Public;
			self::$instance->setup_constants();
			self::$instance->includes();

			if ( is_admin() ) {
				self::$instance->spine_js_admin = new Spine_js_admin();
				self::$instance->spine_js_help = new Spine_js_help();

				$spine_js_help = self::$instance->spine_js_help;
			}
		}
		self::$instance->spine_js_front = new Spine_js_front();
		self::$instance->hooks();

		return self::$instance;

	}
	/*
	* Initialize the class and start calling our hooks and filters
	* @since 2.0.0
	*/
	private function hooks() {
		if (is_admin()) {
			add_action('plugins_loaded', array(self::$instance->spine_js_admin, 'init'), 10);
			add_action('plugins_loaded', array(self::$instance->spine_js_admin, 'edit'), 11);
			add_action('plugins_loaded', array(self::$instance->spine_js_admin, 'hooks'), 10);

		}
	}

	/**
	 * Constants
	 **/
	private function setup_constants() {
		define( 'SPINEAPP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		define( 'SPINEAPP_PLUGIN_DIR', trailingslashit(plugin_dir_path(__FILE__) ) );
		if ( ! defined( 'IS_PRODUCTION' ) ) {
			define( 'IS_PRODUCTION', true );
		}

		require_once(ABSPATH . 'wp-admin/includes/plugin.php');
		$plugin_data = get_plugin_data(dirname(__FILE__) . "/" . $this->plugin_filename);
		define('SPINEAPP_VERSION', $plugin_data['Version']);
	}

	private function includes() {
		require_once SPINEAPP_PLUGIN_DIR . 'class-front.php';
		require_once SPINEAPP_PLUGIN_DIR . 'class-admin.php';
		require_once SPINEAPP_PLUGIN_DIR . 'class-help.php';

		// subclasses
		require_once dirname( __FILE__ ) . '/classes/class-db.php';
		require_once dirname( __FILE__ ) . '/classes/class-wpt.php';
		require_once dirname( __FILE__ ) . '/classes/class-woo.php';
		require_once dirname( __FILE__ ) . '/classes/class-woo-settings-field.php';
	}

}