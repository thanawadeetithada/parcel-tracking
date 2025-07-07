<?php
////  export excel 
ob_start();

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
require_once 'db.php';

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

//ตั้งชื่อหัวตาราง
$sheet->setCellValue('A1', 'เลขทะเบียนหนังสือ');
$sheet->setCellValue('B1', 'ชื่อ-สกุล');
$sheet->setCellValue('C1', 'วิทยาลัย');
$sheet->setCellValue('D1', 'วัน / เดือน / ปี คณะรับเล่มผลงานทางวิชาการ');
$sheet->setCellValue('E1', 'วัน / เดือน / ปี ผ่านอนุกรรมการตรวจสอบ');
$sheet->setCellValue('F1', 'วัน / เดือน / ปี ผ่านคณะกรรมการประจำ');
$sheet->setCellValue('G1', 'เลขที่หนังสือ นำส่งทรัพยากรบุคคล');
$sheet->setCellValue('H1', 'ผ่านมติสภาสถาบัน พระบรมราชชนก');

$sql = "SELECT * FROM faculty_progress";   //ข้อมูลจาก table datanase
$result = $conn->query($sql);
$rowIndex = 2;
//ข้อมูลที่นำไปใส่
while ($row = $result->fetch_assoc()) {
    $sheet->setCellValue('A' . $rowIndex, $row['registration_number']);
    $sheet->setCellValue('B' . $rowIndex, $row['prefix'] . ' ' . $row['fullname']);
    $sheet->setCellValue('C' . $rowIndex, $row['college']);
    $sheet->setCellValue('D' . $rowIndex, !empty($row['date_faculty_received']) && $row['date_faculty_received'] !== '0000-00-00' ? date('d/m/Y', strtotime($row['date_faculty_received'])) : 'อยู่ระหว่างการตรวจสอบ');
    $sheet->setCellValue('E' . $rowIndex, !empty($row['committee_approval_date']) && $row['committee_approval_date'] !== '0000-00-00' ? date('d/m/Y', strtotime($row['committee_approval_date'])) : 'อยู่ระหว่างการตรวจสอบ');
    $sheet->setCellValue('F' . $rowIndex, !empty($row['faculty_approval_date']) && $row['faculty_approval_date'] !== '0000-00-00' ? date('d/m/Y', strtotime($row['faculty_approval_date'])) : 'อยู่ระหว่างการตรวจสอบ');
    
    
    $bookNumberHR = !empty($row['book_number_HR']) ? $row['book_number_HR'] : 'อยู่ระหว่างการตรวจสอบ';
    $bookNumberHRDate = !empty($row['book_number_HR_date']) && $row['book_number_HR_date'] !== '0000-00-00' ? date('d/m/Y', strtotime($row['book_number_HR_date'])) : '';
    $cellValue = $bookNumberHRDate ? $bookNumberHRDate . "\n" . $bookNumberHR : $bookNumberHR;
    $sheet->setCellValueExplicit('G' . $rowIndex, $cellValue, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->getStyle('G' . $rowIndex)->getAlignment()->setWrapText(true);

    $passedInstitution = !empty($row['passed_institution']) ? $row['passed_institution'] : 'อยู่ระหว่างการตรวจสอบ';
    $passedInstitutionDate = !empty($row['passed_institution_date']) && $row['passed_institution_date'] !== '0000-00-00' ? date('d/m/Y', strtotime($row['passed_institution_date'])) : '';
    $cellValue = $passedInstitutionDate ? $passedInstitutionDate . "\n" . $passedInstitution : $passedInstitution;
    $sheet->setCellValueExplicit('H' . $rowIndex, $cellValue, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->getStyle('H' . $rowIndex)->getAlignment()->setWrapText(true);
    $rowIndex++;
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Report.xlsx"');  //ชื่อไฟล์ดาวโหลด
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

ob_end_flush();
exit;
?>
