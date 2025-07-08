<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $prefix = $_POST['prefix'];
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $userrole = $_POST['userrole'];

    $sql = "UPDATE users SET prefix = ?, fullname = ?, email = ?, userrole = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $prefix, $fullname, $email, $userrole, $id);

    if ($stmt->execute()) {
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $id) {
            $_SESSION['fullname'] = $fullname;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = $userrole;
        }
        header("Location: user_management.php");
        exit();
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
}
?>