<?php
require '../config/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: home.php");
}
$isLoggedIn = isset($_SESSION['user_id']); 
$statusMessage = '';
if (isset($_SESSION['msg'])) {
    $msgClass = ($_SESSION['msg_type'] === 'success') ? 'success-msg' : 'error-msg';
    $statusMessage = "<div id='statusMsg' class='$msgClass'>{$_SESSION['msg']}</div>";
    unset($_SESSION['msg']);
    unset($_SESSION['msg_type']);
}

$user_id = $_SESSION['user_id'];

$userQuery = "SELECT name, email, mobile FROM users WHERE id = '$user_id'";
$userResult = mysqli_query($conn, $userQuery);
if (!$userResult) {
    die("User query failed: " . mysqli_error($conn));
}
$user = mysqli_fetch_assoc($userResult);
$today = date('Y-m-d');

$updateStatusQuery = "
  UPDATE subscriptions 
  SET status = 'expired' 
  WHERE user_id = '$user_id' AND end_date < '$today' AND status != 'expired'
";
mysqli_query($conn, $updateStatusQuery);

$activateStatusQuery = "
  UPDATE subscriptions 
  SET status = 'active' 
  WHERE user_id = '$user_id' AND start_date <= '$today' AND end_date >= '$today'
";
mysqli_query($conn, $activateStatusQuery);

$subQuery = "SELECT subscription_name, start_date, end_date, status FROM subscriptions WHERE user_id = '$user_id'";
$subResult = mysqli_query($conn, $subQuery);
if (!$subResult) {
    die("Subscription query failed: " . mysqli_error($conn));
}

$totalSubscriptions = mysqli_num_rows($subResult);
$activeCount = 0;
$expiredCount = 0;
$subscriptions = [];

