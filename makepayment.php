<?php
/**
 * Created by PhpStorm.
 * User: Lakshan
 * Date: 2017-05-25
 * Time: 2:18 AM
 */

require("config.inc.php");

if(!empty($_POST))
{
    if (empty($_POST['nic']) || empty($_POST['date'])|| empty($_POST['trans_id']) || empty($_POST['amount']) || empty($_POST['officer_id'])) {
        $response["success"] = 0;
        $response["message"] = "Low Details.";
        die(json_encode($response));
    }

    $query = "INSERT INTO bill (customer_nic,bill_datetime,bill_id,bill_amount, officer_id) VALUES ( :nic, :date, :trans_id, :amount, :officer_id)";

    //Again, we need to update our tokens with the actual data:
    $query_params = array(
        ':nic' => $_POST['nic'],
        ':date' => $_POST['date'],
        ':trans_id' => $_POST['trans_id'],
        ':amount' => $_POST['amount'],
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

    $query = " 
            SELECT * 
            FROM officer 
            WHERE officer.officer_id = :officer_id 
            ";
    $query_params = array(':officer_id' => $_POST['officer_id']);
    try {
        $stmt = $db->prepare($query);
        $result = $stmt->execute($query_params);
    }catch (PDOException $ex) {
        $response["success"] = 1;
        $response["message"] = "Transaction Incomplete!";
        die(json_encode($response));
    }

    $row = $stmt->fetch();
    $officer_name = 'Not Found';
    if ($row) {
        $officer_name = $row['officer_name'];
    }

    $query = " 
            SELECT * 
            FROM customer 
            WHERE customer.customer_nic = :nic 
            ";
    $query_params = array(':nic' => $_POST['nic']);
    try {
        $stmt = $db->prepare($query);
        $result = $stmt->execute($query_params);
    }catch (PDOException $ex) {
        $response["success"] = 1;
        $response["message"] = "Transaction Incomplete!";
        die(json_encode($response));
    }

    $row = $stmt->fetch();
    $customer_name = 'Not Found';
    $customer_mobile = '';
    $customer_officer = '';

    if ($row) {
        $customer_name = $row['customer_name'];
        $customer_mobile = $row['customer_mobile'];
        $customer_officer = $row['officer_id'];
    }
    list($date, $time) = explode(" ", $_POST['date']);
    list($region, $area, $uid) = explode("/", $customer_officer);
    $to      = 'apnarunaplant@gmail.com';
    $subject = '[INFO] Bill Number:'.$_POST['trans_id'];
    $message = 'Branch: '.$area.
        "\nOfficer: ".$officer_name.' ('.$_POST['officer_id'].')'.
        "\nBill Number: ".$_POST['trans_id'].
        "\nAmount: Rs.".$_POST['amount'].
        "\nDate: ".$date.
        "\nCustomer Name: ".$customer_name.
        "\nCustomer NIC: ".$_POST['nic'];

    $headers = 'From: webapp@arunaplant.com' . "\r\n" .
        'Reply-To: webapp@arunaplant.com' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();

    mail($to, $subject, $message, $headers);


    $name = str_replace(" ","%20",$customer_name);
    $temp = "Welcome to our Agarwood Family%0AYour payment details-%0ABill number: ".$_POST["trans_id"]."%0AAmount: Rs.".$_POST["amount"]."%0ADate: ".$date."%0AThanks for joining us%0AAruna Plant Nursery";
    $msg = str_replace(" ","%20",$temp);
    $send = "http://203.153.222.25:5000/sms/send_sms.php?username=aruna&password=ARN321&src=Aruna%20Plant&dst=".$customer_mobile."&msg=".$msg."&dr=1";

    try {
        $ch = curl_init();
        if (FALSE === $ch)
            throw new Exception('failed to initialize');
        curl_setopt($ch, CURLOPT_URL, $send);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);

        $head = curl_exec($ch);

        if (FALSE === $head)
            throw new Exception(curl_error($ch), curl_errno($ch));

        curl_close($ch);

    } catch(Exception $e) {
        $response["success"] = 1;
        $response["message"] = "Payment Successful! Error Contacting Customer.";
        curl_close($ch);
        die (json_encode($response));
    }

    $response["success"] = 1;
    $response["message"] = "Payment Successful!";
    die (json_encode($response));
}
else
{
    ?>
    <h1>Payment</h1>
    <form action="makepayment.php" method="post">
        NIC:<br />
        <input type="text" name="nic" value="" />
        <br /><br />
        Date:<br />
        <input type="text" name="date" value="" />
        <br /><br />
        Transaction ID:<br />
        <input type="text" name="trans_id" value="" />
        <br /><br />
        Amount:<br />
        <input type="text" name="amount" value="" />
        <br /><br />
        Officer ID:<br />
        <input type="text" name="officer_id" value="" />
        <br /><br />
        <input type="submit" value="Add Payment" />
    </form>
    <?php
}
?>