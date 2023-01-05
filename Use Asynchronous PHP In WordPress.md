


https://github.com/deliciousbrains/wp-background-processing

https://github.com/techcrunch/wp-async-task



https://torquemag.io/2016/01/use-asynchronous-php-wordpress/
example code from  torquemag.io 
```php
<?php
require_once('./wp_async_task.php');

class Josh_Task extends WP_Async_Task {

    /**
    * Action to use to trigger this task
    *
    * @var string
    */
    protected $action = 'save_post';

    /**
    * Prepare POST data to send to session that processes the task
    *
    * @param array $data Params from hook
    *
    * @return array
    */
    protected function prepare_data($data){
        return array(
            'post_id' => $data[0]
        );
    }

    /**
    * Run the asynchronous task
    *
    * Calls send_to_api()
    */
    protected function run_action() {
        if( isset( $_POST[ 'post_id' ] ) && 0 < absint( $_POST[ 'post_id' ] ) ){
            do_action( "wp_async_$this->action", $_POST[ 'post_id' ], get_post( $_POST[ 'post_id' ] ) );
        }

    }

}



add_action( 'wp_async_save_post', 'josh_send_to_api' );
function josh_send_to_api( $id ) {
    $thing = get_post_meta( $id, 'something', true );
    $r = wp_safe_remote_post( add_query_arg( 'id', $thing, 'http://apiexample.com/' ) );
    if ( ! is_wp_error( $r ) ) {
        $body = json_decode( wp_remote_retrieve_body( $r ) ) ;
        if ( isset( $body->key ) ) {
            update_post_meta( $id, 'api_response', $body->key );
        } else {
            update_post_meta( $id, 'api_response', 'none' );
        }

    }

}

```
