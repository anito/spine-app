<?php
defined('ABSPATH') or die("you do not have access to this page!");

if ( ! class_exists( 'db_spine_js' ) ) {

    class Db_spine_js extends Spine_js_admin {

        private static $_this;

        public $show_db_notice = FALSE;
        public $db_notice_id = 'spine_js_db_backup';
        public $user = ['username' => '', 'password' => ''];
        public $backup_domain = '';
        public $option_group = 'db_backup';

        function __construct() {
            if ( isset( self::$_this ) )
                return self::$_this;
                // wp_die( sprintf( __( '%s is a singleton class and you cannot create a second instance.','spine-app' ), get_class( $this ) ) );

            $this->get_options();
            $this->get_admin_options();

            self::$_this = $this;

        }

        public function get_admin_options() {

            $options = get_option('spine_js_settings_db');

            if (isset($options)) {
                $this->user['username'] = isset($options['user']['username']) ? $options['user']['username'] : $this->user['username'];
                $this->user['password'] = isset($options['user']['password']) ? $options['user']['password'] : $this->user['password'];
                $this->show_db_notice = isset($options['show_db_notice']) ? $options['show_db_notice'] : $this->show_db_notice;
                $this->backup_domain = isset($options['backup_domain']) ? $options['backup_domain'] : $this->backup_domain;
            }

        }

        /**
         * Create the settings page form for DB Backup
         *
         * @since  2.0
         *
         * @access public
         *
         */
        public function create_form() {

            add_settings_section('spine_js_settings_sections_db', __("Settings", "spine-app"), array($this, 'section_text'), $this->plugin_slug);

            add_settings_field('id_backup_domain', __("Backup Domain", "spine-app"), array($this, 'get_backup_domain'), $this->plugin_slug, 'spine_js_settings_sections_db');
            add_settings_field('id_username', __("Username", "spine-app"), array($this, 'get_option_username'), $this->plugin_slug, 'spine_js_settings_sections_db');
            add_settings_field('id_password', __("Password", "spine-app"), array($this, 'get_option_password'), $this->plugin_slug, 'spine_js_settings_sections_db');
            add_settings_field('id_show_db_notice', __("Show DB Notice", "spine-app"), array($this, 'get_option_show_db_notice'), $this->plugin_slug, 'spine_js_settings_sections_db');
            add_settings_field('id_option_group', "", array($this, 'get_hidden_input'), $this->plugin_slug, 'spine_js_settings_sections_db');

            // register_setting('spine_js_settings_db', 'spine_js_settings_db', array($this, 'options_validate'));
        }

        /**
         * Register settings
         */
        public function register_setting() {
            // params: option_group, option_name
            register_setting('spine_js_settings_db', 'spine_js_settings_db', array($this, 'options_validate'));
        }

        public  function show_db_backup_notice() {
            //prevent showing the review on edit screen, as gutenberg removes the class which makes it editable.
            $screen = get_current_screen();
            if ( $screen->parent_base === 'edit' ) return;

            if (!current_user_can($this->capability)) return;

            do_action('db_backup_notice');

        }

        public function db_backup_notice() {
            if ($this->show_db_notice) {

                $notices[$this->db_notice_id] = array(
                    'class' => 'notice notice-info backup-info',
                );
                require_once(SPINEAPP_PLUGIN_DIR . 'templates/notice.php');
            }
        }

        /*
        * Enqueue styles and scripts
        * @since 2.0.0
        */
        public function enqueue_assets() {

            wp_deregister_script('jquery');
            wp_enqueue_script ( 'app', SPINEAPP_PLUGIN_URL . 'assets/spine/public/application.js', false, SPINEAPP_VERSION, true );
            wp_enqueue_style('spine-app-styles', SPINEAPP_PLUGIN_URL . 'assets/spine/public/application.css', false, SPINEAPP_VERSION);

            /*
            * Twitter Bootstrap
            */
            wp_register_script('bootstrap', SPINEAPP_PLUGIN_URL . 'assets/spine/node_modules/bootstrap/dist/js/bootstrap.js', array('jquery'), false, true);
            // wp_enqueue_script('bootstrap'); // or load via hem library

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

            $settings = array();
            $settings['user']['username'] = $new_settings['user']['username'];
            $settings['user']['password'] = $new_settings['user']['password'];
            $settings['backup_domain'] = $new_settings['backup_domain'];

            if (!empty($new_settings['show_db_notice']) && $new_settings['show_db_notice'] == '1') {
                $settings['show_db_notice'] = TRUE;
            } else {
                $settings['show_db_notice'] = FALSE;
            }

            return $settings;
        }

        /**
         * Returns a success, error or warning image for the settings page
         *
         * @since  2.0
         *
         * @access public
         *
         * @param string $type the type of image
         *
         * @return string
         */
        public function img($type) {
            if ($type == 'success') {
                return "<img class='spine-js-icons' src='" . trailingslashit(SPINEAPP_PLUGIN_URL) . "assets/img/check-icon.png' alt='success'>";
            } elseif ($type == "error") {
                return "<img class='spine-js-icons' src='" . trailingslashit(SPINEAPP_PLUGIN_URL) . "assets/img/cross-icon.png' alt='error'>";
            } else {
                return "<img class='spine-js-icons' src='" . trailingslashit(SPINEAPP_PLUGIN_URL) . "assets/img/warning-icon.png' alt='warning'>";
            }
        }

        /*
        * Add SpineJS App
        * @since 2.0.0
        */
        public function init_spine_js() {
            echo '<!-- #spine-app -->';
            $options = get_option('spine_js_settings_db');
            ?>
            <script id="spine-app" type="text/javascript">

                (function (exports) {
                    'use strict';

                    exports.base_url = '<?= $options['backup_domain']; ?>';

                    var initApp = function() {
                        var App = require("index");
                        exports.app = new App({
                            el: "#<?= $this->db_notice_id ?>",
                            isProduction:<?= (IS_PRODUCTION) ? 'true': 'false'; ?>,
                            isAdmin:<?= (current_user_can('edit_pages')) ? 'true': 'false'; ?>,
                            'user': <?= json_encode($options['user']) ?>,
                            'url': "<?= $options['backup_domain'] ?>"
                        });
                    }

                    if(!document.getElementById('modal-view')) {
                        // $('body').append('<div tabindex="0" id="modal-view" class="modal fade"><div class="modal-dialog modal-lg" role="document">initially needed by Modal</div></div>');
                    }
                    window.addEventListener( "load", initApp, false );
                })(this)

            </script>

            <?php
        }

        public function get_backup_domain() {

            ?>
            <label class="spine-js-">
                <input id="spine_js_settings_db_backup_domain" name="spine_js_settings_db[backup_domain]" size="40" value="<?= $this->backup_domain ?>" placeholder="<?= __('Domain for Backup', "spine-app") ?>"
                        type="text"  />
            </label>
            <?php
            SPINEJS()->spine_js_help->get_help_tip(__("Domain where DB Backup Tool is located", "spine-app"));
        }

        public function get_option_username() {
            $user = $this->user;

            ?>
            <label class="spine-js-">
                <input id="spine_js_settings_db_username" name="spine_js_settings_db[user][username]" size="40" value="<?= $user['username'] ?>" placeholder="<?= __('Username', "spine-app") ?>"
                        type="text"  />
            </label>
            <?php
            SPINEJS()->spine_js_help->get_help_tip(__("Your DB Backup Tool Username", "spine-app"));
        }

        public function get_option_password() {
            $user = $this->user;

            ?>
            <label class="spine-js-">
                <input id="spine_js_settings_db_password" name="spine_js_settings_db[user][password]" size="40" value="<?=$user['password'] ?>" placeholder="<?= __('Password', "spine-app") ?>" 
                        type="password"  />
            </label>
            <?php
            SPINEJS()->spine_js_help->get_help_tip(__("Your DB Backup Tool Password", "spine-app"));
            if( $this->backup_domain ) { ?>
            <p>
                <a href="<?= $this->backup_domain . '/register' ?>" target="_blank"><?= __("Register new user", "spine-app") ?></a>
                <?php
                SPINEJS()->spine_js_help->get_help_tip(__("Register new user", "spine-app"));
                ?>
            </p>
            <?php };
        }

        public function get_option_show_db_notice()
        {

            ?>
            <label class="spine-js-switch">
                <input id="spine_js_show_db_notice_options" name="spine_js_settings_db[show_db_notice]" size="40" value="1"
                        type="checkbox" <?php checked(1, $this->show_db_notice, true) ?> />
                <span class="spine-js-slider spine-js-round"></span>
            </label>
            <?php
            SPINEJS()->spine_js_help->get_help_tip(__("Enable this option to show DB Tool notice", "spine-app"));

        }

        public function get_hidden_input()
        {

            ?>
            <input type="hidden" name="tab" value="<?php echo $this->option_group ?>" />
            <?php

        }
    }//class closure
} //if class exists closure