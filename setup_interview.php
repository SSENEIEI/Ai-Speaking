<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตั้งค่าการสัมภาษณ์ - AI Speaking</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="text-center mb-20">
            <i class="fas fa-user-tie fa-3x" style="color: var(--accent-color); margin-bottom: 15px;"></i>
            <h2>ตั้งค่าการสัมภาษณ์งาน</h2>
            <p>กำหนดบทบาทและสถานการณ์จำลอง</p>
        </div>
        
        <form action="interview.php" method="POST">
            <div class="form-group">
                <label for="interviewer_role"><i class="fas fa-user-secret"></i> ผู้สัมภาษณ์เป็นใคร (Interviewer Role):</label>
                <input type="text" id="interviewer_role" name="interviewer_role" placeholder="เช่น อาจารย์มหาวิทยาลัย, HR Manager" required>
            </div>

            <div class="form-group">
                <label for="user_role"><i class="fas fa-user"></i> ตัวเราเป็นใคร (User Role):</label>
                <input type="text" id="user_role" name="user_role" placeholder="เช่น นักเรียนม.6, ผู้สมัครงานตำแหน่ง Senior Dev" required>
            </div>

            <div class="form-group">
                <label for="duration"><i class="fas fa-clock"></i> ระยะเวลาสัมภาษณ์ (นาที):</label>
                <input type="number" id="duration" name="duration" value="5" min="1" max="60" required>
            </div>

            <div class="form-group">
                <label for="scenario"><i class="fas fa-file-alt"></i> รายละเอียดการสัมภาษณ์:</label>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">
                    ระบุรายละเอียด เช่น สถานที่, คณะ/สาขา, บริษัท, หรือหัวข้อที่ต้องการเน้น
                </p>
                <textarea name="scenario" id="scenario" required placeholder="พิมพ์รายละเอียดที่นี่..." rows="5"></textarea>
            </div>

            <button type="submit" class="btn">
                <i class="fas fa-play"></i> เริ่มสัมภาษณ์
            </button>
        </form>
        
        <div class="text-center mt-20">
            <a href="index.php" class="btn secondary" style="width: auto; padding: 10px 20px;">
                <i class="fas fa-arrow-left"></i> กลับหน้าหลัก
            </a>
        </div>
    </div>
</body>
</html>