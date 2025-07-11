<?php
require 'db.php';
require 'vendor/autoload.php'; // สำหรับ PhpSpreadsheet และ PHPMailer

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ดึงรายการพัสดุที่ใกล้หมดอายุใน 3 วัน
$today = date('Y-m-d');
$next3Days = date('Y-m-d', strtotime('+30 days'));
$sql = "SELECT * FROM parcels WHERE end_date BETWEEN '$today' AND '$next3Days'";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    echo "<script>alert('ไม่มีรายการที่ใกล้หมดอายุ'); window.location.href='parcel_management.php';</script>";
    exit();
}

// 📄 สร้างไฟล์ Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle("Expiring Parcels");

$sheet->fromArray(
    ['ชื่อ', 'ประเภท', 'ระยะเวลา', 'ราคา', 'งบประมาณ', 'เริ่มต้น', 'สิ้นสุด', 'ผู้ใช้งาน', 'หมายเหตุ'],
    NULL, 'A1'
);

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
        $row['note']
    ], NULL, 'A' . $rowIndex++);
}

// 🔧 สร้างไฟล์ชั่วคราว
$tempFile = tempnam(sys_get_temp_dir(), 'expiring_') . '.xlsx';
$writer = new Xlsx($spreadsheet);
$writer->save($tempFile);

// ✉️ ส่ง Email
$mail = new PHPMailer(true);
try {
    // ตั้งค่าการส่ง
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';      // เปลี่ยนตามผู้ให้บริการ
    $mail->SMTPAuth = true;
    $mail->Username = 'satreertapp@gmail.com';     // 👉 เปลี่ยนเป็นอีเมลคุณ
    $mail->Password = 'oxluxaoyrgcneqyo';       // 👉 รหัสผ่านแอป
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    // ตั้งค่า email
    $mail->setFrom('satreertapp@gmail.com', 'ระบบติดตามการจัดการพัสดุ');

foreach ($_POST['emails'] as $email) {
    $mail->addAddress($email);
}

    $mail->CharSet = 'UTF-8';          // ✅ ตั้งค่า charset ให้รองรับภาษาไทย
    $mail->Encoding = 'base64'; 

    $mail->Subject = 'แจ้งเตือนพัสดุใกล้หมดอายุ';
    $mail->Body = 'แนบไฟล์รายชื่อพัสดุที่ใกล้หมดอายุภายใน 3 วัน';
    $mail->addAttachment($tempFile, 'expiring_parcels.xlsx');

    $mail->send();

    unlink($tempFile); // ลบไฟล์หลังส่ง

    header("Location: parcel_management.php?email_sent=1");
    exit();
} catch (Exception $e) {
    echo "ไม่สามารถส่งอีเมลได้: {$mail->ErrorInfo}";
}
?>