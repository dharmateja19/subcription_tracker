<?php
session_start();
require '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'] ?? null;

    if (!$user_id) {
        die("User not authenticated.");
    }

    $subscription_name = $_POST['subscription_name'];

    if (empty($subscription_name)) {
        die("Subscription name is required.");
    }

    $sql = "DELETE FROM subscriptions WHERE user_id = $user_id AND subscription_name = '$subscription_name'";

    if (mysqli_query($conn, $sql)) {
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error deleting subscription: " . mysqli_error($conn);
    }

    mysqli_close($conn);
} else {
    echo "Invalid request.";
}
?>
