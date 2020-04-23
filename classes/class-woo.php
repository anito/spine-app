<?php
defined('ABSPATH') or die("you do not have access to this page!");

if ( ! class_exists( 'woo_spine_js' ) ) {

    class Woo_spine_js extends Spine_js_admin {
        
        private static $_this;
        
        public $active = true;
        public $option_group = 'woo_action_taxonomy';
        public $custom_meta_fields = array();
        public $admin_options = array();
        public $args = array();

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

            $this->admin_options = get_option('spine_js_settings_woo');
            
            if (isset($this->admin_options)) {
                $this->active = isset($this->admin_options['active']) ? $this->admin_options['active'] : $this->active;
                // RESET ALL FIELDS
                // $this->custom_meta_fields = array();
                $this->custom_meta_fields = isset($this->admin_options['custom_meta_fields']) ? $this->admin_options['custom_meta_fields'] : $this->custom_meta_fields;
            }
        }

        /**
         * Create the settings page form for WP Touch
         *
         * @since  2.0
         * @access public
         *
         */
        public function create_form() {
            add_settings_section('spine_js_settings_sections_woo', __('"Action Produkt" & "Produkt Angebot der Woche" aktivieren', "spine-app"), array($this, 'section_text'), $this->plugin_slug);
            
            add_settings_field('active', __("Active", "spine-app"), array($this, 'get_woo_active'), $this->plugin_slug, 'spine_js_settings_sections_woo');
            $this->add_settings_fields();
            add_settings_field('id_option_group', "", array($this, 'get_hidden_input'), $this->plugin_slug, 'spine_js_settings_sections_woo');

        }
        
        /**
         * Register settings
         */
        public function register_setting() {
            // params: option_group, option_name
            register_setting('spine_js_settings_woo', 'spine_js_settings_woo', array($this, 'options_validate'));
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
            if(array_key_exists('name', $new_settings['add_new']['new']) && ! empty ($new_settings['add_new']['new']['name'])) {
                $new_settings['custom_meta_fields'][] = $new_settings['add_new']['new'];
            }
            
            if( ! empty ($new_settings['custom_meta_fields'] ) ) {

                $settings = array(
                    'custom_meta_fields' => $new_settings['custom_meta_fields']
                );
            }
            $settings['custom_meta_fields'] = array_map( function( $item ) {
                if( ! empty ( $item['name'] ))
                    return array( 'name' => $item['name'], 'slug' => sanitize_title($item['name']) );
            }, $settings['custom_meta_fields']);

            if (!empty($new_settings['active']) && $new_settings['active'] == '1') {
                $settings['active'] = TRUE;
            } else {
                $settings['active'] = FALSE;
            }

            write_log('Validating Options::afterValidating');
            write_log($settings);
            return $settings;
        }

        public function get_woo_active() {

            ?>
            <label class="spine-js-switch">
                <input type="checkbox" id="spine_js_settings_woo_active" name="spine_js_settings_woo[active]" size="40" value="1"
                    <?php checked(1, $this->active, true) ?> />
                <span class="spine-js-slider spine-js-round"></span>
            </label>
            <?php
            SPINEJS()->spine_js_help->get_help_tip(__("Activate the alternate pages menu", "spine-app"));
        }

        public function add_settings_fields() {

            foreach($this->custom_meta_fields as $key => $field) {

                $settings_field = new Settings_field($key);
                if( ! empty ($field) ) {
                    $settings_field->add_settings_field("custom_meta_fields", __("Meta Field", "spine-app"), array($settings_field, 'get_woo_meta_fields'), $this->plugin_slug, 'spine_js_settings_sections_woo', $this->args);
                }
            }

            $settings_field = new Settings_field('new');
            $settings_field->add_settings_field("add_new", __("Add Meta Field", "spine-app"), array($settings_field, 'get_woo_meta_fields'), $this->plugin_slug, 'spine_js_settings_sections_woo', $this->args);
        }

        public function get_hidden_input()
        {

            ?>
            <input type="hidden" name="tab" value="<?php echo $this->option_group ?>" />
            <?php
        }

    }//class closure
} //if class exists closure

