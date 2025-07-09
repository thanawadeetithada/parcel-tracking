<?php
session_start();
require_once 'db.php';

$result = $conn->query("SELECT * FROM parcels ORDER BY id DESC");

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>จัดการพัสดุ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
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

    .table-rounded {
        border-radius: 12px;
        border: 1px solid #dee2e6;
    }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #004085; padding-left: 2rem;">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">จัดการพัสดุ</a>
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
                        <a class="nav-link active" href="parcel_management.php">จัดการพัสดุ</a>
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
        <div class="container py-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2><i class="fa-solid fa-box"></i> ระบบจัดการพัสดุ</h2>
                <div>
                    <button class="btn btn-success me-2"><i class="fa-solid fa-file-export"></i> ส่งออก Excel</button>
                    <button class="btn btn-secondary me-2"><i class="fa-solid fa-file-import"></i> นำเข้า Excel</button>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal"><i
                            class="fa-solid fa-plus"></i> เพิ่มพัสดุ</button>
                </div>
            </div>

            <!-- ฟิลเตอร์ -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="ค้นหาชื่อพัสดุ..." />
                </div>
                <div class="col-md-3">
                    <select class="form-select">
                        <option selected>-- ประเภท --</option>
                        <option>พัสดุสำนักงาน</option>
                        <option>พัสดุก่อสร้าง</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" placeholder="หมดอายุก่อนวันที่..." />
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-primary w-100"><i class="fa-solid fa-filter"></i> กรอง</button>
                </div>
            </div>

            <!-- แจ้งเตือน -->
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                <div>
                    พบพัสดุ 2 รายการใกล้หมดอายุ | ระบบส่งแจ้งเตือนไปยัง Google Chat และอีเมลแล้ว
                </div>
            </div>

            <!-- ตาราง -->
            <div class="table-responsive table-rounded shadow-sm">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ลำดับ</th>
                            <th>สิทธิการใช้งาน</th>
                            <th>User/License</th>
                            <th>ระยะเวลา</th>
                            <th>ราคา</th>
                            <th>งปม</th>
                            <th>วันที่เริ่มต้นใช้งาน</th>
                            <th>วันที่สิ้นสุดการใช้งาน</th>
                            <th>ผู้ใช้งาน / ผู้ดูแล</th>
                            <th>หมายเหตุ</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $i = 1; 
                        while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($row['item_name']) ?></td>
                            <td><?= htmlspecialchars($row['user_license']) ?></td>
                            <td><?= htmlspecialchars($row['usage_duration']) ?></td>
                            <td><?= htmlspecialchars($row['price']) ?></td>
                            <td><?= htmlspecialchars($row['budget_year']) ?></td>
                            <td><?= htmlspecialchars($row['start_date']) ?></td>
                            <td><?= htmlspecialchars($row['end_date']) ?></td>
                            <td><?= htmlspecialchars($row['user_responsible']) ?></td>
                            <td><?= htmlspecialchars($row['note']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning"><i class="fa-solid fa-pen"></i></button>
                                <button class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                        <?php endwhile; ?>

                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal เพิ่มพัสดุ -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-box"></i> เพิ่มพัสดุใหม่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="add_parcel.php" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">สิทธิการใช้งาน</label>
                                <input type="text" name="item_name" class="form-control" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">User/License</label>
                                <input type="number" name="user_license" class="form-control" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ปีงบประมาณ</label>
                                <input type="text" name="budget_year" class="form-control" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ราคา</label>
                                <input type="number" name="price" class="form-control" required />
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">วันที่เริ่มต้นใช้งาน</label>
                                <input type="date" name="start_date" class="form-control" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">วันที่สิ้นสุดการใช้งาน</label>
                                <input type="date" name="end_date" class="form-control" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ระยะเวลา</label>
                                <input type="number" name="usage_duration" class="form-control" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ผู้ใช้งาน/ผู้ดูแล</label>
                                <input type="text" name="user_responsible" class="form-control" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">หมายเหตุ</label>
                                <input type="text" name="note" class="form-control" />
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                            <button type="submit" class="btn btn-primary">บันทึก</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
    console.log("Session Info:");
    console.log("User ID:", <?php echo json_encode($_SESSION['user_id'] ?? 'ไม่พบ'); ?>);
    console.log("Full Name:", <?php echo json_encode($_SESSION['fullname'] ?? 'ไม่พบ'); ?>);
    console.log("Email:", <?php echo json_encode($_SESSION['user_email'] ?? 'ไม่พบ'); ?>);
    console.log("Role:", <?php echo json_encode($_SESSION['user_role'] ?? 'ไม่พบ'); ?>);
    </script>
</body>


</html>