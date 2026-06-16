<?php
// ─── Afrihost cPanel → MySQL Databases → fill these in ───────────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'your_db_username');   // e.g. sazinal_admin
define('DB_PASS', 'your_db_password');
define('DB_NAME', 'your_db_name');       // e.g. sazinal_db
// ─────────────────────────────────────────────────────────────────────────────

define('UPLOAD_DIR', __DIR__ . '/../uploads/players/');
define('UPLOAD_URL', '/uploads/players/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('SITE_URL', '');  // leave empty for relative paths

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            error_log($e->getMessage());
            die(json_encode(['error' => 'Database connection failed.']));
        }
    }
    return $pdo;
}
