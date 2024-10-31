<?php

/*
  Plugin Name: nusvg
  Description: SVG manager
  Version: 0.1
  Author: nuweb
 */


defined('ABSPATH') or die('No script kiddies please!');

require_once('classes/class-nusvg.php');

if (is_admin()) {
    require_once('classes/class-nusvg-plugin.php');
    require_once('classes/class-nusvg-admin.php');
    register_activation_hook(__FILE__, 'Nusvg_Plugin::activation');
    register_uninstall_hook(__FILE__, 'Nusvg_Plugin::uninstall');
    Nusvg_Plugin::check_version();
    add_action('init', function() {
    });
    add_action('admin_menu', function() {
        Nusvg_Admin::init();
        Nusvg_Admin::menu();
    });
}
