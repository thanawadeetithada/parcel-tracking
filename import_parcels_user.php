<?php
session_start();
require_once 'db.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (!isset($_SESSION['fullname'])) {
    header("Location: parcel_management_user.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $fileTmpPath = $_FILES['excel_file']['tmp_name'];
    $spreadsheet = IOFactory::load($fileTmpPath);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    // ลบ header ออก
    unset($rows[0]);

    $fullname = $_SESSION['fullname'];
    $importSuccess = false;
    $today = date('Y-m-d');

    foreach ($rows as $row) {
        $item_name      = trim($row[0] ?? '');
        $category       = trim($row[1] ?? '');
        $usage_duration = intval($row[2] ?? 0);
        $price          = floatval($row[3] ?? 0);
        $budget_year    = trim($row[4] ?? '');
        $note           = trim($row[5] ?? '');

        if (empty($item_name) || empty($category) || empty($budget_year)) {
            continue; // ข้ามแถวว่างหรือไม่ครบ
        }

        // คำนวณวันที่เริ่มและสิ้นสุด
        $start_date = $today;
        $end_date = date('Y-m-d', strtotime("$start_date +$usage_duration days"));

        $stmt = $conn->prepare("INSERT INTO parcels 
            (item_name, category, usage_duration, price, budget_year, note, user_responsible, status, start_date, end_date)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?)");
        $stmt->bind_param("ssidsssss", $item_name, $category, $usage_duration, $price, $budget_year, $note, $fullname, $start_date, $end_date);
        $stmt->execute();
        $importSuccess = true;
    }

    if ($importSuccess) {
        header("Location: parcel_management_user.php?success=1");
    } else {
        header("Location: parcel_management_user.php?success=0");
    }
    exit();
}
?>
