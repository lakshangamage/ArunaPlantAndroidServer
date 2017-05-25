<?php
/**
 * Created by PhpStorm.
 * User: Lakshan
 * Date: 2017-05-25
 * Time: 2:02 AM
 */
require("config.inc.php");

if(!empty($_POST))
{
    if (empty($_POST['nic']) || empty($_POST['name'])|| empty($_POST['officer_id']) || empty($_POST['mobile']) || empty($_POST['address'])) {

        $response["success"] = 0;
        $response["message"] = "Low Details.";
        die(json_encode($response));
    }

    $query = "INSERT INTO customer (customer_nic,customer_name,customer_birthday,customer_mobile,customer_address,officer_id) VALUES ( :nic, :name, :birthday, :mobile, :address, :officer_id) ";

    //Again, we need to update our tokens with the actual data:
    $query_params = array(
        ':nic' => $_POST['nic'],
        ':name' => $_POST['name'],
        ':birthday' => $_POST['birthday'],
        ':mobile' => $_POST['mobile'],
        ':address' => $_POST['address'],
        ':officer_id' => $_POST['officer_id']
    );

    try {
        $stmt   = $db->prepare($query);
        $result = $stmt->execute($query_params);
    }
    catch (PDOException $ex) {
        $response["success"] = 0;
        $response["message"] = "Database Error. Please Try Again!";
        die(json_encode($response));
    }

    $response["success"] = 1;
    $response["message"] = "Customer Successfully Added!";
    die (json_encode($response));
}
else
{
    ?>
    <h1>Register</h1>
    <form action="addcustomer.php" method="post">
        NIC:<br />
        <input type="text" name="nic" value="" />
        <br /><br />
        Name:<br />
        <input type="text" name="name" value="" />
        <br /><br />
        Birthday:<br />
        <input type="text" name="birthday" value="" />
        <br /><br />
        Mobile:<br />
        <input type="text" name="mobile" value="" />
        <br /><br />
        Address:<br />
        <input type="text" name="address" value="" />
        <br /><br />
        Officer ID:<br />
        <input type="text" name="officer_id" value="" />
        <br /><br />
        <input type="submit" value="Add New Customer" />
    </form>
    <?php
}
?>