<?php
session_start();
require 'db.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['user_email']) || empty($_SESSION['user_email'])) {
    header("Location: parcel_management_user.php?email_sent=0");
    exit();
}

$userEmail = $_SESSION['user_email'];
$userFullname = $_SESSION['fullname'];
$today = date('Y-m-d');
$next30 = date('Y-m-d', strtotime('+30 days'));

$sql = "SELECT * FROM parcels 
        WHERE end_date BETWEEN ? AND ? 
        AND status = 'approved'
        AND user_responsible = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $today, $next30, $userFullname);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $name = urlencode($userFullname);
    header("Location: parcel_management_user.php?no_expiring=1&name=$name");
    exit();
}

// สร้างไฟล์ Excel ชั่วคราว
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->fromArray(['ชื่อ', 'ประเภท', 'ระยะเวลา', 'ราคา', 'งปม.', 'เริ่มต้น', 'สิ้นสุด', 'ผู้ใช้งาน', 'หมายเหตุ'], NULL, 'A1');

$rowIndex = 2;
while ($row = $result->fetch_assoc()) {
    $sheet->fromArray([
        $row['item_name'],
        $row['category'],
        $row['usage_duration'],
        $row['price'],
        $row['budget_year'],
        $row['start_date'],
        $row['end_date'],
        $row['user_responsible'],
        $row['note'] ?: '-'
    ], NULL, 'A' . $rowIndex++);
}

$tmpFile = tempnam(sys_get_temp_dir(), 'expiring_') . '.xlsx';
$writer = new Xlsx($spreadsheet);
$writer->save($tmpFile);

// ส่งอีเมล
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'satreertapp@gmail.com';
    $mail->Password = 'oxluxaoyrgcneqyo';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';

    $mail->setFrom('satreertapp@gmail.com', 'ระบบติดตามพัสดุ');
    $mail->addAddress($userEmail, $userFullname);

    $mail->Subject = 'แจ้งเตือนพัสดุใกล้หมดอายุ';
    $mail->Body = "เรียนคุณ $userFullname\n\nกรุณาตรวจสอบรายการพัสดุที่ใกล้หมดอายุจากไฟล์แนบด้านล่าง";
    $mail->addAttachment($tmpFile, 'expiring_parcels.xlsx');

    $mail->send();
    unlink($tmpFile);

    header("Location: parcel_management_user.php?email_sent=1");
    exit();
} catch (Exception $e) {
    unlink($tmpFile);
    header("Location: parcel_management_user.php?email_sent=0");
    exit();
}
