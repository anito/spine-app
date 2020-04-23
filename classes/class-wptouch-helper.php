<?php
defined('ABSPATH') or die("you do not have access to this page!");

if ( ! class_exists( 'spine_js_wpt' ) ) {

    class Spine_js_wpt {
        
        private static $_this;

        function __construct() {
            if ( isset( self::$_this ) )
                return self::$_this;
                // wp_die( sprintf( __( '%s is a singleton class and you cannot create a second instance.','spine-app' ), get_class( $this ) ) );

            $this->hook();
            self::$_this = $this;

        }

        static function this() {
            return self::$_this;
        }

        protected function hook() {
            add_action('foundation_init', array($this, 'mobilestore_register_custom_menu'), 0);
        }

        public function mobilestore_register_custom_menu() {

            if(function_exists('wptouch_register_theme_menu')) {

                wptouch_register_theme_menu(
                    array(
                        'name'            => 'alternate_pages_menu',
                        'friendly_name'   => __( 'Alternate Pages Menu', 'wptouch-pro' ),
                        'settings_domain' => MOBILESTORE_SETTING_DOMAIN,
                        'description'     => __( 'Choose a menu', 'wptouch-pro' ),
                        'tooltip'         => __( 'Off-Canvas left bottom menu', 'wptouch-pro' ),
                        'can_be_disabled' => false,
                    )
                );

            }
		
        }
      
    }//class closure
} //if class exists closure
