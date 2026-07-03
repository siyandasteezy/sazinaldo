<?php
$page = basename($_SERVER['PHP_SELF'], '.php');
function sidebarLink(string $href, string $icon, string $label, string $current, string $badge = ''): string {
    $base   = basename($href, '.php');
    $active = $base === $current ? ' active' : '';
    $badgeHtml = $badge ? ' <span style="background:var(--gold);color:#000;border-radius:50px;padding:0.05rem 0.45rem;font-size:0.65rem;font-weight:700;margin-left:auto;">' . htmlspecialchars($badge) . '</span>' : '';
    return '<a href="' . $href . '" class="sidebar-link' . $active . '" style="' . ($badge ? 'justify-content:flex-start;gap:0.6rem;' : '') . '">'
         . '<span class="icon">' . $icon . '</span>' . htmlspecialchars($label) . $badgeHtml . '</a>';
}
// Unread message count for badge
try {
    $unreadCount = getDB()->query("SELECT COUNT(*) FROM contact_submissions WHERE is_read = 0")->fetchColumn();
} catch (Throwable) {
    $unreadCount = 0;
}
?>
<aside class="sidebar">
  <div class="sidebar-brand">
    <img src="/images/logo.jpg" alt="Sazinaldo" />
    <div class="sidebar-brand-text">
      <span class="name">Sazinaldo</span>
      <span class="role">Admin Portal</span>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="sidebar-section">Main</div>
    <?= sidebarLink('/admin/index.php',       '📊', 'Dashboard',   $page) ?>
    <?= sidebarLink('/admin/players.php',     '⚽', 'Players',     $page) ?>
    <?= sidebarLink('/admin/add-player.php',  '➕', 'Add Player',  $page) ?>
    <?= sidebarLink('/admin/messages.php',    '✉️', 'Messages',    $page, $unreadCount > 0 ? (string)$unreadCount : '') ?>

    <div class="sidebar-section" style="margin-top:1rem;">Site</div>
    <?= sidebarLink('/',                      '🌐', 'View Website', '') ?>
    <?= sidebarLink('/players.php',           '👀', 'Player Directory', '') ?>

    <div class="sidebar-section" style="margin-top:1rem;">Help</div>
    <?= sidebarLink('/admin/guide.php',       '📖', 'Admin Guide',  $page) ?>
  </nav>

  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="avatar"><?= strtoupper(substr($_SESSION['admin_user'] ?? 'A', 0, 1)) ?></div>
      <div>
        <div class="uname"><?= htmlspecialchars($_SESSION['admin_user'] ?? '') ?></div>
        <div class="urole">Administrator</div>
      </div>
    </div>
    <a href="/admin/logout.php" class="btn-admin btn-ghost btn-sm btn-full">Sign Out</a>
  </div>
</aside>
