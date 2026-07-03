<?php
/**
 * Inserts one test row into contact_submissions and reports the result.
 * DELETE from server after use.
 */
require_once __DIR__ . '/../config/db.php';
$db = getDB();

echo '<pre style="font-family:monospace;padding:2rem;">';

try {
    $stmt = $db->prepare("
        INSERT INTO contact_submissions (first_name, last_name, email, phone, country, enquiry_type, subject, message)
        VALUES ('Test', 'User', 'test@example.com', NULL, 'South Africa', 'other', 'general', 'This is a test submission.')
    ");
    $stmt->execute();
    echo "✅ Test row inserted. ID: " . $db->lastInsertId() . "\n";
    echo "   Check the admin Messages page — you should see it there now.\n";
    echo "   <strong>Delete this file after confirming.</strong>\n";
} catch (Throwable $e) {
    echo "❌ Insert failed: " . $e->getMessage() . "\n";
}

echo '</pre>';
