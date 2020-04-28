<?php
defined('ABSPATH') or die("you do not have access to this page!");

class Spine_js_admin {

    private static $_this;

    public $default_tab = 'db_backup';
    public $capability = 'activate_plugins';
    public $plugin_filename = "spine-app.php";
    public $plugin_slug = "spine_js";

    function __construct() {

        if (isset(self::$_this))
            return self::$_this;
            // wp_die(sprintf(__('%s is a singleton class and you cannot create a second instance.', 'spine-js'), get_class($this)));

        self::$_this = $this;

        register_deactivation_hook(dirname(__FILE__) . "/" . $this->plugin_filename, array($this, 'deactivate'));

        add_action('admin_init', array($this, 'add_privacy_info'));

    }

    public function add_privacy_info() {
        if (!function_exists('wp_add_privacy_policy_content')) {
            return;
        }

        $content = sprintf(
            __('Spine Js add-ons do not process any personal identifiable information, so the GDPR does not apply to these plugins or usage of these plugins on your website. You can find our privacy policy <a href="%s" target="_blank">here</a>.', 'spine-js'),
            'https://webpremiere.de/privacy-statement/'
        );

        wp_add_privacy_policy_content(
            'Spine Js',
            wp_kses_post(wpautop($content, false))
        );
    }

    public function init() {
        write_log( 'admin init' );
        $opts = get_option('spine_js_settings_db');

        $this->db = new Db_spine_js();

        if( $opts['show_db_notice'] ) {
            add_action( 'admin_enqueue_scripts', array ( $this->db, 'enqueue_assets' ), 10 );
            add_action( "admin_notices", array($this->db, 'show_db_backup_notice'), 10 );
            add_action( "db_backup_notice", array($this->db, 'db_backup_notice'), 10 );
            add_action( 'admin_footer', array ( $this->db, 'init_spine_js' ), 999) ;
        }

    }

    public function edit() {

        $tab =  $this->get_tab();
        switch($tab) {
            case 'db_backup':

                add_action( 'admin_init', array($this->db, 'register_setting'), 10 );
                add_action( 'admin_init', array($this->db, 'create_form'), 20 );

            break;

            case 'wpt_custom_menu':

                if( ! isset ( $this->wpt ) ) $this->wpt = new Wpt_spine_js();
                add_action( 'admin_init', array($this->wpt, 'register_setting'), 10 );
                add_action( 'admin_init', array($this->wpt, 'create_form'), 20 );
            break;

            case 'woo_action_taxonomy':
                if( ! isset ( $this->woo ) ) $this->woo = new Woo_spine_js();
                add_action( 'admin_init', array($this->woo, 'register_setting'), 10 );
                add_action( 'admin_init', array($this->woo, 'create_form'), 20 );
            break;
        }

    }

    public function hooks() {
        write_log( 'admin hooks' );
        add_action( 'admin_menu', array($this, 'add_settings_page'), 0 );
        add_action( 'admin_init', array($this, 'load_translation'), 20 );
        add_filter( 'body_class', array ( $this, 'body_class' ) );
        add_action( 'admin_init', array($this, 'listen_for_deactivation'), 40 );
        add_action( 'admin_init', array($this, 'add_styles'), 999 );
    }
    public function get_options() {
        //
    }
    public function get_admin_options() {
        //
    }

    /**
     * Adds the admin options page
     *
     * @since  2.0
     *
     * @access public
     *
     */
    public function add_settings_page() {
        if (!current_user_can($this->capability)) return;

        global $spine_js_admin_page;
        $spine_js_admin_page = add_options_page(
            __("Lehmann Settings", "spine-app"), //link title
            __("Lehmann GmbH", "spine-app"), //page title
            $this->capability, //capability
            'spine_js', //url
            array($this, 'settings_page'), // function to output
            0
        ); //function
    }

    /*
    * Public Functions
    */
    public function body_class( $classes ) {
        return $classes;
    }

