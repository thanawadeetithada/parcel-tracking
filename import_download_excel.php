<?php
require 'vendor/autoload.php'; // ใช้ PhpSpreadsheet
require_once 'db.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file']['tmp_name'];

    if (!$file) {
        die("Error: กรุณาอัปโหลดไฟล์ Excel");
    }

    try {
        $spreadsheet = IOFactory::load($file);
        $worksheet = $spreadsheet->getActiveSheet();
        $data = $worksheet->toArray();

        // ตรวจสอบว่าไฟล์มีข้อมูลหรือไม่
        if (count($data) <= 1) {
            die("Error: ไม่มีข้อมูลในไฟล์");
        }

        $conn->begin_transaction(); // ใช้ transaction เพื่อความปลอดภัย
        
        foreach ($data as $index => $row) {
            if ($index == 0) continue; // ข้ามแถวหัวตาราง

            $registration_number = $row[0] ?? '';
            $fullname = $row[1] ?? '';
            $college = $row[2] ?? '';
            $date_faculty_received = !empty($row[3]) ? date('Y-m-d', strtotime($row[3])) : NULL;
            $committee_approval_date = !empty($row[4]) ? date('Y-m-d', strtotime($row[4])) : NULL;
            $faculty_approval_date = !empty($row[5]) ? date('Y-m-d', strtotime($row[5])) : NULL;
            
            $book_number_HR_data = explode("\n", $row[6] ?? '');
            $book_number_HR_date = !empty($book_number_HR_data[0]) ? date('Y-m-d', strtotime($book_number_HR_data[0])) : NULL;
            $book_number_HR = $book_number_HR_data[1] ?? '';
            
            $passed_institution_data = explode("\n", $row[7] ?? '');
            $passed_institution_date = !empty($passed_institution_data[0]) ? date('Y-m-d', strtotime($passed_institution_data[0])) : NULL;
            $passed_institution = $passed_institution_data[1] ?? '';
            
            $sql = "INSERT INTO faculty_progress (registration_number, fullname, college, date_faculty_received, 
                    committee_approval_date, faculty_approval_date, book_number_HR_date, book_number_HR, 
                    passed_institution_date, passed_institution) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssssssssss', $registration_number, $fullname, $college, $date_faculty_received,
                $committee_approval_date, $faculty_approval_date, $book_number_HR_date, $book_number_HR,
                $passed_institution_date, $passed_institution);
            $stmt->execute();
        }

        $conn->commit();
        echo "<script>alert('นำเข้าข้อมูลสำเร็จ'); window.location.href='dashboard.php';</script>";
    } catch (Exception $e) {
        $conn->rollback();
        die("Error: " . $e->getMessage());
    }
}
?>
