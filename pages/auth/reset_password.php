<?php
    $email = $_GET['email'];
    $token = $_GET['token'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
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
        form {
            background: #fff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        h2 {
            text-align: center;
            margin-bottom: 1rem;
        }
        label {
            font-weight: 500;
        }
        input[type="password"] {
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1rem;
        }
        .toggle-show {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }
        .error-msg {
            color: red;
            font-size: 0.9rem;
            margin-top: -15px;
        }
        button {
            padding: 0.75rem;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <form method="POST" action="update_password.php" onsubmit="return validatePassword(event)">
        <h2>Reset Your Password</h2>
        
        <input type="hidden" name="email" value="<?= $email ?>">
        <input type="hidden" name="token" value="<?= $token ?>">

        <label for="password">New Password</label>
        <input type="password" name="password" id="password" required>
        <div id="password-error" class="error-msg"></div>

        <label for="confirm_password">Confirm New Password</label>
        <input type="password" name="confirm_password" id="confirm_password" required>
        <div id="confirm-password-error" class="error-msg"></div>

        <label class="toggle-show">
            <input type="checkbox" onclick="togglePassword()"> Show Password
        </label>

        <button type="submit">Update Password</button>
    </form>

    <script>
        function togglePassword() {
            const pwd = document.getElementById("password");
            const confirmPwd = document.getElementById("confirm_password");
            const type = pwd.type === "password" ? "text" : "password";
            pwd.type = type;
            confirmPwd.type = type;
        }

        function validatePassword(e) {
            const password = document.getElementById("password").value;
            const confirmPassword = document.getElementById("confirm_password").value;
            const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,12}$/;
            let valid = true;

            // Reset error messages
            document.getElementById("password-error").textContent = "";
            document.getElementById("confirm-password-error").textContent = "";

            // Check if passwords match
            if (password !== confirmPassword) {
                document.getElementById("confirm-password-error").textContent = "Passwords do not match!";
                valid = false;
            }

            // Check password strength
            if (!passwordRegex.test(password)) {
                document.getElementById("password-error").textContent = "Password must be 8-12 characters and include:\n- One uppercase letter\n- One lowercase letter\n- One digit\n- One special character";
                valid = false;
            }

            return valid;
        }
    </script>
</body>
</html>