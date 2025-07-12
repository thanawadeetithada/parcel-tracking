<?php
session_start();
require_once 'db.php';

// ตรวจสอบว่า login อยู่หรือไม่
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// รับค่าจากแบบฟอร์ม
$id               = $_POST['id'] ?? '';
$item_name        = $_POST['item_name'] ?? '';
$category         = $_POST['category'] ?? '';
$usage_duration   = $_POST['usage_duration'] ?? 0;
$price            = $_POST['price'] ?? 0;
$budget_year      = $_POST['budget_year'] ?? '';
$note             = $_POST['note'] ?? '';
$status           = 'pending'; // ล็อกสถานะ
$updated_by       = $_SESSION['fullname'] ?? '';

if (empty($id)) {
    echo "รหัสพัสดุไม่ถูกต้อง";
    exit();
}

// ตรวจสอบและดึง start_date และ end_date จาก DB
$sqlExisting = "SELECT start_date, end_date FROM parcels WHERE id = ?";
$stmtExisting = $conn->prepare($sqlExisting);
$stmtExisting->bind_param("i", $id);
$stmtExisting->execute();
$resultExisting = $stmtExisting->get_result();

if ($resultExisting->num_rows === 0) {
    echo "ไม่พบข้อมูลพัสดุที่ต้องการแก้ไข";
    exit();
}

$existing = $resultExisting->fetch_assoc();
$start_date = $existing['start_date'];
$end_date   = $existing['end_date'];

// เตรียมคำสั่ง UPDATE
$stmt = $conn->prepare("UPDATE parcels SET item_name=?, category=?, usage_duration=?, price=?, budget_year=?, start_date=?, end_date=?, user_responsible=?, note=?, status=? WHERE id=?");

$stmt->bind_param(
    "ssidssssssi",
    $item_name,
    $category,
    $usage_duration,
    $price,
    $budget_year,
    $start_date,
    $end_date,
    $updated_by,
    $note,
    $status,
    $id
);

if ($stmt->execute()) {
    header("Location: parcel_management_user.php?update=success");
    exit();
} else {
    echo "เกิดข้อผิดพลาดในการอัปเดต: " . $stmt->error;
}
?>
