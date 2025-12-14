<?php
session_start();
require_once 'db.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "กรุณากรอกชื่อผู้ใช้และรหัสผ่าน";
    } else {
        $sql = "SELECT id, username, password FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($id, $db_username, $db_password);
            $stmt->fetch();

            if (password_verify($password, $db_password)) {
                // รหัสผ่านถูกต้อง
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $db_username;
                header("location: index.php");
                exit;
            } else {
                $error = "รหัสผ่านไม่ถูกต้อง";
            }
        } else {
            $error = "ไม่พบชื่อผู้ใช้นี้";
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
    <title>เข้าสู่ระบบ - AI Speaking</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        .login-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .logo-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        .form-group {
            text-align: left;
            margin-bottom: 20px;
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
            border-color: var(--primary-color);
        }
        .error-msg {
            color: var(--danger-color);
            background: #ffebee;
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
            <i class="fas fa-robot"></i>
        </div>
        <h2 style="margin-bottom: 10px; color: var(--primary-color);">ยินดีต้อนรับกลับมา</h2>
        <p style="color: var(--text-muted); margin-bottom: 30px;">เข้าสู่ระบบเพื่อเริ่มฝึกภาษาอังกฤษ</p>

        <?php if($error): ?>
            <div class="error-msg">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label><i class="fas fa-user"></i> ชื่อผู้ใช้</label>
                <input type="text" name="username" required placeholder="Username">
            </div>
            <div class="form-group">
                <label><i class="fas fa-lock"></i> รหัสผ่าน</label>
                <input type="password" name="password" required placeholder="Password">
            </div>
            <button type="submit" class="btn" style="width: 100%; margin-top: 10px;">
                เข้าสู่ระบบ <i class="fas fa-sign-in-alt"></i>
            </button>
        </form>
        
        <div style="margin-top: 20px; font-size: 0.9rem;">
            ยังไม่มีบัญชี? <a href="register.php" style="color: var(--primary-color); text-decoration: none; font-weight: 600;">สมัครสมาชิก</a>
        </div>
    </div>
</body>
</html>