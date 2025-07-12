<?php
require_once 'db.php';

$id = $_POST['id'];
$item_name = $_POST['item_name'];
$category = $_POST['category'];
$usage_duration = $_POST['usage_duration'];
$price = $_POST['price'];
$budget_year = $_POST['budget_year'];
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];
$user_responsible = $_POST['user_responsible'];
$note = $_POST['note'];
$status = $_POST['status'];

$stmt = $conn->prepare("UPDATE parcels SET item_name=?, category=?, usage_duration=?, price=?, budget_year=?, start_date=?, end_date=?, user_responsible=?, note=?, status=? WHERE id=?");
$stmt->bind_param("ssidssssssi", $item_name, $category, $usage_duration, $price, $budget_year, $start_date, $end_date, $user_responsible, $note, $status, $id);

if ($stmt->execute()) {
    header("Location: parcel_approve.php");
} else {
    echo "เกิดข้อผิดพลาดในการอัปเดต: " . $conn->error;
}
?>
