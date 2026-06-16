<?php
require_once __DIR__ . '/config/db.php';

$db = getDB();

$search   = trim($_GET['q']        ?? '');
$position = trim($_GET['position'] ?? '');
$status   = trim($_GET['status']   ?? '');
$nat      = trim($_GET['nat']      ?? '');
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 12;

$where  = ["status != 'Inactive'"];
$params = [];

if ($search) {
    $where[]  = "(first_name LIKE ? OR last_name LIKE ? OR current_club LIKE ? OR nationality LIKE ?)";
    $params   = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%"]);
}
if ($position) { $where[] = "primary_position = ?"; $params[] = $position; }
if ($status)   { $where[] = "status = ?";           $params[] = $status; }
if ($nat)      { $where[] = "nationality LIKE ?";   $params[] = "%$nat%"; }

$whereSQL = " WHERE " . implode(" AND ", $where);

$countStmt = $db->prepare("SELECT COUNT(*) FROM players" . $whereSQL);
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));

$stmt = $db->prepare(
    "SELECT * FROM players" . $whereSQL .
    " ORDER BY is_featured DESC, created_at DESC LIMIT $perPage OFFSET " . (($page - 1) * $perPage)
);
$stmt->execute($params);
$players = $stmt->fetchAll();

function statusBadge(string $s): string {
    $map = [
        'Seeking Transfer' => ['badge-green',  'Seeking Transfer'],
        'Under Contract'   => ['badge-gold',   'Under Contract'],
        'On Trial'         => ['badge-orange', 'On Trial'],
        'Placed'           => ['badge-blue',   'Placed'],
    ];
    [$cls, $lbl] = $map[$s] ?? ['badge-gray', $s];
    return '<span class="pl-badge ' . $cls . '">' . htmlspecialchars($lbl) . '</span>';
}

