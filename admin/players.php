<?php
require_once __DIR__ . '/auth.php';
requireLogin();

$db = getDB();

// Filters
$search   = trim($_GET['q']        ?? '');
$position = trim($_GET['position'] ?? '');
$status   = trim($_GET['status']   ?? '');
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 20;

$where  = [];
$params = [];

if ($search) {
    $where[]  = "(first_name LIKE ? OR last_name LIKE ? OR current_club LIKE ? OR nationality LIKE ?)";
    $params   = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%"]);
}
if ($position) { $where[] = "primary_position = ?"; $params[] = $position; }
if ($status)   { $where[] = "status = ?";           $params[] = $status; }

$sql  = "SELECT * FROM players" . ($where ? " WHERE " . implode(" AND ", $where) : "");
$countStmt = $db->prepare("SELECT COUNT(*) FROM players" . ($where ? " WHERE " . implode(" AND ", $where) : ""));
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));

$sql .= " ORDER BY created_at DESC LIMIT $perPage OFFSET " . (($page - 1) * $perPage);
$stmt = $db->prepare($sql);
$stmt->execute($params);
$players = $stmt->fetchAll();

$success = flash('success');
$error   = flash('error');

function statusBadge(string $status): string {
    $map = [
        'Seeking Transfer' => 'badge-green',
        'Under Contract'   => 'badge-gold',
        'On Trial'         => 'badge-orange',
        'Placed'           => 'badge-blue',
        'Inactive'         => 'badge-gray',
    ];
    return '<span class="badge ' . ($map[$status] ?? 'badge-gray') . '">' . htmlspecialchars($status) . '</span>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Players | Sazinaldo Admin</title>
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
        <div class="topbar-title">Players</div>
        <div class="topbar-breadcrumb"><a href="/admin/index.php">Dashboard</a> / Players</div>
      </div>
      <a href="/admin/add-player.php" class="btn-admin btn-gold">+ Add Player</a>
    </div>

    <div class="admin-content">

      <?php if ($success): ?><div class="alert alert-success" style="margin-bottom:1rem;"><?= h($success) ?></div><?php endif; ?>
      <?php if ($error):   ?><div class="alert alert-error"   style="margin-bottom:1rem;"><?= h($error)   ?></div><?php endif; ?>

      <div class="table-wrap">
        <div class="table-header">
          <span class="table-title"><?= $total ?> Players</span>
          <form method="GET" class="search-bar">
            <input type="text" name="q" placeholder="Search name, club, country…" value="<?= h($search) ?>" />
            <select name="position">
              <option value="">All Positions</option>
              <?php foreach (POSITIONS as $k => $v): ?>
                <option value="<?= h($k) ?>" <?= $position === $k ? 'selected' : '' ?>><?= h($v) ?></option>
              <?php endforeach; ?>
            </select>
            <select name="status">
              <option value="">All Statuses</option>
              <?php foreach (STATUSES as $k => $v): ?>
                <option value="<?= h($k) ?>" <?= $status === $k ? 'selected' : '' ?>><?= h($v) ?></option>
              <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-admin btn-ghost btn-sm">Filter</button>
            <?php if ($search || $position || $status): ?>
              <a href="/admin/players.php" class="btn-admin btn-ghost btn-sm">Clear</a>
            <?php endif; ?>
          </form>
        </div>

        <table>
          <thead>
            <tr>
              <th>⭐</th>
              <th>Player</th>
              <th>DOB / Age</th>
              <th>Position</th>
              <th>Nationality</th>
              <th>Race</th>
              <th>Club</th>
              <th>Foot</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($players)): ?>
              <tr><td colspan="10" style="text-align:center;padding:2rem;color:var(--text-muted);">No players found.</td></tr>
            <?php else: foreach ($players as $p):
                $dob = $p['date_of_birth'] ? new DateTime($p['date_of_birth']) : null;
                $age = $dob ? $dob->diff(new DateTime())->y : null;
            ?>
              <tr>
                <td>
                  <form method="POST" action="/admin/toggle-featured.php" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>" />
                    <input type="hidden" name="id" value="<?= $p['id'] ?>" />
                    <button type="submit" style="background:none;border:none;cursor:pointer;padding:0;"
                            title="<?= $p['is_featured'] ? 'Remove from featured' : 'Mark as featured' ?>">
                      <span class="featured-star <?= $p['is_featured'] ? '' : 'off' ?>">⭐</span>
                    </button>
                  </form>
                </td>
                <td>
                  <div style="display:flex;align-items:center;gap:0.65rem;">
                    <?php if ($p['photo']): ?>
                      <img class="player-thumb" src="/uploads/players/<?= h($p['photo']) ?>" alt="" />
                    <?php else: ?>
                      <div class="player-thumb-placeholder">⚽</div>
                    <?php endif; ?>
                    <div>
                      <div class="td-name"><?= h($p['first_name'] . ' ' . $p['last_name']) ?></div>
                      <div style="font-size:0.72rem;color:var(--text-muted);"><?= h($p['gender'] ?? '') ?></div>
                    </div>
                  </div>
                </td>
                <td style="white-space:nowrap;">
                  <?php if ($dob): ?>
                    <?= $dob->format('d M Y') ?><br/>
                    <span style="font-size:0.75rem;color:var(--text-muted);"><?= $age ?> yrs</span>
                  <?php else: ?><span style="color:var(--text-muted);">—</span><?php endif; ?>
                </td>
                <td><?= h($p['primary_position'] ?? '—') ?></td>
                <td><?= h($p['nationality']       ?? '—') ?></td>
                <td><?= h($p['race']              ?? '—') ?></td>
                <td>
                  <?= h($p['current_club'] ?? '—') ?>
                  <?php if ($p['current_country']): ?>
                    <div style="font-size:0.72rem;color:var(--text-muted);"><?= h($p['current_country']) ?></div>
                  <?php endif; ?>
                </td>
                <td><?= h($p['preferred_foot'] ?? '—') ?></td>
                <td><?= statusBadge($p['status']) ?></td>
                <td class="actions">
                  <a href="/admin/edit-player.php?id=<?= $p['id'] ?>" class="btn-admin btn-ghost btn-sm">Edit</a>
                  <form method="POST" action="/admin/delete-player.php"
                        onsubmit="return confirm('Delete <?= h($p['first_name'] . ' ' . $p['last_name']) ?>?');"
                        style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>" />
                    <input type="hidden" name="id" value="<?= $p['id'] ?>" />
                    <button type="submit" class="btn-admin btn-danger btn-sm">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>

        <?php if ($pages > 1): ?>
        <div class="pagination">
          <span>Page <?= $page ?> of <?= $pages ?></span>
          <div class="page-links">
            <?php for ($i = 1; $i <= $pages; $i++):
                $params_page = array_merge($_GET, ['page' => $i]);
            ?>
              <a href="?<?= http_build_query($params_page) ?>"
                 class="page-link <?= $i === $page ? 'current' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>

    </div>
  </main>
</div>
</body>
</html>
