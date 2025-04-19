<?php
require '../config/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subscription_name = $_POST['subscription_name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $currency = $_POST['currency'];
    $start_date = $_POST['start_date'];
    $frequency = $_POST['frequency'];
    $end_date = $_POST['end_date'];
    $user_id = $_SESSION['user_id'];

    $check = "SELECT * FROM subscriptions WHERE subscription_name = '$subscription_name' AND user_id = '$user_id'";
    $res = mysqli_query($conn, $check);

    if (mysqli_num_rows($res) > 0) {
        $existing = mysqli_fetch_assoc($res);

        if ($existing['status'] === 'expired' && $start_date > $existing['end_date']) {
            date_default_timezone_set("Asia/Kolkata");
            $today = date("Y-m-d");
            $status = ($end_date >= $today) ? "active" : "expired";

            $sql = "INSERT INTO subscriptions (user_id,subscription_name,category,price,currency,frequency,start_date,end_date,status) VALUES ('$user_id','$subscription_name','$category','$price','$currency','$frequency','$start_date','$end_date','$status')";
            if (mysqli_query($conn, $sql)) {
                $_SESSION['msg'] = "Subscription added successfully.";
                $_SESSION['msg_type'] = "success";
            } else {
                $_SESSION['msg'] = "Error while inserting subscription.";
                $_SESSION['msg_type'] = "error";
            }
        } else {
            $_SESSION['msg'] = "Subscription already exists. Cannot add now.";
            $_SESSION['msg_type'] = "error";
        }
    } else {
        date_default_timezone_set("Asia/Kolkata");
        $today = date("Y-m-d");
        $status = ($end_date >= $today) ? "active" : "expired";

        $sql = "INSERT INTO subscriptions (user_id,subscription_name,category,price,currency,frequency,start_date,end_date,status) VALUES ('$user_id','$subscription_name','$category','$price','$currency','$frequency','$start_date','$end_date','$status')";
        if (mysqli_query($conn, $sql)) {
            $_SESSION['msg'] = "Subscription added successfully.";
            $_SESSION['msg_type'] = "success";
        } else {
            $_SESSION['msg'] = "Error while inserting subscription.";
            $_SESSION['msg_type'] = "error";
        }
    }

    header("Location: dashboard.php");
    exit;
}
?>