<?php
if (!class_exists('Nusvg_Admin_Page_Troubleshooting')) {

    class Nusvg_Admin_Page_Troubleshooting {

        static function _display() {
            global $wpdb;
            ?>
            <h1><?php _e("Troubleshooting", 'nusvg') ?></h1>

            <?php
            if (isset($_POST['nusvg_rewrite_all'])) {
                Nusvg_Admin::rewrite_all();
                ?><p><div class="updated"><?php _e("Done") ?></div></p><?php
            }
            ?>
            <form method="post">
                <button class="button button-primary button-large" type="submit" name="nusvg_rewrite_all"><?php _e('Refresh cache','nusvg') ?></button> 
            </form>
            <?php
        }

    }

}