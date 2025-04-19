<?php
    require "../../config/database.php";

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = $_POST["email"];
        $password = $_POST["password"];

        $sql = "SELECT * FROM users WHERE email = '$email'";
        $res = mysqli_query($conn, $sql);
        $user = mysqli_fetch_assoc($res);

        if ($user && password_verify($password, $user['password'])) {
            if ($user['verified'] == 1) {
                session_start();
                $_SESSION['user_id'] = $user['id'];
                header('Location: ../home.php');
            }
            else {
                echo "<script>alert('Please verify your email first.'); window.location.href='verify_otp.php?email=$email';</script>";
            }
        } else {
            echo "<script>alert('Invalid credentials'); window.location.href='../../templates/login.html';</script>";
        }
    }
?>
