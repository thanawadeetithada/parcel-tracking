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
            $stmt = $conn->prepare("INSERT INTO parcels (item_name, user_license, usage_duration, price, budget_year, start_date, end_date, user_responsible, note) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param(
                "siidsssss",
                $row[0],  // item_name
                $row[1],  // user_license
                $row[2],  // usage_duration
                $row[3],  // price
                $row[4],  // budget_year
                $row[5],  // start_date
                $row[6],  // end_date
                $row[7],  // user_responsible
                $row[8]   // note
            );
            $stmt->execute();
        }
        $conn->commit();
        echo "นำเข้าข้อมูลสำเร็จ";
        header("Location: parcel_management.php?success=1");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
}
?>
