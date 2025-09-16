<?php
session_start();
include 'config.php';
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'];

// Fetch existing medicine details
$stmt = $conn->prepare("SELECT * FROM medicines WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$medicine = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $manufacturer = $_POST['manufacturer'];
    $batch_no = $_POST['batch_no'];
    $quantity = $_POST['quantity'];
    $expiry_date = $_POST['expiry_date'];

    // Keep old photo by default
    $photo = $medicine['photo'];

    // If new photo uploaded
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $fileName = time() . "_" . basename($_FILES['photo']['name']);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
            $photo = $targetFile;
        }
    }

    // Update query
    $stmt = $conn->prepare("UPDATE medicines SET name=?, manufacturer=?, batch_no=?, quantity=?, expiry_date=?, photo=? WHERE id=?");
    $stmt->bind_param("sssissi", $name, $manufacturer, $batch_no, $quantity, $expiry_date, $photo, $id);
    $stmt->execute();

    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Medicine</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card p-4 shadow">
        <h3 class="text-danger">✏️ Edit Medicine</h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label>Name</label>
                <input type="text" name="name" class="form-control" value="<?= $medicine['name'] ?>" required>
            </div>
            <div class="mb-3">
                <label>Manufacturer</label>
                <input type="text" name="manufacturer" class="form-control" value="<?= $medicine['manufacturer'] ?>" required>
            </div>
            <div class="mb-3">
                <label>Batch No</label>
                <input type="text" name="batch_no" class="form-control" value="<?= $medicine['batch_no'] ?>" required>
            </div>
            <div class="mb-3">
                <label>Quantity</label>
                <input type="number" name="quantity" class="form-control" value="<?= $medicine['quantity'] ?>" required>
            </div>
            <div class="mb-3">
                <label>Expiry Date</label>
                <input type="date" name="expiry_date" class="form-control" value="<?= $medicine['expiry_date'] ?>" required>
            </div>
            <div class="mb-3">
                <label>Current Photo</label><br>
                <?php if (!empty($medicine['photo'])): ?>
                    <img src="<?= $medicine['photo'] ?>" alt="Medicine Photo" width="100" height="100" style="object-fit:cover; border-radius:8px;">
                <?php else: ?>
                    <span class="text-muted">No photo uploaded</span>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label>Upload New Photo</label>
                <input type="file" name="photo" class="form-control">
            </div>
            <button type="submit" class="btn btn-danger">Update</button>
            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
</body>
</html>
