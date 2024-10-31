<?php
if (!class_exists('Nusvg_Admin_Page_Uninstall')) {

    class Nusvg_Admin_Page_Uninstall {

        static function _display() {
            ?>
                <h1><?php _e("Uninstall", 'nusvg') ?></h1>
                <?php
                    ?>
                    <p><?php _e("To avoid erasure of useful data, SVG files are not deleted during uninstall process.", 'nusvg') ?></p>
                    <p><?php printf(__("After uninstalling this plugin, please manually delete this folder : %s", 'nusvg'),"wp-content/uploads/nusvg") ?></p>
            <?php
        }

    }

}