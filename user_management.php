<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$userrole = $_SESSION['user_role'];

if ($userrole == 'admin' || $userrole == 'superadmin') {
    $sql = "SELECT * FROM users";
    $result = $conn->query($sql);
} else {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT userrole FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($userrole);
$stmt->fetch();
$stmt->close();

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
        background-color: #d6d6d6;
        font-family: 'Prompt', sans-serif;
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
        justify-content: center;
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

    .modal-header {
        font-weight: bold;
        padding: 25px;
    }

    .modal-body {
        padding: 10px 40px;
    }

    .table-rounded {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #dee2e6;
    }

    tbody td:first-child,
    th:first-child {
        padding-left: 15px;
    }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #004085; padding-left: 2rem;">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">จัดการผู้ใช้งาน</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">ระบบจัดการพัสดุในหน่วยงาน</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="searchreport_student.php">ค้นหาข้อมูลนักเรียน</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="record_score.php">บันทึกคะแนน</a>
                    </li>
                    <?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'superadmin')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="user_management.php">จัดการผู้ใช้งาน</a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link text-warning" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> ออกจากระบบ
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="card">
        <?php if (isset($result) && $result->num_rows > 0): ?>
        <div class="header-card">
            <h3 class="text-left">จัดการผู้ใช้งาน</h3><br>
        </div>
        <?php endif; ?>
        <br>
        <?php if (isset($result) && $result->num_rows > 0): ?>
        <div class="table-responsive table-rounded shadow-sm">
            <table class="table table-bordered table-hover data-table mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>ชื่อ-สกุล</th>
                        <th>อีเมล</th>
                        <th>สถานะ</th>
                        <?php if ($userrole === 'superadmin'): ?>
                        <th class="text-center">จัดการ</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['prefix']); ?>
                            <?php echo htmlspecialchars($row['fullname']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td>
                            <?php
                switch ($row['userrole']) {
                    case 'superadmin': echo 'ผู้ดูแลระบบ'; break;
                    case 'admin': echo 'เจ้าหน้าที่พัสดุ'; break;
                    case 'user': echo 'ผู้ใช้งาน'; break;
                    default: echo htmlspecialchars($row['userrole']);
                }
            ?>
                        </td>

                        <?php if ($userrole === 'superadmin'): ?>
                        <td class="btn-action">
                            <a href="#" class="btn btn-warning btn-sm edit-btn" data-id="<?php echo $row['id']; ?>">
                                <i class="fa-solid fa-pencil"></i>
                            </a>
                            &nbsp;&nbsp;
                            <a href="#" class="btn btn-danger btn-sm delete-btn" data-id="<?php echo $row['id']; ?>">
                                <i class="fa-regular fa-trash-can"></i>
                            </a>
                        </td>
                        <?php endif; ?>
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
                                <option value="user">ผู้ใช้งาน</option>
                                <option value="admin">เจ้าหน้าที่พัสดุ</option>
                                <option value="superadmin">ผู้ดูแลระบบ</option>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
console.log("Session Info:");
console.log("User ID:", <?php echo json_encode($_SESSION['user_id'] ?? 'ไม่พบ'); ?>);
console.log("Full Name:", <?php echo json_encode($_SESSION['fullname'] ?? 'ไม่พบ'); ?>);
console.log("Email:", <?php echo json_encode($_SESSION['user_email'] ?? 'ไม่พบ'); ?>);
console.log("Role:", <?php echo json_encode($_SESSION['user_role'] ?? 'ไม่พบ'); ?>);


$(document).ready(function() {
    // ปุ่มแก้ไข
    $(".edit-btn").on("click", function(e) {
        e.preventDefault();
        var id = $(this).data('id');

        var fullText = $(this).closest('tr').find('td:nth-child(1)').text().trim();
        var splitText = fullText.split(/\s+/);
        var prefix = splitText.length > 1 ? splitText[0].trim() : "";
        var name = splitText.slice(1).join(" ").trim();

        var email = $(this).closest('tr').find('td:nth-child(2)').text().trim();
        var userRoleLabel = $(this).closest('tr').find('td:nth-child(3)').text().trim();

        const roleMap = {
            'ผู้ดูแลระบบ': 'superadmin',
            'เจ้าหน้าที่พัสดุ': 'admin',
            'ผู้ใช้งาน': 'user'
        };
        var userRoleValue = roleMap[userRoleLabel] || '';

        $('#edit_id').val(id);
        $('#edit_prefix').val(prefix);
        $('#edit_name').val(name);
        $('#edit_email').val(email);
        $('#edit_userRole').val(userRoleValue);
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