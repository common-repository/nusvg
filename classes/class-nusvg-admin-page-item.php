<?php
if (!class_exists('Nusvg_Admin_Page_Item')) {

    class Nusvg_Admin_Page_Item {

        static function _display() {
            global $wpdb;

           if (!isset($_GET['slug'])) {
               _e("Page not found");
               return;
            }

            $slug= sanitize_key($_GET['slug']);
            
            ?>
            <p><a href="?page=nusvg-list"><?php _e("Back to list", 'nusvg') ?></a></p>

            <h1><?php _e("Item", 'nusvg') . " " . $slug ?> </h1>
            <?php
            $Nusvg_Item = new Nusvg_Item;
            $Nusvg_Item->slug = $slug;
            if ($Nusvg_Item->select() === false) {
                ?><div class="error"><?php _e("not found") ?></div><?php
                return;
            }

            echo esc_html($Nusvg_Item->edition());
        }

    }

}