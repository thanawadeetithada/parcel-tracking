<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {   //แก้ไขข้อมูล  ปุ่มแก้ไขหน้า dashboard
    $id = $_POST['id'];
    $registration_number = $_POST['registration_number'];
    $prefix = $_POST['prefix'];
    $fullname = $_POST['fullname'];
    $college = $_POST['college'];
    $date_faculty_received = $_POST['date_faculty_received'];
    $committee_approval_date = $_POST['committee_approval_date'];
    $faculty_approval_date = $_POST['faculty_approval_date'];
    $book_number_HR = $_POST['book_number_HR'];
    $book_number_HR_date = $_POST['book_number_HR_date'];
    $passed_institution = $_POST['passed_institution'];
    $passed_institution_date = $_POST['passed_institution_date'];
    
    $sql = "UPDATE faculty_progress SET registration_number = ?, prefix = ?, fullname = ?, college = ?, date_faculty_received = ?, committee_approval_date = ?, faculty_approval_date = ?, book_number_HR = ?, book_number_HR_date = ?, passed_institution = ?, passed_institution_date = ?  WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssssi", $registration_number, $prefix, $fullname, $college, $date_faculty_received, $committee_approval_date, $faculty_approval_date, $book_number_HR, $book_number_HR_date, $passed_institution, $passed_institution_date, $id);

    if ($stmt->execute()) {
        header("Location: dashboard.php");
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
}
?>
