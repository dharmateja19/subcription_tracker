<?php
    require "../../config/database.php";

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = $_POST['email'];
        $otp = $_POST['otp'];

        $sql = "SELECT * FROM users WHERE email='$email' AND otp='$otp'";
        $res = mysqli_query($conn, $sql);
        $user = mysqli_fetch_assoc($res);

        if ($user) {
            if (date("Y-m-d H:i:s") > $user['otp_expiry']) {
                echo "<script>alert('OTP expired'); window.location.href='verify_otp.php?email=$email';</script>";
            } 
            else {
                $update = "UPDATE users SET verified=1, otp=NULL, otp_expiry=NULL WHERE email='$email'";
                mysqli_query($conn, $update);
                echo "<script>alert('Email verified successfully!'); window.location.href='/sub-tracker/templates/login.html';</script>";
            }
        } 
        else {
            echo "<script>alert('Invalid OTP'); window.location.href='verify_otp.php?email=$email';</script>";
        }
    }
?>