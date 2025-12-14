<?php
session_start();
require_once 'db.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($email) || empty($password)) {
        $error = "กรุณากรอกข้อมูลให้ครบถ้วน";
    } elseif ($password !== $confirm_password) {
        $error = "รหัสผ่านไม่ตรงกัน";
    } else {
        // ตรวจสอบว่ามีผู้ใช้นี้อยู่แล้วหรือไม่
        $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "ชื่อผู้ใช้หรืออีเมลนี้ถูกใช้งานแล้ว";
        } else {
            // Hash รหัสผ่าน
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $insert_sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("sss", $username, $email, $hashed_password);

            if ($stmt->execute()) {
                $success = "สมัครสมาชิกสำเร็จ! กำลังนำทางไปหน้าเข้าสู่ระบบ...";
                header("refresh:2;url=login.php");
            } else {
                $error = "เกิดข้อผิดพลาด: " . $conn->error;
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก - AI Speaking</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 20px;
        }
        .login-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 450px;
            text-align: center;
        }
        .logo-icon {
            font-size: 3rem;
            color: var(--secondary-color);
            margin-bottom: 20px;
        }
        .form-group {
            text-align: left;
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-color);
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #eee;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s;
            font-family: 'Prompt', sans-serif;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--secondary-color);
        }
        .error-msg {
            color: var(--danger-color);
            background: #ffebee;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        .success-msg {
            color: var(--success-color);
            background: #e8f5e9;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo-icon">
            <i class="fas fa-user-plus"></i>
        </div>
        <h2 style="margin-bottom: 10px; color: var(--secondary-color);">สร้างบัญชีใหม่</h2>
        <p style="color: var(--text-muted); margin-bottom: 30px;">กรอกข้อมูลเพื่อเริ่มต้นใช้งาน</p>

        <?php if($error): ?>
            <div class="error-msg">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="success-msg">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label><i class="fas fa-user"></i> ชื่อผู้ใช้</label>
                <input type="text" name="username" required placeholder="Username">
            </div>
            <div class="form-group">
                <label><i class="fas fa-envelope"></i> อีเมล</label>
                <input type="email" name="email" required placeholder="Email">
            </div>
            <div class="form-group">
                <label><i class="fas fa-lock"></i> รหัสผ่าน</label>
                <input type="password" name="password" required placeholder="Password">
            </div>
            <div class="form-group">
                <label><i class="fas fa-lock"></i> ยืนยันรหัสผ่าน</label>
                <input type="password" name="confirm_password" required placeholder="Confirm Password">
            </div>
            <button type="submit" class="btn secondary" style="width: 100%; margin-top: 10px;">
                สมัครสมาชิก <i class="fas fa-arrow-right"></i>
            </button>
        </form>
        
        <div style="margin-top: 20px; font-size: 0.9rem;">
            มีบัญชีอยู่แล้ว? <a href="login.php" style="color: var(--secondary-color); text-decoration: none; font-weight: 600;">เข้าสู่ระบบ</a>
        </div>
    </div>
</body>
</html>