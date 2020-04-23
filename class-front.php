<?php
defined('ABSPATH') or die("you do not have access to this page!");

class Spine_js_front {

    private static $_this;

    function __construct() {

        $this->wpt_active = false;
        $this->woo_active = false;

        if (isset(self::$_this))
            return self::$_this;
            // wp_die(sprintf(__('%s is a singleton class and you cannot create a second instance.', 'spine-js'), get_class($this)));

        $this->hooks();
        self::$_this = $this;

    }

    static function this() {
        return self::$_this;
    }

    public function hooks() {
        add_action( 'init', array( $this, 'add_wpt_support' ));
        add_action( 'init', array( $this, 'add_woo_support' ));
    }
    public function add_wpt_support() {

        // WPTouch Off Canvas Menu (the left one) defaults to pages list
        // We change that here by giving the option for an "Alternate Pages Menu" in Menu Settings
        // To make that work the header-bottom.php template in your mobilestore theme should be changed accordingly for this to take effect
        // Additionally adijust the header-bottom.php template in your mobilestore theme accordingly for this to take effect
        $wpt_opt = get_option('spine_js_settings_wpt');
        if( ! empty( $wpt_opt ) && true == $wpt_opt['active'] ) {
            $this->wpt_active = true;

            require_once dirname( __FILE__ ) . '/classes/class-wptouch-helper.php';
            $spine_wptouch_helper = new Spine_js_wpt();
        }
    }
    public function add_woo_support() {
        // Product Custom Meta Fields 
        $woo_opt = get_option('spine_js_settings_woo');
        if( ! empty( $woo_opt ) && true == $woo_opt['active'] ) {
            $this->woo_active = true;

            require_once dirname( __FILE__ ) . '/classes/class-woo-custom-fields.php';
            $spine_woo_tax = new Spine_js_woo();
        }
    }
}