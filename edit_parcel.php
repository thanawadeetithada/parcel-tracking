<?php
require_once 'db.php';

$id = $_POST['id'];
$item_name = $_POST['item_name'];
$user_license = $_POST['user_license'];
$usage_duration = $_POST['usage_duration'];
$price = $_POST['price'];
$budget_year = $_POST['budget_year'];
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];
$user_responsible = $_POST['user_responsible'];
$note = $_POST['note'];

$stmt = $conn->prepare("UPDATE parcels SET item_name=?, user_license=?, usage_duration=?, price=?, budget_year=?, start_date=?, end_date=?, user_responsible=?, note=? WHERE id=?");
$stmt->bind_param("siidsssssi", $item_name, $user_license, $usage_duration, $price, $budget_year, $start_date, $end_date, $user_responsible, $note, $id);

if ($stmt->execute()) {
    header("Location: parcel_management.php");
} else {
    echo "เกิดข้อผิดพลาดในการอัปเดต: " . $conn->error;
}
?>
