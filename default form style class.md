```php

function wpl_owt_cpt_register_metabox()
{

    add_meta_box("cpt-id", "Producer Details", "wpl_owt_cpt_producer_call", "movie", "side", "high");

}

add_action("add_meta_boxes", "wpl_owt_cpt_register_metabox");

function wpl_owt_cpt_producer_call($post)
{
    ?>
    <div class="form-wrap">
        <p class="form-field">
            <label>Name:</label>
            <?php $name = get_post_meta($post->ID, "wpl_producer_name", true)?>
            <input type="text" value="<?php echo $name; ?>" name="txtProducerName" placeholder="Name"/>
        </p>
        <p class="form-field">
            <label>Email:</label>
            <?php $email = get_post_meta($post->ID, "wpl_producer_email", true)?>
            <input type="email" value="<?php echo $email; ?>" name="txtProducerEmail" placeholder="Email"/>
        </p>
    </div>
    
    
    
    
    <table class="form-table" role="presentation">

    <tbody>
        <tr>
            <th scope="row"><label for="blogname">Site Title</label></th>
            <td><input name="blogname" type="text" id="blogname" value="owtprac" class="regular-text"></td>
        </tr>

        <tr>
            <th scope="row"><label for="blogdescription">Tagline</label></th>
            <td><input name="blogdescription" type="text" id="blogdescription" aria-describedby="tagline-description" value="" class="regular-text" placeholder="Just another WordPress site">
            <p class="description" id="tagline-description">In a few words, explain what this site is about.</p></td>
        </tr>
    </tbody>
    </table>

    <?php
}

```

![image](https://user-images.githubusercontent.com/12442613/206927270-3e526247-8fa1-48c5-8f5f-cf4e89d0c48c.png)

