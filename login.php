
<?php
require 'auth.php';
require 'config/db.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: skills.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_auth_token();

    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Please enter a valid email and password.';
    } else {
        $sql = 'SELECT id, profile_id, email, password_hash
                FROM users
                WHERE email = ?
                LIMIT 1';

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result(
                $stmt,
                $userId,
                $profileId,
                $userEmail,
                $storedHash
            );

            $found = mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);

            $passwordOK = false;

            if ($found) {
                // ตรวจสอบแฮชตามปกติก่อน
                if (password_verify($password, $storedHash)) {
                    $passwordOK = true;
                }
                // รองรับข้อมูลเก่าที่เก็บรหัสผ่านเป็นข้อความธรรมดา
                elseif (hash_equals($storedHash, $password)) {
                    $passwordOK = true;

                    // เปลี่ยนรหัสผ่านเดิมให้เป็นแฮชทันที
                    $newHash = password_hash($password, PASSWORD_DEFAULT);

                    $update = mysqli_prepare(
                        $conn,
                        'UPDATE users SET password_hash = ?
                         WHERE id = ?'
                    );

                    if ($update) {
                        mysqli_stmt_bind_param(
                            $update,
                            'si',
                            $newHash,
                            $userId
                        );
                        mysqli_stmt_execute($update);
                        mysqli_stmt_close($update);
                    }
                }
            }

            if ($passwordOK) {
                session_regenerate_id(true);

                $_SESSION['user_id'] = (int)$userId;
                $_SESSION['profile_id'] = (int)$profileId;
                $_SESSION['email'] = $userEmail;

                header('Location: skills.php');
                exit;
            }
        }

        $error = 'Invalid email or password.';
    }
}
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/crud.css">
</head>
<body>
<main class="wrap">
    <div class="panel">
        <h1>Resume Lab · Login</h1>
        <p>PHP Session + MySQL Authentication</p>

        <?php if (isset($_GET['registered'])): ?>
            <p class="ok">Registration complete. Please login.</p>
        <?php endif; ?>

        <?php if (isset($_GET['logged_out'])): ?>
            <p class="ok">Logged out.</p>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <p class="err"><?= auth_escape($error) ?></p>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="auth_csrf"
                   value="<?= auth_escape(auth_token()) ?>">

            <label>Email</label>
            <input name="email" type="email" required
                   autocomplete="username">

            <label>Password</label>
            <input name="password" type="password" required
                   autocomplete="current-password">

            <p><button type="submit">Login</button></p>
        </form>

        <p>New user? <a href="register.php">Register</a></p>
    </div>
</main>
</body>
</html>
