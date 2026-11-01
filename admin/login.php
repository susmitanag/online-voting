<?php
require __DIR__ . '/../config.php';
if (is_admin()) { header('Location: index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('SELECT * FROM admins WHERE email = ?');
    $stmt->execute([trim($_POST['email'] ?? '')]);
    $a = $stmt->fetch();
    if ($a && password_verify($_POST['password'] ?? '', $a['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin'] = ['id' => $a['id'], 'name' => $a['name']];
        header('Location: index.php');
        exit;
    }
    $error = 'Wrong admin email or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin log in | CampusVote</title>
<link rel="icon" type="image/svg+xml" href="../assets/img/favicon.svg">
<link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@700;900&family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="wrap auth-wrap">
    <div class="panel auth-card">
        <a href="../index.php" class="logo" style="margin-bottom:14px"><img src="../assets/img/mark.svg" width="38" height="38" alt=""><span>CampusVote</span></a>
        <h1>Election officer log in</h1>
        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
        <form method="post" class="form">
            <label for="email">Email</label><input type="email" id="email" name="email" required autofocus>
            <label for="password">Password</label><input type="password" id="password" name="password" required>
            <button type="submit" class="btn btn-primary btn-lg btn-block" style="margin-top:18px">Log in</button>
        </form>
        <p class="muted small" style="margin-top:16px">Demo admin: admin@campusvote.test / admin123</p>
    </div>
</div>
</body>
</html>
