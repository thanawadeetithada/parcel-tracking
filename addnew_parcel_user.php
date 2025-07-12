<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// รับค่าจากฟอร์ม
$item_name      = $_POST['item_name'] ?? '';
$category       = $_POST['category'] ?? '';
$usage_duration = $_POST['usage_duration'] ?? 0;
$price          = $_POST['price'] ?? 0;
$budget_year    = $_POST['budget_year'] ?? '';
$note           = $_POST['note'] ?? '';
$user_responsible = $_SESSION['fullname'] ?? '';
$status         = 'pending'; // สถานะเริ่มต้น
$created_at     = date('Y-m-d');
$end_at         = date('Y-m-d', strtotime("+{$usage_duration} days"));

// INSERT
$sql = "INSERT INTO parcels (item_name, category, usage_duration, price, budget_year, start_date, end_date, user_responsible, note, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "sssdssssss",
    $item_name,
    $category,
    $usage_duration,
    $price,
    $budget_year,
    $created_at,
    $end_at,
    $user_responsible,
    $note,
    $status
);

if ($stmt->execute()) {
    header("Location: parcel_management_user.php?success=1");
    exit();
} else {
    echo "เกิดข้อผิดพลาด: " . $stmt->error;
}
