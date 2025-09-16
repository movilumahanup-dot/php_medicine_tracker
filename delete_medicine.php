<?php
session_start();
include 'config.php';
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit(); }

$id = $_GET['id'];
$conn->query("DELETE FROM medicines WHERE id=$id");
header("Location: dashboard.php");
exit();
?>