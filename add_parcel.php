<?php
require_once 'db.php';

function fixDateFormat($inputDate) {
    $date = DateTime::createFromFormat('d-m-y', $inputDate);
    return $date ? $date->format('Y-m-d') : $inputDate;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_name = $_POST['item_name'];
    $category = $_POST['category'];
    $usage_duration = $_POST['usage_duration'];
    $price = $_POST['price'];
    $budget_year = $_POST['budget_year'];
    $start_date = fixDateFormat($_POST['start_date']);
    $end_date = fixDateFormat($_POST['end_date']);
    $user_responsible = $_POST['user_responsible'];
    $note = $_POST['note'];
    $status = $_POST['status'];


    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date)) {
        die("รูปแบบวันที่ไม่ถูกต้อง: $start_date");
    }

    $stmt = $conn->prepare("INSERT INTO parcels 
        (item_name, category, usage_duration, price, budget_year, start_date, end_date, user_responsible, note, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssdisssss", $item_name, $category, $usage_duration, $price, $budget_year, $start_date, $end_date, $user_responsible, $note, $status);

    if ($stmt->execute()) {
        header("Location: parcel_management.php?success=1");
        exit;
    } else {
        echo "เกิดข้อผิดพลาดในการเพิ่มพัสดุ: " . $stmt->error;
    }
}
?>