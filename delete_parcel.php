<?php
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id'])) {
    $id = $_POST['id'];

    $stmt = $conn->prepare("DELETE FROM parcels WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: parcel_management.php");
        exit;
    } else {
        echo "เกิดข้อผิดพลาดในการลบ: " . $conn->error;
    }
} else {
    echo "ไม่พบข้อมูลสำหรับลบ";
}
?>
