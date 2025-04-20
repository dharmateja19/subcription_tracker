<?php
session_start();
require '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'] ?? null;

    if (!$user_id) {
        die("User not authenticated.");
    }

    $original_name = trim($_POST['original_name']);
    $subscription_name = trim($_POST['subscription_name']);
    $category = trim($_POST['category']);
    $price = floatval($_POST['price']);
    $currency = trim($_POST['currency']);
    $frequency = trim($_POST['frequency']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    if (
        empty($subscription_name) || empty($category) || empty($price) || empty($currency) ||
        empty($frequency) || empty($start_date) || empty($end_date)
    ) {
        die("All fields are required.");
    }

    $sql = "UPDATE subscriptions SET 
                subscription_name = '$subscription_name', 
                category = '$category', 
                price = $price, 
                currency = '$currency', 
                frequency = '$frequency', 
                start_date = '$start_date', 
                end_date = '$end_date'
            WHERE user_id = $user_id AND subscription_name = '$original_name'";

    if (mysqli_query($conn, $sql)) {
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error updating subscription: " . mysqli_error($conn);
    }

    mysqli_close($conn);
} else {
    echo "Invalid request.";
}
?>
