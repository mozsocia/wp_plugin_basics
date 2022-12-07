<?php
/*
Plugin Name: moz plugin log
Description: This is a simple plugin for purpose of learning about wordpress CPT
Version: 1.0.0
Author: Mozdalif sikder
 */

add_action('init', 'wpl_owt_cpt_register_movies');

function wpl_owt_cpt_register_movies()
{
    $labels = array(
        'name' => __('Movies'),
        'singular_name' => __('Movie'),
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'movies'),
        // 'show_in_rest' => true,
    );

    register_post_type('movie', $args);
}

if (!function_exists('fiasal_plugin_log')) {
    function isAssoc($arr)
    {
        if (array() === $arr) {
            return false;
        }
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
    function fiasal_plugin_log($entry, $mode = 'a', $file = 'fiasal_plugin_log')
    {
        // Get WordPress uploads directory.
        $upload_dir = wp_upload_dir();
        $upload_dir = $upload_dir['basedir'];
        // If the entry is array, json_encode.
        if (is_array($entry) || isAssoc($entry)) {
            $entry = json_encode($entry);
        }
        if (isAssoc($entry)) {
            $entry = json_encode($entry);
        }
        // Write the log file.
        $file = $upload_dir . '/' . $file . '.log';
        $file = fopen($file, $mode);
        $bytes = fwrite($file, current_time('mysql') . "::" . print_r($entry, true) . "\n\n");
        fclose($file);
        return $bytes;
    }
}

if (!function_exists('moz_plugin_log')) {
    // to use in production site
    function moz_plugin_log($entry, $mode = 'a', $file = 'moz_plugin_log')
    {
        // Get WordPress uploads directory.
        $upload_dir = wp_upload_dir();
        $upload_dir = $upload_dir['basedir'];
        // the entry to json_encode.
        $entry = json_encode($entry);
        // Write the log file.
        $file = $upload_dir . '/' . $file . '.log';
        $file = fopen($file, $mode);
        $bytes = fwrite($file, current_time('mysql') . "::" . print_r($entry, true) . "\n\n");
        fclose($file);
        return $bytes;
    }
}

if (!function_exists('moz_console_log')) {
    function my_console_log(...$data)
    {
        $json = json_encode($data);
        add_action('shutdown', function () use ($json) {
            echo "<script>console.log({$json})</script>";
        });
    }
}

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

function wpl_owt_cpt_save_values($post_id, $post)
{

    $var = "sdfkjsdks";

    my_console_log($post);
    moz_debug_fn(["post_id" => $post_id]);
    moz_debug_fn($var);

    moz_plugin_log($post);

    moz_debug_fn($post);

}

add_action("save_post_movie", "wpl_owt_cpt_save_values", 10, 2);
