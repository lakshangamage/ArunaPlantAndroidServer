<?php

//load and connect to MySQL database stuff
require("config.inc.php");


if (!empty($_POST)) {
    $query = " 
            SELECT * 
            FROM officer 
            WHERE 
                officer_id = :officer_id 
        ";
    $query_params = array(':officer_id' => $_POST['id']);

    $stmt = null;
    try {
        $stmt   = $db->prepare($query);
        $result = $stmt->execute($query_params);
    }
    catch (PDOException $ex) {
        $response["success"] = 0;
        $response["message"] = "Database Error. Please Try Again!";
        die(json_encode($response));

    }
    $row = $stmt->fetch();
    if ($row) {
        $officer_id = $row['officer_id'];
        if ($row['officer_type'] == 0) {
            //customer_officer
            $query = " 
            SELECT * 
            FROM customer 
            WHERE customer.officer_id = :officer_id 
            ";
            $query_params = array(':officer_id' => $_POST['id']);

        } else {
            //manager
            list($region, $area, $uid) = explode("/", $officer_id);
            $query = " 
            SELECT * 
            FROM customer 
            WHERE customer.officer_id LIKE '%".$area."%'
            ";
            $query_params = array(':area_id' => $area);
        }

        $stmt   = $db->prepare($query);
        $result = $stmt->execute($query_params);

        $hasCustomers = false;
        $customerList = array();
        while ($row1 = $stmt->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
            $hasCustomers=true;
            $query = " 
            SELECT * 
            FROM officer 
            WHERE officer_id = :officer_id 
            ";
            $query_params = array(':officer_id' => $row1[5]);
            $stmt2   = $db->prepare($query);
            $result2 = $stmt2->execute($query_params);
            $row2 = $stmt2->fetch();
            $officer_name = "None";
            if ($row2) {
                $officer_name = $row2['officer_name'];
            }
            $row_array['nic'] = $row1[0];
            $row_array['name'] = $row1[1];
            $row_array['address'] = $row1[2];
            $row_array['birthday'] = explode(' ', $row1[3])[0];
            $row_array['mobile'] = "0".$row1[4];
            $row_array['officer_id'] = $row1[5];
            $row_array['officer_name'] = $officer_name;
            array_push($customerList,$row_array);
        }
        if ($hasCustomers) {
            $response["success"] = 1;
            $response["message"] = "Customers Available!";
            $response["customers"] = $customerList;
        } else {
            $response["success"] = 1;
            $response["message"] = "Customers not found";
            $response["customers"] = $customerList;
            die(json_encode($response));
        }

        die(json_encode($response));
    } else {
        $response["success"] = 0;
        $response["message"] = "Officer not found";
        die(json_encode($response));
    }
} else {
    ?>
    <h1>Search Customers</h1>
    <form action="searchcustomer.php" method="post">
        Name:<br />
        <input type="text" name="id" placeholder="id" />
        <br /><br />
        <input type="submit" value="Search" />
    </form>

    <?php
}

?>
