<?php
$statusMessage = '';
$goBackButton = '';

if (isset($_GET['status'])) {
    if ($_GET['status'] === 'success') {
        $statusMessage = "<p class='success-msg'>Reset link sent successfully.</p>";
        $goBackButton = "<a href='../../templates/login.html'><button type='button'>Go Back to Login</button></a>";
    } elseif ($_GET['status'] === 'error') {
        $statusMessage = "<p class='error-msg'>Email not found.</p>";
        $goBackButton = "<a href='../../templates/login.html'><button type='button'>Go Back to Login</button></a>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #fdfcfb, #e2d1c3);
            color: #333;
            line-height: 1.6;
        }

        .reset-form {
            max-width: 400px;
            margin: 80px auto;
            padding: 30px;
            background-color: #f8f9fa;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            font-family: 'Segoe UI', sans-serif;
        }

        .reset-form label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #333;
        }

        .reset-form input[type="email"] {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        .reset-form button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }

        .reset-form button:hover {
            background-color: #0056b3;
        }

        .success-msg {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
        }

        .error-msg {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
        }
    </style>
</head>
<body>

<?php if (empty($statusMessage)): ?>
    <form class="reset-form" method="POST" action="send_reset_link.php">
        <label for="email">Enter your email:</label>
        <input type="email" name="email" id="email" required>
        <button type="submit">Send Reset Link</button>
    </form>
<?php else: ?>
    <div class="reset-form">
        <?= $statusMessage ?>
        <?= $goBackButton ?>
    </div>
<?php endif; ?>

</body>
</html>