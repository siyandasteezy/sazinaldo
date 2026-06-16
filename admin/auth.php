<?php
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin(): void {
    if (empty($_SESSION['admin_id'])) {
        header('Location: /admin/login.php');
        exit;
    }
}

function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('Invalid CSRF token.');
    }
}

function flash(string $key, string $msg = ''): string {
    if ($msg) {
        $_SESSION['flash'][$key] = $msg;
        return '';
    }
    $out = $_SESSION['flash'][$key] ?? '';
    unset($_SESSION['flash'][$key]);
    return $out;
}

function h(mixed $val): string {
    return htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8');
}

function uploadPhoto(array $file): string|false {
    $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $finfo   = new finfo(FILEINFO_MIME_TYPE);
    $mime    = $finfo->file($file['tmp_name']);

    if (!in_array($mime, $allowed, true))       return false;
    if ($file['size'] > MAX_UPLOAD_SIZE)         return false;
    if ($file['error'] !== UPLOAD_ERR_OK)        return false;

    $ext      = match($mime) {
        'image/jpeg', 'image/jpg' => 'jpg',
        'image/png'               => 'png',
        'image/webp'              => 'webp',
        default                   => 'jpg',
    };
    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $dest     = UPLOAD_DIR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) return false;
    return $filename;
}

const POSITIONS = [
    'GK'  => 'Goalkeeper (GK)',
    'RB'  => 'Right Back (RB)',
    'LB'  => 'Left Back (LB)',
    'CB'  => 'Centre Back (CB)',
    'RWB' => 'Right Wing Back (RWB)',
    'LWB' => 'Left Wing Back (LWB)',
    'CDM' => 'Defensive Midfielder (CDM)',
    'CM'  => 'Central Midfielder (CM)',
    'CAM' => 'Attacking Midfielder (CAM)',
    'RM'  => 'Right Midfielder (RM)',
    'LM'  => 'Left Midfielder (LM)',
    'RW'  => 'Right Winger (RW)',
    'LW'  => 'Left Winger (LW)',
    'SS'  => 'Second Striker (SS)',
    'CF'  => 'Centre Forward (CF)',
    'ST'  => 'Striker (ST)',
];

const RACES = [
    'Black African', 'Coloured', 'Indian / Asian', 'White', 'Other / Not Specified',
];

const PROVINCES = [
    'Gauteng', 'Western Cape', 'KwaZulu-Natal', 'Eastern Cape',
    'Limpopo', 'Mpumalanga', 'North West', 'Free State',
    'Northern Cape', 'Outside South Africa',
];

const STATUSES = [
    'Seeking Transfer' => 'Seeking Transfer',
    'Under Contract'   => 'Under Contract',
    'On Trial'         => 'On Trial',
    'Placed'           => 'Placed',
    'Inactive'         => 'Inactive',
];
