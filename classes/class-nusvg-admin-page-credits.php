<?php
if (!class_exists('Nusvg_Admin_Page_Credits')) {

    class Nusvg_Admin_Page_Credits {

        static function _display() {
            global $wpdb;

           ?>
            <h1><?php _e("Credits", 'nusvg') ?></h1>
            <?php
            echo esc_html(Nusvg::get_concat_credits());  
        }

    }

}