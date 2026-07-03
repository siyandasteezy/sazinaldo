<?php
/**
 * Run once to add the contact_submissions table.
 * DELETE this file from the server after running.
 */
require_once __DIR__ . '/../config/db.php';
$db = getDB();

$db->exec("
    CREATE TABLE IF NOT EXISTS contact_submissions (
        id           INT AUTO_INCREMENT PRIMARY KEY,
        first_name   VARCHAR(100)  NOT NULL,
        last_name    VARCHAR(100)  NOT NULL,
        email        VARCHAR(255)  NOT NULL,
        phone        VARCHAR(50)   DEFAULT NULL,
        country      VARCHAR(100)  DEFAULT NULL,
        enquiry_type VARCHAR(100)  DEFAULT NULL,
        subject      VARCHAR(255)  DEFAULT NULL,
        message      TEXT          NOT NULL,
        is_read      TINYINT(1)    NOT NULL DEFAULT 0,
        is_starred   TINYINT(1)    NOT NULL DEFAULT 0,
        created_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

echo '<p style="font-family:sans-serif;color:green;padding:2rem;">
        ✅ <strong>contact_submissions</strong> table created successfully.<br>
        <strong style="color:red;">Delete this file from the server now.</strong>
      </p>';
