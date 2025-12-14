<?php
session_start();

// ตรวจสอบว่าล็อกอินหรือยัง
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
    <title>Dashboard - AI Speaking</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container wide">
        <div class="text-center mb-20">
            <i class="fas fa-robot fa-3x" style="color: var(--primary-color); margin-bottom: 15px;"></i>
            <h2>AI Speaking Assistant</h2>
            <p class="welcome-text">ยินดีต้อนรับ, <strong><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></strong></p>
        </div>

        <div class="menu-grid">
            <div class="menu-card" onclick="window.location.href='setup_practice.php'">
                <i class="fas fa-users fa-2x" style="color: var(--secondary-color); margin-bottom: 15px;"></i>
                <h3>Solo Practice</h3>
                <p>ฝึกพูดภาษาอังกฤษแบบกลุ่ม (จำลอง 3 คน)</p>
            </div>

            <div class="menu-card" onclick="window.location.href='setup_interview.php'">
                <i class="fas fa-user-tie fa-2x" style="color: var(--accent-color); margin-bottom: 15px;"></i>
                <h3>Interview Practice</h3>
                <p>จำลองการสัมภาษณ์งานจริงกับ AI</p>
            </div>
        </div>

        <div class="text-center mt-20">
            <a href="logout.php" class="btn danger" style="width: auto; padding: 10px 30px;">
                <i class="fas fa-sign-out-alt"></i> ออกจากระบบ
            </a>
        </div>
    </div>
</body>
</html>