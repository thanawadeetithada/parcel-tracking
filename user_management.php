<?php
session_start();   //เรียก session
require_once 'db.php'; //ต่อ database

if (!isset($_SESSION['user_id'])) {  //login
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT userrole FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($userrole);
$stmt->fetch();
$stmt->close();

if ($userrole == 'admin' || $userrole == 'superadmin') {  //เช็ค role admin
    $sql = "SELECT * FROM users";  //admin/superadmin เรียกทุกข้อมูล
    $result = $conn->query($sql);
} else {
    echo "คุณไม่มีสิทธิ์เข้าถึงข้อมูลนี้";   //ถ้าไม่ใช่ admin หรือ superadmin ไปหน้า login
    header("Location: index.php"); 
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการผู้ใช้งาน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">


    <style>
    body {
        background-color: #f9fafc;
        font-family: 'Arial', sans-serif;
        height: 100vh;
        margin: 0;
    }

    .card {
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.2);
        background: white;
        margin-top: 50px;
        margin: 3% 5%;
        background-color: #ffffff;
    }

    .table th,
    .table td {
        text-align: center;
        font-size: 14px;

    }

    .table {
        background: #f8f9fa;
        border-radius: 10px;
    }

    .table th {
        background-color: #f9fafc;
        color: black;
    }

    .modal-dialog {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;

    }


    .modal-content {
        width: 100%;
        max-width: 500px;
    }

    .header-card {
        display: flex;
        justify-content: space-between;
    }

    .form-control modal-text {
        height: fit-content;
        width: 50%;
    }

    .table td:nth-child(9) {
        text-align: center;
        vertical-align: middle;
    }

    .btn-action {
        display: flex;
        justify-content: center;
        align-items: center;
    }


    .modal-text {
        width: 100%;
    }

    .search-name {
        width: 50%;
        margin-bottom: 10px;
    }

    .btn-header {
        margin-bottom: 10px;
    }

    .modal-header {
        font-weight: bold;
        padding: 25px;
    }

    .nav-item a {
        color: white;
        margin-right: 1rem;
    }

    .navbar {
        padding: 20px;
    }

    .nav-link:hover {
        color: white;
    }

    .modal-body {
        padding: 10px 40px;
    }
    </style>
</head>

<body>
    <div class="navbar navbar-dark bg-dark justify-content-end">
        <div class="nav-item d-flex">
            <a class="nav-link mr-3" href="dashboard.php"></i>&nbsp;&nbsp;รายการเอกสาร</a>
            <a class="nav-link" href="logout.php"><i class="fa-solid fa-user"></i>&nbsp;&nbsp;Logout</a>
        </div>
    </div>
    <div class="card">
        <?php if (isset($result) && $result->num_rows > 0): ?>
        <div class="header-card">
            <h3 class="text-left">จัดการผู้ใช้งาน</h3><br>
        </div>
        <?php endif; ?>
        <br>
        <?php if (isset($result) && $result->num_rows > 0): ?>
        <div class="table-responsive">
            <!-- ตาราง -->
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ชื่อ-สกุล</th>
                        <th>อีเมล</th>
                        <th>สถานะ</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['prefix']); ?>
                            <?php echo htmlspecialchars($row['fullname']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['userrole']); ?></td>
                        <td class="btn-action">
                            <a href="#" class="btn btn-warning btn-sm edit-btn" data-id="<?php echo $row['id']; ?>"
                                <?php if ($userrole == 'admin' && $row['userrole'] == 'superadmin') echo 'style="visibility:hidden;"'; ?>>
                                <i class="fa-solid fa-pencil"></i>
                            </a>
                            &nbsp;&nbsp;
                            <a href="#" class="btn btn-danger btn-sm delete-btn" data-id="<?php echo $row['id']; ?>"
                                <?php if ($userrole == 'admin' && $row['userrole'] == 'superadmin') echo 'style="visibility:hidden;"'; ?>>
                                <i class="fa-regular fa-trash-can"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <h4 class="text-center">ไม่มีข้อมูล</h4>
        <?php endif; ?>
    </div>

    <!-- Modal ยืนยันการลบ -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">คุณต้องการลบข้อมูลนี้หรือไม่?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>ชื่อ-สกุล : </strong> <span id="deleteName"></span></p>
                    <p><strong>อีเมล : </strong> <span id="deleteEmail"></span></p>
                    <p><strong>สถานะ : </strong> <span id="deleteRole"></span></p>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">ลบ</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal แก้ไขข้อมูล -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <h5 class="modal-header">
                    แก้ไขข้อมูล
                </h5>
                <div class="modal-body">
                    <form method="post" action="update_user.php">
                        <input type="hidden" id="edit_id" name="id">
                        <div class="mb-3">
                            <label for="edit_prefix" class="col-form-label">คำนำหน้าชื่อ</label>
                            <select class="form-control modal-text" id="edit_prefix" name="prefix" required>
                                <option value="นาย">นาย</option>
                                <option value="นาง">นาง</option>
                                <option value="นางสาว">นางสาว</option>
                                <option value="สิบเอก">สิบเอก</option>
                                <option value="ผู้ช่วยศาสตราจารย์">ผู้ช่วยศาสตราจารย์</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_name" class="col-form-label">ชื่อ-สกุล</label>
                            <input type="text" class="form-control modal-text" id="edit_name" name="fullname" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="col-form-label">อีเมล</label>
                            <input class="form-control modal-text" type="email" id="edit_email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_userRole" class="col-form-label">สถานะ</label>
                            <select class="form-control modal-text" id="edit_userRole" name="userrole" required>
                                <option value="superadmin">Superadmin</option>
                                <option value="admin">Admin</option>
                                <option value="user">User</option>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                            <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    // ปุ่มแก้ไข
    $(".edit-btn").on("click", function(e) {
        e.preventDefault();
        var id = $(this).data('id');

        var fullText = $(this).closest('tr').find('td:nth-child(1)').text().trim();
        var splitText = fullText.split(/\s+/); // แยกด้วยช่องว่าง
        var prefix = splitText.length > 1 ? splitText[0].trim() : ""; // คำนำหน้า
        var name = splitText.slice(1).join(" ").trim(); // ชื่อ-สกุลที่เหลือ

        var email = $(this).closest('tr').find('td:nth-child(2)').text().trim();
        var userRole = $(this).closest('tr').find('td:nth-child(3)').text().trim();

        // ใส่ค่าลงฟอร์มแก้ไข
        $('#edit_id').val(id);
        $('#edit_prefix').val(prefix); // กำหนดค่า prefix ที่แยกออกมา
        $('#edit_name').val(name);
        $('#edit_email').val(email);
        $('#edit_userRole').val(userRole);
        $('#editModal').modal('show');
    });

    // ปุ่มลบ

    $(".delete-btn").on("click", function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var name = $(this).closest('tr').find('td:nth-child(1)').text();
        var email = $(this).closest('tr').find('td:nth-child(2)').text();
        var role = $(this).closest('tr').find('td:nth-child(3)').text();

        $('#deleteName').text(name);
        $('#deleteEmail').text(email);
        $('#deleteRole').text(role);

        $('#confirmDelete').on('click', function() {
            $.ajax({
                url: 'delete_user.php',
                type: 'POST',
                data: {
                    id: id
                },
                success: function(response) {
                    if (response === 'success') {
                        $('#deleteModal').modal('hide');
                        location.reload();
                    } else if (response === 'user_deleted') {
                        $('#deleteModal').modal('hide');
                        alert('คุณไม่สามารถลบผู้ใช้นี้ได้');
                    } else {
                        alert('ไม่สามารถลบข้อมูลได้');
                    }
                },
                error: function() {
                    alert('เกิดข้อผิดพลาดในการลบข้อมูล');
                }
            });
        });
        $('#deleteModal').modal('show');
    });
});
</script>

</html>