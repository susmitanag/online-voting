<?php
require __DIR__ . '/config.php';

if (current_voter()) { header('Location: vote.php'); exit; }

$pageTitle = 'Voter log in';
$error = '';
$code = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = strtoupper(trim($_POST['voter_code'] ?? ''));
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM voters WHERE voter_code = ?');
    $stmt->execute([$code]);
    $voter = $stmt->fetch();

    if ($voter && password_verify($password, $voter['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['voter'] = ['id' => $voter['id'], 'name' => $voter['name'], 'voter_code' => $voter['voter_code']];
        header('Location: vote.php');
        exit;
    }
    $error = 'Invalid voter code or password.';
}

require __DIR__ . '/includes/header.php';
?>
<div class="wrap auth-wrap">
    <div class="panel auth-card">
        <h1>Voter log in</h1>
        <p class="muted">Enter the code and password you were given.</p>
        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
        <form method="post" action="login.php" class="form">
            <label for="voter_code">Voter code</label>
            <input type="text" id="voter_code" name="voter_code" value="<?= e($code) ?>" placeholder="V1001" required autofocus autocomplete="username">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">
            <button type="submit" class="btn btn-primary btn-lg btn-block" style="margin-top:18px">Log in</button>
        </form>
        <p class="muted small" style="margin-top:18px">Demo voters: V1001 to V1008, password voter123</p>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
