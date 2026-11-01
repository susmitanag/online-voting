<?php
require __DIR__ . '/config.php';
if (!current_voter() && !is_admin()) { header('Location: login.php'); exit; }

$el = election($pdo);
$rows = results($pdo);
$t = turnout($pdo);
$total = array_sum(array_column($rows, 'vote_count'));

// The same data is used for the page and for the live refresh (results.php?json=1).
$payload = ['total' => $total, 'registered' => $t['registered'], 'pct' => $t['pct'], 'rows' => array_map(fn($r) => [
    'id' => (int) $r['id'], 'name' => $r['name'], 'party' => $r['party'], 'color' => $r['color'],
    'votes' => (int) $r['vote_count'], 'pct' => $total ? (int) round($r['vote_count'] / $total * 100) : 0,
], $rows)];

if (isset($_GET['json'])) {
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

$pageTitle = 'Results';
$currentPage = 'results';
$justVoted = isset($_GET['voted']);
$leaderVotes = $rows ? (int) $rows[0]['vote_count'] : 0;
require __DIR__ . '/includes/header.php';
?>
<section class="page-head">
    <div class="wrap">
        <p class="kicker"><?= e($el['title']) ?></p>
        <h1><?= $el['is_open'] ? 'Live results' : 'Final results' ?></h1>
        <span class="status-pill <?= $el['is_open'] ? 'is-open' : 'is-closed' ?>"><?= $el['is_open'] ? 'Voting is open' : 'Voting is closed' ?></span>
    </div>
</section>
<div class="wrap" style="padding-top:28px;padding-bottom:72px">
    <?php if ($justVoted): ?><div class="alert alert-ok">Your vote was recorded. Thank you for voting!</div><?php endif; ?>
    <div x-data="liveResults(<?= e(json_encode($payload)) ?>, <?= $el['is_open'] ? 'true' : 'false' ?>)">
        <div class="stats stats-inline">
            <div class="stat"><strong x-text="total"><?= $total ?></strong><span>Votes counted</span></div>
            <div class="stat"><strong x-text="registered"><?= $t['registered'] ?></strong><span>Registered voters</span></div>
            <div class="stat"><strong x-text="pct + '%'"><?= $t['pct'] ?>%</strong><span>Turnout</span></div>
        </div>
        <div class="results-grid">
            <div class="chart-card"><div class="chart-box"><canvas x-ref="chart" aria-label="Share of votes by candidate" role="img"></canvas></div></div>
            <div class="results-list" id="results">
                <template x-for="r in rows" :key="r.id">
                    <div class="result-row" :class="{ 'is-leader': total > 0 && r.votes === top }">
                        <span class="avatar" :style="'--c:' + r.color + ';width:44px;height:44px;font-size:17px'" x-text="initials(r.name)"></span>
                        <div class="result-main">
                            <div class="result-label"><strong x-text="r.name"></strong><span class="candidate-party" x-text="r.party"></span></div>
                            <div class="result-bar-track"><div class="result-bar-fill" :style="'width:' + r.pct + '%;background:' + r.color"></div></div>
                        </div>
                        <div class="result-count"><strong x-text="r.votes"></strong><span x-text="r.pct + '%'"></span></div>
                    </div>
                </template>
                <noscript><?php foreach ($rows as $row): ?><p><?= e($row['name']) ?>: <?= (int) $row['vote_count'] ?></p><?php endforeach; ?></noscript>
            </div>
        </div>
    </div>
    <p class="muted small" style="margin-top:18px"><?= $el['is_open'] ? 'This page refreshes by itself every few seconds.' : 'Voting has ended, so these numbers are final.' ?></p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
