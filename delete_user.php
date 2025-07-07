<?php  // ลบข้อมูลหน้าจัดการ
session_start();   //เรียก session
require_once 'db.php'; //ต่อกบ database

if (!isset($_SESSION['user_id']) || !isset($_POST['id'])) {
    echo 'error';
    exit();
}

$user_id = $_SESSION['user_id'];
$id_to_delete = $_POST['id'];


if ($user_id == $id_to_delete) {
    echo 'user_deleted';
    exit();
}

$sql = "DELETE FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_to_delete);
if ($stmt->execute()) {
    echo 'success';
} else {
    echo 'error';
}
$stmt->close();
?>