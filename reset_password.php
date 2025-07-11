<?php
require 'db.php';

date_default_timezone_set('Asia/Bangkok');

if (!isset($_GET['token']) || empty($_GET['token'])) {
    header("Location: reset_password.php?error=invalid_token");
    exit;
}

$token = $_GET['token'];

$query = "SELECT * FROM users WHERE reset_token = ? AND reset_expiry > NOW() LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php?error=expired");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เปลี่ยนรหัสผ่าน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <style>
    body {
        font-family: 'Prompt', sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
        background-color: #d6d6d6;
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
        background: #007bff;
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

    <div class="modal fade" id="passwordNotMatchModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal">
                <div class="modal-header">
                    <h5 class="modal-title mx-auto">แจ้งเตือน</h5>
                </div>
                <div class="modal-body text-center">
                    <h5>รหัสผ่านไม่ตรงกัน</h5>
                    <button type="button" class="btn btn-primary" data-dismiss="modal">ตกลง</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="invalidTokenModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal">
                <div class="modal-header">
                    <h5 class="modal-title mx-auto">แจ้งเตือน</h5>
                </div>
                <div class="modal-body text-center">
                    <h5>ลิงก์ไม่ถูกต้องหรือหมดอายุ</h5>
                    <button type="button" class="btn btn-primary"
                        onclick="window.location.href='index.php'">ตกลง</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
    $('form').on('submit', function() {
        $(this).find('button[type="submit"]').prop('disabled', true).text('กำลังดำเนินการ...');
    });

    $(document).ready(function() {
        const urlParams = new URLSearchParams(window.location.search);
        const url = new URL(window.location.href);
        console.log("data", urlParams.get('error'));

        if (urlParams.get('error') === 'notmatch') {
            $('#passwordNotMatchModal').modal('show');
            $('#passwordNotMatchModal').on('hidden.bs.modal', function() {
                clearQueryString();
            });
        }

        if (urlParams.get('error') === 'invalid_token') {
            $('#invalidTokenModal').modal('show');
             $('#invalidTokenModal').on('hidden.bs.modal', function() {
                clearQueryString();
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