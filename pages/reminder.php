<?php
    require '../config/database.php';
    date_default_timezone_set('Asia/Kolkata');

    $logFile = '../reminder_log.txt';
    file_put_contents($logFile, "[" . date("Y-m-d H:i:s") . "] Reminder script started.\n", FILE_APPEND);

    $daysBeforeList = [7, 5, 3, 2, 1];
    $today = date('Y-m-d');
    $reminderSent = false;

    foreach ($daysBeforeList as $daysBefore) {
        $targetDate = date('Y-m-d', strtotime("+$daysBefore days"));

        $query = "SELECT u.email, u.name, s.subscription_name, s.end_date
                FROM subscriptions s
                JOIN users u ON s.user_id = u.id
                WHERE s.end_date = '$targetDate' AND u.verified = 1";

        $result = mysqli_query($conn, $query);

        if (!$result) {
            file_put_contents($logFile, "[" . date("Y-m-d H:i:s") . "] Query failed: " . mysqli_error($conn) . "\n", FILE_APPEND);
            die("Query failed: " . mysqli_error($conn) . "\nSQL: $query");
        }

        if (mysqli_num_rows($result) > 0) {
            $reminderSent = true;
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $email = $row['email'];
            $name = $row['name'];
            $subscription = $row['subscription_name'];
            $end = $row['end_date'];

            $subject = "Reminder: '$subscription' subscription expires in $daysBefore day(s)";
            $message = "Hi $name,\n\nThis is a reminder that your subscription for '$subscription' will expire on $end.\nPlease consider renewing it soon to avoid any interruption.\n\nThanks,\nYour Subscription Tracker";

            if (mail($email, $subject, $message)) {
                file_put_contents($logFile, "[" . date("Y-m-d H:i:s") . "] Sent reminder to $email for '$subscription' (expires in $daysBefore day(s))\n", FILE_APPEND);
            } else {
                file_put_contents($logFile, "[" . date("Y-m-d H:i:s") . "] Failed to send to $email\n", FILE_APPEND);
            }
        }
    }

    if (!$reminderSent) {
        file_put_contents($logFile, "[" . date("Y-m-d H:i:s") . "] No reminders today.\n", FILE_APPEND);
    }
?>

