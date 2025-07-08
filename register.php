<?php
include('db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {    
    $prefix = $_POST['prefix'];
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password != $confirm_password) {  
        $error_message = "รหัสผ่านและการยืนยันรหัสผ่านไม่ตรงกัน"; 
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // ตรวจสอบว่าอีเมลนี้ถูกใช้งานแล้วหรือไม่
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "อีเมลนี้มีผู้ใช้งานแล้ว";
        } else {
            $userrole = 'user';

            $stmt = $conn->prepare("INSERT INTO users (prefix, fullname, email, password, userrole) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $prefix, $fullname, $email, $hashed_password, $userrole);

            if ($stmt->execute()) {
                    $registration_success = true;
            } else {
                $error_message = "เกิดข้อผิดพลาดในการลงทะเบียน กรุณาลองใหม่";
            }
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบติดตาม</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
    * {
        box-sizing: border-box;
    }

    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f9fafc;
    }

    .login-container {
        background-color: #ffffff;
        padding: 2rem;
        width: 90%;
        max-width: 500px;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        transition: box-shadow 0.3s ease;
    }

    .login-container:hover {
        box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.3);
    }

    p {
        margin-top: 15px;
        font-size: 0.9rem;
        color: #000;
    }

    .login-title {
        color: #000;
        font-size: 2rem;
        margin-bottom: 2rem;
        text-align: center;
        width: 100%;
        padding-left: 20px;

    }

    .login-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        height: 100vh;
        justify-content: center;
    }

    .form-group {
        text-align: justify;
    }

    form button {
        width: 100%;
        margin-top: 15px;
    }

    .login-wrapper i {
        font-size: -webkit-xxx-large;
        color: #045cbb;
    }

    .alert-danger {
        font-size: 1.2rem;
        background-color: #f8d7da;
        color: #721c24;
        border-color: #f5c6cb;
    }
    </style>
</head>

<body>
    <div class="login-wrapper">
        <i class="fa-regular fa-file-lines"></i>
        <br>
        <h2 class="login-title">ลงทะเบียนผู้ใช้งานใหม่</h2>
        <div class="login-container">
            <!-- โชว์ error จากที่เขียนเช็คไว้ บรรทัด 11, 21-->
            <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error_message; ?>
            </div>
            <?php endif; ?>
            <form method="POST" action="register.php">
                <div class="form-group">
                    <label for="prefix">คำนำหน้าชื่อ</label>
                    <select class="form-control" id="prefix" name="prefix" required>
                        <option value="">เลือกคำนำหน้า</option>
                        <option value="นาย" <?php echo (isset($prefix) && $prefix == "นาย") ? "selected" : ""; ?>>นาย
                        </option>
                        <option value="นาง" <?php echo (isset($prefix) && $prefix == "นาง") ? "selected" : ""; ?>>นาง
                        </option>
                        <option value="นางสาว" <?php echo (isset($prefix) && $prefix == "นางสาว") ? "selected" : ""; ?>>
                            นางสาว</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="fullname">ชื่อ-สกุล</label>
                    <input type="text" class="form-control" id="fullname" name="fullname" required autocomplete="off"
                        value="<?php echo isset($fullname) ? $fullname : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="email">อีเมล</label>
                    <input type="email" class="form-control" id="email" name="email" required autocomplete="off"
                        value="<?php echo isset($email) ? $email : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="password">รหัสผ่าน</label>
                    <input type="password" class="form-control" id="password" name="password" required value="">
                </div>
                <div class="form-group">
                    <label for="confirm_password">ยืนยันรหัสผ่าน</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required
                        value="">
                </div>
                <button type="submit" class="btn btn-primary">ลงทะเบียน</button>
            </form>

            <p>
                <a href="index.php">มีบัญชีใช้แล้ว? เข้าสู่ระบบ</a>
            </p>
        </div>
    </div>

    <div class="modal fade" id="successModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title mx-auto">สมัครสมาชิกสำเร็จ</h5>
                </div>
                <div class="modal-body text-center">
                    ยินดีต้อนรับ! กรุณาเข้าสู่ระบบ<br>
                    <a href="index.php" class="btn btn-primary mt-3">เข้าสู่ระบบ</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <?php if (!empty($registration_success)): ?>
    <script>
    $(document).ready(function() {
        $('#successModal').modal('show');
    });
    </script>
    <?php endif; ?>

</body>

</html>