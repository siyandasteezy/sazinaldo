<?php
require_once __DIR__ . '/auth.php';
requireLogin();

$db = getDB();

// Filters
$filter  = $_GET['filter'] ?? 'all';   // all | unread | starred
$search  = trim($_GET['q'] ?? '');

$where   = [];
$params  = [];

if ($filter === 'unread')  { $where[] = 'm.is_read = 0'; }
if ($filter === 'starred') { $where[] = 'm.is_starred = 1'; }

if ($search !== '') {
    $where[]        = '(m.first_name LIKE :q OR m.last_name LIKE :q OR m.email LIKE :q OR m.subject LIKE :q OR m.message LIKE :q)';
    $params[':q']   = '%' . $search . '%';
}

$sql = "SELECT * FROM contact_submissions m"
     . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
     . " ORDER BY m.created_at DESC";

$messages = $db->prepare($sql);
$messages->execute($params);
$messages = $messages->fetchAll();

$unread = $db->query("SELECT COUNT(*) FROM contact_submissions WHERE is_read = 0")->fetchColumn();
$total  = $db->query("SELECT COUNT(*) FROM contact_submissions")->fetchColumn();

$success = flash('success');
$error   = flash('error');

function enquiryLabel(string $v): string {
    return [
        'player'  => 'Player / Athlete',
        'club'    => 'Club / Academy',
        'agent'   => 'Agent / Scout',
        'sponsor' => 'Sponsor / Investor',
        'media'   => 'Media / Broadcaster',
        'partner' => 'Potential Partner',
        'other'   => 'Other',
    ][$v] ?? ucfirst($v);
}
function subjectLabel(string $v): string {
    return [
        'player-placement'       => 'Player Placement & Scouting',
        'club-professionalisation'=> 'Club Professionalisation',
        'player-protection'      => 'Player Protection',
        'agent-partnership'      => 'Agent / Scout Partnership',
        'sponsorship'            => 'Commercial Sponsorship',
        'athlete-grants'         => 'Athlete Grants & Education',
        'advisory'               => 'Strategic Advisory',
        'media'                  => 'Media & Broadcast',
        'general'                => 'General Enquiry',
    ][$v] ?? ucfirst(str_replace('-', ' ', $v));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Messages | Sazinaldo Admin</title>
  <link rel="icon" href="/images/logo.jpg" type="image/jpeg" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/admin/css/admin.css" />
  <style>
    .msg-filters { display:flex; gap:0.5rem; margin-bottom:1.5rem; flex-wrap:wrap; align-items:center; }
    .filter-btn  { padding:0.4rem 1rem; border-radius:50px; border:1px solid var(--border); background:transparent; color:var(--text-secondary); font-size:0.8rem; cursor:pointer; transition:var(--transition); text-decoration:none; }
    .filter-btn.active, .filter-btn:hover { border-color:var(--gold); color:var(--gold); background:rgba(196,146,42,0.08); }
    .filter-btn .count { display:inline-block; background:var(--gold); color:#000; border-radius:50px; padding:0.05rem 0.45rem; font-size:0.65rem; font-weight:700; margin-left:0.35rem; }
    .msg-search  { flex:1; min-width:180px; }

    .msg-table td { vertical-align:top; }
    .msg-unread td { background:rgba(196,146,42,0.04); }
    .msg-unread .td-name { font-weight:700; color:var(--text); }
    .msg-dot { width:8px; height:8px; border-radius:50%; background:var(--gold); display:inline-block; margin-right:0.3rem; flex-shrink:0; }
    .msg-dot.read { background:transparent; border:1px solid var(--border); }

    .star-btn { background:none; border:none; cursor:pointer; font-size:1rem; line-height:1; padding:0.25rem; color:#6b7280; transition:color 0.15s; }
    .star-btn.starred { color:#f59e0b; }
    .star-btn:hover { color:#f59e0b; }

    /* Slide-out detail panel */
    .msg-panel { position:fixed; top:0; right:-520px; width:520px; max-width:100vw; height:100vh; background:var(--bg-secondary); border-left:1px solid var(--border); z-index:200; display:flex; flex-direction:column; transition:right 0.28s ease; box-shadow:-8px 0 32px rgba(0,0,0,0.4); }
    .msg-panel.open { right:0; }
    .panel-header { padding:1.25rem 1.5rem; border-bottom:1px solid var(--border); display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; }
    .panel-close  { background:none; border:none; color:var(--text-muted); font-size:1.4rem; cursor:pointer; line-height:1; padding:0.1rem 0.4rem; border-radius:4px; }
    .panel-close:hover { color:var(--text); background:rgba(255,255,255,0.05); }
    .panel-body   { flex:1; overflow-y:auto; padding:1.5rem; }
    .panel-footer { padding:1rem 1.5rem; border-top:1px solid var(--border); display:flex; gap:0.75rem; flex-wrap:wrap; }

    .meta-row { display:flex; gap:0.5rem; align-items:baseline; margin-bottom:0.6rem; font-size:0.85rem; }
    .meta-lbl { color:var(--text-muted); min-width:80px; font-size:0.78rem; text-transform:uppercase; letter-spacing:0.06em; }
    .meta-val { color:var(--text-secondary); }
    .meta-val a { color:var(--gold); }

    .msg-body-text { margin-top:1.25rem; padding:1.25rem; background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius); font-size:0.9rem; line-height:1.75; color:var(--text-secondary); white-space:pre-wrap; word-break:break-word; }

    .overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:199; }
    .overlay.show { display:block; }

    .empty-state { text-align:center; padding:4rem 2rem; color:var(--text-muted); }
    .empty-state .icon { font-size:2.5rem; margin-bottom:1rem; }
  </style>
</head>
<body>
<div class="admin-layout">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>

  <main class="admin-main">
    <div class="admin-topbar">
      <div>
        <div class="topbar-title">Messages</div>
        <div class="topbar-breadcrumb"><?= $total ?> total &middot; <?= $unread ?> unread</div>
      </div>
      <?php if ($messages): ?>
        <form method="POST" action="/admin/messages-action.php" style="display:inline;">
          <input type="hidden" name="action" value="mark_all_read" />
          <input type="hidden" name="csrf" value="<?= csrfToken() ?>" />
          <button class="btn-admin btn-ghost">Mark All Read</button>
        </form>
      <?php endif; ?>
    </div>

    <div class="admin-content">

      <?php if ($success): ?><div class="alert alert-success" style="margin-bottom:1rem;"><?= h($success) ?></div><?php endif; ?>
      <?php if ($error):   ?><div class="alert alert-error"   style="margin-bottom:1rem;"><?= h($error) ?></div><?php endif; ?>

      <!-- Filters + search -->
      <div class="msg-filters">
        <a href="?filter=all"<?= $filter==='all'     ? ' class="filter-btn active"' : ' class="filter-btn"' ?>>
          All <span class="count"><?= $total ?></span>
        </a>
        <a href="?filter=unread"<?= $filter==='unread'  ? ' class="filter-btn active"' : ' class="filter-btn"' ?>>
          Unread<?php if ($unread > 0): ?> <span class="count"><?= $unread ?></span><?php endif; ?>
        </a>
        <a href="?filter=starred"<?= $filter==='starred' ? ' class="filter-btn active"' : ' class="filter-btn"' ?>>
          ⭐ Starred
        </a>
        <form method="GET" style="display:flex;gap:0.5rem;flex:1;min-width:200px;">
          <input type="hidden" name="filter" value="<?= h($filter) ?>" />
          <input type="text" name="q" value="<?= h($search) ?>" placeholder="Search messages…" class="msg-search" style="padding:0.4rem 0.75rem;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-sm);color:var(--text);font-size:0.82rem;min-width:0;" />
          <button class="btn-admin btn-ghost btn-sm">Search</button>
          <?php if ($search): ?><a href="?filter=<?= h($filter) ?>" class="btn-admin btn-ghost btn-sm">Clear</a><?php endif; ?>
        </form>
      </div>

      <!-- Table -->
      <div class="table-wrap">
        <?php if (empty($messages)): ?>
          <div class="empty-state">
            <div class="icon">📭</div>
            <p><?= $search ? 'No messages match your search.' : 'No messages yet.' ?></p>
          </div>
        <?php else: ?>
        <table>
          <thead>
            <tr>
              <th style="width:28px;"></th>
              <th style="width:28px;"></th>
              <th>From</th>
              <th>Subject</th>
              <th>Role</th>
              <th>Date</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($messages as $m): ?>
            <tr class="msg-row<?= $m['is_read'] ? '' : ' msg-unread' ?>" data-id="<?= $m['id'] ?>" style="cursor:pointer;">
              <td onclick="event.stopPropagation()">
                <form method="POST" action="/admin/messages-action.php">
                  <input type="hidden" name="action" value="toggle_star" />
                  <input type="hidden" name="id" value="<?= $m['id'] ?>" />
                  <input type="hidden" name="csrf" value="<?= csrfToken() ?>" />
                  <button class="star-btn<?= $m['is_starred'] ? ' starred' : '' ?>" title="<?= $m['is_starred'] ? 'Unstar' : 'Star' ?>">★</button>
                </form>
              </td>
              <td><span class="msg-dot<?= $m['is_read'] ? ' read' : '' ?>"></span></td>
              <td>
                <div class="td-name"><?= h($m['first_name'] . ' ' . $m['last_name']) ?></div>
                <div style="font-size:0.75rem;color:var(--text-muted);"><?= h($m['email']) ?></div>
              </td>
              <td style="max-width:260px;">
                <div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:260px;font-size:0.85rem;">
                  <?= h(subjectLabel($m['subject'] ?? '')) ?>
                </div>
                <div style="font-size:0.75rem;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:260px;">
                  <?= h(mb_substr($m['message'], 0, 80)) ?>…
                </div>
              </td>
              <td style="font-size:0.8rem;color:var(--text-muted);white-space:nowrap;">
                <?= h(enquiryLabel($m['enquiry_type'] ?? '')) ?>
              </td>
              <td style="font-size:0.8rem;color:var(--text-muted);white-space:nowrap;">
                <?= date('d M Y', strtotime($m['created_at'])) ?><br/>
                <span style="font-size:0.72rem;"><?= date('H:i', strtotime($m['created_at'])) ?></span>
              </td>
              <td onclick="event.stopPropagation()" class="actions">
                <form method="POST" action="/admin/messages-action.php" onsubmit="return confirm('Delete this message?')">
                  <input type="hidden" name="action" value="delete" />
                  <input type="hidden" name="id" value="<?= $m['id'] ?>" />
                  <input type="hidden" name="csrf" value="<?= csrfToken() ?>" />
                  <button class="btn-admin btn-danger btn-sm">Delete</button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>

    </div>
  </main>
</div>

<!-- Overlay -->
<div class="overlay" id="overlay" onclick="closePanel()"></div>

<!-- Slide-out detail panel -->
<div class="msg-panel" id="msg-panel">
  <div class="panel-header">
    <div>
      <div id="panel-subject" style="font-family:var(--font-h);font-size:1.1rem;color:var(--text);margin-bottom:0.25rem;"></div>
      <div id="panel-date" style="font-size:0.78rem;color:var(--text-muted);"></div>
    </div>
    <button class="panel-close" onclick="closePanel()" aria-label="Close">×</button>
  </div>
  <div class="panel-body">
    <div class="meta-row"><span class="meta-lbl">From</span><span class="meta-val" id="panel-name"></span></div>
    <div class="meta-row"><span class="meta-lbl">Email</span><span class="meta-val"><a id="panel-email" href="#"></a></span></div>
    <div class="meta-row" id="panel-phone-row"><span class="meta-lbl">Phone</span><span class="meta-val" id="panel-phone"></span></div>
    <div class="meta-row" id="panel-country-row"><span class="meta-lbl">Country</span><span class="meta-val" id="panel-country"></span></div>
    <div class="meta-row"><span class="meta-lbl">Role</span><span class="meta-val" id="panel-role"></span></div>
    <div class="msg-body-text" id="panel-message"></div>
  </div>
  <div class="panel-footer">
    <a id="panel-reply" href="#" class="btn-admin btn-gold">Reply via Email</a>
    <form method="POST" action="/admin/messages-action.php" id="panel-read-form">
      <input type="hidden" name="action" value="toggle_read" />
      <input type="hidden" name="id" id="panel-read-id" value="" />
      <input type="hidden" name="csrf" value="<?= csrfToken() ?>" />
      <button id="panel-read-btn" class="btn-admin btn-ghost">Mark as Unread</button>
    </form>
    <form method="POST" action="/admin/messages-action.php" id="panel-delete-form" onsubmit="return confirm('Delete this message?')">
      <input type="hidden" name="action" value="delete" />
      <input type="hidden" name="id" id="panel-delete-id" value="" />
      <input type="hidden" name="csrf" value="<?= csrfToken() ?>" />
      <button class="btn-admin btn-danger">Delete</button>
    </form>
  </div>
</div>

<script>
const messages = <?= json_encode(array_map(fn($m) => [
  'id'           => $m['id'],
  'first_name'   => $m['first_name'],
  'last_name'    => $m['last_name'],
  'email'        => $m['email'],
  'phone'        => $m['phone'] ?? '',
  'country'      => $m['country'] ?? '',
  'enquiry_type' => enquiryLabel($m['enquiry_type'] ?? ''),
  'subject'      => subjectLabel($m['subject'] ?? ''),
  'message'      => $m['message'],
  'is_read'      => (bool)$m['is_read'],
  'created_at'   => $m['created_at'],
], $messages), JSON_HEX_TAG) ?>;

const panel     = document.getElementById('msg-panel');
const overlay   = document.getElementById('overlay');
let currentId   = null;

document.querySelectorAll('.msg-row').forEach(row => {
  row.addEventListener('click', () => openPanel(+row.dataset.id));
});

function openPanel(id) {
  const m = messages.find(x => x.id === id);
  if (!m) return;
  currentId = id;

  document.getElementById('panel-subject').textContent  = m.subject || '(No subject)';
  document.getElementById('panel-date').textContent     = new Date(m.created_at).toLocaleString('en-ZA', {day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'});
  document.getElementById('panel-name').textContent     = m.first_name + ' ' + m.last_name;
  const emailEl = document.getElementById('panel-email');
  emailEl.textContent = m.email;
  emailEl.href        = 'mailto:' + m.email;
  document.getElementById('panel-reply').href           = 'mailto:' + m.email + '?subject=Re: ' + encodeURIComponent(m.subject);

  const phoneRow = document.getElementById('panel-phone-row');
  document.getElementById('panel-phone').textContent    = m.phone;
  phoneRow.style.display = m.phone ? '' : 'none';

  const countryRow = document.getElementById('panel-country-row');
  document.getElementById('panel-country').textContent  = m.country;
  countryRow.style.display = m.country ? '' : 'none';

  document.getElementById('panel-role').textContent     = m.enquiry_type;
  document.getElementById('panel-message').textContent  = m.message;

  document.getElementById('panel-read-id').value    = id;
  document.getElementById('panel-delete-id').value  = id;
  document.getElementById('panel-read-btn').textContent = m.is_read ? 'Mark as Unread' : 'Mark as Read';

  // Auto-mark as read if opening an unread message
  if (!m.is_read) {
    fetch('/admin/messages-action.php', {
      method: 'POST',
      body: new URLSearchParams({
        action: 'mark_read',
        id,
        csrf: document.querySelector('#panel-read-form input[name=csrf]').value,
      }),
    }).then(() => {
      const row = document.querySelector(`.msg-row[data-id="${id}"]`);
      if (row) {
        row.classList.remove('msg-unread');
        const dot = row.querySelector('.msg-dot');
        if (dot) dot.classList.add('read');
      }
      m.is_read = true;
      document.getElementById('panel-read-btn').textContent = 'Mark as Unread';
    });
  }

  panel.classList.add('open');
  overlay.classList.add('show');
  document.body.style.overflow = 'hidden';
}

function closePanel() {
  panel.classList.remove('open');
  overlay.classList.remove('show');
  document.body.style.overflow = '';
  currentId = null;
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closePanel(); });
</script>
</body>
</html>
