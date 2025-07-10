<?php
session_start();
require_once 'db.php';

$search = $_GET['search'] ?? '';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

$searchSql = $conn->real_escape_string($search);
$startDateSql = $conn->real_escape_string($startDate);
$endDateSql = $conn->real_escape_string($endDate);

$sql = "SELECT * FROM parcels WHERE 1=1";

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
        <div class="container py-1">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2><i class="fa-solid fa-box"></i> ระบบจัดการพัสดุ</h2>
                <div>
                    <a id="export-btn" class="btn btn-success me-2">
                        <i class="fa-solid fa-file-export"></i> ส่งออก Excel
                    </a>
                    <!-- ปุ่มนำเข้า -->
                    <form action="import_parcels.php" method="POST" enctype="multipart/form-data" id="import-form"
                        style="display: inline;">
                        <input type="file" name="excel_file" id="excel-file" accept=".xlsx" style="display: none;"
                            onchange="document.getElementById('import-form').submit();">
                        <button type="button" class="btn btn-secondary me-2"
                            onclick="document.getElementById('excel-file').click();">
                            <i class="fa-solid fa-file-import"></i> นำเข้า Excel
                        </button>
                    </form>

                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal"><i
                            class="fa-solid fa-plus"></i> เพิ่มพัสดุ</button>
                </div>
            </div>

            <!-- ฟิลเตอร์ -->
            <div class="row g-3 mb-4">
                <!-- ฟิลเตอร์ -->
                <form method="GET" id="search-form" class="row g-3 align-items-end mb-4">
                    <div class="col-md-4">
                        <input type="text" name="search" id="search-input" class="form-control"
                            placeholder="ค้นหาชื่อพัสดุ..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" />
                    </div>

                    <div class="col-md-3">
                        <label for="start-date" class="form-label">วันที่เริ่มต้นใช้งาน</label>
                        <input type="date" name="start_date" id="start-date" class="form-control"
                            value="<?= htmlspecialchars($_GET['start_date'] ?? '') ?>" />
                    </div>

                    <div class="col-md-3">
                        <label for="end-date" class="form-label">วันที่สิ้นสุดใช้งาน</label>
                        <input type="date" name="end_date" id="end-date" class="form-control"
                            value="<?= htmlspecialchars($_GET['end_date'] ?? '') ?>" />
                    </div>

                    <div class="col-md-1 d-grid" style="white-space: nowrap;">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fa-solid fa-filter"></i> ค้นหา
                        </button>
                    </div>

                    <div class="col-md-1 d-grid" style="white-space: nowrap;">
                        <a href="parcel_management.php" class="btn btn-outline-secondary">
                            <i class="fa-solid fa-rotate-left"></i> ล้างข้อมูล
                        </a>
                    </div>
                </form>

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
                        <tr class="header-table">
                            <th>ลำดับ</th>
                            <th>ชื่อ</th>
                            <th>User/License</th>
                            <th>ระยะเวลา</th>
                            <th>ราคา</th>
                            <th>งปม</th>
                            <th>เริ่มต้นใช้งาน</th>
                            <th>สิ้นสุดการใช้งาน</th>
                            <th>ผู้ใช้งาน</th>
                            <th>หมายเหตุ</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody id="parcel-table-body">
                        <?php if ($result->num_rows > 0): ?>
                        <?php $i = 1; while($row = $result->fetch_assoc()): ?>
                        <tr class="text-center">
                            <td><?= $i++ ?></td>
                            <td class="text-left"><?= htmlspecialchars($row['item_name']) ?></td>
                            <td><?= htmlspecialchars($row['user_license']) ?></td>
                            <td><?= htmlspecialchars($row['usage_duration']) ?></td>
                            <td style="white-space: nowrap;"><?= htmlspecialchars($row['price']) ?></td>
                            <td style="white-space: nowrap;"><?= htmlspecialchars($row['budget_year']) ?></td>
                            <td style="white-space: nowrap;"><?= (new DateTime($row['start_date']))->format('d-m-Y') ?>
                            </td>
                            <td style="white-space: nowrap;"><?= (new DateTime($row['end_date']))->format('d-m-Y') ?>
                            </td>
                            <td style="white-space: nowrap;"><?= htmlspecialchars($row['user_responsible']) ?></td>
                            <td><?= htmlspecialchars($row['note']) ?: '-' ?></td>
                            <td class="btn-edit-delete">
                                <button class="btn btn-sm btn-warning btn-edit" data-bs-toggle="modal"
                                    data-bs-target="#editModal" data-id="<?= $row['id'] ?>"
                                    data-item_name="<?= htmlspecialchars($row['item_name']) ?>"
                                    data-user_license="<?= $row['user_license'] ?>"
                                    data-usage_duration="<?= $row['usage_duration'] ?>"
                                    data-price="<?= $row['price'] ?>" data-budget_year="<?= $row['budget_year'] ?>"
                                    data-start_date="<?= $row['start_date'] ?>" data-end_date="<?= $row['end_date'] ?>"
                                    data-user_responsible="<?= htmlspecialchars($row['user_responsible']) ?>"
                                    data-note="<?= htmlspecialchars($row['note']) ?>">
                                    <i class="fa-solid fa-pen"></i>
                                </button>

                                <button class="btn btn-sm btn-danger btn-delete" data-bs-toggle="modal"
                                    data-bs-target="#deleteModal" data-id="<?= $row['id'] ?>">
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
                    <form action="add_parcel.php" method="POST">
                        <div class="row g-3" style="margin-bottom: 20px;">
                            <div class="col-md-6">
                                <label class="form-label">ชื่อ</label>
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
                                <label class="form-label">เริ่มต้นใช้งาน</label>
                                <input type="date" name="start_date" class="form-control" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">สิ้นสุดการใช้งาน</label>
                                <input type="date" name="end_date" class="form-control" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ระยะเวลา</label>
                                <input type="number" name="usage_duration" class="form-control" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ผู้ใช้งาน</label>
                                <select name="user_responsible" class="form-select select2" required>
                                    <option value="">-- เลือกผู้ใช้งาน --</option>
                                    <?php foreach ($userOptions as $user): ?>
                                    <option value="<?= $user ?>"><?= htmlspecialchars($user) ?></option>
                                    </option>
                                    <?php endforeach; ?>
                                </select>

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

    <!-- Modal แก้ไขพัสดุ -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="edit_parcel.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fa-solid fa-pen-to-square"></i> แก้ไขข้อมูลพัสดุ</h5>
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
                                <label class="form-label">User/License</label>
                                <input type="number" name="user_license" id="edit-user_license" class="form-control"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ปีงบประมาณ</label>
                                <input type="text" name="budget_year" id="edit-budget_year" class="form-control"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ราคา</label>
                                <input type="number" name="price" id="edit-price" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">เริ่มต้นใช้งาน</label>
                                <input type="date" name="start_date" id="edit-start_date" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">สิ้นสุดการใช้งาน</label>
                                <input type="date" name="end_date" id="edit-end_date" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ระยะเวลา</label>
                                <input type="number" name="usage_duration" id="edit-usage_duration" class="form-control"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ผู้ใช้งาน</label>
                                <select name="user_responsible" id="edit-user_responsible" class="form-select select2"
                                    required>
                                    <option value="">-- เลือกผู้ใช้งาน --</option>
                                    <?php foreach ($userOptions as $user): ?>
                                    <option value="<?= $user ?>"><?= htmlspecialchars($user) ?></option>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
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
                <form action="delete_parcel.php" method="POST">
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

    $("#excel-file").on("change", function() {
        $("#loading-overlay").show(); // แสดง overlay
        $("#import-form").submit();
    });

    $(document).ready(function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get("success") === "1") {
            const modal = new bootstrap.Modal(document.getElementById("importSuccessModal"));
            modal.show();
            setTimeout(() => {
                modal.hide();
                window.history.replaceState({}, document.title, window.location.pathname);
            }, 2500);
        }

        $('.select2').select2({
            width: '100%',
            placeholder: "-- เลือกผู้ใช้งาน --",
            allowClear: true
        });

        $('#addModal, #editModal').on('shown.bs.modal', function() {
            $(this).find('.select2').select2({
                width: '100%',
                dropdownParent: $(this)
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
                $.get("search_parcels.php", {
                    search: searchValue
                }, function(data) {
                    $("#parcel-table-body").html(data);
                });
            }, doneTypingInterval);
        });
    })


    $(document).on("click", ".btn-edit", function() {
        const btn = $(this);
        $("#edit-id").val(btn.data("id"));
        $("#edit-item_name").val(btn.data("item_name"));
        $("#edit-user_license").val(btn.data("user_license"));
        $("#edit-usage_duration").val(btn.data("usage_duration"));
        $("#edit-price").val(btn.data("price"));
        $("#edit-budget_year").val(btn.data("budget_year"));
        $("#edit-start_date").val(btn.data("start_date"));
        $("#edit-end_date").val(btn.data("end_date"));
        $("#edit-user_responsible").val(btn.data("user_responsible")).change();
        $("#edit-note").val(btn.data("note"));
    });

    $(document).on("click", ".btn-delete", function() {
        const id = $(this).data("id");
        $("#delete-id").val(id);
    });

    document.getElementById("export-btn").addEventListener("click", function() {
        const params = new URLSearchParams(window.location.search);
        window.location.href = "export_parcels.php?" + params.toString();
    });
    </script>

</body>


</html>