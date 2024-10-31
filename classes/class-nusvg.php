<?php

if (!class_exists('Nusvg')) {

    require_once('class-nusvg-item.php');

    /*
     * init() is executed at the bottom of this class
     */

    class Nusvg {

        static $basedir = '';
        static $baseurl = '';

        static function init() {
            add_shortcode('nusvg', 'Nusvg::shortcode_get');
            add_shortcode('nusvg_credits', 'Nusvg::get_concat_credits');
        }
        
        /*
        * [nusvg _nusvg_example]
        * [nusvg _nusvg_example {"width":600,"height":600,"bg":"red"}]
        */

        static function shortcode_get($atts = array()) {
            if ($atts=="") {
                return "";
            }
            $vars = array();
            $slug = $atts[0];
            if (count($atts) > 1) {
                $vars = json_decode($atts[1],true);
            }
            return self::get($slug, $vars);
        }

        static function get_basedir($subfolder = '') {
            if (!self::$basedir) {
                $extra = 'nusvg';
                $wp_get_upload_dir = wp_get_upload_dir();
                self::$basedir = trailingslashit($wp_get_upload_dir['basedir']) . 'nusvg';
            }
            return self::$basedir . ($subfolder ? '/' . $subfolder : '');
        }

        static function get_baseurl($subfolder = '') {
            if (!self::$baseurl) {
                $wp_get_upload_dir = wp_get_upload_dir();
                self::$baseurl = trailingslashit($wp_get_upload_dir['baseurl']) . 'nusvg';
            }
            return self::$baseurl . ($subfolder ? '/' . $subfolder : '');
        }

        static function get_url($slug, $check_if_exists = false) {
            $filename = $slug . '.svg';
            if ($check_if_exists) {
                $path = self::get_basedir('svg') . '/' . $filename;
                if (!file_exists($path)) {
                    return false;
                }
            }
            return self::get_baseurl('svg') . '/' . $filename;
        }

        static function slug_exists($slug) {
            global $wpdb;
            return ($wpdb->get_var("SELECT count(*) FROM `" . $wpdb->prefix . "nusvg_items` WHERE slug='" . addslashes($slug) . "';") > 0);
        }

        /*
         * Nusvg::get('funny_picto'); // use default parameters
         * Nusvg::get('funny_picto',['fill'=>'yellow']);
         * Nusvg::get('funny_picto',['fill'=>'yellow','width'=>'60px','height'=>true])->format('$base64');
         * Nusvg::get('funny_picto',['fill'=>'yellow'],['patterns'=>['patt1','patt2']]);
         * Nusvg::get('funny_picto',['fill'=>'yellow'],['patterns'=>['patt1','patt2']])->format('$base64');
         */

        static function get($slugs, $vars = [], $args = []) {
            if (is_array($slugs)) {
                require_once('class-nusvg-collection.php');
                $Collection = new Nusvg_Collection;
                foreach ($slugs as $slug) {
                    $Nusvg_Item = new Nusvg_Item;
                    $Nusvg_Item->slug = $slug;
                    $Nusvg_Item->load();
                    if ($Nusvg_Item->preproc == '') {
                        continue;
                    }
                    $Nusvg_Item->process($vars, $args);
                    $Collection->Items[$slug] = $Nusvg_Item;
                }
                return $Collection;
            } else {
                $Nusvg_Item = new Nusvg_Item;
                $Nusvg_Item->slug = $slugs;
                $Nusvg_Item->load();
                if ($Nusvg_Item->preproc == '') {
                    return '[[SVG:' . $slugs . ']]';
                }
                $Nusvg_Item->process($vars, $args);
                return $Nusvg_Item;
            }
        }

        static function get_concat_credits() {
            $path_concat = self::get_basedir() . '/concat_credits.html';
            if (file_exists($path_concat)) {
                return file_get_contents($path_concat);
            }
        }

    }

    Nusvg::init();
}