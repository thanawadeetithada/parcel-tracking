<?php
require 'db.php';

date_default_timezone_set('Asia/Bangkok');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token'], $_POST['password'], $_POST['confirm_password'])) {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password === $confirm_password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $query = "SELECT * FROM users WHERE reset_token = ? AND reset_expiry > NOW() LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $updateQuery = "UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE reset_token = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("ss", $hashed_password, $token);
            $stmt->execute();

        header("Location: index.php?reset_success=1");
        exit();        
    } else {
        header("Location: reset_password.php?error=expired&token=$token");
        exit();        }
    } else {
       header("Location: reset_password.php?error=notmatch&token=$token");
        exit();
    }
}
?>