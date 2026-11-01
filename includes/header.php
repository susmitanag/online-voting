<?php
$pageTitle = $pageTitle ?? '';
$currentPage = $currentPage ?? '';
$voter = current_voter();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle !== '' ? e($pageTitle) . ' | CampusVote' : 'CampusVote | Vote online, privately' ?></title>
<meta name="description" content="CampusVote is a demo online voting system. Log in with your voter code, cast one private ballot and watch live results.">
<link rel="icon" type="image/svg+xml" href="assets/img/favicon.svg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@700;900&family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>
<div class="gov-strip"><div class="wrap">An official demo election site. For portfolio use only, not a real election.</div></div>
<header class="site-header" x-data="{ open: false }">
    <div class="wrap header-inner">
        <a href="index.php" class="logo" aria-label="CampusVote home">
            <img src="assets/img/mark.svg" width="46" height="46" alt="">
            <span class="logo-words"><strong>CampusVote</strong><small>Student Election Commission</small></span>
        </a>
        <button class="nav-toggle" type="button" aria-label="Open menu" :aria-expanded="open.toString()" aria-controls="site-nav" @click="open = !open">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
        </button>
    </div>
    <nav class="nav-bar" id="site-nav" aria-label="Main" :class="{ 'is-open': open }">
        <div class="wrap nav-bar-inner">
            <a href="index.php" class="<?= $currentPage === 'home' ? 'is-current' : '' ?>">Home</a>
            <?php if ($voter): ?>
                <a href="vote.php" class="<?= $currentPage === 'vote' ? 'is-current' : '' ?>">My ballot</a>
                <a href="results.php" class="<?= $currentPage === 'results' ? 'is-current' : '' ?>">Results</a>
                <span class="nav-spacer"></span>
                <span class="voter-name">Signed in: <?= e($voter['name']) ?></span>
                <a href="logout.php">Log out</a>
            <?php else: ?>
                <a href="login.php" class="<?= $currentPage === 'login' ? 'is-current' : '' ?>">Voter log in</a>
                <span class="nav-spacer"></span>
                <a href="admin/login.php">Election officer</a>
            <?php endif; ?>
        </div>
    </nav>
</header>
<main id="main">