while ($sub = mysqli_fetch_assoc($subResult)) {
    $subscriptions[] = $sub;
    if ($sub['end_date'] >= $today && $sub['start_date'] <= $today) {
        $activeCount++;
    } elseif ($sub['end_date'] < $today) {
        $expiredCount++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Subscription Dashboard</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      transition: background 0.3s, color 0.3s;
    }

    body.light-theme {
      background: #f5f7fa;
      color: #222;
      --card-bg: #ffffff;
      --card-hover-bg: #eaeaea;
    }

    body.dark-theme {
      background: #121212;
      color: #eee;
      --card-bg: #1e1e1e;
      --card-hover-bg: #2c2c2c;
    }

    .top-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 20px 40px;
    }

    .top-bar h1 {
      font-size: 1.8rem;
      color: #ff6f61;
      margin: 0;
    }

    .theme-toggle button {
      padding: 8px 16px;
      background: #ff6f61;
      border: none;
      border-radius: 8px;
      color: white;
      font-weight: bold;
      cursor: pointer;
    }

    .dashboard {
      display: flex;
      flex-wrap: wrap;
      gap: 30px;
      padding: 20px 40px;
      justify-content: center;
    }

    .user-info {
      flex: 1;
      min-width: 280px;
      max-width: 350px;
      padding: 30px;
      border-radius: 20px;
      background: var(--card-bg);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.07);
    }

    .user-info h2 {
      color: #ff6f61;
      margin-bottom: 15px;
    }

    .user-info p {
      font-size: 1rem;
      margin: 10px 0;
    }

    .subscriptions {
      flex: 2;
      min-width: 400px;
    }

    .subscriptions h2 {
      margin-bottom: 20px;
      font-size: 1.5rem;
      color: #ff6f61;
    }

    .subscription-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
    }

    .card {
      border-left: 6px solid transparent;
      border-radius: 16px;
      padding: 20px;
      background: var(--card-bg);
      transition: transform 0.2s ease, background-color 0.3s ease;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
      cursor: pointer;
    }

    .card:hover {
      transform: translateY(-5px);
      background-color: var(--card-hover-bg);
    }

    .card h3 {
      margin-top: 10px;
    }

    .card p {
      font-size: 0.95rem;
      margin: 6px 0;
    }

    .card img {
      width: 40px;
      height: 40px;
      object-fit: contain;
      display: block;
    }

    .active {
      border-left: 6px solid #4CAF50;
    }

    .expired {
      border-left: 6px solid #f44336;
    }

    .add-sub-btn {
        margin-top: 20px;
        padding: 10px 20px;
        background-color: #ff6f61;
        color: white;
        font-size: 1rem;
        font-weight: bold;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.2s ease;
    }

    .add-sub-btn:hover {
        background-color: #ff3b2f;
        transform: scale(1.05);
    }

    .success-msg, .error-msg {
      padding: 12px;
      margin: 15px 40px 0 40px;
      border-radius: 8px;
      font-weight: bold;
      opacity: 1;
    }

    .success-msg {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .error-msg {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }
    .filter-buttons {
      margin-bottom: 20px;
    }

    .filter-buttons button {
      margin-right: 10px;
      padding: 8px 16px;
      border: none;
      border-radius: 8px;
      font-weight: bold;
      cursor: pointer;
      background-color: #ff6f61;
      color: white;
    }

    .filter-buttons button.active-btn {
      background-color: #555;
    }
    header {
    background: rgba(255, 255, 255, 0.85);
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 50px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    position: sticky;
    top: 0;
    z-index: 1000;
  }
  
  .logo {
    font-size: 1.8rem;
    font-weight: 600;
    color: #ff6f61;
  }
  
  nav a {
    margin-left: 24px;
    text-decoration: none;
    color: #333;
    font-weight: 500;
    transition: color 0.3s ease;
  }
  
  nav a:hover {
    color: #ff6f61;
  }

    footer {
      text-align: center;
      padding: 30px 20px;
      background: rgba(255, 255, 255, 0.85);
      color: #555;
      font-size: 0.9rem;
    }
  </style>
</head>
<body class="light-theme">
<header>
    <div class="logo">Subscription Tracking</div>
    <nav>
      <a href="home.php">Home</a>
      <?php if ($isLoggedIn): ?>
        <a href="dashboard.php">Dashboard</a>
        <a href="./auth/logout.php">Logout</a>
      <?php else: ?>
        <a href="../templates/login.html">Login</a>
        <a href="../templates/register.html">Register</a>
      <?php endif; ?>
    </nav>
  </header>
  <div class="top-bar">
    <h1>Subscription Dashboard</h1>
    <div class="theme-toggle">
      <button id="toggleTheme">🌙 Switch Theme</button>
    </div>
  </div>

  <?= $statusMessage ?>

  <div class="dashboard">
    <div class="user-info">
      <h2><?= htmlspecialchars($user['name']) ?></h2>
      <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
      <p><strong>Mobile:</strong> <?= htmlspecialchars($user['mobile']) ?></p>
      <p><strong>Total Subscriptions:</strong> <?= $totalSubscriptions ?></p>
      <p><strong>Active:</strong> <?= $activeCount ?></p>
      <p><strong>Expired:</strong> <?= $expiredCount ?></p>

      <a href="../templates/add_subscription.html"><button class="add-sub-btn">➕ Add Subscription</button></a>
    </div>

    <div class="subscriptions">
      <h2>Your Subscriptions</h2>
      <div class="filter-buttons">
        <button class="active-btn" onclick="filterCards('all')">View All</button>
        <button onclick="filterCards('active')">Active</button>
        <button onclick="filterCards('expired')">Expired</button>
      </div>
      <div class="subscription-cards">
        <?php foreach ($subscriptions as $subscription): 
          $isActive = ($subscription['end_date'] >= $today && $subscription['start_date'] <= $today);
          $isExpired = ($subscription['end_date'] < $today);
          $cardClass = $isActive ? 'active' : ($isExpired ? 'expired' : '');
          $subName = strtolower(trim($subscription['subscription_name']));
          $logoURL = "https://logo.clearbit.com/$subName.com";
          $statusColor = ($subscription['status'] === 'active') ? 'green' : 'red';
        ?>
          <div class="card <?= $cardClass ?>">
            <img src="<?= $logoURL ?>" alt="<?= $subName ?> logo" onerror="this.src='default.png'">
            <h3><?= htmlspecialchars($subscription['subscription_name']) ?></h3>
            <p><strong>Start:</strong> <?= htmlspecialchars($subscription['start_date']) ?></p>
            <p><strong>End:</strong> <?= htmlspecialchars($subscription['end_date']) ?></p>
            <p><strong>Status:</strong> <span style="color: <?= $statusColor ?>; font-weight: bold;"><?= htmlspecialchars($subscription['status']) ?></span></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <footer>
    <div class="footer-content">
      <p>&copy; 2025 SubTrack. All rights reserved.</p>
      <p>Email: <a href="mailto:subscriptiontrackertest@gmail.com">subscriptiontrackertest@gmail.com</a></p>
      <p>Phone: <a href="tel:+916303733839">+91 6303733839</a></p>
    </div>
  </footer>
  <script>
    const toggleBtn = document.getElementById('toggleTheme');
    const body = document.body;

    toggleBtn.addEventListener('click', () => {
      body.classList.toggle('dark-theme');
      body.classList.toggle('light-theme');
      toggleBtn.textContent = body.classList.contains('dark-theme')
        ? '☀ Switch Theme'
        : '🌙 Switch Theme';
    });

    const statusMsg = document.getElementById('statusMsg');
    if (statusMsg) {
      setTimeout(() => {
        statusMsg.style.transition = 'opacity 0.5s ease';
        statusMsg.style.opacity = '0';
        setTimeout(() => statusMsg.remove(), 500);
      }, 3000);
    }
    function filterCards(type) {
      const cards = document.querySelectorAll('.card');
      const buttons = document.querySelectorAll('.filter-buttons button');

      buttons.forEach(btn => btn.classList.remove('active-btn'));

      if (type === 'all') {
        cards.forEach(card => card.style.display = 'block');
        buttons[0].classList.add('active-btn');
      } else {
        cards.forEach(card => {
          card.style.display = card.classList.contains(type) ? 'block' : 'none';
        });
        if (type === 'active') buttons[1].classList.add('active-btn');
        else buttons[2].classList.add('active-btn');
      }
    }
  </script>
</body>
</html>