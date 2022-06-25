### list-student.php
```php
<?php
global $wpdb;

/**
 * Delete operation
 */
$action = isset($_GET['action']) ? trim($_GET['action']) : "";
$id = isset($_GET['id']) ? intval($_GET['id']) : "";

if (!empty($action) && $action == "delete") {

    $row_exists = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * from wp_next_plugin_tbl WHERE id = %d", $id
        )
    );

    if (!empty($row_exists)) {
        $wpdb->delete("wp_next_plugin_tbl", array(
            "id" => $id,
        ));
    }
}

/**
 * List operation add get list data from database
 */

$all_students = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * from wp_next_plugin_tbl", ""
    ), ARRAY_A
);

if (count($all_students) > 0) {
    ?>
    <table cellpadding="10" border="1">
        <tr>
            <th>Sr No</th>
            <th>Name</th>
            <th>Email</th>
            <th>Action</th>
        </tr>
        <?php

    foreach ($all_students as $index => $student) {
        ?>
            <tr>
                <td><?php echo $index + 1; ?></td>
                <td><?php echo $student['name'] ?></td>
                <td><?php echo $student['email'] ?></td>
                <td>
                    <a href=<?php echo "admin.php?page=wp-next-add&action=edit&id=" . $student['id']; ?>>Edit</a>
                    <a href=<?php echo "admin.php?page=wp-next-plugin&id=" . $student['id'] . "&action=delete"; ?>
                    onclick="return confirm('Are you sure want to delete?')"
                    >Delete</a>

                </td>
            </tr>
            <?php
}
    ?>
    </table>

    <?php
}
?>
```

### add-student.php

```php

<?php
global $wpdb;
$msg = '';

/**
 * code for update and insert
 */
$action = isset($_GET['action']) ? trim($_GET['action']) : "";
$id = isset($_GET['id']) ? intval($_GET['id']) : "";

if (isset($_POST['btnsubmit'])) {

    if (!empty($action)) {

        $wpdb->update("wp_next_plugin_tbl", array(
            "name" => $_POST['txtname'],
            "email" => $_POST['txtemail'],
        ), array(
            "id" => $id,
        ));

        $msg = "Form data updated successfully";
    } else {

        $wpdb->insert("wp_next_plugin_tbl", array(
            "name" => $_POST['txtname'],
            "email" => $_POST['txtemail'],
        ));

        if ($wpdb->insert_id > 0) {
            $msg = "Form data saved successfully";
        } else {
            $msg = "Failed to save data";
        }

        ?>
        <script>
            location.href = "<?php echo site_url() ?>/wp-admin/admin.php?page=wp-next-plugin";
        </script>
    <?php
}
}

/**
 * code for reading the data to update
 */
if (!empty($action)) {
    $row_details = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * from wp_next_plugin_tbl WHERE id = %d", $id
        ), ARRAY_A
    );
    print_r("hello" . $row_details);
}

?>

<p><?php echo $msg; ?></p>
<form
action=<?php echo ($_SERVER['PHP_SELF'] . "?page=wp-next-add" . (!empty($action) ? "&action=edit&id=" . $id : "")) ?>   method="post">

    <p>
        <label>
            Name
        </label>
        <input type="text" name="txtname" value="<?php echo isset($row_details['name']) ? $row_details['name'] : ""; ?>" placeholder="Enter name"/>
    </p>
    <p>
        <label>
            Email
        </label>
        <input type="email" name="txtemail" value="<?php echo isset($row_details['email']) ? $row_details['email'] : ""; ?>" placeholder="Enter email"/>
    </p>
    <p>
        <button type="submit" name="btnsubmit">Submit</button>
    </p>
</form>

```