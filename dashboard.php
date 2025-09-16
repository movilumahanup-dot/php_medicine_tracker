<?php
session_start();
include 'config.php';
if (!isset($_SESSION['admin'])) { 
    header("Location: login.php"); 
    exit(); 
}

// Stats
$total = $conn->query("SELECT COUNT(*) as total FROM medicines")->fetch_assoc()['total'];
$expiring = $conn->query("SELECT COUNT(*) as total FROM medicines WHERE expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetch_assoc()['total'];
$expired = $conn->query("SELECT COUNT(*) as total FROM medicines WHERE expiry_date < CURDATE()")->fetch_assoc()['total'];

$events = [];
$notifications = [];
$today = date("Y-m-d");

// For calendar + notifications
$meds_all = $conn->query("SELECT * FROM medicines");
while ($row = $meds_all->fetch_assoc()) {
    $color = "#28a745"; // Safe = green

    if ($row['expiry_date'] < $today) {
        $color = "#dc3545"; // Expired = red
        $notifications[] = "⚠️ <b>{$row['name']}</b> (Batch {$row['batch_no']}) from <b>{$row['manufacturer']}</b> has <span class='text-danger'>EXPIRED</span>!";
   } else {
    $expiryDate = new DateTime($row['expiry_date']);
    $todayDate  = new DateTime($today);
    $diff       = $todayDate->diff($expiryDate);
    $daysLeft   = (int)$diff->format("%r%a"); // signed difference (negative if expired)

    if ($daysLeft < 0) {
        $color = "#dc3545"; // Expired
        $notifications[] = "⚠️ <b>{$row['name']}</b> (Batch {$row['batch_no']}) from <b>{$row['manufacturer']}</b> has <span class='text-danger'>EXPIRED</span>!";
    } elseif ($daysLeft == 0) {
        $color = "#ffc107"; // Expires today
        $notifications[] = "⏳ <b>{$row['name']}</b> (Batch {$row['batch_no']}) from <b>{$row['manufacturer']}</b> expires <span class='text-warning'>TODAY</span>!";
    } elseif ($daysLeft == 1) {
        $color = "#ffc107"; // Expires tomorrow
        $notifications[] = "⏳ <b>{$row['name']}</b> (Batch {$row['batch_no']}) from <b>{$row['manufacturer']}</b> will expire <span class='text-warning'>TOMORROW</span>!";
    } elseif ($daysLeft <= 30) {
        $color = "#ffc107"; // Expiring soon
        $notifications[] = "⏳ <b>{$row['name']}</b> (Batch {$row['batch_no']}) from <b>{$row['manufacturer']}</b> will expire in <span class='text-warning'>{$daysLeft} days</span>!";
    }
}



    $events[] = [
        'title' => $row['name'] . " (Batch: " . $row['batch_no'] . ") - " . $row['manufacturer'],
        'start' => $row['expiry_date'],
        'color' => $color
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Medicine Tracker Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark px-4" style="background:#e53935;">
    <a class="navbar-brand fw-bold" href="#">💊 Medicine Tracker</a>
    <div class="ms-auto">
        <a href="add_medicine.php" class="btn btn-light text-danger">+ Add Medicine</a>
        <a href="logout.php" class="btn btn-outline-light ms-2">Logout</a>
    </div>
</nav>

<div class="container mt-4">

    <!-- Notifications -->
    <?php if (!empty($notifications)): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <h5 class="alert-heading">🔔 Notifications</h5>
            <ul>
                <?php foreach($notifications as $note): ?>
                    <li><?= $note ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="row text-center mb-4">
        <div class="col-md-4">
            <div class="card p-3 shadow-sm">
                <div class="fs-2 text-danger"><?= $total ?></div>
                <p>Total Medicines</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 shadow-sm">
                <div class="fs-2 text-warning"><?= $expiring ?></div>
                <p>Expiring Soon</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 shadow-sm">
                <div class="fs-2 text-danger"><?= $expired ?></div>
                <p>Expired</p>
            </div>
        </div>
    </div>

    <!-- Medicines Table -->
    <?php $meds = $conn->query("SELECT * FROM medicines ORDER BY expiry_date ASC"); ?>
    <?php if ($total == 0): ?>
        <div class="text-center mt-5">
            <h5>No medicines found</h5>
            <a href="add_medicine.php" class="btn btn-danger mt-2">+ Add Medicine</a>
        </div>
    <?php else: ?>
        <table class="table table-bordered table-striped">
    <thead class="table-danger">
        <tr>
            <th>Name</th>
            <th>Manufacturer</th>
            <th>Batch</th>
            <th>Qty</th>
            <th>Expiry</th>
            <th>Days Left</th>
            <th>Status</th>
            <th>Photo</th> <!-- NEW COLUMN -->
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $result = $conn->query("SELECT * FROM medicines");
        while ($row = $result->fetch_assoc()) {
            $expiry = new DateTime($row['expiry_date']);
            $today = new DateTime();
            $diff = $today->diff($expiry)->days;
            $status = ($expiry < $today) ? "Expired" : (($diff <= 30) ? "Expiring Soon" : "Safe");

            echo "<tr>";
            echo "<td>{$row['name']}</td>";
            echo "<td>{$row['manufacturer']}</td>";
            echo "<td>{$row['batch_no']}</td>";
            echo "<td>{$row['quantity']}</td>";
            echo "<td>{$row['expiry_date']}</td>";
            echo "<td>{$diff} days left</td>";
            echo "<td><span class='badge " . 
                ($status == 'Expired' ? 'bg-danger' : ($status == 'Expiring Soon' ? 'bg-warning text-dark' : 'bg-success')) . 
                "'>$status</span></td>";

            // Photo display
            if (!empty($row['photo'])) {
                echo "<td><img src='{$row['photo']}' alt='Medicine Photo' width='60' height='60' style='object-fit:cover; border-radius:8px;'></td>";
            } else {
                echo "<td><span class='text-muted'>No photo</span></td>";
            }

            echo "<td>
                    <a href='edit_medicine.php?id={$row['id']}' class='btn btn-warning btn-sm'>Edit</a>
                    <a href='delete_medicine.php?id={$row['id']}' class='btn btn-danger btn-sm'>Delete</a>
                  </td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>

    <?php endif; ?>

    <!-- Calendar -->
    <div id="calendar"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView: 'dayGridMonth',
        events: <?= json_encode($events) ?>
    });
    calendar.render();
});
</script>
</body>
</html>
