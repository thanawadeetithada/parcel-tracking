<?php
    session_start();
    require_once 'db.php';

    if (! isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit();
    }

    $search    = $_GET['search'] ?? '';
    $startDate = $_GET['start_date'] ?? '';
    $endDate   = $_GET['end_date'] ?? '';
    $statusFilter = $_GET['status'] ?? '';

    $searchSql    = $conn->real_escape_string($search);
    $startDateSql = $conn->real_escape_string($startDate);
    $endDateSql   = $conn->real_escape_string($endDate);

    $sql = "SELECT * FROM parcels WHERE 1=1";

    if (! empty($statusFilter)) {
        $sql .= " AND status = '$statusFilter'";
    }

    if (! empty($searchSql)) {
        $sql .= " AND item_name LIKE '%$searchSql%'";
    }
    if (! empty($startDateSql)) {
        $sql .= " AND start_date >= '$startDateSql'";
    }
    if (! empty($endDateSql)) {
        $sql .= " AND end_date <= '$endDateSql'";
    }

    $sql .= " ORDER BY id DESC";
    $result = $conn->query($sql);

    $userOptions = [];
    $userResult  = $conn->query("SELECT fullname FROM users ORDER BY fullname ASC");
    if ($userResult && $userResult->num_rows > 0) {
        while ($row = $userResult->fetch_assoc()) {
            $userOptions[] = $row['fullname'];
        }
    }

    $today            = date('Y-m-d');
    $next3Days        = date('Y-m-d', strtotime('+30 days'));
    $sqlExpiringCount = "SELECT COUNT(*) as count FROM parcels WHERE end_date BETWEEN '$today' AND '$next3Days'";
    $expiringCount    = $conn->query($sqlExpiringCount)->fetch_assoc()['count'];

    $emailOptions = [];
    $emailResult  = $conn->query("SELECT email FROM users WHERE email IS NOT NULL AND email != '' ORDER BY email ASC");
    while ($row = $emailResult->fetch_assoc()) {
        $emailOptions[] = $row['email'];
    }
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ขออนุมัติ</title>
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
            <a class="navbar-brand" href="parcel_approve.php">ขออนุมัติ</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'superadmin')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">ระบบจัดการพัสดุในหน่วยงาน</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="parcel_management.php">จัดการพัสดุ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="parcel_approve.php">ขออนุมัติ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="user_management.php">จัดการผู้ใช้งาน</a>
                    </li>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'user')): ?>
                    <li class="nav-item">
                        <a class="nav-link active" href="parcel_management_user.php">จัดการพัสดุ</a>
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
            <h3><i class="fa-solid fa-box"></i> ระบบจัดการพัสดุ</h3><br>
            <div class="mb-3">
                <div class="row">
                    <div
                        class="col-12 d-flex flex-wrap align-items-end gap-2 flex-column flex-md-row justify-content-md-end">
                        <!-- <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-warning" data-bs-toggle="modal"
                                data-bs-target="#emailModal">
                                <i class="fa-solid fa-envelope"></i> ส่ง Email แจ้งเตือน
                            </button>
                        </div> -->

                        <!-- บรรทัดใหม่ -->
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            <a id="export-btn" class="btn btn-success">
                                <i class="fa-solid fa-file-export"></i> ส่งออก Excel
                            </a>
                        </div>
                    </div>
                </div>
            </div>

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
                            <a href="parcel_approve.php" class="btn btn-outline-secondary">
                                <i class="fa-solid fa-rotate-left"></i> ล้างข้อมูล
                            </a>
                        </div>
                    </div>
                </form>
            </div>


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
                            <?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'superadmin')): ?>
                            <th>จัดการ</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody id="parcel-table-body">
                        <?php if ($result->num_rows > 0): ?>
                        <?php $i = 1;while ($row = $result->fetch_assoc()): ?>
                        <tr class="text-center">
                            <td><?php echo $i++?></td>
                            <td class="text-left"><?php echo htmlspecialchars($row['item_name'])?></td>
                            <td><?php echo htmlspecialchars($row['category'])?></td>
                            <td><?php echo htmlspecialchars($row['usage_duration'])?></td>
                            <td style="white-space: nowrap;"><?php echo htmlspecialchars($row['price'])?></td>
                            <td style="white-space: nowrap;"><?php echo htmlspecialchars($row['budget_year'])?></td>
                            <td style="white-space: nowrap;">
                                <?php echo (new DateTime($row['start_date']))->format('d-m-Y')?>
                            </td>
                            <td style="white-space: nowrap;">
                                <?php echo (new DateTime($row['end_date']))->format('d-m-Y')?>
                            </td>
                            <td style="white-space: nowrap;"><?php echo htmlspecialchars($row['user_responsible'])?>
                            </td>

                            <td><?php echo htmlspecialchars($row['note']) ?: '-'?></td>
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

                            <?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'superadmin')): ?>
                            <td class="btn-edit-delete">
                                <button class="btn btn-sm btn-warning btn-edit" data-bs-toggle="modal"
                                    data-bs-target="#editModal" data-id="<?php echo $row['id']?>"
                                    data-item_name="<?php echo htmlspecialchars($row['item_name'])?>"
                                    data-category="<?php echo $row['category']?>"
                                    data-usage_duration="<?php echo $row['usage_duration']?>"
                                    data-price="<?php echo $row['price']?>"
                                    data-budget_year="<?php echo $row['budget_year']?>"
                                    data-start_date="<?php echo $row['start_date']?>"
                                    data-end_date="<?php echo $row['end_date']?>"
                                    data-user_responsible="<?php echo htmlspecialchars($row['user_responsible'])?>"
                                    data-note="<?php echo htmlspecialchars($row['note'])?>"
                                    data-status="<?= $row['status'] ?>">

                                    <i class="fa-solid fa-pen"></i>
                                </button>

                                <button class="btn btn-sm btn-danger btn-delete" data-bs-toggle="modal"
                                    data-bs-target="#deleteModal" data-id="<?php echo $row['id']?>">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                            <?php endif; ?>
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

    <!-- Modal แก้ไขพัสดุ -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="fetch_approve_parcel.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fa-solid fa-pen-to-square"></i> อนุมัติข้อมูลพัสดุ</h5>
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
                                <select name="user_responsible" id="edit-user_responsible"
                                    class="form-select select2-user" required>
                                    <option value="">-- เลือกผู้ใช้งาน --</option>
                                    <?php foreach ($userOptions as $user): ?>
                                    <option value="<?php echo $user?>"><?php echo htmlspecialchars($user)?></option>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">หมายเหตุ</label>
                                <input type="text" name="note" id="edit-note" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">อนุมัติการใช้งาน</label>
                                <select name="status" id="edit-status" class="form-select" required>
                                    <option value="pending">⏳ รออนุมัติ</option>
                                    <option value="approved">✅ อนุมัติ</option>
                                    <option value="rejected">❌ ไม่อนุมัติ</option>
                                </select>
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
                <form action="delete_parcel_approve.php" method="POST">
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

    <!-- Loading Overlay -->
    <div id="loading-overlay"
        style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(255,255,255,0.7); z-index:9999;">
        <div class="d-flex justify-content-center align-items-center h-100">
            <div class="spinner-border text-primary" style="width: 4rem; height: 4rem;" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>

    <div class="modal fade" id="emailModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="send_expiring_email.php" method="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fa-solid fa-envelope"></i> เลือกอีเมลผู้รับ</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">เลือกอีเมล (เลือกได้หลายคน)</label>
                        <select name="emails[]" class="form-select select2-email" multiple required>
                            <?php foreach ($emailOptions as $email): ?>
                            <option value="<?= $email ?>"><?= $email ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">หากไม่มีในรายการสามารถพิมพ์เพิ่มได้</small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-warning">ส่งอีเมล</button>
                    </div>
                </div>
            </form>
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

    $("#dropdown-request").on('change', function() {
        $('#search-form').submit();
    });

    $("#excel-file").on("change", function() {
        $("#loading-overlay").show(); // แสดง overlay
        $("#import-form").submit();
    });

    $(document).ready(function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get("email_sent") === "1") {
            const modal = new bootstrap.Modal(document.getElementById("emailSuccessModal"));
            modal.show();
            setTimeout(() => {
                modal.hide();
                window.history.replaceState({}, document.title, window.location.pathname);
            }, 2500);
        }

        if (urlParams.get("success") === "1") {
            const modal = new bootstrap.Modal(document.getElementById("importSuccessModal"));
            modal.show();
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
        $("#edit-category").val(btn.data("category"));
        $("#edit-usage_duration").val(btn.data("usage_duration"));
        $("#edit-price").val(btn.data("price"));
        $("#edit-budget_year").val(btn.data("budget_year"));
        $("#edit-start_date").val(btn.data("start_date"));
        $("#edit-end_date").val(btn.data("end_date"));
        $("#edit-user_responsible").val(btn.data("user_responsible")).change();
        $("#edit-note").val(btn.data("note"));
        $("#edit-status").val(btn.data("status")).change();
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