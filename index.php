<?php
require __DIR__ . '/config.php';

$pageTitle = '';
$currentPage = 'home';
$el = election($pdo);
$t = turnout($pdo);
$count = (int) $pdo->query('SELECT COUNT(*) FROM candidates')->fetchColumn();

require __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div class="wrap hero-inner">
        <span class="status-pill <?= $el['is_open'] ? 'is-open' : 'is-closed' ?>"><?= $el['is_open'] ? 'Voting is open' : 'Voting is closed' ?></span>
        <h1><?= e($el['title']) ?></h1>
        <p class="hero-lead"><?= e($el['subtitle']) ?> <?= $count ?> candidates, one ballot each, results you can watch live.</p>
        <div class="hero-actions">
            <?php if (current_voter()): ?>
                <a href="vote.php" class="btn btn-brass btn-lg"><?= current_voter()['has_voted'] ? 'See my ballot' : 'Go to my ballot' ?></a>
                <a href="results.php" class="btn btn-ghost btn-lg">Live results</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-brass btn-lg">Log in to vote</a>
                <a href="#how" class="btn btn-ghost btn-lg">How it works</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="wrap stats">
    <div class="stat"><strong><?= $t['registered'] ?></strong><span>Registered voters</span></div>
    <div class="stat"><strong><?= $t['voted'] ?></strong><span>Ballots cast</span></div>
    <div class="stat"><strong><?= $t['pct'] ?>%</strong><span>Turnout</span></div>
</section>

<section class="section" id="how">
    <div class="wrap">
        <p class="kicker">How it works</p>
        <h2>Three steps, about a minute</h2>
        <div class="steps">
            <div class="step"><span class="step-num">1</span><h3>Log in</h3><p>Use your voter code and password. Each voter has one account.</p></div>
            <div class="step"><span class="step-num">2</span><h3>Choose and confirm</h3><p>Pick a candidate and check your choice on the review screen before you submit.</p></div>
            <div class="step"><span class="step-num">3</span><h3>Watch the results</h3><p>Totals update live. Your vote is private, because the system never stores who voted for whom.</p></div>
        </div>
    </div>
</section>

<section class="section section-tint">
    <div class="wrap trust">
        <div><h3>One person, one vote</h3><p>The vote and the "has voted" mark are saved together in one database transaction. A second vote is refused, even if two requests arrive at the same moment.</p></div>
        <div><h3>A secret ballot</h3><p>The votes table holds only the candidate. There is no link back to the voter.</p></div>
        <div><h3>Passwords are protected</h3><p>Passwords are hashed with bcrypt and every query uses prepared statements.</p></div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
