<?php
require_once __DIR__ . '/auth.php';
requireLogin();

$isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest'
        || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

function respond(bool $ok, string $msg = ''): void {
    global $isAjax;
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => $ok]);
        exit;
    }
    if (!$ok) flash('error', $msg ?: 'Action failed.');
    else      flash('success', $msg);
    header('Location: /admin/messages.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /admin/messages.php');
    exit;
}

verifyCsrf($_POST['csrf'] ?? '');

$db     = getDB();
$action = $_POST['action'] ?? '';
$id     = (int)($_POST['id'] ?? 0);

match ($action) {
    'mark_read' => (function() use ($db, $id) {
        $db->prepare("UPDATE contact_submissions SET is_read = 1 WHERE id = ?")->execute([$id]);
        respond(true);
    })(),

    'toggle_read' => (function() use ($db, $id) {
        $db->prepare("UPDATE contact_submissions SET is_read = NOT is_read WHERE id = ?")->execute([$id]);
        respond(true, 'Read status updated.');
    })(),

    'toggle_star' => (function() use ($db, $id) {
        $db->prepare("UPDATE contact_submissions SET is_starred = NOT is_starred WHERE id = ?")->execute([$id]);
        respond(true);
    })(),

    'mark_all_read' => (function() use ($db) {
        $db->exec("UPDATE contact_submissions SET is_read = 1");
        respond(true, 'All messages marked as read.');
    })(),

    'delete' => (function() use ($db, $id) {
        $db->prepare("DELETE FROM contact_submissions WHERE id = ?")->execute([$id]);
        respond(true, 'Message deleted.');
    })(),

    default => respond(false, 'Unknown action.'),
};