function h(mixed $v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$positions = [
    'GK'=>'Goalkeeper','RB'=>'Right Back','LB'=>'Left Back','CB'=>'Centre Back',
    'RWB'=>'Right Wing Back','LWB'=>'Left Wing Back','CDM'=>'Def. Mid','CM'=>'Central Mid',
    'CAM'=>'Att. Mid','RM'=>'Right Mid','LM'=>'Left Mid','RW'=>'Right Winger',
    'LW'=>'Left Winger','SS'=>'Second Striker','CF'=>'Centre Forward','ST'=>'Striker',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Browse South African female football players available for global placement through Sazinaldo Dust Roots Consultancy." />
  <title>Player Directory | Sazinaldo Dust Roots Consultancy</title>
  <link rel="icon" href="/images/logo.jpg" type="image/jpeg" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/css/styles.css" />
  <style>
    /* Player directory extras */
    .players-hero { padding: 9rem 0 4rem; text-align: center; background: linear-gradient(180deg, rgba(196,146,42,0.07) 0%, transparent 100%); border-bottom: 1px solid var(--border); }
    .players-hero .eyebrow { display:inline-block; font-size:0.8rem; font-weight:600; letter-spacing:0.15em; text-transform:uppercase; color:var(--gold); margin-bottom:1rem; }

    .filter-bar { background: var(--bg-secondary); border-bottom: 1px solid var(--border); padding: 1.25rem 0; position: sticky; top: 0; z-index: 50; backdrop-filter: blur(10px); }
    .filter-inner { display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap; }
    .filter-inner input, .filter-inner select {
      background: var(--bg-card); border: 1px solid var(--border); border-radius: 50px;
      padding: 0.55rem 1rem; color: var(--text-primary); font-size: 0.85rem; outline: none;
      transition: border-color 0.2s; font-family: var(--font-body); -webkit-appearance: none;
    }
    .filter-inner input:focus, .filter-inner select:focus { border-color: var(--gold); }
    .filter-inner input { min-width: 200px; }
    .filter-count { margin-left: auto; font-size: 0.82rem; color: var(--text-muted); white-space: nowrap; }

    .players-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1.5rem; }

    .player-card {
      background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius);
      overflow: hidden; transition: all 0.3s; cursor: pointer; display: flex; flex-direction: column;
    }
    .player-card:hover { border-color: var(--border-hover); transform: translateY(-4px); box-shadow: var(--shadow-gold); }

    .player-card-photo {
      width: 100%; height: 220px; object-fit: cover; object-position: center top;
      background: var(--bg-secondary);
    }
    .player-card-no-photo {
      width: 100%; height: 220px; background: rgba(196,146,42,0.06);
      display: flex; align-items: center; justify-content: center; font-size: 4rem;
    }
    .player-card-body { padding: 1.25rem; flex: 1; display: flex; flex-direction: column; gap: 0.4rem; }
    .player-card-name { font-family: var(--font-heading); font-size: 1.1rem; font-weight: 700; color: var(--text-primary); }
    .player-card-pos { font-size: 0.8rem; color: var(--gold); font-weight: 600; }
    .player-card-meta { display: flex; flex-wrap: wrap; gap: 0.4rem; margin-top: 0.25rem; }
    .player-card-meta span { font-size: 0.75rem; color: var(--text-muted); }
    .player-card-meta span::before { content: '·'; margin-right: 0.3rem; }
    .player-card-meta span:first-child::before { display: none; }
    .player-card-footer { padding: 0.75rem 1.25rem; border-top: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }

    .pl-badge { display: inline-block; padding: 0.2rem 0.65rem; border-radius: 50px; font-size: 0.72rem; font-weight: 600; }
    .badge-green  { background: rgba(34,197,94,0.1);  color: #22c55e; }
    .badge-gold   { background: rgba(196,146,42,0.15); color: var(--gold); }
    .badge-orange { background: rgba(245,158,11,0.1); color: #f59e0b; }
    .badge-blue   { background: rgba(59,130,246,0.1); color: #3b82f6; }
    .badge-gray   { background: rgba(107,114,128,0.1);color: var(--text-muted); }

    .featured-pin { display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.72rem; color: var(--gold); }

    /* Modal */
    .player-modal-bg {
      position: fixed; inset: 0; background: rgba(0,0,0,0.75); z-index: 2000;
      display: flex; align-items: center; justify-content: center; padding: 1.5rem;
      opacity: 0; visibility: hidden; transition: all 0.25s;
    }
    .player-modal-bg.open { opacity: 1; visibility: visible; }
    .player-modal {
      background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius);
      max-width: 680px; width: 100%; max-height: 90vh; overflow-y: auto;
      transform: translateY(20px); transition: transform 0.25s;
    }
    .player-modal-bg.open .player-modal { transform: translateY(0); }
    .modal-header { display: flex; gap: 1.5rem; padding: 1.75rem; border-bottom: 1px solid var(--border); align-items: flex-start; }
    .modal-photo { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; object-position: center top; border: 2px solid var(--border); flex-shrink: 0; }
    .modal-no-photo { width: 100px; height: 100px; border-radius: 50%; background: rgba(196,146,42,0.08); border: 2px solid var(--border); display:flex;align-items:center;justify-content:center; font-size:2rem; flex-shrink:0; }
    .modal-name { font-family: var(--font-heading); font-size: 1.5rem; margin-bottom: 0.3rem; }
    .modal-sub { font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.6rem; }
    .modal-close { margin-left: auto; background: none; border: none; color: var(--text-muted); font-size: 1.5rem; cursor: pointer; padding: 0 0 0 1rem; line-height: 1; }
    .modal-close:hover { color: var(--text-primary); }
    .modal-body { padding: 1.5rem 1.75rem; }
    .modal-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem 1.5rem; margin-bottom: 1.25rem; }
    .modal-field label { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-muted); display: block; margin-bottom: 0.15rem; }
    .modal-field span { font-size: 0.9rem; color: var(--text-primary); }
    .modal-bio { margin-top: 1rem; font-size: 0.9rem; color: var(--text-secondary); line-height: 1.7; }
    .modal-video { margin-top: 1.25rem; }
    .modal-video a { color: var(--gold); font-size: 0.9rem; }
    .modal-footer { padding: 1.25rem 1.75rem; border-top: 1px solid var(--border); display: flex; gap: 1rem; align-items: center; }

    .no-players { text-align: center; padding: 5rem 1.5rem; color: var(--text-muted); }
    .no-players h3 { font-size: 1.2rem; margin-bottom: 0.5rem; color: var(--text-secondary); }

    @media(max-width: 600px) {
      .filter-inner { flex-direction: column; align-items: stretch; }
      .filter-count { margin-left: 0; }
      .modal-grid { grid-template-columns: 1fr; }
      .modal-header { flex-direction: column; }
    }
  </style>
