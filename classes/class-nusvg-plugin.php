<?php

if (!class_exists('Nusvg_Plugin')) {

    class Nusvg_Plugin {

        const plugin_version = '0.1';
        const db_version = '0.1';

        static function activation() {
            add_option('nusvg_db_version', self::db_version);
            add_option('nusvg_plugin_version', self::plugin_version);
            self::create_table();
            self::create_upload_folders();
            self::insert_example_item();
        }

        static function uninstall() {
            delete_option('nusvg_plugin_version');
            delete_option('nusvg_db_version');
            delete_option('nusvg_expiration');
            self::delete_tables();
            //         self::delete_files();
        }

        static function check_version() {
            if (!get_option('nusvg_plugin_version')) {
                add_option('nusvg_plugin_version', self::plugin_version);
            } elseif (get_option('nusvg_plugin_version') != self::plugin_version) {
                update_option('nusvg_plugin_version', self::plugin_version);
            }
            if (!get_option('nusvg_db_version')) {
                add_option('nusvg_db_version', self::db_version);
            } elseif (get_option('nusvg_db_version') != self::db_version) {
                update_option('nusvg_db_version', self::db_version);
            }
        }

        static function create_upload_folders() {
            $uploads_dir = trailingslashit(wp_upload_dir()['basedir']) . 'nusvg';
            wp_mkdir_p($uploads_dir);
            // file credits.txt
            file_put_contents($uploads_dir . '/concat_credits.html', '');
            // subfolder /preproc
            $uploads_dir = trailingslashit(wp_upload_dir()['basedir']) . 'nusvg/preproc';
            wp_mkdir_p($uploads_dir);
            // subfolder /SVG
            $uploads_dir = trailingslashit(wp_upload_dir()['basedir']) . 'nusvg/svg';
            wp_mkdir_p($uploads_dir);
            // subfolder /credits
            $uploads_dir = trailingslashit(wp_upload_dir()['basedir']) . 'nusvg/credits';
            wp_mkdir_p($uploads_dir);
        }

        static function create_table() {
            global $wpdb;
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            $table_name = $wpdb->prefix . 'nusvg_items';
            $sql = "CREATE TABLE IF NOT EXISTS $table_name (
  slug varchar(50) NOT NULL,
  preproc text NOT NULL,
  default_width float UNSIGNED NOT NULL,
  default_height float UNSIGNED NOT NULL,
  preview_width varchar(255) NOT NULL DEFAULT 50,
  preview_height varchar(255) NOT NULL DEFAULT 50,
  preview_background varchar(255) NOT NULL,
  preview_stripes tinyint(4) NOT NULL,
  credits text NOT NULL,
  PRIMARY KEY  (slug)
) " . $wpdb->get_charset_collate() . ";";
            dbDelta($sql);
        }

        static function delete_tables() {
            global $wpdb;
            $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "nusvg_items;");
        }

        static function insert_example_item() {
            $slug = '_nusvg_example';
            $Item = new Nusvg_Item;
            $Item->slug = $slug;
            $Item->insert();
            $Item->default_width = 18;
            $Item->default_height = 18;
            $Item->preview_width = 60;
            $Item->preview_height = 60;
            $Item->preview_background = '#009B10';
            $Item->preview_stripes = 1;
            $Item->preproc = '<svg xmlns="http://www.w3.org/2000/svg" width="[[width:18]]" height="[[height:18]]" viewBox="0 0 63 63"><defs><pattern width="[[pattern_width:20px]]" height="[[pattern_height:20px]]" id="UNIQID_losanges" patternContentUnits="userSpaceOnUse" patternUnits="userSpaceOnUse"  viewBox="0,0,20,20"><polygon points="0,0 5,0 0,5" style="fill:[[losange_1:cornflowerblue]];stroke:black;stroke-width:0"/><polygon points="5,0 10,5 5,10 0,5" style="fill:[[losange_2:darkorange]];stroke:black;stroke-width:0"/><polygon points="5,0 15,0 10,5" style="fill:[[losange_4:gold]];stroke:black;stroke-width:0"/><polygon points="0,5 5,10 0,15" style="fill:[[losange_4:gold]];stroke:black;stroke-width:0"/><polygon points="10,5 15,10 10,15 5,10" style="fill:[[losange_1:cornflowerblue]];stroke:black;stroke-width:0"/><polygon points="5,10 10,15 5,20,0,15" style="fill:[[losange_3:yellowgreen]];stroke:black;stroke-width:0"/><polygon points="10,15 15,20 5,20" style="fill:[[losange_4:gold]];stroke:black;stroke-width:0"/><polygon points="0,15 5,20 0,20" style="fill:[[losange_1:cornflowerblue]];stroke:black;stroke-width:0"/><polygon points="15,0 20,5 15,10 10,5" style="fill:[[losange_3:yellowgreen]];stroke:black;stroke-width:0"/><polygon points="15,0 20,0 20,5" style="fill:[[losange_1:cornflowerblue]];stroke:black;stroke-width:0"/><polygon points="20,5 20,15 15,10" style="fill:[[losange_4:gold]];stroke:black;stroke-width:0"/><polygon points="15,10 20,15 15,20 10,15" style="fill:[[losange_2:darkorange]];stroke:black;stroke-width:0"/><polygon points="20,15 20,20 15,20" style="fill:[[losange_1:cornflowerblue]];stroke:black;stroke-width:0"/></pattern></defs><rect x="0" y="0" width="100%" height="100%"  fill="[[writing:url(#UNIQID_losanges)]]" /><polygon points="0 0, 63 0, 63 63, 0 63, 0 9, 7 9, 7 54, 14 54, 14 33, 21 54, 28 54, 28 27, 35 27, 35 54, 56 54, 56 27, 49 27, 49 45, 42 45, 42 27, 28 27, 28 9, 35 9, 35 18, 42 18, 42 9, 49 9, 49 18, 56 18, 56 9, 21 9, 21 30, 14 9, 0 9, 0 0" fill="[[bg:#000]]" ></polygon></svg>';
            $Item->update();
            $Item->process();
            $Item->write();
            global $wpdb;
        }

    }

}