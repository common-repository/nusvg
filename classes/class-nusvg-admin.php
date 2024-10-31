<?php
if (!class_exists('Nusvg_Admin')) {

    class Nusvg_Admin {

        static function __callStatic($method_name, $args) {
            if (substr($method_name, 0, 13) == 'page_project_') {
                $project_name = substr($method_name, 13);
                self::page_project($project_name);
            }
        }

        static function init() {

            load_plugin_textdomain('nusvg', false, dirname(plugin_basename(__FILE__)) . '/../lang/');

            add_action('admin_enqueue_scripts', function() {
                wp_enqueue_style('nusvg_admin', plugins_url('/../public/css/nusvg-admin.css', __FILE__), array(), '1.1');
                wp_enqueue_script('nusvg_admin', plugins_url('/../public/js/nusvg-admin.js', __FILE__), array('jquery'), '1.1.0', true);
            });
            
        }

        static function menu() {

            if (is_super_admin()) {

                add_menu_page('nusvg', 'nusvg', 'manage_options', 'nusvg', 'Nusvg_Admin::router', 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 63 63"><polygon points="7 9, 14 9, 21 29, 21 9, 28 9, 28 54, 21 54, 14 34, 14 54, 7 54" fill="black" /><polygon points="35 27, 35 54, 56 54, 56 27, 49 27, 49 45, 42 45, 42 27" fill="black" /><polygon points="35 9, 42 9, 42 18, 35 18" fill="black" /><polygon points="49 9, 56 9, 56 18, 49 18" fill="black" /></svg>') /*, position */);

                add_submenu_page('nusvg' /* $parent_slug */, __("List", 'nusvg') /* $page_title */, __("List", 'nusvg') /*  $menu_title */, 'read' /* $capability */, 'nusvg-list' /* $menu_slug */, 'Nusvg_Admin::router' /* $function */, null /* $position */);
                add_submenu_page('nusvg' /* $parent_slug */, __("Documentation", 'nusvg') /* $page_title */, __("Documentation", 'nusvg') /*  $menu_title */, 'read' /* $capability */, 'nusvg-documentation' /* $menu_slug */, 'Nusvg_Admin::router' /* $function */, null /* $position */);
                add_submenu_page('nusvg' /* $parent_slug */, __("Credits", 'nusvg') /* $page_title */, __("Credits", 'nusvg') /*  $menu_title */, 'read' /* $capability */, 'nusvg-credits' /* $menu_slug */, 'Nusvg_Admin::router' /* $functio */, null /* $position */);
                add_submenu_page('nusvg' /* $parent_slug */, __("Troubleshooting", 'nusvg') /* $page_title */, __("Troubleshooting", 'nusvg') /*  $menu_title */, 'read' /* $capability */, 'nusvg-troubleshooting' /* $menu_slug */, 'Nusvg_Admin::router' /* $function */, null /* $position */);
                
                add_submenu_page('nusvg' /* $parent_slug */, __("Uninstall", 'nusvg') /* $page_title */, __("Uninstall", 'nusvg') /*  $menu_title */, 'read' /* $capability */, 'nusvg-uninstall' /* $menu_slug */, 'Nusvg_Admin::router' /* $function */, null /* $position */);
                
                
                add_submenu_page(null /* null => hidden */ /* $parent_slug */, __("Item", 'nusvg') /* $page_title */, false /*  $menu_title */, 'read' /* $capability */, 'nusvg-item' /* $menu_slug */, 'Nusvg_Admin::router' /* $function */, null /* $position */);
                
                
            }
        }


        static function router() {
            if (!is_super_admin()) {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }
            $plugin = 'nusvg';
            $args = '';
            if (!isset($_GET['page'])) {
                return;
            }
            if ($_GET['page'] == $plugin) {
                // $page = 'home'; // show list instead of home
                $page = 'list';
            } else {
                $exploded = explode('--args_', substr($_GET['page'], strlen($plugin) + 1));
                $page = $exploded[0];
                if (isset($exploded[1])) {
                    $args = $exploded[1];
                }
            }
            $classname_lowercase = $plugin . '-admin-page-' . $page;
            $filename = 'class-' . $classname_lowercase . '.php';
            if (!file_exists(__DIR__ . '/' . $filename)) {
                return;
            }
            require(__DIR__ . '/' . $filename);
            $classname = implode('_', array_map(function($s) {
                        return ucfirst($s);
                    }, explode('-', $classname_lowercase)));
            if (!class_exists($classname)) {
                return;
            }
            if (!method_exists($classname, '_display')) {
                return;
            }
            call_user_func($classname . '::_display', $args);
        }

        static function page_item() {

            if (!is_super_admin()) {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }
            if (!isset($_GET['slug'])) {
                wp_redirect('?page=nusvg-list');
            }

            ?>
            <p><a href="?page=nusvg-list"><?php _e("Back to list", 'nusvg') ?></a></p>

            <h1><?php _e("Item", 'nusvg') . " " . $_GET['slug'] ?> </h1>
            <?php
            $Nusvg_Item = new Nusvg_Item;
            $Nusvg_Item->slug = $_GET['slug'];
            if ($Nusvg_Item->select() === false) {
                ?><div class="error"><?php _e("not found") ?></div><?php
                return;
            }

            echo esc_html($Nusvg_Item->edition());
        }

        static function update_concat_credits() {
            $path_concat = Nusvg::get_basedir() . '/concat_credits.html';
            $g = glob(Nusvg::get_basedir('credits') . '/*.html');
            if ($g && is_array($g)) {
                $has_credits = false;
                file_put_contents($path_concat, '<ul class="nusvg-concat-credits">');
                foreach ($g as $path) {
                    $credits = file_get_contents($path);
                    if ($credits) {
                        $has_credits = true;
                        file_put_contents($path_concat, '<li>' . $credits . '</li>', FILE_APPEND);
                    }
                }
                if ($has_credits) {
                    file_put_contents($path_concat, '</ul>', FILE_APPEND);
                } else {
                    // empty file
                    file_put_contents($path_concat, '');
                }
            }
        }

        static function rewrite_all() {
            global $wpdb;
            $rows = $wpdb->get_results("SELECT * FROM `" . $wpdb->prefix . "nusvg_items` ORDER BY slug ASC;");
            foreach ($rows as $row) {
                $Nusvg_Item = new Nusvg_Item;
                $Nusvg_Item->row2hydrate($row);
                $Nusvg_Item->process();
                $Nusvg_Item->write();
            }
        }

    }

}