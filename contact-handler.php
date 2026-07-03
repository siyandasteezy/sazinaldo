<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

// Honeypot
if (!empty($_POST['_gotcha'])) {
    echo json_encode(['ok' => true]); // Silently succeed for bots
    exit;
}

require_once __DIR__ . '/config/db.php';

// Sanitise helpers
$str  = fn(string $k, int $max = 255): string =>
    mb_substr(trim($_POST[$k] ?? ''), 0, $max);

$first_name   = $str('first_name', 100);
$last_name    = $str('last_name',  100);
$email        = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$phone        = $str('phone',    50);
$country      = $str('country',  100);
$enquiry_type = $str('enquiry_type', 100);
$subject      = $str('subject',  255);
$message      = $str('message',  5000);
$consent      = !empty($_POST['consent']);

// Validation
$errors = [];
if (!$first_name) $errors[] = 'First name is required.';
if (!$last_name)  $errors[] = 'Last name is required.';
if (!$email)      $errors[] = 'A valid email address is required.';
if (!$message)    $errors[] = 'Message is required.';
if (!$consent)    $errors[] = 'Please accept the consent checkbox.';

if ($errors) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => implode(' ', $errors)]);
    exit;
}

try {
    $db = getDB();
    $stmt = $db->prepare("
        INSERT INTO contact_submissions
            (first_name, last_name, email, phone, country, enquiry_type, subject, message)
        VALUES
            (:fn, :ln, :email, :phone, :country, :enquiry, :subject, :msg)
    ");
    $stmt->execute([
        ':fn'      => $first_name,
        ':ln'      => $last_name,
        ':email'   => $email,
        ':phone'   => $phone   ?: null,
        ':country' => $country ?: null,
        ':enquiry' => $enquiry_type ?: null,
        ':subject' => $subject ?: null,
        ':msg'     => $message,
    ]);

    // Optional email notification to admin
    $to      = 'siyandaedwana@gmail.com';
    $subjectLine = '[Sazinaldo] New enquiry from ' . $first_name . ' ' . $last_name;
    $body    = "Name: $first_name $last_name\n"
             . "Email: $email\n"
             . "Phone: $phone\n"
             . "Country: $country\n"
             . "Role: $enquiry_type\n"
             . "Subject: $subject\n\n"
             . "Message:\n$message";
    $headers = "From: noreply@sazinaldo.co.za\r\nReply-To: $email";
    @mail($to, $subjectLine, $body, $headers);

    echo json_encode(['ok' => true]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Server error — please try again later.']);
}
