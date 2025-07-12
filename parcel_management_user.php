<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$currentUser = $_SESSION['fullname'];

$search = $_GET['search'] ?? '';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$statusFilter = $_GET['status'] ?? '';

$searchSql = $conn->real_escape_string($search);
$startDateSql = $conn->real_escape_string($startDate);
$endDateSql = $conn->real_escape_string($endDate);

$sql = "SELECT * FROM parcels WHERE 1=1";

// กรองเฉพาะ user ปัจจุบัน ถ้าไม่ใช่ admin/superadmin
if (!in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
    $currentUser = $conn->real_escape_string($_SESSION['fullname']);
    $sql .= " AND user_responsible = '$currentUser'";
}

if (! empty($statusFilter)) {
        $sql .= " AND status = '$statusFilter'";
    }

if (!empty($searchSql)) {
    $sql .= " AND item_name LIKE '%$searchSql%'";
}
if (!empty($startDateSql)) {
    $sql .= " AND start_date >= '$startDateSql'";
}
if (!empty($endDateSql)) {
    $sql .= " AND end_date <= '$endDateSql'";
}

$sql .= " ORDER BY id DESC";
$result = $conn->query($sql);


$userOptions = [];
$userResult = $conn->query("SELECT fullname FROM users ORDER BY fullname ASC");
if ($userResult && $userResult->num_rows > 0) {
    while ($row = $userResult->fetch_assoc()) {
        $userOptions[] = $row['fullname'];
    }
}

$today = date('Y-m-d');
$next3Days = date('Y-m-d', strtotime('+30 days'));
$expiringCount = 0;

$sqlExpiringCount = "SELECT COUNT(*) as count FROM parcels WHERE end_date BETWEEN '$today' AND '$next3Days' AND status = 'approved'";
if (!in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
    $userEscaped = $conn->real_escape_string($_SESSION['fullname']);
    $sqlExpiringCount .= " AND user_responsible = '$userEscaped'";
}

$expiringResult = $conn->query($sqlExpiringCount);
if ($expiringResult && $expiringResult->num_rows > 0) {
    $expiringCount = $expiringResult->fetch_assoc()['count'];
}


