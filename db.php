<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
 ///ต่อกับ local database
$servername = "localhost";
$username = "root"; 
$password = "";
$dbname = "parcel_tracking";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    if (!$conn->set_charset("utf8")) {
        throw new Exception("Error setting charset: " . $conn->error);
    }

} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
