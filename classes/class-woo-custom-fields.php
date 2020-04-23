<?php
defined('ABSPATH') or die("you do not have access to this page!");

if ( ! class_exists( 'Spine_js_woo' ) ) {

    class Spine_js_woo {

        private static $_this;

        function __construct() {
            if ( isset( self::$_this ) )
                return self::$_this;
                // wp_die( sprintf( __( '%s is a singleton class and you cannot create a second instance.','spine-app' ), get_class( $this ) ) );

            $this->tax_data = array(
                'product_specials' => array(
                    $labels = array(
                        'name'                       => 'Special Features',
                        'singular_name'              => 'Special Feature',
                        'menu_name'                  => 'Specials',
                        'all_items'                  => 'All Specials',
                        'parent_item'                => 'Parent Specials',
                        'parent_item_colon'          => 'Parent Specials:',
                        'new_item_name'              => 'New Special',
                        'add_new_item'               => 'Add New Special Feature',
                        'edit_item'                  => 'Edit Special',
                        'update_item'                => 'Update Specials',
                        'separate_items_with_commas' => 'Separate Specials with commas',
                        'search_items'               => 'Search Specials',
                        'add_or_remove_items'        => 'Add or remove Specials',
                        'choose_from_most_used'      => 'Choose from the most used Specials',
                    ),
                    $args = array(
                        'labels'                     => $labels,
                        'hierarchical'               => false,
                        'public'                     => true,
                        'show_ui'                    => true,
                        'show_admin_column'          => true,
                        'show_in_nav_menus'          => true,
                        'show_tagcloud'              => true,
                    )
                )
            );
            self::$_this = $this;
            $this->init();

        }

        static function instance() {
            return self::$_this;
        }
        protected function init() {
            $this->hooks();
            $this->woo_add_taxonomies();
        }
        protected function hooks() {
            // adds custom input fields to generals tab in product editor
            add_action( 'woocommerce_product_options_general_product_data', array( $this, 'woo_add_custom_general_fields') ); // Display Fields
            add_action( 'woocommerce_process_product_meta', array( $this, 'woo_add_custom_general_fields_save' )); // Save Fields

            // prepare meta query for our new products metas
            add_filter( 'woocommerce_product_data_store_cpt_get_products_query', array( $this, 'woo_product_data_store_cpt_get_products_query_checkbox' ), 10, 3 );

            // Register Rest endpoint
            add_action( 'rest_api_init', array( $this, 'rest_api_includes' ), 15 );
            add_action( 'rest_api_init', array( $this, 'register_rest_routes' ), 20 );

        }

        // public data_store_cpt_callback(  )

        protected function get_custom_fields() {

            $options = get_option('spine_js_settings_woo');
            return $options['custom_meta_fields'];

        }
        public function woo_add_custom_general_fields () {
            global $post;

            $post_id = $post->ID;

            echo '<div class="options_group">';

            $custom_fields = $this->get_custom_fields();

            foreach( $custom_fields as $cmf ) {

                $this->add_pruduct_general_fields( $cmf );

            }

            echo '</div>';
        }

        public function add_pruduct_general_fields( $cmf ) {

            // Number Field
            // woocommerce_wp_text_input(
            //     array(
            //         'id'                => '_hal_number_field_aow',
            //         'label'             => __( 'My Number Field', 'woocommerce' ),
            //         'placeholder'       => '',
            //         'description'       => __( 'Enter the custom value here.', 'woocommerce' ),
            //         'type'              => 'number',
            //         'custom_attributes' => array(
            //                 'step' 	=> 'any',
            //                 'min'	=> '0'
            //             )
            //     )
            // );
            
            // Select
            // woocommerce_wp_select(
            //     array(
            //         'id'      => '_hal_select',
            //         'label'   => __( 'My Select Field', 'woocommerce' ),
            //         'options' => array(
            //             'one'   => __( 'Option 1', 'woocommerce' ),
            //             'two'   => __( 'Option 2', 'woocommerce' ),
            //             'three' => __( 'Option 3', 'woocommerce' )
            //         )
            //     )
            // );
            
            // Checkbox
            woocommerce_wp_checkbox(
                array(
                    'id'            => "_hal_checkbox_{$cmf['slug']}",
                    'wrapper_class' => '', //'show_if_simple',
                    'label'         => __( $cmf['name'], 'woocommerce' ),
                    'description'   => __( "Als {$cmf['name']} kennzeichnen", 'woocommerce' )
                )
            );
            // Text Field
            woocommerce_wp_text_input(
                array(
                    'id'                => "_hal_header_{$cmf['slug']}",
                    'label'             => __( "{$cmf['name']} Titel", 'woocommerce' ),
                    'placeholder'       => '',
                    'description'       => __( 'Titel im Icon', 'woocommerce' ),
                    'type'              => 'text'
                )
            );
            // Textarea
            woocommerce_wp_text_input(
                array(
                    'id'          => "_hal_footer_{$cmf['slug']}",
                    'label'       => __( "{$cmf['name']} Fusszeile", 'woocommerce' ),
                    'placeholder' => '',
                    'description' => __( 'Extra Text in der Fusszeile', 'woocommerce' )
                )
            );
        }

        public function woo_product_data_store_cpt_get_products_query_checkbox( $query, $query_vars, $data_store_cpt ) {

            $custom_fields = $this->get_custom_fields();

            foreach( $custom_fields as $cmf ) {

                // Prepare WOO Products Query

                $meta_key = "_hal_checkbox_{$cmf['slug']}"; // The custom meta_key

                if ( ! empty( $query_vars[$meta_key] ) ) {
                    write_log($cmf);
                    $query['meta_query'][] = array(
                        'key'   => $meta_key,
                        'value' => esc_attr( $query_vars[$meta_key] ),
                        'compare' => 'IN', // <=== Here you can set other comparison arguments
                    );
                    $query['numberposts'] = -1;
                    $query['posts_per_page'] = 5;
                break;
                }
            }
            return $query;
        }

        public function woo_add_custom_general_fields_save() {
            global $woocommerce, $post;

            $post_id = $post->ID;
            $custom_fields = $this->get_custom_fields();

            foreach( $custom_fields as $cmf ) {
                $name = $cmf['name'];
                $slug = $cmf['slug'];

                // Text Fields
                $woocommerce_text_field = sanitize_text_field( $_POST["_hal_header_{$slug}"] );
                update_post_meta( $post_id, "_hal_header_{$slug}", esc_attr( $woocommerce_text_field ) );

                // Textareas
                $woocommerce_textarea = sanitize_text_field( $_POST["_hal_footer_{$slug}"] );
                update_post_meta( $post_id, "_hal_footer_{$slug}", $woocommerce_textarea );

                // Checkboxes
                $woocommerce_checkbox = isset( $_POST["_hal_checkbox_{$slug}"] ) ? 'yes' : 'no';
                if( 'yes' == $woocommerce_checkbox ) $this->reset_meta( "_hal_checkbox_{$slug}", $woocommerce_checkbox );
                update_post_meta( $post_id, "_hal_checkbox_{$slug}", $woocommerce_checkbox );

                // MORE INPUT TYPES

                // Number Field
                // $woocommerce_number_field = $_POST['_hal_number_field'];
                // if( !empty( $woocommerce_number_field ) )
                //     update_post_meta( $post_id, '_hal_number_field', esc_attr( $woocommerce_number_field ) );

                // Select
                // $woocommerce_select = $_POST['_hal_select'];
                // if( !empty( $woocommerce_select ) )
                //     update_post_meta( $post_id, '_hal_select', esc_attr( $woocommerce_select ) );

                // Checkbox generic
                // $woocommerce_checkbox_aow = isset( $_POST['_hal_checkbox'] ) ? 'yes' : 'no';
                // if( 'yes' == $woocommerce_checkbox_aow ) $this->reset_meta( '_hal_checkbox_aow', $woocommerce_checkbox_aow );
                // update_post_meta( $post_id, '_hal_checkbox', $woocommerce_checkbox_aow );

            }
        }

        public  function get_product_meta( $id, $key_part, $slug = '' ) {
            $product = wc_get_product( $id );
            if( !empty( $product ) ) {
                return $product->get_meta( "{$key_part}_{$slug}" );
            }
            return '';
        }
        public function reset_meta( $meta_field, $value ) {
            global $post;

            $query = new WP_Query( array(
                'post_type' => 'product',
                'meta_query' => array(
                    array(
                        'key' => $meta_field,
                        'value' => 'yes',
                        'compare' => 'IN',
                    )
                ),
                'numberposts' => -1,
                'posts_per_page' => 5
            ) );

            // reset posts
            $active_posts = $query->posts;
            foreach( $active_posts as $active_post ) {
                update_post_meta( $active_post->ID, $meta_field, 'no' );
            }
        }
        public function woo_add_taxonomies() {
            foreach( $this->tax_data as $key => $val ) {
                register_taxonomy( $key, 'product', $val[1] );
                register_taxonomy_for_object_type( $key, 'product' );
            }
            $taxonomies = get_object_taxonomies('product');
        }

        public function get_metas() {

            $metas = array();
            $custom_fields = $this->get_custom_fields();

            foreach( $custom_fields as $cmf ) {

                $metas[] = $cmf;

            }

            return $metas;
        }
        public function get_products_from_meta( $id ) {

            $metas = $this->get_metas();
            $slug = $metas[$id]['slug'];
            $products = $this->get_products_from_slug( $slug );

            return $products;
        }

        // generic query
        public function get_products_from_slug( $slug ) {
            $meta_key = "_hal_checkbox_{$slug}";
            $queried_products = wc_get_products( array(
                $meta_key => 'yes',
                'return' => 'ids'
            ) );

            return $queried_products;
        }

        public function rest_api_includes() {
            include_once dirname( __FILE__ ) . '/RestApi/ProductAow.php';
        }

        public function register_rest_routes() {
            $controllers = array(
                // REST API v1 controllers.
                'ProductAow'
            );

            foreach ( $controllers as $controller ) {
                $this->$controller = new $controller();
                $this->$controller->register_routes();
            }
        }

    }//class closure
} //if class exists closure
