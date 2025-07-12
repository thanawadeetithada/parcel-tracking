<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// รับค่าจากฟอร์ม
$item_name = $_POST['item_name'] ?? '';
$category = $_POST['category'] ?? '';
$budget_year = $_POST['budget_year'] ?? '';
$price = $_POST['price'] ?? 0;
$usage_duration = $_POST['usage_duration'] ?? 0;
$note = $_POST['note'] ?? null;
$user_responsible = $_SESSION['fullname'];
$status = 'pending';

// ให้ start_date = วันนี้, end_date = วันนี้ + $usage_duration วัน (ถ้ามี)
$start_date = date('Y-m-d');
$end_date = date('Y-m-d', strtotime("+{$usage_duration} days"));

$stmt = $conn->prepare("INSERT INTO parcels 
    (item_name, category, budget_year, price, usage_duration, start_date, end_date, user_responsible, note, status) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param("sssdisssss", 
    $item_name, $category, $budget_year, $price, $usage_duration, 
    $start_date, $end_date, $user_responsible, $note, $status);

if ($stmt->execute()) {
    header("Location: parcel_management_user.php?success=1");
    exit;
} else {
    echo "Error: " . $stmt->error;
}
