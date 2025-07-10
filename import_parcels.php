<?php
require 'vendor/autoload.php';
require 'db.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] === 0) {
    $spreadsheet = IOFactory::load($_FILES['excel_file']['tmp_name']);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    $conn->begin_transaction();

    try {
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            // แยกค่าจาก Excel
            $item_name = $row[0];
            $category = $row[1];
            $usage_duration = intval($row[2]);
            $price = floatval($row[3]);
            $budget_year = $row[4];
            $startDate = date('Y-m-d', strtotime($row[5]));
            $endDate   = date('Y-m-d', strtotime($row[6]));
            $user_responsible = $row[7];
            $note = !empty($row[8]) ? $row[8] : '-';

            $stmt = $conn->prepare("
                INSERT INTO parcels (
                    item_name, category, usage_duration, price, budget_year,
                    start_date, end_date, user_responsible, note
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "ssidsssss",
                $item_name,
                $category,
                $usage_duration,
                $price,
                $budget_year,
                $startDate,
                $endDate,
                $user_responsible,
                $note
            );

            $stmt->execute();
        }

        $conn->commit();
        header("Location: parcel_management.php?success=1");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        echo "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
}
?>
