<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') { //แก้ไขข้อมูลผู้ใช้งาน  ปุ่มแก้ไขหน้า จัดการ
    $id = $_POST['id'];
    $prefix = $_POST['prefix'];
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $userrole = $_POST['userrole'];
    
    $sql = "UPDATE users SET prefix = ?, fullname = ?, email = ?, userrole = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $prefix,$fullname, $email, $userrole, $id);
    
    if ($stmt->execute()) {
        header("Location: user_management.php");
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
}
?>