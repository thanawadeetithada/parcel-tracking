<?php
require_once 'db.php';

require 'vendor/autoload.php'; // PhpSpreadsheet
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Header
$sheet->fromArray([
    'ชื่อ', 'User/License', 'ระยะเวลา', 'ราคา', 'งปม',
    'เริ่มใช้งาน', 'สิ้นสุดใช้งาน', 'ผู้ใช้งาน', 'หมายเหตุ'
], NULL, 'A1');

// Rows
$rowIndex = 2;
while ($row = $result->fetch_assoc()) {
    $sheet->fromArray([
        $row['item_name'],
        $row['user_license'],
        $row['usage_duration'],
        $row['price'],
        $row['budget_year'],
        $row['start_date'],
        $row['end_date'],
        $row['user_responsible'],
        $row['note'] ?: '-'
    ], NULL, 'A' . $rowIndex);
    $rowIndex++;
}

// Output
$filename = "parcels_export_" . date('Ymd_His') . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
$writer = new Xlsx($spreadsheet);
$writer->save("php://output");
exit;
