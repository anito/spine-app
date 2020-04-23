<?php
defined('ABSPATH') or die("you do not have access to this page!");

if ( ! class_exists( 'wpt_spine_js' ) ) {

    class Wpt_spine_js extends Spine_js_admin {
        
        private static $_this;
        
        public $title = 'Test';
        public $active = true;
        public $option_group = 'wpt_custom_menu';

        function __construct() {
            if ( isset( self::$_this ) )
                return self::$_this;
                // wp_die( sprintf( __( '%s is a singleton class and you cannot create a second instance.','spine-app' ), get_class( $this ) ) );

            $this->get_options();
            $this->get_admin_options();

            self::$_this = $this;

        }

        static function this() {
            return self::$_this;
        }

        public function get_admin_options() {

            $options = get_option('spine_js_settings_wpt');
            
            if (isset($options)) {
                $this->title = isset($options['title']) ? $options['title'] : $this->title;
                $this->active = isset($options['active']) ? $options['active'] : $this->active;
            }
            
        }

        /**
         * Create the settings page form for WP Touch
         *
         * @since  2.0
         *
         * @access public
         *
         */
        public function create_form() {
            
            add_settings_section('spine_js_settings_sections_wpt', __("Overwrite Pages Menü", "spine-app"), array($this, 'section_text'), $this->plugin_slug);
            
            add_settings_field('active', __("Active", "spine-app"), array($this, 'get_wpt_active'), $this->plugin_slug, 'spine_js_settings_sections_wpt');
            add_settings_field('title', __("Menu Title", "spine-app"), array($this, 'get_wpt_title'), $this->plugin_slug, 'spine_js_settings_sections_wpt');
            add_settings_field('id_option_group', "", array($this, 'get_hidden_input'), $this->plugin_slug, 'spine_js_settings_sections_wpt');

            // register_setting('spine_js_settings_wpt', 'spine_js_settings_wpt', array($this, 'options_validate'));
        }
        
        /**
         * Register settings
         */
        public function register_setting() {
            // params: option_group, option_name
            register_setting('spine_js_settings_wpt', 'spine_js_settings_wpt', array($this, 'options_validate'));
        }

        /**
         * Check the posted values in the settings page for validity
         *
         * @since  2.0
         *
         * @access public
         *
         */
        public function options_validate( $new_settings ) {
            //fill array with current values, so we don't lose any
            write_log('Validating Options...');
            write_log($new_settings);

            $settings = array(
                'active' => $new_settings['active'],
                'title' => $new_settings['title']
            );

            if (!empty($new_settings['active']) && $new_settings['active'] == '1') {
                $settings['active'] = TRUE;
            } else {
                $settings['active'] = FALSE;
            }

            return $settings;
        }

        public function get_wpt_active() {

            ?>
            <label class="spine-js-switch">
                <input type="checkbox" id="spine_js_settings_wpt_active" name="spine_js_settings_wpt[active]" size="40" value="1"
                        type="checkbox" <?php checked(1, $this->active, true) ?> />
                <span class="spine-js-slider spine-js-round"></span>
            </label>
            <?php
            SPINEJS()->spine_js_help->get_help_tip(__("Activate the alternate pages menu", "spine-app"));
        }

        public function get_wpt_title() {

            ?>
            <label class="spine-js-">
                <input id="spine_js_settings_wpt_title" name="spine_js_settings_wpt[title]" size="40" value="<?= $this->title ?>" placeholder="<?= __('WP Touch Menu Tile', "spine-app") ?>"
                        type="text"  />
            </label>
            <?php
            SPINEJS()->spine_js_help->get_help_tip(__("Title of alternate pages menu", "spine-app"));
        }

        public function get_hidden_input()
        {

            ?>
            <input type="hidden" name="tab" value="<?php echo $this->option_group ?>" />
            <?php
        }

    }//class closure
} //if class exists closure

