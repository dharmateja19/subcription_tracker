<?php
    require "../../config/database.php";

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = $_POST['email'];
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $otp_expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

        $update = "UPDATE users SET otp='$otp', otp_expiry='$otp_expiry' WHERE email='$email'";
        if (mysqli_query($conn, $update)) {
            $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM users WHERE email='$email'"));
            $name = $user['name'];

            $subject = "Resent OTP Code";
            $message = "Hi $name,\n\nYour new OTP is: $otp\nIt expires in 10 minutes.";

            mail($email, $subject, $message);

            echo "<script>alert('OTP resent successfully'); window.location.href='verify_otp.php?email=$email';</script>";
        } else {
            echo "<script>alert('Error resending OTP'); window.location.href='../../templates/login.html';</script>";
        }
    }
?>