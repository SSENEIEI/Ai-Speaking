<?php
$servername = "localhost";
$username = "root";
$password = ""; // รหัสผ่าน XAMPP ปกติจะเป็นค่าว่าง
$dbname = "ai_speaking_db";

// สร้างการเชื่อมต่อ (ยังไม่เลือก DB เพราะอาจจะยังไม่มี)
$conn = new mysqli($servername, $username, $password);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>