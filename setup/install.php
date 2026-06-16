<?php
/**
 * Sazinaldo — One-time database installer.
 * Run once via browser: yourdomain.co.za/setup/install.php
 * DELETE this file immediately after running.
 */

require_once __DIR__ . '/../config/db.php';

// ── Set your initial admin credentials here before running ──
$adminUsername = 'admin';
$adminEmail    = 'info@sazinaldo.co.za';
$adminPassword = 'Sazinaldo2026!';  // CHANGE THIS
// ────────────────────────────────────────────────────────────

try {
    $pdo = getDB();

    // Users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id            INT AUTO_INCREMENT PRIMARY KEY,
            username      VARCHAR(50)  NOT NULL UNIQUE,
            email         VARCHAR(100) NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Players table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS players (
            id                 INT AUTO_INCREMENT PRIMARY KEY,
            -- Personal
            first_name         VARCHAR(100) NOT NULL,
            last_name          VARCHAR(100) NOT NULL,
            date_of_birth      DATE,
            gender             ENUM('Female','Male','Other') DEFAULT 'Female',
            nationality        VARCHAR(100),
            province           VARCHAR(100),
            race               VARCHAR(50),
            photo              VARCHAR(255),
            -- Football
            primary_position   VARCHAR(50),
            secondary_position VARCHAR(50),
            preferred_foot     ENUM('Left','Right','Both') DEFAULT 'Right',
            height_cm          SMALLINT UNSIGNED,
            weight_kg          SMALLINT UNSIGNED,
            -- Club
            current_club       VARCHAR(150),
            current_league     VARCHAR(150),
            current_country    VARCHAR(100),
            jersey_number      TINYINT UNSIGNED,
            contract_expiry    DATE,
            -- Status & Targets
            status             ENUM('Seeking Transfer','Under Contract','On Trial','Placed','Inactive') DEFAULT 'Seeking Transfer',
            target_leagues     TEXT,
            target_countries   TEXT,
            -- Bio
            bio                TEXT,
            achievements       TEXT,
            highlight_url      VARCHAR(500),
            -- Agent (admin-only)
            agent_name         VARCHAR(150),
            agent_email        VARCHAR(150),
            agent_phone        VARCHAR(50),
            -- Meta
            is_featured        TINYINT(1) DEFAULT 0,
            created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Insert admin user (skip if exists)
    $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $check->execute([$adminUsername]);
    if (!$check->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
        $stmt->execute([
            $adminUsername,
            $adminEmail,
            password_hash($adminPassword, PASSWORD_BCRYPT, ['cost' => 12])
        ]);
    }

    echo '<h2 style="font-family:sans-serif;color:green;">✓ Installation complete!</h2>';
    echo '<p style="font-family:sans-serif;">Tables created and admin user set up.</p>';
    echo '<p style="font-family:sans-serif;color:red;"><strong>DELETE this file now:</strong> /setup/install.php</p>';
    echo '<p style="font-family:sans-serif;">Login at: <a href="/admin/login.php">/admin/login.php</a></p>';

} catch (Exception $e) {
    echo '<h2 style="font-family:sans-serif;color:red;">✕ Error</h2>';
    echo '<pre style="font-family:sans-serif;">' . htmlspecialchars($e->getMessage()) . '</pre>';
}
