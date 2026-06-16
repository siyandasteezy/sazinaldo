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
$db->prepare("UPDATE players SET is_featured = NOT is_featured WHERE id = ?")->execute([$id]);

header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/admin/players.php'));
exit;
