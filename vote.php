<?php
require __DIR__ . '/config.php';
require_login();

$pageTitle = 'My ballot';
$currentPage = 'vote';
$voter = current_voter();
$el = election($pdo);
$error = '';

$stmt = $pdo->prepare('SELECT has_voted FROM voters WHERE id = ?');
$stmt->execute([$voter['id']]);
$row = $stmt->fetch();
if (!$row) { unset($_SESSION['voter']); header('Location: login.php'); exit; }
$hasVoted = (bool) $row['has_voted'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$hasVoted) {
    $candidateId = (int) ($_POST['candidate_id'] ?? 0);
    $check = $pdo->prepare('SELECT id FROM candidates WHERE id = ?');
    $check->execute([$candidateId]);

    if (!$el['is_open']) {
        $error = 'Voting is closed. Your ballot was not counted.';
    } elseif (!$check->fetch()) {
        $error = 'Please choose a candidate.';
    } else {
        try {
            $pdo->beginTransaction();
            // Claim the vote first. If this voter already voted, no row changes and we stop.
            $claim = $pdo->prepare('UPDATE voters SET has_voted = 1 WHERE id = ? AND has_voted = 0');
            $claim->execute([$voter['id']]);
            if ($claim->rowCount() !== 1) {
                $pdo->rollBack();
                $hasVoted = true;
                $error = 'You have already voted.';
            } else {
                // The vote row stores the candidate only, so the ballot stays secret.
                $pdo->prepare('INSERT INTO votes (candidate_id) VALUES (?)')->execute([$candidateId]);
                $pdo->commit();
                header('Location: results.php?voted=1');
                exit;
            }
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            $error = 'Something went wrong. Please try again.';
        }
    }
}

$candidates = $pdo->query('SELECT * FROM candidates ORDER BY name')->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<section class="page-head">
    <div class="wrap">
        <p class="kicker"><?= e($el['title']) ?></p>
        <h1>My ballot</h1>
    </div>
</section>
<div class="wrap narrow" style="padding-top:28px;padding-bottom:72px">
<?php if ($hasVoted): ?>
    <div class="panel center">
        <div class="success-icon"><svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></div>
        <h2>Your vote is in</h2>
        <p class="muted">Thank you, <?= e(explode(' ', $voter['name'])[0]) ?>. Your ballot was counted and cannot be changed.</p>
        <a href="results.php" class="btn btn-primary">See live results</a>
    </div>
<?php elseif (!$el['is_open']): ?>
    <div class="panel center"><h2>Voting is closed</h2><p class="muted">The election has ended. You can still see the final results.</p><a href="results.php" class="btn btn-primary">See results</a></div>
<?php else: ?>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <p class="muted">Choose one candidate. You will review your choice before the vote is counted.</p>
    <div x-data="ballot()" @keydown.escape.window="open = false">
        <form method="post" action="vote.php" class="ballot" id="ballot-form" x-ref="form" @submit.prevent="if (selected) open = true">
            <?php foreach ($candidates as $c): ?>
                <label class="candidate-option" :class="{ selected: selected === '<?= (int) $c['id'] ?>' }">
                    <input type="radio" name="candidate_id" value="<?= (int) $c['id'] ?>" required
                           @change="pick($event.target.value, '<?= e(addslashes($c['name'])) ?>', '<?= e(addslashes($c['party'])) ?>', '<?= e($c['color']) ?>')">
                    <?= avatar($c['name'], $c['color'], 56) ?>
                    <span class="candidate-details">
                        <strong><?= e($c['name']) ?></strong>
                        <span class="candidate-party"><?= e($c['party']) ?></span>
                        <span class="candidate-bio"><?= e($c['bio']) ?></span>
                    </span>
                    <span class="radio-mark" aria-hidden="true"></span>
                </label>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-primary btn-lg btn-block" id="submit-ballot" :disabled="!selected">Review my vote</button>
        </form>

        <div class="modal" x-show="open" x-cloak @click.self="open = false">
            <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="modal-title">
                <h2 id="modal-title">Confirm your vote</h2>
                <p class="muted">You are voting for:</p>
                <p class="modal-choice">
                    <span class="avatar" :style="'--c:' + color + ';width:44px;height:44px'" x-text="initials"></span>
                    <span x-text="name + ' (' + party + ')'"></span>
                </p>
                <p class="muted small">Once you confirm, your vote is counted and cannot be changed.</p>
                <div class="modal-actions">
                    <button type="button" class="btn btn-light" @click="open = false">Go back</button>
                    <button type="button" class="btn btn-primary" :disabled="sending" @click="confirm()" x-text="sending ? 'Submitting...' : 'Confirm vote'">Confirm vote</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
