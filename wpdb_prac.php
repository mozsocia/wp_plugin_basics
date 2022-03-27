<?php
/**
 * Plugin Name:       wpdb_prac
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
 * Text Domain:       moz
 **/
/*
$wpdb->get_var("Write Query");
$wpdb->get_row("Write Query", Format_parameter);
$wpdb->get_col("Write Query");
$wpdb->get_results("Write Query", Format_parameter);
*/
function moz_db_admin_menu()
{
    add_menu_page(
        __('Db Menu', 'my—plugin'),
        __('Db Menu', 'my—plugin'),
        'manage_options',
        'db-menu',
        'moz_db_menu_template_callback',
        'dashicons-carrot',
        10
    );
}
add_action('admin_menu', 'moz_db_admin_menu');

function moz_db_menu_template_callback()
{

    ?>
    <h2>Db Prac Menu</h2>
    <?php

}

function wpdb_prac_all_fn()
{
        global $wpdb;
        $result2 = $wpdb->get_results("SELECT * FROM  {$wpdb->prefix}posts LIMIT 10");


        $sin = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT post_title FROM {$wpdb->posts} WHERE id = %d", 2
            )
        );
        // output:  Sample Page

        $row1 = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->posts} WHERE id = %d", 2
            ), ARRAY_A
        );
        // output
        // stdClass Object(
        //     [ID] => 2
        //     [post_author] => 1
        //     [post_date] => 2022-01-11 06:11:21
        //     [post_date_gmt] => 2022-01-11 06:11:21
        //     [post_content] => 
        //     This is an example page.

        //     [post_title] => Sample Page
        //     [post_excerpt] => 
        //     [post_status] => publish
        //     [comment_status] => closed
        //     [ping_status] => open
        //     [post_password] => 
        //     [post_name] => sample-page
        //     [to_ping] => 
        //     [pinged] => 
        //     [post_modified] => 2022-01-11 06:11:21
        //     [post_modified_gmt] => 2022-01-11 06:11:21
        //     [post_content_filtered] => 
        //     [post_parent] => 0
        //     [guid] => http://first_pro.test/?page_id=2
        //     [menu_order] => 0
        //     [post_type] => page
        //     [post_mime_type] => 
        //     [comment_count] => 0
        // )


        
        $col1 = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT post_title FROM {$wpdb->posts} WHERE post_type = %s", "post"
            )
        );





        $wpdb->insert(
            $wpdb->prefix."posts", array(
            "post_title" => "Sample Post12", 
            "post_content" => "This is sample content of this post",
            "post_name" => "sample-post",
            "post_status" => "publish"
            ), array(
            "%s",
            "%s",
            "%s",
            "%s"
            )
        );



        $number_of_row_inserted = $wpdb->insert(
            $wpdb->prefix."posts", array(
            "post_title" => "Sample Post123", 
            "post_content" => "This is sample content of this post",
            "post_name" => "sample-post",
            "post_status" => "publish"
            )
        );

        echo $number_of_row_inserted . "<br/>";
    
        echo $wpdb->insert_id; // It prints the return row ID after insertion.




        /*
        $wpdb->update($table, $data, $where, $format = null, $where_format = null);
        */

        $wpdb->update( 
            $wpdb->prefix."posts", 
            array(
            "post_title" => "Updated Post Title", 
            "post_content" => "This is sample content update of this post", 
            "post_name" => "my-updated-post", 
            "post_excerpt" => "Sample content update"
            ), 
            array("ID" => 4), 
            array("%s", "%s", "%s","%s"), 
            array("%d") 
        );


        /*
        $wpdb->delete(string $table, array $where, array|string $where_format = null)
        */
        $wpdb->delete(
            $wpdb->prefix."posts",
            array("ID" => 4),
            array("%d")
        );


        $post_id = $_POST['post_id'];
        $key     = $_POST['meta_key'];

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $wpdb->postmeta
                    WHERE post_id = %d
                    AND meta_key = %s",
                $post_id,
                $key
            )
        );


        echo "<pre>";
        print_r($col1);
        echo "</pre>";


        
    


}
        
