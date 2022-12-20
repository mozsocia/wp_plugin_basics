https://www.youtube.com/watch?v=AHRJreCIdT0  <br>


https://developer.wordpress.org/plugins/users/roles-and-capabilities/#adding-capabilities  <br>

https://wpmudev.com/blog/wordpress-roles-capabilities/ <br>


https://usersinsights.com/get_user-functions/


https://rudrastyh.com/wordpress/get-user-id.html

### common way of getting user data
```php
    global $user_ID;
    global $current_user;

    $userdetails = get_userdata($user_ID);

    $user_id = get_current_user_id();

// Get the current logged-in user's data as an object
    $user = wp_get_current_user();
    
    $author_obj = get_user_by('id', 1);
```

