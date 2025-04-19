<?php
require "../../config/database.php";
date_default_timezone_set('Asia/Kolkata'); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $token = $_POST['token'];
    $password = $_POST['password'];
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $sql = "SELECT * FROM users WHERE email = '$email' AND reset_token = '$token'";
    $res = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($res);

    if ($user) {
        $current_time = date('Y-m-d H:i:s');
        $token_expiry = $user['token_expiry'];

        if ($token_expiry >= $current_time) {
            $update = "UPDATE users 
                       SET password = '$hashed', reset_token = NULL, token_expiry = NULL 
                       WHERE email = '$email'";
            if (mysqli_query($conn, $update)) {
                echo "<script>alert('Password updated successfully. Please login.'); window.location.href='/sub-tracker/templates/login.html';</script>";
            } else {
                echo "Error updating password: " . mysqli_error($conn);
            }
        } else {
            echo "<script>alert('Reset link expired. Please try again.'); window.location.href='forgot_password.php';</script>";
        }
    } else {
        echo "<script>alert('Invalid reset link.'); window.location.href='forgot_password.php';</script>";
    }
}
?>