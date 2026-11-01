<?php
// Database settings. These match the XAMPP defaults (user root, no password).
$DB_HOST = 'localhost';
$DB_NAME = 'online_voting';
$DB_USER = 'root';
$DB_PASS = '';

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Could not connect to the database. Check that MySQL is running and that you imported db/schema.sql. Details: ' . $e->getMessage());
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function current_voter(): ?array {
    return $_SESSION['voter'] ?? null;
}

function require_login(): void {
    if (!current_voter()) {
        header('Location: login.php');
        exit;
    }
}

function is_admin(): bool {
    return !empty($_SESSION['admin']);
}

function require_admin(): void {
    if (!is_admin()) {
        header('Location: login.php');
        exit;
    }
}

function election(PDO $pdo): array {
    return $pdo->query('SELECT * FROM election WHERE id = 1')->fetch() ?: ['title' => 'Election', 'subtitle' => '', 'is_open' => 0];
}

/* Totals used on the home page, results and admin: registered voters, ballots cast, turnout. */
function turnout(PDO $pdo): array {
    $registered = (int) $pdo->query('SELECT COUNT(*) FROM voters')->fetchColumn();
    $voted = (int) $pdo->query('SELECT COUNT(*) FROM voters WHERE has_voted = 1')->fetchColumn();
    return ['registered' => $registered, 'voted' => $voted, 'pct' => $registered ? (int) round($voted / $registered * 100) : 0];
}

function results(PDO $pdo): array {
    return $pdo->query(
        'SELECT c.id, c.name, c.party, c.color, COUNT(v.id) AS vote_count
         FROM candidates c LEFT JOIN votes v ON v.candidate_id = c.id
         GROUP BY c.id, c.name, c.party, c.color
         ORDER BY vote_count DESC, c.name'
    )->fetchAll();
}

function initials(string $name): string {
    $parts = preg_split('/\s+/', trim($name));
    $first = mb_substr($parts[0] ?? '', 0, 1);
    $last = count($parts) > 1 ? mb_substr(end($parts), 0, 1) : '';
    return mb_strtoupper($first . $last);
}

function avatar(string $name, string $color, int $size = 56): string {
    return '<span class="avatar" style="--c:' . e($color) . ';width:' . $size . 'px;height:' . $size . 'px;font-size:' . round($size * .38) . 'px" aria-hidden="true">' . e(initials($name)) . '</span>';
}
