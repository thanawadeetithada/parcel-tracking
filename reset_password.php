<?php
require 'db.php';

date_default_timezone_set('Asia/Bangkok');

if (!isset($_GET['token']) || empty($_GET['token'])) {
    echo "<script>alert('Invalid token!'); window.location.href = 'index.php';</script>";
    exit;
}

$token = $_GET['token'];

$query = "SELECT * FROM users WHERE reset_token = ? AND reset_expiry > NOW() LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Token is invalid or expired!'); window.location.href = 'index.php';</script>";
    exit;
}
?>

<head>
<style>
    body {
        font-family: 'Prompt', sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }

    .container {
        background: rgba(255, 255, 255, 0.9);
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 500px;
        text-align: center;
        margin: 30px;
    }

    .form-group {
        display: flex;
        align-items: center;
        margin: 10px 0;
    }

    .form-group label {
        width: 40%;
        margin-right: 10px;
        font-size: 16px;
    }

    .form-group input {
        width: 50%;
        padding: 12px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 16px;
        box-sizing: border-box;
    }

    button {
        width: 50%;
        padding: 12px;
        background: #8c99bc;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 18px;
        cursor: pointer;
        transition: background 0.3s;
        margin-top: 15px;
    }
</style>
</head>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เปลี่ยนรหัสผ่าน</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>เปลี่ยนรหัสผ่าน</h2>
        <form action="process_reset_password.php" method="POST">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <div class="form-group">
                <label for="password">รหัสผ่าน</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">ยืนยันรหัสผ่าน</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>
            <button type="submit">เปลี่ยนรหัสผ่าน</button>
        </form>
    </div>
</body>
</html>