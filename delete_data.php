<?php   // ลบข้อมูลในตาราง
if (isset($_POST['id'])) {
    $id = $_POST['id'];
    require_once 'db.php';

    $sql = "DELETE FROM faculty_progress WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }

    $stmt->close();
    $conn->close();
}
?>
