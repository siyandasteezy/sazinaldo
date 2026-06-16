<?php
require_once __DIR__ . '/auth.php';
requireLogin();

$errors = [];
$data   = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $data = [
        'first_name'         => trim($_POST['first_name']         ?? ''),
        'last_name'          => trim($_POST['last_name']          ?? ''),
        'date_of_birth'      => trim($_POST['date_of_birth']      ?? '') ?: null,
        'gender'             => trim($_POST['gender']             ?? 'Female'),
        'nationality'        => trim($_POST['nationality']        ?? ''),
        'province'           => trim($_POST['province']           ?? ''),
        'race'               => trim($_POST['race']               ?? ''),
        'primary_position'   => trim($_POST['primary_position']   ?? ''),
        'secondary_position' => trim($_POST['secondary_position'] ?? ''),
        'preferred_foot'     => trim($_POST['preferred_foot']     ?? 'Right'),
        'height_cm'          => (int)($_POST['height_cm']         ?? 0) ?: null,
        'weight_kg'          => (int)($_POST['weight_kg']         ?? 0) ?: null,
        'current_club'       => trim($_POST['current_club']       ?? ''),
        'current_league'     => trim($_POST['current_league']     ?? ''),
        'current_country'    => trim($_POST['current_country']    ?? ''),
        'jersey_number'      => (int)($_POST['jersey_number']     ?? 0) ?: null,
        'contract_expiry'    => trim($_POST['contract_expiry']    ?? '') ?: null,
        'status'             => trim($_POST['status']             ?? 'Seeking Transfer'),
        'target_leagues'     => trim($_POST['target_leagues']     ?? ''),
        'target_countries'   => trim($_POST['target_countries']   ?? ''),
        'bio'                => trim($_POST['bio']                ?? ''),
        'achievements'       => trim($_POST['achievements']       ?? ''),
        'highlight_url'      => trim($_POST['highlight_url']      ?? ''),
        'agent_name'         => trim($_POST['agent_name']         ?? ''),
        'agent_email'        => trim($_POST['agent_email']        ?? ''),
        'agent_phone'        => trim($_POST['agent_phone']        ?? ''),
        'is_featured'        => isset($_POST['is_featured']) ? 1 : 0,
    ];

    if (!$data['first_name']) $errors[] = 'First name is required.';
    if (!$data['last_name'])  $errors[] = 'Last name is required.';

    // Handle photo upload
    $photo = null;
    if (!empty($_FILES['photo']['name'])) {
        $photo = uploadPhoto($_FILES['photo']);
        if (!$photo) $errors[] = 'Photo must be JPG, PNG or WebP and under 5MB.';
    }

    if (empty($errors)) {
        $data['photo'] = $photo;
        $cols = implode(', ', array_keys($data));
        $vals = implode(', ', array_fill(0, count($data), '?'));
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO players ($cols) VALUES ($vals)");
        $stmt->execute(array_values($data));
        flash('success', 'Player ' . $data['first_name'] . ' ' . $data['last_name'] . ' added successfully.');
        header('Location: /admin/players.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Add Player | Sazinaldo Admin</title>
  <link rel="icon" href="/images/logo.jpg" type="image/jpeg" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/admin/css/admin.css" />
</head>
<body>
<div class="admin-layout">

  <?php include __DIR__ . '/partials/sidebar.php'; ?>

  <main class="admin-main">
    <div class="admin-topbar">
      <div>
        <div class="topbar-title">Add New Player</div>
        <div class="topbar-breadcrumb">
          <a href="/admin/index.php">Dashboard</a> /
          <a href="/admin/players.php">Players</a> / Add
        </div>
      </div>
      <a href="/admin/players.php" class="btn-admin btn-ghost">← Back</a>
    </div>

    <div class="admin-content">

      <?php if ($errors): ?>
        <div class="alert alert-error" style="margin-bottom:1rem;">
          <?= implode('<br>', array_map('h', $errors)) ?>
        </div>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data" class="form-card">
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>" />

        <!-- PERSONAL -->
        <div class="form-card-section">
          <div class="form-section-title">Personal Information</div>

          <div class="photo-preview-wrap">
            <div class="photo-placeholder" id="photo-placeholder">👤</div>
            <img id="photo-img" class="photo-preview" style="display:none;" src="" alt="" />
            <div>
              <label style="display:block;font-size:0.8rem;font-weight:600;color:var(--text-secondary);margin-bottom:0.4rem;text-transform:uppercase;letter-spacing:0.05em;">
                Profile Photo
              </label>
              <input type="file" name="photo" id="photo" accept="image/jpeg,image/png,image/webp"
                     onchange="previewPhoto(this)" style="font-size:0.82rem;color:var(--text-secondary);" />
              <div style="font-size:0.72rem;color:var(--text-muted);margin-top:0.3rem;">JPG, PNG or WebP — max 5MB</div>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>First Name *</label>
              <input type="text" name="first_name" required value="<?= h($data['first_name'] ?? '') ?>" />
            </div>
            <div class="form-group">
              <label>Last Name *</label>
              <input type="text" name="last_name" required value="<?= h($data['last_name'] ?? '') ?>" />
            </div>
          </div>

          <div class="form-row-3">
            <div class="form-group">
              <label>Date of Birth</label>
              <input type="date" name="date_of_birth" value="<?= h($data['date_of_birth'] ?? '') ?>" />
            </div>
            <div class="form-group">
              <label>Gender</label>
              <select name="gender">
                <?php foreach (['Female','Male','Other'] as $g): ?>
                  <option value="<?= h($g) ?>" <?= ($data['gender'] ?? 'Female') === $g ? 'selected' : '' ?>><?= h($g) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Race / Ethnicity</label>
              <select name="race">
                <option value="">— Select —</option>
                <?php foreach (RACES as $r): ?>
                  <option value="<?= h($r) ?>" <?= ($data['race'] ?? '') === $r ? 'selected' : '' ?>><?= h($r) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Nationality / Country</label>
              <input type="text" name="nationality" placeholder="e.g. South African" value="<?= h($data['nationality'] ?? '') ?>" />
            </div>
            <div class="form-group">
              <label>Province (SA)</label>
              <select name="province">
                <option value="">— Select —</option>
                <?php foreach (PROVINCES as $pv): ?>
                  <option value="<?= h($pv) ?>" <?= ($data['province'] ?? '') === $pv ? 'selected' : '' ?>><?= h($pv) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>

        <!-- FOOTBALL -->
        <div class="form-card-section">
          <div class="form-section-title">Football Profile</div>

          <div class="form-row">
            <div class="form-group">
              <label>Primary Position</label>
              <select name="primary_position">
                <option value="">— Select —</option>
                <?php foreach (POSITIONS as $k => $v): ?>
                  <option value="<?= h($k) ?>" <?= ($data['primary_position'] ?? '') === $k ? 'selected' : '' ?>><?= h($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Secondary Position</label>
              <select name="secondary_position">
                <option value="">— None —</option>
                <?php foreach (POSITIONS as $k => $v): ?>
                  <option value="<?= h($k) ?>" <?= ($data['secondary_position'] ?? '') === $k ? 'selected' : '' ?>><?= h($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-row-3">
            <div class="form-group">
              <label>Preferred Foot</label>
              <select name="preferred_foot">
                <?php foreach (['Right','Left','Both'] as $f): ?>
                  <option value="<?= h($f) ?>" <?= ($data['preferred_foot'] ?? 'Right') === $f ? 'selected' : '' ?>><?= h($f) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Height (cm)</label>
              <input type="number" name="height_cm" min="140" max="210" placeholder="e.g. 168"
                     value="<?= h($data['height_cm'] ?? '') ?>" />
            </div>
            <div class="form-group">
              <label>Weight (kg)</label>
              <input type="number" name="weight_kg" min="40" max="100" placeholder="e.g. 62"
                     value="<?= h($data['weight_kg'] ?? '') ?>" />
            </div>
          </div>
        </div>

        <!-- CLUB -->
        <div class="form-card-section">
          <div class="form-section-title">Current Club</div>
          <div class="form-row">
            <div class="form-group">
              <label>Club Name</label>
              <input type="text" name="current_club" placeholder="e.g. Mamelodi Sundowns Ladies"
                     value="<?= h($data['current_club'] ?? '') ?>" />
            </div>
            <div class="form-group">
              <label>League</label>
              <input type="text" name="current_league" placeholder="e.g. Hollywoodbets SSWL"
                     value="<?= h($data['current_league'] ?? '') ?>" />
            </div>
          </div>
          <div class="form-row-3">
            <div class="form-group">
              <label>Club Country</label>
              <input type="text" name="current_country" placeholder="e.g. South Africa"
                     value="<?= h($data['current_country'] ?? '') ?>" />
            </div>
            <div class="form-group">
              <label>Jersey Number</label>
              <input type="number" name="jersey_number" min="1" max="99"
                     value="<?= h($data['jersey_number'] ?? '') ?>" />
            </div>
            <div class="form-group">
              <label>Contract Expiry</label>
              <input type="date" name="contract_expiry" value="<?= h($data['contract_expiry'] ?? '') ?>" />
            </div>
          </div>
        </div>

        <!-- STATUS & TARGETS -->
        <div class="form-card-section">
          <div class="form-section-title">Status &amp; Transfer Targets</div>
          <div class="form-row">
            <div class="form-group">
              <label>Status</label>
              <select name="status">
                <?php foreach (STATUSES as $k => $v): ?>
                  <option value="<?= h($k) ?>" <?= ($data['status'] ?? 'Seeking Transfer') === $k ? 'selected' : '' ?>><?= h($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Target Countries</label>
              <input type="text" name="target_countries" placeholder="e.g. USA, Germany, Spain"
                     value="<?= h($data['target_countries'] ?? '') ?>" />
            </div>
          </div>
          <div class="form-group">
            <label>Target Leagues</label>
            <input type="text" name="target_leagues" placeholder="e.g. NWSL, WSL, Liga F"
                   value="<?= h($data['target_leagues'] ?? '') ?>" />
          </div>
        </div>

        <!-- BIO -->
        <div class="form-card-section">
          <div class="form-section-title">Bio &amp; Achievements</div>
          <div class="form-group">
            <label>Player Bio</label>
            <textarea name="bio" rows="4" placeholder="Describe the player's background, style, strengths…"><?= h($data['bio'] ?? '') ?></textarea>
          </div>
          <div class="form-group">
            <label>Notable Achievements</label>
            <textarea name="achievements" rows="3" placeholder="Trophies, caps, awards, records…"><?= h($data['achievements'] ?? '') ?></textarea>
          </div>
          <div class="form-group">
            <label>Highlight Reel URL</label>
            <input type="url" name="highlight_url" placeholder="https://youtube.com/watch?v=..."
                   value="<?= h($data['highlight_url'] ?? '') ?>" />
          </div>
        </div>

        <!-- AGENT (admin only) -->
        <div class="form-card-section">
          <div class="form-section-title">Agent / Contact (Admin Only — not shown publicly)</div>
          <div class="form-row-3">
            <div class="form-group">
              <label>Agent Name</label>
              <input type="text" name="agent_name" value="<?= h($data['agent_name'] ?? '') ?>" />
            </div>
            <div class="form-group">
              <label>Agent Email</label>
              <input type="email" name="agent_email" value="<?= h($data['agent_email'] ?? '') ?>" />
            </div>
            <div class="form-group">
              <label>Agent Phone</label>
              <input type="tel" name="agent_phone" value="<?= h($data['agent_phone'] ?? '') ?>" />
            </div>
          </div>
        </div>

        <!-- OPTIONS -->
        <div class="form-card-section">
          <div class="form-section-title">Display Options</div>
          <label style="display:flex;align-items:center;gap:0.65rem;cursor:pointer;font-size:0.9rem;">
            <input type="checkbox" name="is_featured" value="1"
                   <?= !empty($data['is_featured']) ? 'checked' : '' ?>
                   style="width:auto;accent-color:var(--gold);" />
            <span style="color:var(--text-secondary);">⭐ Feature this player on the homepage</span>
          </label>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-admin btn-gold">Save Player</button>
          <a href="/admin/players.php" class="btn-admin btn-ghost">Cancel</a>
        </div>
      </form>

    </div>
  </main>
</div>

<script>
function previewPhoto(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      document.getElementById('photo-img').src = e.target.result;
      document.getElementById('photo-img').style.display = 'block';
      document.getElementById('photo-placeholder').style.display = 'none';
    };
    reader.readAsDataURL(input.files[0]);
  }
}
</script>
</body>
</html>
