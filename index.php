<?php
session_start();  // เรียกใช้ session

include('db.php');  // เชื่อมต่อฐานข้อมูล

if ($_SERVER["REQUEST_METHOD"] == "POST") { // ตรวจสอบหากเป็นคำขอแบบ POST
    $email = $_POST['email'];
    $password = $_POST['password']; 

    // ตรวจสอบข้อมูลผู้ใช้จากฐานข้อมูล
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {   
        // ตรวจสอบรหัสผ่าน
        if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['userrole'];
                $_SESSION['fullname'] = $user['fullname']; 
                
                if ($user['userrole'] === 'user') {
                    header("Location: parcel_management_user.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit();
        } else {
            $error_message = "❌ รหัสผ่านไม่ถูกต้อง";
        }
    } else {
        $error_message = "❌ อีเมลนี้ไม่ได้ลงทะเบียน";
    }

    $stmt->close();
}

$conn->close();
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
    }

    .login-wrapper i {
        font-size: -webkit-xxx-large;
        color: #045cbb;
    }

    button {
        margin-top: 15px;
    }

    .modal-btn button {
        width: 30%;
        margin: 5px;
    }

    .modal-btn {
        display: flex;
        justify-content: center;
    }
    </style>
</head>

<body>
    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
    <script>
    alert("ลงทะเบียนสำเร็จแล้ว");
    </script>
    <?php endif; ?>
    <div class="login-wrapper">
        <br>
        <h2 class="login-title">ระบบติดตามการจัดการพัสดุ
            <br>ของสำนักวิทยบริการ <br>มหาวิทยาลัยอุบลราชธานี
        </h2>

        <div class="login-container">
            <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error_message; ?>
            </div>
            <?php endif; ?>

            <form action="index.php" method="POST">
                <div class="form-group">
                    <label for="exampleInputEmail1">อีเมล</label>
                    <input type="email" class="form-control" id="exampleInputEmail1" name="email" required
                        autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">รหัสผ่าน</label>
                    <input type="password" class="form-control" id="exampleInputPassword1" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">เข้าสู่ระบบ</button>
            </form>
            <p>
                <a href="#" id="forgotPasswordLink" data-toggle="modal" data-target="#forgotPasswordModal">
                    ลืมรหัสผ่าน</a>
                <br> <a href="register.php">ยังไม่มีบัญชี? ลงทะเบียนใหม่</a>
            </p>
        </div>
    </div>

    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal">
                <div class="modal-header align-items-center">
                    <h5 class="modal-title mx-auto">ลืมรหัสผ่าน</h5>
                </div>
                <div class="modal-body px-4">
                    <form id="forgotPasswordForm" method="POST" action="process_forgot_password.php">
                        <div class="form-group">
                            <input type="email" name="email" class="form-control" placeholder="กรุณาใส่อีเมล" required
                                autocomplete="off">
                        </div>
                        <div class="modal-btn">
                            <button type="submit" class="btn btn-primary">ตกลง</button>
                            <button type="button" class="btn btn-danger" data-dismiss="modal">ยกเลิก</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="resetLinkSentModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal">
                <div class="modal-header">
                    <h5 class="modal-title mx-auto">ส่งลิงก์สำเร็จ</h5>
                </div>
                <div class="modal-body text-center">
                    <h5>ลิงก์สำหรับรีเซ็ตรหัสผ่านถูกส่งไปที่อีเมลของคุณแล้ว</h5>
                    <button type="button" class="btn btn-primary" data-dismiss="modal">ตกลง</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="resetPasswordSuccessModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal">
                <div class="modal-header">
                    <h5 class="modal-title mx-auto">สำเร็จ</h5>
                </div>
                <div class="modal-body text-center">
                    <h5>เปลี่ยนรหัสผ่านเรียบร้อยแล้ว</h5>
                    <button type="button" class="btn btn-primary" data-dismiss="modal">ตกลง</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ❌ ลิงก์หมดอายุ Modal -->
    <div class="modal fade" id="expiredTokenModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal">
                <div class="modal-header">
                    <h5 class="modal-title mx-auto">ลิงก์หมดอายุ</h5>
                </div>
                <div class="modal-body text-center">
                    <h5>ลิงก์สำหรับเปลี่ยนรหัสผ่านนี้หมดอายุแล้ว</h5>
                    <button type="button" class="btn btn-primary" data-dismiss="modal">ตกลง</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="passwordNotMatchModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal">
                <div class="modal-header">
                    <h5 class="modal-title mx-auto">รหัสผ่านไม่ถูกต้อง</h5>
                </div>
                <div class="modal-body text-center">
                    <h5>รหัสผ่านไม่ตรงกัน</h5>
                    <button type="button" class="btn btn-primary" data-dismiss="modal">ตกลง</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    $(document).ready(function() {
        const urlParams = new URLSearchParams(window.location.search);
        const url = new URL(window.location.href);

        if (urlParams.get('reset_sent') === '1') {
            $('#resetLinkSentModal').modal('show');

            $('#resetLinkSentModal').on('hidden.bs.modal', function() {
                clearQueryString();
            });
        }

        if (urlParams.get('reset_success') === '1') {
            $('#resetPasswordSuccessModal').modal('show');

            $('#resetPasswordSuccessModal').on('hidden.bs.modal', function() {
                clearQueryString();
            });
        }

        if (urlParams.get('error') === 'expired') {
            $('#expiredTokenModal').modal('show');

            $('#expiredTokenModal').on('hidden.bs.modal', function() {
                const cleanUrl = window.location.protocol + "//" + window.location.host + window
                    .location.pathname;
                window.history.replaceState({}, document.title, cleanUrl);
            });
        }  

        function clearQueryString() {
            if (window.history.replaceState) {
                const cleanUrl = window.location.protocol + "//" + window.location.host + window.location
                    .pathname;
                window.history.replaceState({}, document.title, cleanUrl);
            }
        }
    });
    </script>

</body>

</html>