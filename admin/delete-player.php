<?php
require_once __DIR__ . '/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /admin/players.php');
    exit;
}

verifyCsrf();

$id = (int)($_POST['id'] ?? 0);
$db = getDB();

$stmt = $db->prepare("SELECT photo FROM players WHERE id = ?");
$stmt->execute([$id]);
$player = $stmt->fetch();

if ($player) {
    if ($player['photo'] && file_exists(UPLOAD_DIR . $player['photo'])) {
        unlink(UPLOAD_DIR . $player['photo']);
    }
    $db->prepare("DELETE FROM players WHERE id = ?")->execute([$id]);
    flash('success', 'Player deleted.');
} else {
    flash('error', 'Player not found.');
}

header('Location: /admin/players.php');
exit;
