<?php
require_once __DIR__ . '/auth.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Guide | Sazinaldo</title>
  <link rel="icon" href="/images/logo.jpg" type="image/jpeg" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/admin/css/admin.css" />
  <style>
    /* ── Guide-specific styles ── */
    .guide-wrap { max-width: 820px; }

    .guide-intro {
      background: linear-gradient(135deg, rgba(196,146,42,0.12), rgba(139,105,20,0.06));
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 2rem 2.25rem;
      margin-bottom: 2.5rem;
      display: flex;
      gap: 1.5rem;
      align-items: flex-start;
    }
    .guide-intro-icon { font-size: 2.5rem; flex-shrink: 0; }
    .guide-intro h2 { font-family: var(--font-h); font-size: 1.4rem; margin-bottom: 0.4rem; }
    .guide-intro p  { font-size: 0.9rem; margin: 0; }

    /* TOC */
    .toc {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 1.5rem 1.75rem;
      margin-bottom: 2.5rem;
    }
    .toc h3 { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.12em; color: var(--text-muted); margin-bottom: 1rem; font-family: var(--font); }
    .toc ol  { counter-reset: toc; padding: 0; display: flex; flex-direction: column; gap: 0.5rem; }
    .toc li  { counter-increment: toc; display: flex; align-items: center; gap: 0.75rem; }
    .toc li::before {
      content: counter(toc);
      width: 22px; height: 22px; border-radius: 50%;
      background: rgba(196,146,42,0.15); color: var(--gold);
      font-size: 0.72rem; font-weight: 700;
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
    }
    .toc a { font-size: 0.88rem; color: var(--text-secondary); transition: color 0.2s; }
    .toc a:hover { color: var(--gold); }

    /* Steps */
    .guide-section {
      margin-bottom: 3rem;
      scroll-margin-top: 80px;
    }
    .guide-section-header {
      display: flex; align-items: center; gap: 1rem;
      margin-bottom: 1.5rem;
      padding-bottom: 0.75rem;
      border-bottom: 1px solid var(--border);
    }
    .section-num {
      width: 36px; height: 36px; border-radius: 50%;
      background: linear-gradient(135deg, var(--gold-light), var(--gold-dark));
      color: #000; font-weight: 700; font-size: 0.9rem;
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
    }
    .guide-section-header h2 { font-family: var(--font-h); font-size: 1.3rem; margin: 0; }

    .step {
      display: flex;
      gap: 1rem;
      margin-bottom: 1.25rem;
      align-items: flex-start;
    }
    .step-num {
      width: 26px; height: 26px; border-radius: 50%;
      background: rgba(196,146,42,0.1); border: 1px solid var(--border);
      color: var(--gold); font-size: 0.75rem; font-weight: 700;
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0; margin-top: 2px;
    }
    .step-body { flex: 1; }
    .step-body strong { color: var(--text); font-size: 0.9rem; display: block; margin-bottom: 0.2rem; }
    .step-body p { font-size: 0.85rem; margin: 0; line-height: 1.6; }

    /* Tip / warning boxes */
    .tip, .warn, .info-box {
      display: flex; gap: 0.75rem; align-items: flex-start;
      padding: 0.9rem 1.1rem; border-radius: 8px;
      margin: 1rem 0; font-size: 0.85rem;
    }
    .tip  { background: rgba(34,197,94,0.07);  border: 1px solid rgba(34,197,94,0.2);  color: #22c55e; }
    .warn { background: rgba(239,68,68,0.07);  border: 1px solid rgba(239,68,68,0.2);  color: #ef4444; }
    .info-box { background: rgba(59,130,246,0.07); border: 1px solid rgba(59,130,246,0.2); color: #60a5fa; }
    .tip p, .warn p, .info-box p { color: inherit; margin: 0; }
    .tip-icon { font-size: 1.1rem; flex-shrink: 0; }

    /* ── UI MOCKUPS ── */
    .mockup {
      background: #0A0E1A;
      border: 1px solid rgba(196,146,42,0.25);
      border-radius: 10px;
      overflow: hidden;
      margin: 1.25rem 0;
      box-shadow: 0 8px 32px rgba(0,0,0,0.5);
    }
    .mockup-bar {
      background: #0D1220;
      border-bottom: 1px solid rgba(196,146,42,0.15);
      padding: 0.6rem 1rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    .mockup-dot { width: 10px; height: 10px; border-radius: 50%; }
    .dot-r { background: #ff5f57; }
    .dot-y { background: #febc2e; }
    .dot-g { background: #28c840; }
    .mockup-url {
      flex: 1; background: rgba(255,255,255,0.05); border-radius: 4px;
      padding: 0.25rem 0.75rem; font-size: 0.72rem; color: #6b7280;
      margin: 0 0.5rem; font-family: monospace;
    }

    /* Login mockup */
    .mock-login-body {
      display: flex; align-items: center; justify-content: center;
      padding: 2rem;
      background: radial-gradient(ellipse at 60% 40%, rgba(196,146,42,0.06) 0%, transparent 60%);
    }
    .mock-login-card {
      background: #111827; border: 1px solid rgba(196,146,42,0.2);
      border-radius: 12px; padding: 1.75rem; width: 280px; text-align: center;
    }
    .mock-logo-circle {
      width: 56px; height: 56px; border-radius: 50%;
      background: rgba(196,146,42,0.15); border: 2px solid rgba(196,146,42,0.3);
      margin: 0 auto 0.75rem;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.5rem;
    }
    .mock-h { font-family: var(--font-h); font-size: 1rem; color: #f0f2f5; margin-bottom: 0.2rem; }
    .mock-sub { font-size: 0.68rem; color: #6b7280; margin-bottom: 1.25rem; }
    .mock-field {
      background: #080c16; border: 1px solid rgba(196,146,42,0.2);
      border-radius: 6px; padding: 0.55rem 0.75rem;
      font-size: 0.75rem; color: #6b7280; text-align: left;
      margin-bottom: 0.6rem;
    }
    .mock-field.active { border-color: var(--gold); color: #f0f2f5; }
    .mock-btn {
      width: 100%; padding: 0.6rem;
      background: linear-gradient(135deg, #d4a843, #8b6914);
      border-radius: 6px; font-size: 0.8rem; font-weight: 700;
      color: #000; text-align: center; margin-top: 0.25rem;
    }

    /* Dashboard mockup */
    .mock-layout { display: flex; height: 260px; }
    .mock-sidebar {
      width: 140px; min-width: 140px;
      background: #0d1220; border-right: 1px solid rgba(196,146,42,0.15);
      padding: 0.75rem;
    }
    .mock-brand {
      display: flex; align-items: center; gap: 0.4rem;
      padding-bottom: 0.75rem; border-bottom: 1px solid rgba(196,146,42,0.15);
      margin-bottom: 0.75rem;
    }
    .mock-brand-circle {
      width: 22px; height: 22px; border-radius: 50%;
      background: rgba(196,146,42,0.2); font-size: 0.6rem;
      display: flex; align-items: center; justify-content: center;
    }
    .mock-brand-txt { font-size: 0.6rem; color: var(--gold); font-family: var(--font-h); }
    .mock-nav-lbl { font-size: 0.55rem; color: #6b7280; letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 0.4rem; }
    .mock-nav-item {
      display: flex; align-items: center; gap: 0.35rem;
      padding: 0.35rem 0.5rem; border-radius: 5px;
      font-size: 0.68rem; color: #9ca3af; margin-bottom: 0.15rem;
    }
    .mock-nav-item.active { background: rgba(196,146,42,0.12); color: var(--gold); }
    .mock-main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
    .mock-topbar {
      background: #111827; border-bottom: 1px solid rgba(196,146,42,0.15);
      padding: 0.6rem 1rem; display: flex; align-items: center; justify-content: space-between;
    }
    .mock-topbar-title { font-size: 0.8rem; font-weight: 600; color: #f0f2f5; }
    .mock-topbar-btn {
      background: linear-gradient(135deg, #d4a843, #8b6914);
      color: #000; font-size: 0.62rem; font-weight: 700;
      padding: 0.3rem 0.7rem; border-radius: 5px;
    }
    .mock-content { padding: 0.75rem 1rem; flex: 1; overflow: hidden; }
    .mock-stat-row { display: flex; gap: 0.6rem; margin-bottom: 0.75rem; }
    .mock-stat {
      flex: 1; background: #111827; border: 1px solid rgba(196,146,42,0.15);
      border-radius: 7px; padding: 0.6rem 0.75rem;
      display: flex; align-items: center; gap: 0.5rem;
    }
    .mock-stat-icon {
      width: 28px; height: 28px; border-radius: 6px;
      background: rgba(196,146,42,0.1);
      display: flex; align-items: center; justify-content: center;
      font-size: 0.9rem; flex-shrink: 0;
    }
    .mock-stat-num { font-family: var(--font-h); font-size: 1.1rem; color: #f0f2f5; line-height: 1; }
    .mock-stat-lbl { font-size: 0.55rem; color: #6b7280; text-transform: uppercase; }
    .mock-table-wrap { background: #111827; border: 1px solid rgba(196,146,42,0.15); border-radius: 7px; overflow: hidden; }
    .mock-table-hdr { padding: 0.5rem 0.75rem; border-bottom: 1px solid rgba(196,146,42,0.1); display: flex; justify-content: space-between; align-items: center; }
    .mock-table-title { font-size: 0.68rem; font-weight: 600; color: #f0f2f5; }
    .mock-row {
      padding: 0.45rem 0.75rem; display: flex; align-items: center; gap: 0.6rem;
      border-bottom: 1px solid rgba(255,255,255,0.03); font-size: 0.65rem;
    }
    .mock-avatar { width: 20px; height: 20px; border-radius: 50%; background: rgba(196,146,42,0.15); flex-shrink:0; }
    .mock-name { color: #f0f2f5; flex: 1; font-size: 0.65rem; }
    .mock-pos  { color: #9ca3af; font-size: 0.6rem; }
    .mock-badge {
      padding: 0.12rem 0.45rem; border-radius: 50px; font-size: 0.55rem; font-weight: 600;
    }
    .mb-green  { background: rgba(34,197,94,0.1);  color: #22c55e; }
    .mb-gold   { background: rgba(196,146,42,0.15); color: var(--gold); }
    .mb-blue   { background: rgba(59,130,246,0.1); color: #60a5fa; }

    /* Form mockup */
    .mock-form-wrap { padding: 1rem 1.25rem; }
    .mock-section-title {
      font-size: 0.6rem; text-transform: uppercase; letter-spacing: 0.1em;
      color: var(--gold); border-bottom: 1px solid rgba(196,146,42,0.15);
      padding-bottom: 0.4rem; margin-bottom: 0.75rem;
    }
    .mock-form-row { display: flex; gap: 0.6rem; margin-bottom: 0.5rem; }
    .mock-input {
      flex: 1; background: #080c16; border: 1px solid rgba(196,146,42,0.2);
      border-radius: 5px; padding: 0.4rem 0.6rem;
      font-size: 0.65rem; color: #6b7280;
    }
    .mock-input.filled { color: #f0f2f5; border-color: rgba(196,146,42,0.4); }
    .mock-input-lbl { font-size: 0.55rem; color: #9ca3af; margin-bottom: 0.2rem; }
    .mock-save-btn {
      background: linear-gradient(135deg, #d4a843, #8b6914);
      color: #000; font-size: 0.7rem; font-weight: 700;
      padding: 0.45rem 1.1rem; border-radius: 6px; display: inline-block; margin-top: 0.5rem;
    }

    /* Players grid mockup */
    .mock-grid { padding: 0.75rem 1rem; display: grid; grid-template-columns: repeat(3,1fr); gap: 0.6rem; }
    .mock-player-card {
      background: #111827; border: 1px solid rgba(196,146,42,0.15); border-radius: 7px; overflow: hidden;
    }
    .mock-player-img { height: 60px; background: rgba(196,146,42,0.08); display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
    .mock-player-body { padding: 0.5rem; }
    .mock-player-name { font-size: 0.65rem; font-weight: 600; color: #f0f2f5; margin-bottom: 0.15rem; }
    .mock-player-pos  { font-size: 0.58rem; color: var(--gold); margin-bottom: 0.3rem; }
    .mock-player-foot { display: flex; justify-content: space-between; align-items: center; padding: 0.35rem 0.5rem; border-top: 1px solid rgba(255,255,255,0.05); }

    /* Callout highlight */
    .highlight-ring {
      display: inline-block;
      background: rgba(196,146,42,0.1);
      border: 1px dashed var(--gold);
      border-radius: 6px;
      padding: 0.1rem 0.4rem;
      font-size: 0.8rem;
      color: var(--gold);
    }

    /* Field reference table */
    .field-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; margin: 1rem 0; }
    .field-table th {
      text-align: left; padding: 0.5rem 0.75rem;
      font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.08em;
      color: var(--text-muted); border-bottom: 1px solid var(--border);
    }
    .field-table td { padding: 0.55rem 0.75rem; border-bottom: 1px solid rgba(255,255,255,0.04); color: var(--text-secondary); vertical-align: top; }
    .field-table tr:hover td { background: rgba(255,255,255,0.02); }
    .field-table td:first-child { color: var(--text); font-weight: 500; white-space: nowrap; }
    .req { color: var(--gold); font-weight: 700; }

    @media(max-width: 600px) {
      .mock-layout { height: auto; flex-direction: column; }
      .mock-sidebar { width: 100%; min-width: unset; height: auto; display: none; }
      .mock-grid { grid-template-columns: repeat(2,1fr); }
      .mock-stat-row { flex-wrap: wrap; }
    }
  </style>
</head>
<body>
<div class="admin-layout">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>

  <main class="admin-main">
    <div class="admin-topbar">
      <div>
        <div class="topbar-title">Admin Guide</div>
        <div class="topbar-breadcrumb"><a href="/admin/index.php">Dashboard</a> / Guide</div>
      </div>
      <a href="/admin/index.php" class="btn-admin btn-ghost">← Dashboard</a>
    </div>

    <div class="admin-content">
    <div class="guide-wrap">

      <!-- INTRO -->
      <div class="guide-intro">
        <div class="guide-intro-icon">📖</div>
        <div>
          <h2>Welcome to the Sazinaldo Admin Panel</h2>
          <p>This guide walks you through everything you can do in the admin area — from logging in and adding players, to managing the public player directory. No technical knowledge required.</p>
        </div>
      </div>

      <!-- TABLE OF CONTENTS -->
      <div class="toc">
        <h3>Contents</h3>
        <ol>
          <li><a href="#s1">Logging In</a></li>
          <li><a href="#s2">The Dashboard</a></li>
          <li><a href="#s3">Adding a New Player</a></li>
          <li><a href="#s4">Editing a Player</a></li>
          <li><a href="#s5">Featuring a Player</a></li>
          <li><a href="#s6">Deleting a Player</a></li>
          <li><a href="#s7">The Public Player Directory</a></li>
          <li><a href="#s8">Player Field Reference</a></li>
        </ol>
      </div>


      <!-- ══ 1. LOGGING IN ══ -->
      <div class="guide-section" id="s1">
        <div class="guide-section-header">
          <div class="section-num">1</div>
          <h2>Logging In</h2>
        </div>

        <div class="step">
          <div class="step-num">1</div>
          <div class="step-body">
            <strong>Go to the admin login page</strong>
            <p>In your browser, navigate to: <span class="highlight-ring">yourdomain.co.za/admin/login.php</span></p>
          </div>
        </div>
        <div class="step">
          <div class="step-num">2</div>
          <div class="step-body">
            <strong>Enter your username and password</strong>
            <p>Type your admin username and password into the fields below, then click <strong style="color:var(--gold);">Sign In</strong>.</p>
          </div>
        </div>
        <div class="step">
          <div class="step-num">3</div>
          <div class="step-body">
            <strong>You'll land on the Dashboard</strong>
            <p>A successful login takes you straight to the admin dashboard.</p>
          </div>
        </div>

        <!-- LOGIN MOCKUP -->
        <div class="mockup">
          <div class="mockup-bar">
            <div class="mockup-dot dot-r"></div>
            <div class="mockup-dot dot-y"></div>
            <div class="mockup-dot dot-g"></div>
            <div class="mockup-url">yourdomain.co.za/admin/login.php</div>
          </div>
          <div class="mock-login-body">
            <div class="mock-login-card">
              <div class="mock-logo-circle">⚽</div>
              <div class="mock-h">Admin Portal</div>
              <div class="mock-sub">Sazinaldo Dust Roots Consultancy</div>
              <div style="font-size:0.6rem;color:#9ca3af;text-align:left;margin-bottom:0.3rem;text-transform:uppercase;letter-spacing:0.06em;">Username</div>
              <div class="mock-field active">admin</div>
              <div style="font-size:0.6rem;color:#9ca3af;text-align:left;margin-bottom:0.3rem;text-transform:uppercase;letter-spacing:0.06em;">Password</div>
              <div class="mock-field active">••••••••••••</div>
              <div class="mock-btn">Sign In</div>
            </div>
          </div>
        </div>

        <div class="warn">
          <div class="tip-icon">⚠️</div>
          <p><strong>Wrong password?</strong> You'll see a red error message. Double-check your username and password — both are case-sensitive.</p>
        </div>
      </div>


      <!-- ══ 2. DASHBOARD ══ -->
      <div class="guide-section" id="s2">
        <div class="guide-section-header">
          <div class="section-num">2</div>
          <h2>The Dashboard</h2>
        </div>

        <p style="font-size:0.9rem;color:var(--text-secondary);margin-bottom:1rem;">
          After logging in, you'll see the Dashboard. It gives you a quick overview of all players in the system and lets you navigate to every section.
        </p>

        <!-- DASHBOARD MOCKUP -->
        <div class="mockup">
          <div class="mockup-bar">
            <div class="mockup-dot dot-r"></div>
            <div class="mockup-dot dot-y"></div>
            <div class="mockup-dot dot-g"></div>
            <div class="mockup-url">yourdomain.co.za/admin/index.php</div>
          </div>
          <div class="mock-layout">
            <div class="mock-sidebar">
              <div class="mock-brand">
                <div class="mock-brand-circle">⚽</div>
                <div class="mock-brand-txt">Sazinaldo</div>
              </div>
              <div class="mock-nav-lbl">Main</div>
              <div class="mock-nav-item active">📊 Dashboard</div>
              <div class="mock-nav-item">⚽ Players</div>
              <div class="mock-nav-item">➕ Add Player</div>
              <div class="mock-nav-lbl" style="margin-top:0.6rem;">Site</div>
              <div class="mock-nav-item">🌐 View Website</div>
              <div class="mock-nav-item">👀 Player Directory</div>
            </div>
            <div class="mock-main">
              <div class="mock-topbar">
                <div>
                  <div class="mock-topbar-title">Dashboard</div>
                  <div style="font-size:0.6rem;color:#6b7280;">Welcome back, admin</div>
                </div>
                <div class="mock-topbar-btn">+ Add Player</div>
              </div>
              <div class="mock-content">
                <div class="mock-stat-row">
                  <div class="mock-stat"><div class="mock-stat-icon">⚽</div><div><div class="mock-stat-num">12</div><div class="mock-stat-lbl">Total Players</div></div></div>
                  <div class="mock-stat"><div class="mock-stat-icon">🌍</div><div><div class="mock-stat-num">8</div><div class="mock-stat-lbl">Seeking Transfer</div></div></div>
                  <div class="mock-stat"><div class="mock-stat-icon">🏆</div><div><div class="mock-stat-num">3</div><div class="mock-stat-lbl">Placed</div></div></div>
                  <div class="mock-stat"><div class="mock-stat-icon">⭐</div><div><div class="mock-stat-num">2</div><div class="mock-stat-lbl">Featured</div></div></div>
                </div>
                <div class="mock-table-wrap">
                  <div class="mock-table-hdr"><span class="mock-table-title">Recently Added</span><span style="font-size:0.6rem;color:var(--gold);">View All →</span></div>
                  <div class="mock-row"><div class="mock-avatar"></div><div class="mock-name">Thandi Nkosi</div><div class="mock-pos">ST</div><div class="mock-badge mb-green">Seeking</div></div>
                  <div class="mock-row"><div class="mock-avatar"></div><div class="mock-name">Lerato Dlamini</div><div class="mock-pos">CM</div><div class="mock-badge mb-gold">Contract</div></div>
                  <div class="mock-row"><div class="mock-avatar"></div><div class="mock-name">Ayanda Mokoena</div><div class="mock-pos">GK</div><div class="mock-badge mb-blue">Placed</div></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div style="margin-top:1.25rem;">
          <div class="step">
            <div class="step-num">A</div>
            <div class="step-body">
              <strong>Stat cards (top row)</strong>
              <p>Show total players, how many are seeking a transfer, how many have been placed, and how many are featured on the public site.</p>
            </div>
          </div>
          <div class="step">
            <div class="step-num">B</div>
            <div class="step-body">
              <strong>Recently Added table</strong>
              <p>Shows the 8 most recently added players. Click <strong style="color:var(--gold);">Edit</strong> next to any player to update their information.</p>
            </div>
          </div>
          <div class="step">
            <div class="step-num">C</div>
            <div class="step-body">
              <strong>Left sidebar</strong>
              <p>Navigate between sections here. Click <strong style="color:var(--gold);">View Website</strong> to open the public site in a new tab, or <strong style="color:var(--gold);">Player Directory</strong> to see what visitors see.</p>
            </div>
          </div>
        </div>
      </div>


      <!-- ══ 3. ADDING A PLAYER ══ -->
      <div class="guide-section" id="s3">
        <div class="guide-section-header">
          <div class="section-num">3</div>
          <h2>Adding a New Player</h2>
        </div>

        <div class="step">
          <div class="step-num">1</div>
          <div class="step-body">
            <strong>Click "Add Player"</strong>
            <p>Either click <span class="highlight-ring">+ Add Player</span> in the top-right of any page, or click <strong>➕ Add Player</strong> in the sidebar.</p>
          </div>
        </div>
        <div class="step">
          <div class="step-num">2</div>
          <div class="step-body">
            <strong>Upload a profile photo (optional but recommended)</strong>
            <p>Click the file selector under <em>Profile Photo</em>. Choose a JPG, PNG or WebP image — max 5MB. You'll see a live preview of the photo as a circle before saving.</p>
          </div>
        </div>
        <div class="step">
          <div class="step-num">3</div>
          <div class="step-body">
            <strong>Fill in the player's details</strong>
            <p>The form is split into sections. Work through each one — only <strong style="color:var(--gold);">First Name</strong> and <strong style="color:var(--gold);">Last Name</strong> are required. All other fields are optional but the more you fill in, the richer the player's profile on the public directory.</p>
          </div>
        </div>
        <div class="step">
          <div class="step-num">4</div>
          <div class="step-body">
            <strong>Click "Save Player"</strong>
            <p>Scroll to the bottom and click the gold <span class="highlight-ring">Save Player</span> button. You'll be taken back to the player list with a confirmation message.</p>
          </div>
        </div>

        <!-- ADD PLAYER MOCKUP -->
        <div class="mockup">
          <div class="mockup-bar">
            <div class="mockup-dot dot-r"></div>
            <div class="mockup-dot dot-y"></div>
            <div class="mockup-dot dot-g"></div>
            <div class="mockup-url">yourdomain.co.za/admin/add-player.php</div>
          </div>
          <div class="mock-form-wrap">
            <div class="mock-section-title">Personal Information</div>
            <div style="display:flex;align-items:center;gap:1rem;margin-bottom:0.75rem;">
              <div style="width:50px;height:50px;border-radius:50%;background:rgba(196,146,42,0.08);border:2px dashed rgba(196,146,42,0.3);display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;">👤</div>
              <div>
                <div style="font-size:0.62rem;color:#9ca3af;margin-bottom:0.2rem;text-transform:uppercase;letter-spacing:0.05em;">Profile Photo</div>
                <div style="font-size:0.68rem;color:var(--gold);background:rgba(196,146,42,0.1);border:1px solid rgba(196,146,42,0.3);border-radius:4px;padding:0.25rem 0.6rem;display:inline-block;">Choose file…</div>
              </div>
            </div>
            <div class="mock-form-row">
              <div style="flex:1"><div class="mock-input-lbl">First Name *</div><div class="mock-input filled">Thandi</div></div>
              <div style="flex:1"><div class="mock-input-lbl">Last Name *</div><div class="mock-input filled">Nkosi</div></div>
            </div>
            <div class="mock-form-row">
              <div style="flex:1"><div class="mock-input-lbl">Date of Birth</div><div class="mock-input filled">2001-03-14</div></div>
              <div style="flex:1"><div class="mock-input-lbl">Gender</div><div class="mock-input filled">Female</div></div>
              <div style="flex:1"><div class="mock-input-lbl">Race / Ethnicity</div><div class="mock-input filled">Black African</div></div>
            </div>
            <div style="margin-top:0.5rem;">
              <div class="mock-section-title">Football Profile</div>
              <div class="mock-form-row">
                <div style="flex:1"><div class="mock-input-lbl">Primary Position</div><div class="mock-input filled">Striker (ST)</div></div>
                <div style="flex:1"><div class="mock-input-lbl">Preferred Foot</div><div class="mock-input filled">Right</div></div>
                <div style="flex:1"><div class="mock-input-lbl">Height (cm)</div><div class="mock-input filled">167</div></div>
              </div>
            </div>
            <div class="mock-save-btn">Save Player</div>
          </div>
        </div>

        <div class="tip">
          <div class="tip-icon">💡</div>
          <p><strong>Agent details are private.</strong> The agent name, email and phone you enter are only visible inside the admin — they never appear on the public website.</p>
        </div>
      </div>


      <!-- ══ 4. EDITING A PLAYER ══ -->
      <div class="guide-section" id="s4">
        <div class="guide-section-header">
          <div class="section-num">4</div>
          <h2>Editing a Player</h2>
        </div>

        <div class="step">
          <div class="step-num">1</div>
          <div class="step-body">
            <strong>Go to Players in the sidebar</strong>
            <p>Click <span class="highlight-ring">⚽ Players</span> in the left sidebar to see the full player list.</p>
          </div>
        </div>
        <div class="step">
          <div class="step-num">2</div>
          <div class="step-body">
            <strong>Find the player</strong>
            <p>Use the search bar to type a name, club or country. Use the dropdowns to filter by position or status. The list updates when you click <strong>Filter</strong>.</p>
          </div>
        </div>
        <div class="step">
          <div class="step-num">3</div>
          <div class="step-body">
            <strong>Click "Edit"</strong>
            <p>Click the <span class="highlight-ring">Edit</span> button on the right of the player's row. This opens the same form as Add Player, but pre-filled with the existing information.</p>
          </div>
        </div>
        <div class="step">
          <div class="step-num">4</div>
          <div class="step-body">
            <strong>Make your changes and click "Save Changes"</strong>
            <p>Update any fields you need. To replace the photo, just choose a new file — if you leave the photo field blank, the existing photo is kept.</p>
          </div>
        </div>

        <!-- PLAYERS LIST MOCKUP -->
        <div class="mockup">
          <div class="mockup-bar">
            <div class="mockup-dot dot-r"></div>
            <div class="mockup-dot dot-y"></div>
            <div class="mockup-dot dot-g"></div>
            <div class="mockup-url">yourdomain.co.za/admin/players.php</div>
          </div>
          <div style="padding:0.75rem 1rem;">
            <div style="display:flex;gap:0.5rem;margin-bottom:0.75rem;align-items:center;flex-wrap:wrap;">
              <div class="mock-input" style="flex:2;min-width:120px;">🔍 Search name, club…</div>
              <div class="mock-input" style="flex:1;min-width:80px;">All Positions</div>
              <div class="mock-input" style="flex:1;min-width:80px;">All Statuses</div>
              <div style="background:rgba(196,146,42,0.15);color:var(--gold);font-size:0.65rem;font-weight:700;padding:0.35rem 0.75rem;border-radius:5px;border:1px solid rgba(196,146,42,0.3);">Filter</div>
            </div>
            <div class="mock-table-wrap">
              <div style="display:grid;grid-template-columns:20px 1fr 60px 50px 70px 60px auto;gap:0.5rem;padding:0.45rem 0.75rem;border-bottom:1px solid rgba(196,146,42,0.1);">
                <div style="font-size:0.55rem;color:#6b7280;text-transform:uppercase;">⭐</div>
                <div style="font-size:0.55rem;color:#6b7280;text-transform:uppercase;">Player</div>
                <div style="font-size:0.55rem;color:#6b7280;text-transform:uppercase;">Position</div>
                <div style="font-size:0.55rem;color:#6b7280;text-transform:uppercase;">Race</div>
                <div style="font-size:0.55rem;color:#6b7280;text-transform:uppercase;">Club</div>
                <div style="font-size:0.55rem;color:#6b7280;text-transform:uppercase;">Status</div>
                <div style="font-size:0.55rem;color:#6b7280;text-transform:uppercase;">Actions</div>
              </div>
              <?php foreach ([
                ['Thandi Nkosi','ST','Black African','Sundowns Ladies','Seeking Transfer','mb-green'],
                ['Lerato Dlamini','CM','Coloured','JVW FC','Under Contract','mb-gold'],
                ['Ayanda Mokoena','GK','Black African','Mamelodi','Placed','mb-blue'],
              ] as [$name,$pos,$race,$club,$status,$cls]): ?>
              <div style="display:grid;grid-template-columns:20px 1fr 60px 50px 70px 60px auto;gap:0.5rem;padding:0.45rem 0.75rem;border-bottom:1px solid rgba(255,255,255,0.03);align-items:center;">
                <div style="font-size:0.7rem;color:#6b7280;">⭐</div>
                <div style="display:flex;align-items:center;gap:0.4rem;"><div class="mock-avatar"></div><span style="font-size:0.65rem;color:#f0f2f5;"><?= $name ?></span></div>
                <div style="font-size:0.6rem;color:#9ca3af;"><?= $pos ?></div>
                <div style="font-size:0.6rem;color:#9ca3af;"><?= $race ?></div>
                <div style="font-size:0.6rem;color:#9ca3af;"><?= $club ?></div>
                <div><span class="mock-badge <?= $cls ?>"><?= explode(' ',$status)[0] ?></span></div>
                <div style="display:flex;gap:0.3rem;">
                  <div style="font-size:0.6rem;color:var(--gold);background:rgba(196,146,42,0.1);border:1px solid rgba(196,146,42,0.3);padding:0.2rem 0.5rem;border-radius:4px;">Edit</div>
                  <div style="font-size:0.6rem;color:#ef4444;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);padding:0.2rem 0.5rem;border-radius:4px;">Delete</div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>


      <!-- ══ 5. FEATURING A PLAYER ══ -->
      <div class="guide-section" id="s5">
        <div class="guide-section-header">
          <div class="section-num">5</div>
          <h2>Featuring a Player</h2>
        </div>

        <p style="font-size:0.9rem;color:var(--text-secondary);margin-bottom:1rem;">
          Featured players appear at the top of the public Player Directory and have a ⭐ "Featured" label on their card. Use this to highlight your best or most available players.
        </p>

        <div class="step">
          <div class="step-num">1</div>
          <div class="step-body">
            <strong>Go to the Players list</strong>
            <p>Click <span class="highlight-ring">⚽ Players</span> in the sidebar.</p>
          </div>
        </div>
        <div class="step">
          <div class="step-num">2</div>
          <div class="step-body">
            <strong>Click the ⭐ star in the first column</strong>
            <p>A <strong style="color:var(--gold);">gold star ⭐</strong> means the player is currently featured. A <strong style="color:var(--text-muted);">grey star</strong> means they are not. Clicking it instantly toggles the status — no need to save anything.</p>
          </div>
        </div>

        <div class="tip">
          <div class="tip-icon">💡</div>
          <p>You can also tick the <strong style="color:var(--gold);">⭐ Feature this player</strong> checkbox at the bottom of the Add Player or Edit Player form.</p>
        </div>
      </div>


      <!-- ══ 6. DELETING A PLAYER ══ -->
      <div class="guide-section" id="s6">
        <div class="guide-section-header">
          <div class="section-num">6</div>
          <h2>Deleting a Player</h2>
        </div>

        <div class="step">
          <div class="step-num">1</div>
          <div class="step-body">
            <strong>Find the player in the Players list</strong>
            <p>Use search or scroll to locate the player you want to remove.</p>
          </div>
        </div>
        <div class="step">
          <div class="step-num">2</div>
          <div class="step-body">
            <strong>Click the red "Delete" button</strong>
            <p>A confirmation popup will appear asking: <em>"Delete [player name]?"</em></p>
          </div>
        </div>
        <div class="step">
          <div class="step-num">3</div>
          <div class="step-body">
            <strong>Click OK to confirm</strong>
            <p>The player and their photo are permanently removed from the system.</p>
          </div>
        </div>

        <div class="warn">
          <div class="tip-icon">⚠️</div>
          <p><strong>This cannot be undone.</strong> Deleting a player permanently removes all their information and their profile photo from the server. Make sure you mean it before confirming.</p>
        </div>
      </div>


      <!-- ══ 7. PUBLIC DIRECTORY ══ -->
      <div class="guide-section" id="s7">
        <div class="guide-section-header">
          <div class="section-num">7</div>
          <h2>The Public Player Directory</h2>
        </div>

        <p style="font-size:0.9rem;color:var(--text-secondary);margin-bottom:1rem;">
          Everything you add in the admin automatically appears on the public-facing <strong style="color:var(--text);">Player Directory</strong> page, visible to scouts, agents, and visitors.
        </p>

        <div class="step">
          <div class="step-num">A</div>
          <div class="step-body">
            <strong>Visitors can search and filter</strong>
            <p>A filter bar lets visitors search by name, filter by position, and filter by status (Seeking Transfer, On Trial, etc.).</p>
          </div>
        </div>
        <div class="step">
          <div class="step-num">B</div>
          <div class="step-body">
            <strong>Clicking a player card opens their full profile</strong>
            <p>A pop-up shows all their details — position, nationality, club, height, bio, achievements, and a link to their highlight reel if you've added one. Agent details are <strong>not</strong> shown here.</p>
          </div>
        </div>
        <div class="step">
          <div class="step-num">C</div>
          <div class="step-body">
            <strong>Inactive players are hidden</strong>
            <p>Players with the status <em>Inactive</em> do not appear on the public directory — only in the admin. Use this to hide players temporarily without deleting them.</p>
          </div>
        </div>

        <!-- PUBLIC DIRECTORY MOCKUP -->
        <div class="mockup">
          <div class="mockup-bar">
            <div class="mockup-dot dot-r"></div>
            <div class="mockup-dot dot-y"></div>
            <div class="mockup-dot dot-g"></div>
            <div class="mockup-url">yourdomain.co.za/players.php</div>
          </div>
          <div style="background:#0a0e1a;padding:0.5rem 0.75rem;border-bottom:1px solid rgba(196,146,42,0.1);display:flex;gap:0.5rem;align-items:center;">
            <div class="mock-input" style="flex:2;">🔍  Search name, club, country…</div>
            <div class="mock-input">All Positions</div>
            <div class="mock-input">All Statuses</div>
            <div style="font-size:0.6rem;color:#6b7280;margin-left:auto;">12 players</div>
          </div>
          <div class="mock-grid">
            <?php foreach ([
              ['Thandi Nkosi','Striker','🇿🇦','Sundowns','Seeking','mb-green'],
              ['⭐ Lerato Dlamini','Central Mid','🇿🇦','JVW FC','Contract','mb-gold'],
              ['Ayanda Mokoena','Goalkeeper','🇿🇦','Mamelodi','Placed','mb-blue'],
            ] as [$n,$p,$f,$c,$s,$cls]): ?>
            <div class="mock-player-card">
              <div class="mock-player-img">⚽</div>
              <div class="mock-player-body">
                <div class="mock-player-name"><?= $n ?></div>
                <div class="mock-player-pos"><?= $p ?></div>
                <div style="font-size:0.58rem;color:#6b7280;"><?= $f ?> · <?= $c ?></div>
              </div>
              <div class="mock-player-foot">
                <span class="mock-badge <?= $cls ?>"><?= $s ?></span>
                <span style="font-size:0.55rem;color:#6b7280;">Right foot</span>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="info-box">
          <div class="tip-icon">ℹ️</div>
          <p>To preview the public directory, click <strong style="color:#60a5fa;">👀 Player Directory</strong> in the admin sidebar — it opens in the same tab.</p>
        </div>
      </div>


      <!-- ══ 8. FIELD REFERENCE ══ -->
      <div class="guide-section" id="s8">
        <div class="guide-section-header">
          <div class="section-num">8</div>
          <h2>Player Field Reference</h2>
        </div>
        <p style="font-size:0.9rem;color:var(--text-secondary);margin-bottom:1rem;">
          A full list of every field in the Add / Edit Player form and what it means. Fields marked <span class="req">*</span> are required.
        </p>

        <table class="field-table">
          <thead><tr><th>Field</th><th>What to enter</th><th>Shown publicly?</th></tr></thead>
          <tbody>
            <tr><td>First Name <span class="req">*</span></td><td>Player's first name</td><td>✅ Yes</td></tr>
            <tr><td>Last Name <span class="req">*</span></td><td>Player's last name</td><td>✅ Yes</td></tr>
            <tr><td>Profile Photo</td><td>JPG, PNG or WebP — max 5MB. Best: a clear headshot or action shot.</td><td>✅ Yes</td></tr>
            <tr><td>Date of Birth</td><td>Used to auto-calculate age</td><td>✅ Yes (with age)</td></tr>
            <tr><td>Gender</td><td>Female / Male / Other</td><td>✅ Yes</td></tr>
            <tr><td>Race / Ethnicity</td><td>Black African, Coloured, Indian/Asian, White, Other</td><td>✅ Yes</td></tr>
            <tr><td>Nationality</td><td>e.g. South African, Zimbabwean</td><td>✅ Yes</td></tr>
            <tr><td>Province</td><td>SA province or "Outside South Africa"</td><td>✅ Yes</td></tr>
            <tr><td>Primary Position</td><td>Main playing position (GK, CB, ST, etc.)</td><td>✅ Yes</td></tr>
            <tr><td>Secondary Position</td><td>Alternative position if versatile</td><td>✅ Yes</td></tr>
            <tr><td>Preferred Foot</td><td>Right, Left, or Both</td><td>✅ Yes</td></tr>
            <tr><td>Height (cm)</td><td>e.g. 167</td><td>✅ Yes</td></tr>
            <tr><td>Weight (kg)</td><td>e.g. 62</td><td>✅ Yes</td></tr>
            <tr><td>Current Club</td><td>Full club name</td><td>✅ Yes</td></tr>
            <tr><td>League</td><td>e.g. Hollywoodbets SSWL</td><td>✅ Yes</td></tr>
            <tr><td>Club Country</td><td>Country where the club is based</td><td>✅ Yes</td></tr>
            <tr><td>Jersey Number</td><td>Squad number at current club</td><td>✅ Yes</td></tr>
            <tr><td>Contract Expiry</td><td>When their current contract ends</td><td>❌ Admin only</td></tr>
            <tr><td>Status</td><td>Seeking Transfer / Under Contract / On Trial / Placed / Inactive</td><td>✅ Yes</td></tr>
            <tr><td>Target Leagues</td><td>e.g. NWSL, WSL, Liga F</td><td>✅ Yes</td></tr>
            <tr><td>Target Countries</td><td>e.g. USA, Germany, Spain</td><td>✅ Yes</td></tr>
            <tr><td>Player Bio</td><td>Short paragraph about the player's background and strengths</td><td>✅ Yes</td></tr>
            <tr><td>Notable Achievements</td><td>Trophies, caps, awards, records</td><td>✅ Yes</td></tr>
            <tr><td>Highlight Reel URL</td><td>YouTube or Vimeo link to their best footage</td><td>✅ Yes (as link)</td></tr>
            <tr><td>Agent Name</td><td>Name of their agent or representative</td><td>❌ Admin only</td></tr>
            <tr><td>Agent Email</td><td>Agent's contact email</td><td>❌ Admin only</td></tr>
            <tr><td>Agent Phone</td><td>Agent's phone number</td><td>❌ Admin only</td></tr>
            <tr><td>Feature this player ⭐</td><td>Tick to pin this player to the top of the public directory</td><td>✅ Yes (position)</td></tr>
          </tbody>
        </table>

        <div class="tip" style="margin-top:1.5rem;">
          <div class="tip-icon">💡</div>
          <p><strong>Status meanings at a glance:</strong><br/>
          <span class="mock-badge mb-green" style="display:inline-block;margin:0.2rem 0.2rem 0 0;">Seeking Transfer</span> — available and looking for a move<br/>
          <span class="mock-badge mb-gold" style="display:inline-block;margin:0.2rem 0.2rem 0 0;">Under Contract</span> — signed, may still accept offers<br/>
          <span class="mock-badge" style="background:rgba(245,158,11,0.1);color:#f59e0b;padding:0.12rem 0.45rem;border-radius:50px;font-size:0.55rem;font-weight:600;display:inline-block;margin:0.2rem 0.2rem 0 0;">On Trial</span> — currently trialling at a club<br/>
          <span class="mock-badge mb-blue" style="display:inline-block;margin:0.2rem 0.2rem 0 0;">Placed</span> — successfully placed internationally<br/>
          <span class="mock-badge" style="background:rgba(107,114,128,0.15);color:#6b7280;padding:0.12rem 0.45rem;border-radius:50px;font-size:0.55rem;font-weight:600;display:inline-block;margin:0.2rem 0.2rem 0 0;">Inactive</span> — hidden from public directory</p>
        </div>
      </div>

      <!-- SIGNING OUT -->
      <div class="guide-section">
        <div class="guide-section-header">
          <div class="section-num" style="background:rgba(107,114,128,0.3);">↩</div>
          <h2>Signing Out</h2>
        </div>
        <p style="font-size:0.9rem;color:var(--text-secondary);">
          Always sign out when you're done, especially on a shared computer. Click <span class="highlight-ring">Sign Out</span> at the bottom of the left sidebar.
        </p>
      </div>

    </div><!-- /guide-wrap -->
    </div><!-- /admin-content -->
  </main>
</div>
</body>
</html>
