<?php

/**
 * Plugin Name:       my_shortcode_prac
 * Plugin URI:        https://example.com/plugins/the-basics/
 * Description:       Handle the basics with this plugin.
 * Version:           1.10.3
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            John Mozdalif
 * Author URI:        https://author.mozdalif.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 * Text Domain:       my-sh
 **/

 
if (!defined('ABSPATH')) : exit();
endif;

/**
 * define coustom constant
 */

define('MYSH_PATH', trailingslashit(plugin_dir_path(__FILE__)));
define('MYSH_URL', trailingslashit(plugins_url('/', __FILE__)));


add_shortcode('owt', 'owt_shcode_part1');

function owt_shcode_part1()
{
    return "My shortcode first practice ";
}


add_shortcode('owt-file', 'owt_shcode_part2');

function owt_shcode_part2()
{
    include_once MYSH_PATH."/views/owt-sh-panel.php";
}


add_shortcode('owt-paylist', "owt_shcode_part3");
// [owt-paylist name= "mozdalif"  age = "23"] this is the content [/owt-paylist]
function owt_shcode_part3($atts, $content)
{
    $atts2 = shortcode_atts(
        array( // this is how to define defult value for parameters
        "name" => "No name Defined",
        "age" => "No age Defined",
        "address" => "No address Defined",
        ), $atts
    );
    
    echo "name : ". esc_attr($atts2['name']);
    echo "age : " . esc_attr($atts2['age']);
    echo "content" . esc_attr($content);
}
