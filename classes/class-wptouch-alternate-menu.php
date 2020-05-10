<?php
defined('ABSPATH') or die("you do not have access to this page!");

if ( ! class_exists( 'Spine_js_wpt_alternate_menu' ) ) {

    class Spine_js_wpt_alternate_menu {

        private static $_this;

        function __construct() {
            if ( isset( self::$_this ) )
                return self::$_this;

            $this->hook();
            self::$_this = $this;

        }

        static function this() {
            return self::$_this;
        }

        protected function hook() {
            add_action( 'webpremiere_register_custom_menu', array( $this, 'mobilestore_register_custom_menu' ) );
        }

        public function mobilestore_register_custom_menu() {

            if( function_exists( 'wptouch_register_theme_menu' ) ) {

                wptouch_register_theme_menu(
                    array(
                        'name'            => 'alternate_pages_menu',
                        'friendly_name'   => __( 'Alternate Pages Menu', 'spine-app' ),
                        'settings_domain' => MOBILESTORE_SETTING_DOMAIN,
                        'description'     => __( 'Choose a menu', 'spine-app' ),
                        'tooltip'         => __( 'Off-Canvas left bottom menu', 'spine-app' ),
                        'can_be_disabled' => false,
                    )
                );

            }
        }
    }//class closure
} //if class exists closure
