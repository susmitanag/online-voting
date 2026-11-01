<?php
require __DIR__ . '/../config.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action'] ?? '';
    if ($act === 'open' || $act === 'close') {
        $pdo->prepare('UPDATE election SET is_open = ? WHERE id = 1')->execute([$act === 'open' ? 1 : 0]);
        $_SESSION['flash'] = $act === 'open' ? 'Voting is now open.' : 'Voting is now closed.';
    } elseif ($act === 'reset') {
        $pdo->beginTransaction();
        $pdo->exec('DELETE FROM votes');
        $pdo->exec('UPDATE voters SET has_voted = 0');
        $pdo->commit();
        $_SESSION['flash'] = 'All votes were cleared. Every voter can vote again.';
    }
    header('Location: index.php');
    exit;
}
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

$el = election($pdo);
$t = turnout($pdo);
$rows = results($pdo);
$total = array_sum(array_column($rows, 'vote_count'));
// Only who has voted is shown here, never who they voted for.
$voters = $pdo->query('SELECT voter_code, name, has_voted FROM voters ORDER BY voter_code')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin | CampusVote</title>
<link rel="icon" type="image/svg+xml" href="../assets/img/favicon.svg">
<link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@700;900&family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="wrap header-inner">
        <a href="../index.php" class="logo"><img src="../assets/img/mark.svg" width="38" height="38" alt=""><span>CampusVote</span></a>
        <nav class="site-nav is-static"><span class="voter-name">Election officer</span><a href="../index.php">View site</a><a href="logout.php" class="btn btn-light btn-sm">Log out</a></nav>
    </div>
</header>
<main class="wrap" style="padding-top:32px;padding-bottom:72px">
    <div class="admin-head">
        <div><p class="kicker">Admin</p><h1 style="margin:0"><?= e($el['title']) ?></h1></div>
        <span class="status-pill <?= $el['is_open'] ? 'is-open' : 'is-closed' ?>"><?= $el['is_open'] ? 'Voting is open' : 'Voting is closed' ?></span>
    </div>
    <?php if ($flash): ?><div class="alert alert-ok"><?= e($flash) ?></div><?php endif; ?>

    <div class="stats stats-inline">
        <div class="stat"><strong><?= $t['registered'] ?></strong><span>Registered voters</span></div>
        <div class="stat"><strong><?= $t['voted'] ?></strong><span>Ballots cast</span></div>
        <div class="stat"><strong><?= $t['pct'] ?>%</strong><span>Turnout</span></div>
    </div>

    <div class="chart-card" style="margin-bottom:20px">
        <h3>Votes by candidate and turnout</h3>
        <div class="admin-charts">
            <div class="chart-box is-short"><canvas id="chart-votes" aria-label="Votes by candidate" role="img"></canvas></div>
            <div class="chart-box is-short"><canvas id="chart-turnout" aria-label="Voters who have voted" role="img"></canvas></div>
        </div>
    </div>

    <div class="admin-two">
        <div class="panel">
            <h2>Election controls</h2>
            <div class="admin-actions">
                <form method="post"><input type="hidden" name="action" value="<?= $el['is_open'] ? 'close' : 'open' ?>"><button class="btn btn-primary" type="submit"><?= $el['is_open'] ? 'Close voting' : 'Open voting' ?></button></form>
                <form method="post" data-confirm="Clear every vote and let all voters vote again? This cannot be undone."><input type="hidden" name="action" value="reset"><button class="btn btn-danger" type="submit">Reset election</button></form>
            </div>
            <h2 style="margin-top:26px">Results so far</h2>
            <?php foreach ($rows as $r): $pct = $total ? (int) round($r['vote_count'] / $total * 100) : 0; ?>
                <div class="line-item"><span><?= e($r['name']) ?> <span class="muted small"><?= e($r['party']) ?></span></span><strong><?= (int) $r['vote_count'] ?> <span class="muted small">(<?= $pct ?>%)</span></strong></div>
            <?php endforeach; ?>
        </div>
        <div class="panel">
            <h2>Voter list</h2>
            <p class="muted small">This list shows who has voted. It does not show who they voted for.</p>
            <?php foreach ($voters as $v): ?>
                <div class="line-item"><span><strong><?= e($v['voter_code']) ?></strong> <?= e($v['name']) ?></span>
                    <span class="status-pill <?= $v['has_voted'] ? 'is-open' : 'is-neutral' ?>"><?= $v['has_voted'] ? 'Voted' : 'Not yet' ?></span></div>
            <?php endforeach; ?>
        </div>
    </div>
</main>
<script src="../assets/vendor/chart.umd.js"></script>
<script src="../assets/js/app.js"></script>
<script>
(function () {
    var rows = <?= json_encode(array_map(fn($r) => ['n' => $r['name'], 'v' => (int) $r['vote_count'], 'c' => $r['color']], $rows)) ?>;
    new Chart(document.getElementById('chart-votes'), {
        type: 'bar',
        data: { labels: rows.map(function (r) { return r.n; }), datasets: [{ data: rows.map(function (r) { return r.v; }), backgroundColor: rows.map(function (r) { return r.c; }) }] },
        options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });
    new Chart(document.getElementById('chart-turnout'), {
        type: 'doughnut',
        data: { labels: ['Voted', 'Not yet'], datasets: [{ data: [<?= (int) $t['voted'] ?>, <?= (int) ($t['registered'] - $t['voted']) ?>], backgroundColor: ['#0B3D91', '#D9DEE8'], borderWidth: 2, borderColor: '#fff' }] },
        options: { maintainAspectRatio: false, cutout: '60%', plugins: { legend: { position: 'bottom' } } }
    });
})();
</script>
</body>
</html>
