<?php
session_start();
include 'config.php';
if (!isset($_SESSION['admin'])) { 
    header("Location: login.php"); 
    exit(); 
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $manufacturer = $_POST['manufacturer'];
    $batch_no = $_POST['batch_no'];
    $quantity = $_POST['quantity'];
    $expiry_date = $_POST['expiry_date'];

    // handle photo upload
    $photo = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true); // create folder if not exists
        }
        $fileName = time() . "_" . basename($_FILES['photo']['name']);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
            $photo = $targetFile;
        }
    }

    $stmt = $conn->prepare("INSERT INTO medicines (name, manufacturer, batch_no, quantity, expiry_date, photo) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssiss", $name, $manufacturer, $batch_no, $quantity, $expiry_date, $photo);
    $stmt->execute();

    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Medicine</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card p-4 shadow">
        <h3 class="text-danger">➕ Add Medicine</h3>
        <form method="POST" enctype="multipart/form-data"> <!-- enctype is IMPORTANT -->
            <div class="mb-3">
                <label>Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Manufacturer</label>
                <input type="text" name="manufacturer" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Batch No</label>
                <input type="text" name="batch_no" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Quantity</label>
                <input type="number" name="quantity" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Expiry Date</label>
                <input type="date" name="expiry_date" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Photo</label>
                <input type="file" name="photo" class="form-control">
            </div>
            <button type="submit" class="btn btn-danger">Save</button>
            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
</body>
</html>