</head>
<body>

  <!-- NAVBAR -->
  <nav class="navbar">
    <div class="nav-inner">
      <a href="/index.html" class="nav-logo">
        <img src="/images/logo.jpg" alt="Sazinaldo Logo" />
        <div class="nav-logo-text">
          <span class="name">Sazinaldo</span>
          <span class="tagline">Dust Roots Consultancy</span>
        </div>
      </a>
      <div class="nav-links">
        <a href="/index.html">Home</a>
        <a href="/about.html">About</a>
        <a href="/services.html">Services</a>
        <a href="/players.php" class="active">Players</a>
        <a href="/contact.html">Contact</a>
      </div>
      <a href="/contact.html" class="btn btn-primary nav-cta">Get in Touch</a>
      <button class="hamburger" aria-label="Open menu"><span></span><span></span><span></span></button>
    </div>
  </nav>

  <div class="mobile-nav">
    <button class="mobile-nav-close" aria-label="Close menu">&times;</button>
    <a href="/index.html">Home</a>
    <a href="/about.html">About</a>
    <a href="/services.html">Services</a>
    <a href="/players.php">Players</a>
    <a href="/contact.html">Contact</a>
    <a href="/contact.html" class="btn btn-primary">Get in Touch</a>
  </div>

  <!-- HERO -->
  <section class="players-hero">
    <div class="container">
      <div class="breadcrumb">
        <a href="/index.html">Home</a>
        <span class="sep">/</span>
        <span>Player Directory</span>
      </div>
      <span class="eyebrow">Our Athletes</span>
      <h1>South African <span class="gold-gradient">Player Directory</span></h1>
      <p style="max-width:580px;margin:1rem auto 0;font-size:1.05rem;">
        Browse elite South African female football talent available for global placement.
        Every player in our network is verified and supported by our advisory team.
      </p>
    </div>
  </section>

  <!-- FILTER BAR -->
  <div class="filter-bar">
    <div class="container">
      <form method="GET" class="filter-inner">
        <input type="text" name="q" placeholder="🔍  Search name, club, country…" value="<?= h($search) ?>" />
        <select name="position">
          <option value="">All Positions</option>
          <?php foreach ($positions as $k => $v): ?>
            <option value="<?= h($k) ?>" <?= $position === $k ? 'selected' : '' ?>><?= h($v) ?></option>
          <?php endforeach; ?>
        </select>
        <select name="status">
          <option value="">All Statuses</option>
          <?php foreach (['Seeking Transfer','Under Contract','On Trial','Placed'] as $s): ?>
            <option value="<?= h($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= h($s) ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary" style="padding:0.55rem 1.25rem;border-radius:50px;font-size:0.85rem;">Filter</button>
        <?php if ($search || $position || $status || $nat): ?>
          <a href="/players.php" class="btn btn-outline" style="padding:0.55rem 1.25rem;border-radius:50px;font-size:0.85rem;">Clear</a>
        <?php endif; ?>
        <span class="filter-count"><?= $total ?> player<?= $total !== 1 ? 's' : '' ?></span>
      </form>
    </div>
  </div>

  <!-- PLAYERS GRID -->
  <section class="section-padding">
    <div class="container">

      <?php if (empty($players)): ?>
        <div class="no-players">
          <h3>No players found</h3>
          <p>Try adjusting your filters or <a href="/players.php" style="color:var(--gold);">clear the search</a>.</p>
        </div>
      <?php else: ?>

        <div class="players-grid">
          <?php foreach ($players as $p):
            $dob = $p['date_of_birth'] ? new DateTime($p['date_of_birth']) : null;
            $age = $dob ? $dob->diff(new DateTime())->y : null;
            $posLabel = $positions[$p['primary_position'] ?? ''] ?? $p['primary_position'] ?? '';
          ?>
            <div class="player-card fade-up" onclick="openModal(<?= $p['id'] ?>)">
              <?php if ($p['photo']): ?>
                <img class="player-card-photo" src="/uploads/players/<?= h($p['photo']) ?>" alt="<?= h($p['first_name'] . ' ' . $p['last_name']) ?>" loading="lazy" />
              <?php else: ?>
                <div class="player-card-no-photo">⚽</div>
              <?php endif; ?>

              <div class="player-card-body">
                <?php if ($p['is_featured']): ?>
                  <div class="featured-pin">⭐ Featured</div>
                <?php endif; ?>
                <div class="player-card-name"><?= h($p['first_name'] . ' ' . $p['last_name']) ?></div>
                <div class="player-card-pos"><?= h($posLabel) ?></div>
                <div class="player-card-meta">
                  <?php if ($p['nationality']): ?><span><?= h($p['nationality']) ?></span><?php endif; ?>
                  <?php if ($age): ?><span><?= $age ?> yrs</span><?php endif; ?>
                  <?php if ($p['current_club']): ?><span><?= h($p['current_club']) ?></span><?php endif; ?>
                  <?php if ($p['height_cm']): ?><span><?= h($p['height_cm']) ?>cm</span><?php endif; ?>
                </div>
              </div>

              <div class="player-card-footer">
                <?= statusBadge($p['status']) ?>
                <span style="font-size:0.78rem;color:var(--text-muted);"><?= h($p['preferred_foot'] ?? '') ?> foot</span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- PAGINATION -->
        <?php if ($pages > 1): ?>
          <div style="display:flex;justify-content:center;gap:0.5rem;margin-top:3rem;">
            <?php
            $base = array_filter(['q'=>$search,'position'=>$position,'status'=>$status,'nat'=>$nat]);
            for ($i = 1; $i <= $pages; $i++):
              $q = http_build_query(array_merge($base, ['page' => $i]));
            ?>
              <a href="?<?= $q ?>"
                 style="width:38px;height:38px;border-radius:8px;display:flex;align-items:center;justify-content:center;
                        border:1px solid var(--border);font-size:0.85rem;transition:all 0.2s;
                        <?= $i === $page ? 'background:var(--gold);color:#000;border-color:var(--gold);font-weight:700;' : 'color:var(--text-secondary);' ?>">
                <?= $i ?>
              </a>
            <?php endfor; ?>
          </div>
        <?php endif; ?>

      <?php endif; ?>
    </div>
  </section>

  <!-- PLAYER MODAL -->
  <div class="player-modal-bg" id="modal-bg" onclick="closeModalOnBg(event)">
    <div class="player-modal" id="modal">
      <div class="modal-header">
        <div id="modal-photo-wrap"></div>
        <div style="flex:1;">
          <div class="modal-name" id="modal-name"></div>
          <div class="modal-sub" id="modal-sub"></div>
          <div id="modal-status"></div>
        </div>
        <button class="modal-close" onclick="closeModal()">×</button>
      </div>
      <div class="modal-body">
        <div class="modal-grid" id="modal-grid"></div>
        <div class="modal-bio" id="modal-bio"></div>
        <div class="modal-video" id="modal-video"></div>
      </div>
      <div class="modal-footer">
        <a href="/contact.html?ref=player" class="btn btn-primary" style="font-size:0.9rem;padding:0.65rem 1.5rem;">
          Enquire About This Player
        </a>
        <button onclick="closeModal()" class="btn btn-outline" style="font-size:0.9rem;padding:0.65rem 1.5rem;">Close</button>
      </div>
    </div>
  </div>

  <!-- CTA -->
  <section class="cta-section">
    <div class="container fade-up">
      <span style="display:inline-block;font-size:0.8rem;font-weight:600;letter-spacing:0.15em;text-transform:uppercase;color:var(--gold);margin-bottom:0.75rem;">Scouts & Agents</span>
      <h2>Looking for South African <span class="gold-gradient">Talent</span>?</h2>
      <p style="max-width:520px;margin:0 auto 2rem;">Connect with our team to access our full verified player database, receive tailored player recommendations, and begin the placement process.</p>
      <div class="cta-actions">
        <a href="/contact.html" class="btn btn-primary">Contact Our Team</a>
        <a href="/services.html" class="btn btn-outline">Our Services</a>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer>
    <div class="container">
      <div class="footer-grid">
        <div class="footer-brand">
          <a href="/index.html" class="nav-logo">
            <img src="/images/logo.jpg" alt="Sazinaldo Logo" />
            <div class="nav-logo-text">
              <span class="name">Sazinaldo</span>
              <span class="tagline">Dust Roots Consultancy</span>
            </div>
          </a>
          <p>Bridging the gap between South African female football talent and the world's most prestigious leagues.</p>
        </div>
        <div class="footer-col">
          <h4>Navigation</h4>
          <ul>
            <li><a href="/index.html">Home</a></li>
            <li><a href="/about.html">About Us</a></li>
            <li><a href="/services.html">Our Services</a></li>
            <li><a href="/players.php">Player Directory</a></li>
            <li><a href="/contact.html">Contact</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h4>Services</h4>
          <ul>
            <li><a href="/services.html">Global Talent Bridge</a></li>
            <li><a href="/services.html">Player Protection</a></li>
            <li><a href="/services.html">Club Professionalization</a></li>
            <li><a href="/services.html">Visibility & Sponsorship</a></li>
          </ul>
        </div>
        <div class="footer-col footer-newsletter">
          <h4>Stay Updated</h4>
          <p>Get the latest news on South African women's football.</p>
          <form class="newsletter-form" onsubmit="return false;">
            <input type="email" placeholder="Your email address" required />
            <button type="submit">Subscribe</button>
          </form>
        </div>
      </div>
      <div class="footer-bottom">
        <p>&copy; 2026 Sazinaldo Dust Roots Consultancy. All rights reserved. | Ekurhuleni, Gauteng, South Africa</p>
        <div class="social-links">
          <a href="#" class="social-link" aria-label="LinkedIn">in</a>
          <a href="#" class="social-link" aria-label="Twitter">𝕏</a>
          <a href="#" class="social-link" aria-label="Instagram">📷</a>
        </div>
      </div>
    </div>
  </footer>

  <button class="scroll-top" aria-label="Back to top">↑</button>
  <script src="/js/main.js"></script>

  <script>
  // Player data for modal
  const players = <?= json_encode(array_map(function($p) {
    return [
      'id'         => $p['id'],
      'name'       => $p['first_name'] . ' ' . $p['last_name'],
      'photo'      => $p['photo'] ? '/uploads/players/' . $p['photo'] : null,
      'position'   => $p['primary_position'] ?? '',
      'pos2'       => $p['secondary_position'] ?? '',
      'nat'        => $p['nationality'] ?? '',
      'dob'        => $p['date_of_birth'] ?? '',
      'race'       => $p['race'] ?? '',
      'gender'     => $p['gender'] ?? '',
      'height'     => $p['height_cm'] ?? '',
      'weight'     => $p['weight_kg'] ?? '',
      'foot'       => $p['preferred_foot'] ?? '',
      'club'       => $p['current_club'] ?? '',
      'league'     => $p['current_league'] ?? '',
      'country'    => $p['current_country'] ?? '',
      'jersey'     => $p['jersey_number'] ?? '',
      'status'     => $p['status'],
      'targets'    => $p['target_leagues'] ?? '',
      'bio'        => $p['bio'] ?? '',
      'achievements' => $p['achievements'] ?? '',
      'highlight'  => $p['highlight_url'] ?? '',
      'featured'   => (bool)$p['is_featured'],
    ];
  }, $players), JSON_UNESCAPED_UNICODE) ?>;

  const posMap = <?= json_encode(array_merge(
    ['GK'=>'Goalkeeper','RB'=>'Right Back','LB'=>'Left Back','CB'=>'Centre Back',
     'RWB'=>'Right Wing Back','LWB'=>'Left Wing Back','CDM'=>'Def. Mid','CM'=>'Central Mid',
     'CAM'=>'Att. Mid','RM'=>'Right Mid','LM'=>'Left Mid','RW'=>'Right Winger',
     'LW'=>'Left Winger','SS'=>'Second Striker','CF'=>'Centre Forward','ST'=>'Striker']
  )) ?>;

  const statusClasses = {
    'Seeking Transfer': 'badge-green',
    'Under Contract':   'badge-gold',
    'On Trial':         'badge-orange',
    'Placed':           'badge-blue',
  };

  function openModal(id) {
    const p = players.find(x => x.id == id);
    if (!p) return;

    const bg = document.getElementById('modal-bg');

    // Photo
    const photoWrap = document.getElementById('modal-photo-wrap');
    photoWrap.innerHTML = p.photo
      ? `<img class="modal-photo" src="${p.photo}" alt="${p.name}" />`
      : `<div class="modal-no-photo">⚽</div>`;

    document.getElementById('modal-name').textContent = p.name;

    const posParts = [posMap[p.position] || p.position, posMap[p.pos2] || p.pos2].filter(Boolean);
    document.getElementById('modal-sub').textContent = posParts.join(' / ') + (p.club ? ' · ' + p.club : '');

    const sCls = statusClasses[p.status] || 'badge-gray';
    document.getElementById('modal-status').innerHTML = `<span class="pl-badge ${sCls}" style="margin-top:0.5rem;">${p.status}</span>`;

    // Grid fields
    const age = p.dob ? Math.floor((new Date() - new Date(p.dob)) / 31557600000) : null;
    const fields = [
      ['Nationality',  p.nat],
      ['Date of Birth',p.dob ? new Date(p.dob).toLocaleDateString('en-ZA', {day:'2-digit',month:'short',year:'numeric'}) + (age ? ` (${age} yrs)` : '') : ''],
      ['Gender',       p.gender],
      ['Race',         p.race],
      ['Height',       p.height ? p.height + ' cm' : ''],
      ['Weight',       p.weight ? p.weight + ' kg' : ''],
      ['Preferred Foot', p.foot],
      ['Jersey No.',   p.jersey ? '#' + p.jersey : ''],
      ['Current Club', p.club],
      ['League',       p.league],
      ['Club Country', p.country],
      ['Target Leagues', p.targets],
    ].filter(f => f[1]);

    document.getElementById('modal-grid').innerHTML = fields.map(([l, v]) =>
      `<div class="modal-field"><label>${l}</label><span>${v}</span></div>`
    ).join('');

    let bioHTML = '';
    if (p.bio)          bioHTML += `<p>${p.bio}</p>`;
    if (p.achievements) bioHTML += `<p><strong style="color:var(--gold);">Achievements:</strong> ${p.achievements}</p>`;
    document.getElementById('modal-bio').innerHTML = bioHTML;

    document.getElementById('modal-video').innerHTML = p.highlight
      ? `<a href="${p.highlight}" target="_blank" rel="noopener">▶ Watch Highlight Reel</a>`
      : '';

    bg.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closeModal() {
    document.getElementById('modal-bg').classList.remove('open');
    document.body.style.overflow = '';
  }

  function closeModalOnBg(e) {
    if (e.target === document.getElementById('modal-bg')) closeModal();
  }

  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
  </script>
</body>
</html>
