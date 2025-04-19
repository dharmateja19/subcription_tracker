<?php $email = $_GET['email']; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Email</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #fdfcfb, #e2d1c3);
            color: #333;
            line-height: 1.6;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .verify-container {
            background: #fff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
            align-items: center;
        }

        h2 {
            margin: 0;
            text-align: center;
        }

        form {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        label {
            font-weight: 500;
        }

        input[type="text"] {
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1rem;
            width: 100%;
        }

        button {
            padding: 0.75rem;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        .resend-form {
            margin-top: -0.5rem;
            text-align: center;
        }

        .resend-form button {
            background-color: #6c757d;
        }

        .resend-form button:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>

<div class="verify-container">
    <h2>Verify Your Email</h2>

    <form action="verify_otp_process.php" method="POST">
        <input type="hidden" name="email" value="<?= $email ?>">
        <label>Enter OTP:</label>
        <input type="text" name="otp" maxlength="6" required>
        <button type="submit">Verify</button>
    </form>

    <form action="resend_otp.php" method="POST" class="resend-form">
        <input type="hidden" name="email" value="<?= $email ?>">
        <button type="submit">Resend OTP</button>
    </form>
</div>

</body>
</html>