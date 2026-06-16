<?php
require_once __DIR__ . '/auth.php';
requireLogin();

$db = getDB();
$id = (int)($_GET['id'] ?? 0);

$player = $db->prepare("SELECT * FROM players WHERE id = ?");
$player->execute([$id]);
$player = $player->fetch();

if (!$player) {
    flash('error', 'Player not found.');
    header('Location: /admin/players.php');
    exit;
}

$errors = [];

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

    // Handle photo replacement
    if (!empty($_FILES['photo']['name'])) {
        $newPhoto = uploadPhoto($_FILES['photo']);
        if (!$newPhoto) {
            $errors[] = 'Photo must be JPG, PNG or WebP and under 5MB.';
        } else {
            // Delete old photo
            if ($player['photo'] && file_exists(UPLOAD_DIR . $player['photo'])) {
                unlink(UPLOAD_DIR . $player['photo']);
            }
            $data['photo'] = $newPhoto;
        }
    } else {
        $data['photo'] = $player['photo'];
    }

    if (empty($errors)) {
        $sets = implode(', ', array_map(fn($c) => "$c = ?", array_keys($data)));
        $stmt = $db->prepare("UPDATE players SET $sets WHERE id = ?");
        $stmt->execute([...array_values($data), $id]);
        flash('success', h($data['first_name'] . ' ' . $data['last_name']) . ' updated successfully.');
        header('Location: /admin/players.php');
        exit;
    }

    // Keep submitted values for re-display
    $player = array_merge($player, $data);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Edit <?= h($player['first_name'] . ' ' . $player['last_name']) ?> | Sazinaldo Admin</title>
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
        <div class="topbar-title">Edit Player</div>
        <div class="topbar-breadcrumb">
          <a href="/admin/index.php">Dashboard</a> /
          <a href="/admin/players.php">Players</a> /
          <?= h($player['first_name'] . ' ' . $player['last_name']) ?>
        </div>
      </div>
      <a href="/admin/players.php" class="btn-admin btn-ghost">← Back</a>
    </div>

    <div class="admin-content">

      <?php if ($errors): ?>
        <div class="alert alert-error" style="margin-bottom:1rem;"><?= implode('<br>', array_map('h', $errors)) ?></div>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data" class="form-card">
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>" />

        <!-- PERSONAL -->
        <div class="form-card-section">
          <div class="form-section-title">Personal Information</div>
          <div class="photo-preview-wrap">
            <?php if ($player['photo']): ?>
              <img id="photo-img" class="photo-preview" src="/uploads/players/<?= h($player['photo']) ?>" alt="" />
              <div id="photo-placeholder" class="photo-placeholder" style="display:none;">👤</div>
            <?php else: ?>
              <div id="photo-placeholder" class="photo-placeholder">👤</div>
              <img id="photo-img" class="photo-preview" style="display:none;" src="" alt="" />
            <?php endif; ?>
            <div>
              <label style="display:block;font-size:0.8rem;font-weight:600;color:var(--text-secondary);margin-bottom:0.4rem;text-transform:uppercase;letter-spacing:0.05em;">
                Replace Photo
              </label>
              <input type="file" name="photo" id="photo" accept="image/jpeg,image/png,image/webp"
                     onchange="previewPhoto(this)" style="font-size:0.82rem;color:var(--text-secondary);" />
              <div style="font-size:0.72rem;color:var(--text-muted);margin-top:0.3rem;">Leave blank to keep current photo</div>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>First Name *</label>
              <input type="text" name="first_name" required value="<?= h($player['first_name']) ?>" />
            </div>
            <div class="form-group">
              <label>Last Name *</label>
              <input type="text" name="last_name" required value="<?= h($player['last_name']) ?>" />
            </div>
          </div>
          <div class="form-row-3">
            <div class="form-group">
              <label>Date of Birth</label>
              <input type="date" name="date_of_birth" value="<?= h($player['date_of_birth'] ?? '') ?>" />
            </div>
            <div class="form-group">
              <label>Gender</label>
              <select name="gender">
                <?php foreach (['Female','Male','Other'] as $g): ?>
                  <option value="<?= h($g) ?>" <?= ($player['gender'] ?? 'Female') === $g ? 'selected' : '' ?>><?= h($g) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Race / Ethnicity</label>
              <select name="race">
                <option value="">— Select —</option>
                <?php foreach (RACES as $r): ?>
                  <option value="<?= h($r) ?>" <?= ($player['race'] ?? '') === $r ? 'selected' : '' ?>><?= h($r) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Nationality / Country</label>
              <input type="text" name="nationality" value="<?= h($player['nationality'] ?? '') ?>" />
            </div>
            <div class="form-group">
              <label>Province (SA)</label>
              <select name="province">
                <option value="">— Select —</option>
                <?php foreach (PROVINCES as $pv): ?>
                  <option value="<?= h($pv) ?>" <?= ($player['province'] ?? '') === $pv ? 'selected' : '' ?>><?= h($pv) ?></option>
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
                  <option value="<?= h($k) ?>" <?= ($player['primary_position'] ?? '') === $k ? 'selected' : '' ?>><?= h($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Secondary Position</label>
              <select name="secondary_position">
                <option value="">— None —</option>
                <?php foreach (POSITIONS as $k => $v): ?>
                  <option value="<?= h($k) ?>" <?= ($player['secondary_position'] ?? '') === $k ? 'selected' : '' ?>><?= h($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="form-row-3">
            <div class="form-group">
              <label>Preferred Foot</label>
              <select name="preferred_foot">
                <?php foreach (['Right','Left','Both'] as $f): ?>
                  <option value="<?= h($f) ?>" <?= ($player['preferred_foot'] ?? 'Right') === $f ? 'selected' : '' ?>><?= h($f) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Height (cm)</label>
              <input type="number" name="height_cm" min="140" max="210" value="<?= h($player['height_cm'] ?? '') ?>" />
            </div>
            <div class="form-group">
              <label>Weight (kg)</label>
              <input type="number" name="weight_kg" min="40" max="100" value="<?= h($player['weight_kg'] ?? '') ?>" />
            </div>
          </div>
        </div>

        <!-- CLUB -->
        <div class="form-card-section">
          <div class="form-section-title">Current Club</div>
          <div class="form-row">
            <div class="form-group">
              <label>Club Name</label>
              <input type="text" name="current_club" value="<?= h($player['current_club'] ?? '') ?>" />
            </div>
            <div class="form-group">
              <label>League</label>
              <input type="text" name="current_league" value="<?= h($player['current_league'] ?? '') ?>" />
            </div>
          </div>
          <div class="form-row-3">
            <div class="form-group">
              <label>Club Country</label>
              <input type="text" name="current_country" value="<?= h($player['current_country'] ?? '') ?>" />
            </div>
            <div class="form-group">
              <label>Jersey Number</label>
              <input type="number" name="jersey_number" min="1" max="99" value="<?= h($player['jersey_number'] ?? '') ?>" />
            </div>
            <div class="form-group">
              <label>Contract Expiry</label>
              <input type="date" name="contract_expiry" value="<?= h($player['contract_expiry'] ?? '') ?>" />
            </div>
          </div>
        </div>

        <!-- STATUS -->
        <div class="form-card-section">
          <div class="form-section-title">Status &amp; Transfer Targets</div>
          <div class="form-row">
            <div class="form-group">
              <label>Status</label>
              <select name="status">
                <?php foreach (STATUSES as $k => $v): ?>
                  <option value="<?= h($k) ?>" <?= ($player['status'] ?? '') === $k ? 'selected' : '' ?>><?= h($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Target Countries</label>
              <input type="text" name="target_countries" value="<?= h($player['target_countries'] ?? '') ?>" />
            </div>
          </div>
          <div class="form-group">
            <label>Target Leagues</label>
            <input type="text" name="target_leagues" value="<?= h($player['target_leagues'] ?? '') ?>" />
          </div>
        </div>

        <!-- BIO -->
        <div class="form-card-section">
          <div class="form-section-title">Bio &amp; Achievements</div>
          <div class="form-group">
            <label>Player Bio</label>
            <textarea name="bio" rows="4"><?= h($player['bio'] ?? '') ?></textarea>
          </div>
          <div class="form-group">
            <label>Notable Achievements</label>
            <textarea name="achievements" rows="3"><?= h($player['achievements'] ?? '') ?></textarea>
          </div>
          <div class="form-group">
            <label>Highlight Reel URL</label>
            <input type="url" name="highlight_url" value="<?= h($player['highlight_url'] ?? '') ?>" />
          </div>
        </div>

        <!-- AGENT -->
        <div class="form-card-section">
          <div class="form-section-title">Agent / Contact (Admin Only)</div>
          <div class="form-row-3">
            <div class="form-group">
              <label>Agent Name</label>
              <input type="text" name="agent_name" value="<?= h($player['agent_name'] ?? '') ?>" />
            </div>
            <div class="form-group">
              <label>Agent Email</label>
              <input type="email" name="agent_email" value="<?= h($player['agent_email'] ?? '') ?>" />
            </div>
            <div class="form-group">
              <label>Agent Phone</label>
              <input type="tel" name="agent_phone" value="<?= h($player['agent_phone'] ?? '') ?>" />
            </div>
          </div>
        </div>

        <!-- OPTIONS -->
        <div class="form-card-section">
          <div class="form-section-title">Display Options</div>
          <label style="display:flex;align-items:center;gap:0.65rem;cursor:pointer;font-size:0.9rem;">
            <input type="checkbox" name="is_featured" value="1"
                   <?= $player['is_featured'] ? 'checked' : '' ?>
                   style="width:auto;accent-color:var(--gold);" />
            <span style="color:var(--text-secondary);">⭐ Feature this player on the homepage</span>
          </label>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-admin btn-gold">Save Changes</button>
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
