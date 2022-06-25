```php
function next_menus_development()
{
    add_menu_page("Next Plugin", "Next Plugin", "manage_options", "wp-next-plugin", "next_wp_list_call");
    add_submenu_page("wp-next-plugin", "List Students", "List Students", "manage_options", "wp-next-plugin", "next_wp_list_call");
    add_submenu_page("wp-next-plugin", "Addd Student", "Add Student", "manage_options", "wp-next-add", "next_wp_add_call");

}

add_action("admin_menu", "next_menus_development");

function next_wp_list_call()
{
    include_once NEXT_PLUGIN_DIR_PATH . '/views/list_student.php';
    // echo "This is main menu";
}

function next_wp_add_call()
{
    include_once NEXT_PLUGIN_DIR_PATH . '/views/add_student.php';

}

```