$emailOptions = [];
$emailResult = $conn->query("SELECT email FROM users WHERE email IS NOT NULL AND email != '' ORDER BY email ASC");
while ($row = $emailResult->fetch_assoc()) {
    $emailOptions[] = $row['email'];
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>จัดการพัสดุ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

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

    .btn-edit-delete {
        display: flex;
        justify-content: center;

        .btn-edit {
            margin-right: 5px;
        }
    }

    .header-table {
        text-align: center;
        white-space: nowrap;
    }

    .table td {
        padding: 16px;
    }

    .select2-container .select2-selection--single {
        height: calc(2.25rem + 2px);
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        line-height: 1.5;
        border-radius: 0.375rem;
        border: 1px solid #ced4da;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 1.5;
        padding-left: 0;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100%;
        top: 0px;
        right: 0.75rem;
    }

    .select2-container--default .select2-selection--multiple {
        margin-bottom: 5px;
        padding-bottom: 10px;
    }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #004085; padding-left: 2rem;">
        <div class="container-fluid">
            <a class="navbar-brand" href="parcel_management_user.php">จัดการพัสดุ</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="parcel_management_user.php">จัดการพัสดุ</a>
                    </li>
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
        <div class="container py-1">
            <h3><i class="fa-solid fa-box"></i> ระบบจัดการพัสดุ</h3><br>
            <div class="mb-3">
                <div class="row">
                    <div
                        class="col-12 d-flex flex-wrap align-items-end gap-2 flex-column flex-md-row justify-content-md-end">
                        <div class="d-flex flex-wrap gap-2">
                            <a href="send_expiring_email_user.php" class="btn btn-warning" id="send-email-btn">
                                <i class="fa-solid fa-envelope"></i> ส่ง Email แจ้งเตือน
                            </a>

                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                                <i class="fa-solid fa-plus"></i> เพิ่มพัสดุ
                            </button>
                        </div>

                        <!-- บรรทัดใหม่ -->
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            <a id="export-btn" class="btn btn-success">
                                <i class="fa-solid fa-file-export"></i> ส่งออก Excel
                            </a>

                            <form action="import_parcels_user.php" method="POST" enctype="multipart/form-data"
                                id="import-form" style="display: inline;">
                                <input type="file" name="excel_file" id="excel-file" accept=".xlsx"
                                    style="display: none;" onchange="document.getElementById('import-form').submit();">
                                <button type="button" class="btn btn-secondary"
                                    onclick="document.getElementById('excel-file').click();">
                                    <i class="fa-solid fa-file-import"></i> นำเข้า Excel
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <br>
            <!-- ฟิลเตอร์ -->
            <div class="row g-3 mb-4">
                <!-- ฟิลเตอร์ -->
                <form method="GET" id="search-form">
                    <div class="d-flex flex-wrap align-items-end justify-content-center gap-3">
                        <div class="form-group">
                            <label for="search-input" class="form-label">ค้นหาชื่อพัสดุ</label>
                            <input type="text" name="search" id="search-input" class="form-control"
                                value="<?php echo htmlspecialchars($_GET['search'] ?? '')?>" />
                        </div>

                        <div class="form-group">
                            <label for="start-date" class="form-label">วันที่เริ่มต้นใช้งาน</label>
                            <input type="date" name="start_date" id="start-date" class="form-control"
                                value="<?php echo htmlspecialchars($_GET['start_date'] ?? '')?>" />
                        </div>

                        <div class="form-group">
                            <label for="end-date" class="form-label">วันที่สิ้นสุดใช้งาน</label>
                            <input type="date" name="end_date" id="end-date" class="form-control"
                                value="<?php echo htmlspecialchars($_GET['end_date'] ?? '')?>" />
                        </div>

                        <div class="form-group">
                            <label for="dropdown-request" class="form-label">สถานะ</label>
                            <select name="status" id="dropdown-request" class="form-select">
                                <option value="">ทั้งหมด</option>
                                <option value="approved" <?= $statusFilter == 'approved' ? 'selected' : '' ?>>อนุมัติ
                                </option>
                                <option value="pending" <?= $statusFilter == 'pending' ? 'selected' : '' ?>>รออนุมัติ
                                </option>
                                <option value="rejected" <?= $statusFilter == 'rejected' ? 'selected' : '' ?>>ไม่อนุมัติ
                                </option>
                            </select>
                        </div>

                        <div class="form-group d-flex gap-2">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fa-solid fa-filter"></i> ค้นหา
                            </button>
                            <a href="parcel_management_user.php" class="btn btn-outline-secondary">
                                <i class="fa-solid fa-rotate-left"></i> ล้างข้อมูล
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- แจ้งเตือน -->
            <?php if ($expiringCount > 0): ?>
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                <div>
                    พบพัสดุ <?= $expiringCount ?> รายการใกล้หมดอายุ
                </div>
            </div>
            <?php endif; ?>

            <!-- ตาราง -->
            <div class="table-responsive table-rounded shadow-sm">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="table-dark">
                        <tr class="header-table">
                            <th>ลำดับ</th>
                            <th>ชื่อ</th>
                            <th>ประเภท</th>
                            <th>ระยะเวลา</th>
                            <th>ราคา</th>
                            <th>งปม.</th>
                            <th>เริ่มต้นใช้งาน</th>
                            <th>สิ้นสุดการใช้งาน</th>
                            <th>ผู้ใช้งาน</th>
                            <th>หมายเหตุ</th>
                            <th>สถานะ</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody id="parcel-table-body">
                        <?php if ($result->num_rows > 0): ?>
                        <?php $i = 1; while($row = $result->fetch_assoc()): ?>
                        <tr class="text-center">
                            <td><?= $i++ ?></td>
                            <td class="text-left"><?= htmlspecialchars($row['item_name']) ?></td>
                            <td><?= htmlspecialchars($row['category']) ?></td>
                            <td><?= htmlspecialchars($row['usage_duration']) ?></td>
                            <td style="white-space: nowrap;"><?= htmlspecialchars($row['price']) ?></td>
                            <td style="white-space: nowrap;"><?= htmlspecialchars($row['budget_year']) ?></td>
                            <td style="white-space: nowrap;">
                                <?= $row['start_date'] ? (new DateTime($row['start_date']))->format('d-m-Y') : '-' ?>
                            </td>
                            <td style="white-space: nowrap;">
                                <?= $row['end_date'] ? (new DateTime($row['end_date']))->format('d-m-Y') : '-' ?>
                            </td>
                            <td style="white-space: nowrap;"><?= htmlspecialchars($row['user_responsible']) ?></td>
                            <td><?= htmlspecialchars($row['note']) ?: '-' ?></td>
                            <td style="white-space: nowrap;">
                                <?php
                                    $statusText  = '';
                                    $statusClass = '';

                                    switch ($row['status']) {
                                        case 'approved':
                                            $statusText  = 'อนุมัติ';
                                            $statusClass = 'text-success';
                                            break;
                                        case 'pending':
                                            $statusText  = 'รออนุมัติ';
                                            $statusClass = 'text-warning';
                                            break;
                                        case 'rejected':
                                            $statusText  = 'ไม่อนุมัติ';
                                            $statusClass = 'text-danger';
                                            break;
                                        default:
                                            $statusText  = htmlspecialchars($row['status']);
                                            $statusClass = '';
                                    }
                                ?>
                                <span class="<?php echo $statusClass?>"><i
                                        class="fa-solid fa-circle me-1"></i><?php echo $statusText?></span>
                            </td>
                            <td class="btn-edit-delete">
                                <button class="btn btn-sm btn-warning btn-edit" data-bs-toggle="modal"
                                    data-bs-target="#editModal" data-id="<?= $row['id'] ?>"
                                    data-item_name="<?= htmlspecialchars($row['item_name']) ?>"
                                    data-category="<?= $row['category'] ?>"
                                    data-usage_duration="<?= $row['usage_duration'] ?>"
                                    data-price="<?= $row['price'] ?>" data-budget_year="<?= $row['budget_year'] ?>"
                                    data-start_date="<?= $row['start_date'] ?>" data-end_date="<?= $row['end_date'] ?>"
                                    data-user_responsible="<?= htmlspecialchars($row['user_responsible']) ?>"
                                    data-note="<?= htmlspecialchars($row['note']) ?>"
                                    data-status="<?= $row['status'] ?>">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <?php
    $isApproved = $row['status'] === 'approved';
    $deleteDisabled = $isApproved ? 'disabled title="ไม่สามารถลบได้เมื่ออนุมัติแล้ว"' : '';
?>
                                <button class="btn btn-sm btn-danger btn-delete" data-bs-toggle="modal"
                                    data-bs-target="#deleteModal" data-id="<?= $row['id'] ?>" <?= $deleteDisabled ?>>
                                    <i class="fa-solid fa-trash"></i>
                                </button>

                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">
                                <i class="fa-solid fa-circle-exclamation me-2"></i> ไม่พบข้อมูลพัสดุในระบบ
                            </td>
                        </tr>
                        <?php endif; ?>
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
                    <form action="add_parcel_user.php" method="POST">
                        <div class="row g-3" style="margin-bottom: 20px;">
                            <div class="col-md-6">
                                <label class="form-label">ชื่อ</label>
                                <input type="text" name="item_name" class="form-control" required autocomplete="off" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ประเภท</label>
                                <input type="text" name="category" class="form-control" required autocomplete="off" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">งปม.</label>
                                <input type="text" name="budget_year" class="form-control" required
                                    autocomplete="off" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ราคา</label>
                                <input type="number" name="price" class="form-control" required autocomplete="off" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ระยะเวลา</label>
                                <input type="number" name="usage_duration" class="form-control" required
                                    autocomplete="off" />
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">หมายเหตุ</label>
                                <input type="text" name="note" class="form-control" autocomplete="off" />
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

    <!-- Modal แก้ไขพัสดุ -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="edit_parcel_user.php" method="POST">
                    <input type="hidden" name="status" value="pending">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fa-solid fa-pen-to-square"></i> แก้ไขคำขอข้อมูลพัสดุ</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit-id">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">ชื่อ</label>
                                <input type="text" name="item_name" id="edit-item_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ประเภท</label>
                                <input type="text" name="category" id="edit-category" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">งปม.</label>
                                <input type="text" name="budget_year" id="edit-budget_year" class="form-control"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ราคา</label>
                                <input type="number" name="price" id="edit-price" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ระยะเวลา</label>
                                <input type="number" name="usage_duration" id="edit-usage_duration" class="form-control"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">หมายเหตุ</label>
                                <input type="text" name="note" id="edit-note" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">อัปเดต</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal ยืนยันการลบ -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="delete_parcel_user.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger"><i class="fa-solid fa-triangle-exclamation"></i> ยืนยันการลบ
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="delete-id">
                        <p class="mb-0">คุณแน่ใจหรือไม่ว่าต้องการลบรายการนี้?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-danger">ตกลง</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal แจ้งผลนำเข้าสำเร็จ -->
    <div class="modal fade" id="importSuccessModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center p-4">
                <h5 class="mb-3">
                    <i class="fa-solid fa-circle-check text-success fa-2x"></i>
                </h5>
                <p class="mb-3">นำเข้าข้อมูลสำเร็จแล้ว</p>
                <div>
                    <button style="width: 30%;" type="button" class="btn btn-success" data-bs-dismiss="modal">
                        ปิด
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loading-overlay"
        style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(255,255,255,0.7); z-index:9999;">
        <div class="d-flex justify-content-center align-items-center h-100">
            <div class="spinner-border text-primary" style="width: 4rem; height: 4rem;" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>

    <!-- Modal แจ้งผลส่งอีเมลสำเร็จ -->
    <div class="modal fade" id="emailSuccessModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center p-4">
                <h5 class="mb-3">
                    <i class="fa-solid fa-circle-check text-success fa-2x"></i>
                </h5>
                <p class="mb-3">ส่งอีเมลแจ้งเตือนพัสดุสำเร็จแล้ว</p>
                <div>
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal แจ้งผลส่งอีเมลล้มเหลว -->
    <div class="modal fade" id="emailFailModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center p-4">
                <h5 class="mb-3">
                    <i class="fa-solid fa-circle-xmark text-danger fa-2x"></i>
                </h5>
                <p class="mb-3">เกิดข้อผิดพลาดในการส่งอีเมล</p>
                <div>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: ไม่พบพัสดุใกล้หมดอายุ -->
    <div class="modal fade" id="noExpiringModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center p-4">
                <h5 class="mb-3">
                    <i class="fa-solid fa-circle-info text-warning fa-2x"></i>
                </h5>
                <p class="mb-3" id="no-expiring-message">
                    ไม่พบพัสดุที่ใกล้หมดอายุของคุณ <strong id="no-expiring-name"></strong>
                </p>
                <div>
                    <button type="button" class="btn btn-warning" data-bs-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($_GET['email_sent']) && $_GET['email_sent'] == 0): ?>
    <!-- Modal แจ้งเตือน -->
    <div class="modal fade" id="emailFailedModal" tabindex="-1" aria-labelledby="emailFailedModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="emailFailedModalLabel">ไม่พบพัสดุ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    ไม่พบพัสดุที่ใกล้หมดอายุที่ได้รับการอนุมัติแล้ว
                </div>
            </div>
        </div>
    </div>

    <script>
    var emailModal = new bootstrap.Modal(document.getElementById('emailFailedModal'));
    emailModal.show();
    </script>
    <?php endif; ?>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
    console.log("Session Info:");
    console.log("User ID:", <?php echo json_encode($_SESSION['user_id'] ?? 'ไม่พบ'); ?>);
    console.log("Full Name:", <?php echo json_encode($_SESSION['fullname'] ?? 'ไม่พบ'); ?>);
    console.log("Email:", <?php echo json_encode($_SESSION['user_email'] ?? 'ไม่พบ'); ?>);
    console.log("Role:", <?php echo json_encode($_SESSION['user_role'] ?? 'ไม่พบ'); ?>);

    $("#dropdown-request").on('change', function() {
        $('#search-form').submit();
    });

    $("#excel-file").on("change", function() {
        $("#loading-overlay").show(); // แสดง overlay
        $("#import-form").submit();
    });

    document.getElementById("send-email-btn").addEventListener("click", function() {
        document.getElementById("loading-overlay").style.display = "block";
    });


    $(document).ready(function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get("email_sent") === "1") {
            const modal = new bootstrap.Modal(document.getElementById("emailSuccessModal"));
            modal.show();
            document.getElementById("loading-overlay").style.display = "none";
            setTimeout(() => {
                modal.hide();
                window.history.replaceState({}, document.title, window.location.pathname);
            }, 2500);
        }

        if (urlParams.get("no_expiring") === "1") {
            const fullname = decodeURIComponent(urlParams.get("name") || '');
            document.getElementById("no-expiring-name").textContent = fullname;

            const modal = new bootstrap.Modal(document.getElementById("noExpiringModal"));
            modal.show();
            document.getElementById("loading-overlay").style.display = "none";
            // ลบ query หลังจากแสดงแล้ว
            setTimeout(() => {
                window.history.replaceState({}, document.title, window.location.pathname);
            }, 1500);
        }

        if (urlParams.get("email_sent") === "0") {
            const modal = new bootstrap.Modal(document.getElementById("emailFailModal"));
            modal.show();
            document.getElementById("loading-overlay").style.display = "none";
            setTimeout(() => {
                modal.hide();
                window.history.replaceState({}, document.title, window.location.pathname);
            }, 2500);
        }


        if (urlParams.get("success") === "1") {
            const modal = new bootstrap.Modal(document.getElementById("importSuccessModal"));
            modal.show();
            document.getElementById("loading-overlay").style.display = "none";
            setTimeout(() => {
                modal.hide();
                window.history.replaceState({}, document.title, window.location.pathname);
            }, 2500);
        }

        $('.select2-user').select2({
            placeholder: "-- เลือกผู้ใช้งาน --",
            width: '100%',
            allowClear: true
        });

        // สำหรับอีเมล
        $('.select2-email').select2({
            placeholder: "เลือกหรือพิมพ์อีเมลผู้รับ...",
            width: '100%',
            tags: true, // ให้พิมพ์อีเมลเองได้
            tokenSeparators: [',', ' '],
            maximumSelectionLength: 10 // จำกัดจำนวนสูงสุดถ้าต้องการ
        });

        $('#addModal, #editModal').on('shown.bs.modal', function() {
            $(this).find('.select2-user').select2({
                dropdownParent: $(this),
                width: '100%',
                placeholder: "-- เลือกผู้ใช้งาน --",
                allowClear: true
            });
        });

        $('#emailModal').on('shown.bs.modal', function() {
            $(this).find('.select2-email').select2({
                dropdownParent: $(this),
                width: '100%',
                placeholder: "เลือกหรือพิมพ์อีเมลผู้รับ...",
                tags: true
            });
        });

        let typingTimer;
        const doneTypingInterval = 500;

        $("#search-input").on("keyup", function() {
            clearTimeout(typingTimer);
            const searchValue = $(this).val().trim();
            const params = new URLSearchParams(window.location.search);

            // อัปเดต URL query ทันทีที่พิมพ์
            if (searchValue !== "") {
                params.set("search", searchValue);
            } else {
                params.delete("search");
            }

            const newUrl = window.location.pathname + "?" + params.toString();
            window.history.replaceState({}, "", newUrl);

            // เรียก AJAX หลังจากพิมพ์เสร็จ
            typingTimer = setTimeout(function() {
                $.get("search_parcels_user.php", {
                    search: searchValue
                }, function(data) {
                    $("#parcel-table-body").html(data);
                });
            }, doneTypingInterval);
        });
    })


    $(document).on("click", ".btn-edit", function() {
        const btn = $(this);
        const status = btn.data("status");

        $("#edit-id").val(btn.data("id"));
        $("#edit-item_name").val(btn.data("item_name"));
        $("#edit-category").val(btn.data("category"));
        $("#edit-usage_duration").val(btn.data("usage_duration"));
        $("#edit-price").val(btn.data("price"));
        $("#edit-budget_year").val(btn.data("budget_year"));
        $("#edit-user_responsible").val(btn.data("user_responsible")).change();
        $("#edit-note").val(btn.data("note"));

        const form = $("#editModal form");
        const submitBtn = $("#editModal button[type=submit]");
        if (status === "approved") {
            form.attr("action", "addnew_parcel_user.php");
            submitBtn.text("เพิ่มรายการ");
            submitBtn.removeClass("btn-primary").addClass("btn-success");
            $("#edit-id").val(""); // ไม่ส่ง id ถ้าจะเพิ่มใหม่
        } else {
            form.attr("action", "edit_parcel_user.php");
            submitBtn.text("อัปเดต");
            submitBtn.removeClass("btn-success").addClass("btn-primary");
        }
    });

    $(document).on("click", ".btn-delete", function() {
        const id = $(this).data("id");
        $("#delete-id").val(id);
    });

    document.getElementById("export-btn").addEventListener("click", function() {
        const params = new URLSearchParams(window.location.search);
        window.location.href = "export_parcels_user.php?" + params.toString();
    });
    </script>

</body>


</html>