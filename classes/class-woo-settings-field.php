<?php
defined('ABSPATH') or die("you do not have access to this page!");

if ( ! class_exists( 'Settings_field' ) ) {

    class Settings_field extends Woo_spine_js {

        static public $k = 0;

        function __construct( $key ) {
            $this->key = isset($key) ? $key : self::$k++;
        }

        public function add_settings_field( $id, $title, $callback, $page, $section = 'default', $args ) {

            $args = wp_parse_args($args, array('id' => $id) );
            add_settings_field("{$id}[{$this->key}]", $title, $callback, $page, $section, $args );

        }

        public function remove_settings_field( $key ) {

            return delete_metadata( $post_id, 'custom_meta_fields[{$this->key}]', $meta_value );

        }

        public function may_be_get_field_option( ) {

            if( $option = get_option("custom_meta_fields[{$this->key}]") ) {

                return array( 'option' => $option, 'key' => $this->key );

            }

            return $option;
        }

        public function get_woo_meta_fields($args) {
            $options = get_option('spine_js_settings_woo');
            $id = $args['id'];

            if($this->key === 'new') {
                $placeholder = __('Custom Meta Name', 'spine-app');
                $name = '';
                $slug = '';
            } else {
                $placeholder = '';
                $name = $options['custom_meta_fields'][$this->key]['name'];
                $slug = $options['custom_meta_fields'][$this->key]['slug'];
            }
            ?>
            <input id="spine_js_settings_woo_custom_meta_fields_meta<?= $this->key ?>" type="text" name="spine_js_settings_woo[<?=$id ?>][<?= $this->key ?>][name]" placeholder="<?= $placeholder ?>" size="40" value="<?= $name ?>" />
            <input id="spine_js_settings_woo_custom_meta_fields_slug_<?= $this->key ?>" type="text" name="spine_js_settings_woo[<?=$id ?>][<?= $this->key ?>][slug]" size="40" value="<?= $slug ?>" disabled />
            <?php
        }

    }
}