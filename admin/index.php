<?php
require_once __DIR__ . '/auth.php';
requireLogin();

$db = getDB();

$stats = [
    'total'    => $db->query("SELECT COUNT(*) FROM players")->fetchColumn(),
    'seeking'  => $db->query("SELECT COUNT(*) FROM players WHERE status = 'Seeking Transfer'")->fetchColumn(),
    'placed'   => $db->query("SELECT COUNT(*) FROM players WHERE status = 'Placed'")->fetchColumn(),
    'featured' => $db->query("SELECT COUNT(*) FROM players WHERE is_featured = 1")->fetchColumn(),
];

$recent = $db->query(
    "SELECT id, first_name, last_name, primary_position, nationality, status, photo, created_at
     FROM players ORDER BY created_at DESC LIMIT 8"
)->fetchAll();

$success = flash('success');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard | Sazinaldo Admin</title>
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
        <div class="topbar-title">Dashboard</div>
        <div class="topbar-breadcrumb">Welcome back, <?= h($_SESSION['admin_user']) ?></div>
      </div>
      <a href="/admin/add-player.php" class="btn-admin btn-gold">+ Add Player</a>
    </div>

    <div class="admin-content">

      <?php if ($success): ?>
        <div class="alert alert-success" style="margin-bottom:1.5rem;"><?= h($success) ?></div>
      <?php endif; ?>

      <div class="stat-grid">
        <div class="stat-card">
          <div class="stat-icon gold">⚽</div>
          <div>
            <div class="stat-num"><?= $stats['total'] ?></div>
            <div class="stat-lbl">Total Players</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon green">🌍</div>
          <div>
            <div class="stat-num"><?= $stats['seeking'] ?></div>
            <div class="stat-lbl">Seeking Transfer</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon blue">🏆</div>
          <div>
            <div class="stat-num"><?= $stats['placed'] ?></div>
            <div class="stat-lbl">Placed</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon gold">⭐</div>
          <div>
            <div class="stat-num"><?= $stats['featured'] ?></div>
            <div class="stat-lbl">Featured</div>
          </div>
        </div>
      </div>

      <div class="table-wrap">
        <div class="table-header">
          <span class="table-title">Recently Added Players</span>
          <a href="/admin/players.php" class="btn-admin btn-ghost btn-sm">View All</a>
        </div>
        <table>
          <thead>
            <tr>
              <th>Player</th>
              <th>Position</th>
              <th>Nationality</th>
              <th>Status</th>
              <th>Added</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($recent)): ?>
              <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted);">No players yet. <a href="/admin/add-player.php" style="color:var(--gold);">Add the first one →</a></td></tr>
            <?php else: foreach ($recent as $p): ?>
              <tr>
                <td>
                  <div style="display:flex;align-items:center;gap:0.65rem;">
                    <?php if ($p['photo']): ?>
                      <img class="player-thumb" src="/uploads/players/<?= h($p['photo']) ?>" alt="" />
                    <?php else: ?>
                      <div class="player-thumb-placeholder">⚽</div>
                    <?php endif; ?>
                    <span class="td-name"><?= h($p['first_name'] . ' ' . $p['last_name']) ?></span>
                  </div>
                </td>
                <td><?= h($p['primary_position'] ?? '—') ?></td>
                <td><?= h($p['nationality'] ?? '—') ?></td>
                <td><?= statusBadge($p['status']) ?></td>
                <td><?= date('d M Y', strtotime($p['created_at'])) ?></td>
                <td class="actions">
                  <a href="/admin/edit-player.php?id=<?= $p['id'] ?>" class="btn-admin btn-ghost btn-sm">Edit</a>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>

    </div>
  </main>
</div>

<?php
function statusBadge(string $status): string {
    $map = [
        'Seeking Transfer' => 'badge-green',
        'Under Contract'   => 'badge-gold',
        'On Trial'         => 'badge-orange',
        'Placed'           => 'badge-blue',
        'Inactive'         => 'badge-gray',
    ];
    $cls = $map[$status] ?? 'badge-gray';
    return '<span class="badge ' . $cls . '">' . htmlspecialchars($status) . '</span>';
}
?>
</body>
</html>
