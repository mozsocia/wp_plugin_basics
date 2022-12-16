<?php
/*
Plugin Name: Moz Test Plugin
Description: Simple Plugin shows the  form submission
Version: 1.0.0
Author: Mozdalif Sikder
 */

//
// ========== some default defination ====================
define("MOZ_PLUGIN_DIR_PATH", plugin_dir_path(__FILE__));

if (!function_exists('moz_debug_fn')) {
    function moz_debug_fn($data)
    {
        // to theme directory
        // $file = get_stylesheet_directory() .'/coutom_log.txt';
        // to this plugin dir
        $file = plugin_dir_path(__FILE__) . '/custom_log.txt';
        file_put_contents($file, current_time('mysql') . " :: " . print_r($data, true) . "\n\n", FILE_APPEND);
    };
}
;

//
// ======================= main code start ===========================

//
// ====== setting admin_menu =============
function moz_menus_development()
{
    add_menu_page("Moz Plugin", "Moz Plugin", "manage_options", "wp-moz-plugin", "moz_wp_list_call");

    add_submenu_page("wp-moz-plugin", "Addd Some", "Add Some", "manage_options", "wp-moz-add", "moz_wp_add_call");
}

add_action("admin_menu", "moz_menus_development");

function moz_wp_list_call()
{
    include_once MOZ_PLUGIN_DIR_PATH . '/views/list-some.php';
}

function moz_wp_add_call()
{
    include_once MOZ_PLUGIN_DIR_PATH . '/views/add-some.php';
}

//
// ========== ajax call code must be in main plugin file

add_action('wp_ajax_my_ajax_form_action', 'my_ajax_form_handler');
add_action('wp_ajax_nopriv_my_ajax_form_action', 'my_ajax_form_handler');

function my_ajax_form_handler()
{

    // if (!isset($_POST['_moz_nonce']) || !wp_verify_nonce($_POST['_moz_nonce'], 'moz_nonce_secret')) {
    //     wp_send_json('invalid request ');
    //     return;
    // }

    // Verify the nonce
    check_ajax_referer('moz_nonce_secret', '_moz_nonce');

    moz_debug_fn([$_POST]);

    // Do something with the data (e.g. save to database, send email, etc.)

    // Return a response
    wp_send_json(['msg' => 'Form submitted successfully!']);
}
