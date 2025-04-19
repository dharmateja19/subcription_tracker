<?php
require "../../config/database.php";
date_default_timezone_set('Asia/Kolkata');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $check = "SELECT * FROM users WHERE email = '$email'";
    $res = mysqli_query($conn, $check);

    if (mysqli_num_rows($res) === 1) {
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+30 minutes'));

        $sql = "UPDATE users SET reset_token = '$token', token_expiry = '$expiry' WHERE email = '$email'";
        mysqli_query($conn, $sql);

        $link = "http://localhost/project/reset_password.php?email=$email&token=$token";
        mail($email, "Password Reset", "Click the link to reset your password: $link");

        header("Location: forgot_password.php?status=success");
        exit();
    } else {
        header("Location: forgot_password.php?status=error");
        exit();
    }
}
?>