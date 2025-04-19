<?php
    require "../../config/database.php";

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $name = $_POST["name"];
        $email = $_POST["email"];
        $mobile = $_POST["mobile_no"];
        $password = $_POST["password"];
        $pass_hash = password_hash($password, PASSWORD_DEFAULT);

        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $otp_expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

        $check = "SELECT * FROM users WHERE email = '$email'";
        
        $res = mysqli_query($conn, $check);
        if (mysqli_num_rows($res) > 0) {
            echo "<script>alert('Email already registered. Please login.'); window.location.href='/sub-tracker/templates/login.html';</script>";
            exit;
        }

        $sql = "INSERT INTO users (name, email, mobile, password, otp, otp_expiry, verified)
                VALUES ('$name', '$email', '$mobile', '$pass_hash', '$otp', '$otp_expiry', 0)";
        
        if (mysqli_query($conn, $sql)) {
            $subject = "Your OTP Verification Code";
            $message = "Hi $name,\n\nYour OTP code is: $otp\nIt expires in 10 minutes.";

            mail($email, $subject, $message);
            header("Location: verify_otp.php?email=$email");
            exit;
        } 
        else {
            echo "Error: " . mysqli_error($conn);
        }
    }
?>