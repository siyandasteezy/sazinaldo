<?php
/**
 * Diagnostic only — DELETE from server after use.
 */
require_once __DIR__ . '/../config/db.php';
$db = getDB();

echo '<pre style="font-family:monospace;padding:2rem;">';

// 1. Check table exists
try {
    $count = $db->query("SELECT COUNT(*) FROM contact_submissions")->fetchColumn();
    echo "✅ Table exists. Row count: $count\n\n";
} catch (Throwable $e) {
    echo "❌ Table error: " . $e->getMessage() . "\n";
    exit;
}

// 2. Show last 5 rows
$rows = $db->query("SELECT id, first_name, email, is_read, created_at FROM contact_submissions ORDER BY id DESC LIMIT 5")->fetchAll();
if ($rows) {
    echo "Last 5 submissions:\n";
    foreach ($rows as $r) {
        echo "  [{$r['id']}] {$r['first_name']} ({$r['email']}) — read:{$r['is_read']} — {$r['created_at']}\n";
    }
} else {
    echo "⚠️  No rows in contact_submissions — submissions are not reaching the database.\n";
    echo "    Most likely cause: honeypot field (_gotcha) is being filled by browser autofill.\n";
}
echo '</pre>';
