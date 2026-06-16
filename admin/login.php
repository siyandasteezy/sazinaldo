<?php
require_once __DIR__ . '/auth.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: /admin/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = getDB()->prepare("SELECT id, password_hash FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id']   = $user['id'];
            $_SESSION['admin_user'] = $username;
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            header('Location: /admin/index.php');
            exit;
        }
    }
    $error = 'Incorrect username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Login | Sazinaldo</title>
  <link rel="icon" href="/images/logo.jpg" type="image/jpeg" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/admin/css/admin.css" />
</head>
<body class="login-body">
  <div class="login-wrap">
    <div class="login-card">
      <div class="login-logo">
        <img src="/images/logo.jpg" alt="Sazinaldo" />
      </div>
      <h1>Admin Portal</h1>
      <p class="login-sub">Sazinaldo Dust Roots Consultancy</p>

      <?php if ($error): ?>
        <div class="alert alert-error"><?= h($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="/admin/login.php">
        <div class="form-group">
          <label>Username</label>
          <input type="text" name="username" required autofocus
                 value="<?= h($_POST['username'] ?? '') ?>" placeholder="Enter username" />
        </div>
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" required placeholder="Enter password" />
        </div>
        <button type="submit" class="btn-admin btn-gold btn-full">Sign In</button>
      </form>

      <a href="/" class="back-link">← Back to website</a>
    </div>
  </div>
</body>
</html>
