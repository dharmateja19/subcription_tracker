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

$subQuery = "SELECT subscription_name,category,id,price,frequency,currency, start_date, end_date, status FROM subscriptions WHERE user_id = '$user_id'";
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
  <link href="../assets/css/dashboard.css" rel="stylesheet">
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
            <p><strong>Price:</strong> <?= htmlspecialchars($subscription['price']) ?></p>
            <p><strong>Category:</strong> <?= htmlspecialchars($subscription['category']) ?></p>
            <p><strong>Status:</strong> <span style="color: <?= $statusColor ?>; font-weight: bold;"><?= htmlspecialchars($subscription['status']) ?></span></p>
            <div class="subscription-actions">
              <form action="delete_subscription.php" method="post" id="deleteForm">
                <input type="hidden" name="subscription_name" value="<?= htmlspecialchars($subscription['subscription_name']) ?>">
                <button type="button" class="delete-btn" onclick="confirmDelete(event)">Delete</button>
              </form>
              <button class="update-btn"
                data-subscription='<?= json_encode($subscription) ?>'
                onclick="openUpdateModal(this)">
                Update
              </button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div id="editModal" class="modal">
    <form id="editForm" action="update_subscription.php" method="POST">
      <div class="error-msg" id="editErrorMsg"></div>

      <input type="hidden" name="original_name" id="originalName" />

      <input type="text" name="subscription_name" id="editName" placeholder="Subscription Name" required />

      <select name="category" id="editCategory" required>
        <option value="" disabled>Select Category</option>
        <option value="sports">Sports</option>
        <option value="news">News</option>
        <option value="entertainment">Entertainment</option>
        <option value="lifestyle">Lifestyle</option>
        <option value="technology">Technology</option>
        <option value="finance">Finance</option>
        <option value="politics">Politics</option>
        <option value="other">Other</option>
      </select>

      <input type="number" name="price" id="editPrice" placeholder="Price" step="0.01" min="0.01" required />

      <select name="currency" id="editCurrency" required>
        <option value="" disabled>Select Currency</option>
        <option value="USD">USD</option>
        <option value="INR">INR</option>
        <option value="EUR">EUR</option>
      </select>

      <select name="frequency" id="editFrequency" required>
        <option value="" disabled>Select Frequency</option>
        <option value="monthly">Monthly</option>
        <option value="quarterly">Quarterly</option>
        <option value="halfyearly">Halfyearly</option>
        <option value="yearly">Yearly</option>
      </select>

      <input type="date" name="start_date" id="editStartDate" required />
      <input type="date" name="end_date" id="editEndDate" readonly hidden />

      <button type="submit">Update Subscription</button>
      <button type="button" onclick="closeModal()">Cancel</button>
    </form>
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
      toggleBtn.textContent = body.classList.contains('dark-theme') ?
        '☀ Switch Theme' :
        '🌙 Switch Theme';
    });
    const statusMsg = document.getElementById('statusMsg');
    if (statusMsg) {
      setTimeout(() => {
        statusMsg.style.transition = 'opacity 0.5s ease';
        statusMsg.style.opacity = '0';
        setTimeout(() => statusMsg.remove(), 500);
      }, 3000);
    }

    function openUpdateModal(button) {
      console.log('update called');
      const modal = document.getElementById('editModal');
      const subscriptionData = JSON.parse(button.getAttribute('data-subscription'));

      const editCategory = document.getElementById('editCategory');
      if (!editCategory) {
        console.error('Element with ID editCategory not found');
        return;
      }

      document.getElementById('originalName').value = subscriptionData.subscription_name;
      document.getElementById('editName').value = subscriptionData.subscription_name;
      document.getElementById('editCategory').value = subscriptionData.category || '';
      document.getElementById('editPrice').value = subscriptionData.price || '';
      document.getElementById('editCurrency').value = subscriptionData.currency || '';
      document.getElementById('editFrequency').value = subscriptionData.frequency || '';
      document.getElementById('editStartDate').value = subscriptionData.start_date || '';
      calculateEditEndDate(new Date(subscriptionData.start_date), subscriptionData.frequency);

      modal.classList.add('open');
    }

    function closeModal() {
      const modal = document.getElementById('editModal');
      modal.classList.remove('open');
      document.getElementById('editErrorMsg').textContent = '';
    }

    function calculateEditEndDate(startDate, frequency) {
      let months = 0;
      if (frequency === "monthly") months = 1;
      else if (frequency === "quarterly") months = 3;
      else if (frequency === "halfyearly") months = 6;
      else if (frequency === "yearly") months = 12;

      const endDate = new Date(startDate);
      endDate.setMonth(endDate.getMonth() + months);

      const yyyy = endDate.getFullYear();
      const mm = String(endDate.getMonth() + 1).padStart(2, "0");
      const dd = String(endDate.getDate()).padStart(2, "0");

      document.getElementById('editEndDate').value = `${yyyy}-${mm}-${dd}`;
    }

    document.querySelectorAll('.update-btn').forEach(button => {
      button.addEventListener('click', () => openUpdateModal(button));
    });

    document.getElementById('editFrequency').addEventListener('change', (e) => {
      const startDate = document.getElementById('editStartDate').value;
      if (startDate) {
        calculateEditEndDate(new Date(startDate), e.target.value);
      }
    });

    document.getElementById('editStartDate').addEventListener('change', (e) => {
      const frequency = document.getElementById('editFrequency').value;
      if (frequency) {
        calculateEditEndDate(new Date(e.target.value), frequency);
      }
    });

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

    function confirmDelete(event) {
      const isConfirmed = confirm("Are you sure you want to delete this subscription?");
      if (isConfirmed) {
        event.target.closest('form').submit();
      }
    }
  </script>
</body>

</html>