    /**
     * Load the translation files
     *
     * @since  1.0
     *
     * @access public
     *
     */
    public function load_translation() {
        load_plugin_textdomain('spine-js', FALSE, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    /*
     * Deactivate the plugin while keeping SSL
     * Activated when the 'uninstall_keep_ssl' button is clicked in the settings tab
     *
     */
    public function listen_for_deactivation() {

    }

    /**
     * Get the current Tab
     *
     * @since  2.1
     *
     * @access public
     *
     */
    public function get_tab() {
        if (isset ($_POST['tab'])) $tab = $_POST['tab']; else if (isset ($_GET['tab'])) $tab = $_GET['tab']; else $tab = $this->default_tab;
        return $tab;
    }

    /**
     * Build the settings page
     *
     * @since  2.0
     *
     * @access public
     *
     */
    public function settings_page() {

        if (!current_user_can($this->capability)) return;

        if (isset ($_GET['tab'])) $this->admin_tabs($_GET['tab']); else $this->admin_tabs($this->default_tab); // print tabs
        $tab = $this->get_tab();

        ?>
        <div class="spine-js-container">
            <div class="spine-js-main">
                <form action="options.php" method="post">
            <?php
                switch ($tab) {

                    case 'db_backup' :
                        settings_fields('spine_js_settings_db'); // matches option group
                        do_settings_sections('spine_js'); // prints form fields
                    break;

                    case 'wpt_custom_menu' :
                        settings_fields('spine_js_settings_wpt'); // matches option group
                        do_settings_sections('spine_js'); // prints form fields
                    break;

                    case 'woo_action_taxonomy' :
                        settings_fields('spine_js_settings_woo'); // matches option group
                        do_settings_sections('spine_js'); // prints form fields
                    break;

                }
                //possibility to hook into the tabs.
                do_action("show_tab_{$tab}");
                ?>
                    <input class="button button-primary" name="Submit" type="submit" value="<?php echo __("Save", "spine-app"); ?>"/>
                </form>
            </div><!-- end spine-js-main-->
        </div><!-- end spine-js-main-->
        <?php

    }

    /**
     * Create tabs on the settings page
     *
     * @since  2.1
     *
     * @access public
     *
     */
    public function admin_tabs($current) {
        $tabs = array(
            'db_backup'           => __("DB Backup Tool", "spine-app"),
            'wpt_custom_menu'     => __("WP Touch", "spine-app"),
            'woo_action_taxonomy' => __("Action Products", "spine-app")
        );

        $tabs = apply_filters("spine_js_tabs", $tabs);

        echo '<h2 class="nav-tab-wrapper">';

        $page = $this->plugin_slug;
        foreach ($tabs as $tab => $name) {
            $class = ($tab == $current) ? ' nav-tab-active' : '';
            echo "<a class='nav-tab$class' href='?page=$page&tab=$tab'>$name</a>";
        }
        echo '</h2>';
    }

    /**
     * Insert some explanation above the form
     *
     * @since  2.0
     *
     * @access public
     *
     */
    public function section_text() {

        $tab = $this->get_tab();
        switch($tab) {
            case 'db_backup':
                $text = __("Please authorize in DB Backup Tool", "spine-app");
                break;
            case 'wpt_custom_menu':
                $text = __("Replace default Pages Menu in WP Touch", "spine-app");
                break;
            case 'woo_action_taxonomy':
                $text = __("Ativiert zusätzliche Merkmale eines Produktes.", "spine-app");
                break;
            default:
                $text = __("Short description here", "spine-app");

        }
        ?>
        <?=  '<p>' . $text . '</p>'?>
        <?php
    }

    /**
     * Check the posted values in the settings page for validity
     *
     * @since  2.0
     *
     * @access public
     *
     */
    public function options_validate( array $new_settings ) {
        return $new_settings;
    }

    /**
     * Save the plugin options
     *
     * @since  2.0
     *
     * @access public
     *
     */
    public function save_options() {
        //any options added here should also be added to function options_validate()
        $options = array();

        update_option('spine_js_options', $options);
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

    public function add_styles() {
        wp_register_style('spine-js-css', SPINEAPP_PLUGIN_URL . 'assets/css/main.css', false, SPINEAPP_VERSION);
        wp_enqueue_style('spine-js-css');
    }
    /**
     * Handles deactivation of this plugin
     *
     * @since  2.0
     *
     * @access public
     *
     */
    public function deactivate($networkwide) {

    }
}