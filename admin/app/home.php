<?php
// Catatan: variabel $nama, $role, $initial sudah disiapkan oleh admin/index.php.
// File ini HANYA berisi konten dashboard — tanpa html/head/body/session/routing,
// karena sudah dirender di dalam layout admin/index.php.
$email = $_SESSION['email'];
?>

<style>
    .home-dashboard {
        position: relative;
        max-width: 760px;
        margin: 0 auto;
        border-radius: 24px;
        background: linear-gradient(160deg, var(--panel) 0%, var(--panel-2) 100%);
        border: 1px solid rgba(200,134,42,0.18);
        padding: 40px 36px;
        box-shadow: 0 24px 60px rgba(0,0,0,0.18);
        color: var(--cream);
        overflow: hidden;
    }

    .home-dashboard::before,
    .home-dashboard::after {
        content: "";
        position: absolute;
        width: 320px;
        height: 320px;
        border-radius: 50%;
        filter: blur(90px);
        z-index: 0;
        pointer-events: none;
    }
    .home-dashboard::before { background: var(--wine); opacity: 0.25; top: -120px; left: -100px; }
    .home-dashboard::after  { background: var(--gold);  opacity: 0.14; bottom: -140px; right: -110px; }

    .home-dashboard .topline,
    .home-dashboard .info-grid,
    .home-dashboard .action-row {
        position: relative;
        z-index: 1;
    }

    .home-dashboard .topline {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 28px;
    }

    .home-dashboard .avatar {
        width: 58px;
        height: 58px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Fraunces', serif;
        font-weight: 700;
        font-size: 1.4rem;
        color: var(--wine);
        background: linear-gradient(150deg, var(--gold-soft), var(--gold));
        flex-shrink: 0;
    }

    .home-dashboard .greet-eyebrow {
        color: var(--muted);
        font-size: 0.82rem;
        margin: 0 0 4px;
    }

    .home-dashboard .greet-name {
        font-family: 'Fraunces', serif;
        font-style: italic;
        font-weight: 700;
        font-size: 1.7rem;
        margin: 0;
        color: var(--cream);
    }

    .home-dashboard .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 6px;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 0.76rem;
        font-weight: 600;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        background: rgba(200,134,42,0.16);
        color: var(--gold-soft);
        border: 1px solid rgba(200,134,42,0.3);
    }

    .home-dashboard .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
        margin-bottom: 32px;
    }

    .home-dashboard .info-tile {
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 14px;
        padding: 16px 18px;
    }

    .home-dashboard .info-label {
        display: flex;
        align-items: center;
        gap: 6px;
        color: var(--muted);
        font-size: 0.76rem;
        margin-bottom: 6px;
    }

    .home-dashboard .info-value {
        font-size: 0.98rem;
        font-weight: 600;
        color: var(--cream);
        word-break: break-word;
    }

    .home-dashboard .action-row {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .home-dashboard .btn-action {
        flex: 1;
        min-width: 160px;
        text-align: center;
        text-decoration: none;
        border-radius: 12px;
        padding: 13px 18px;
        font-weight: 700;
        font-size: 0.92rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .home-dashboard .btn-primary-action {
        color: var(--panel-2);
        background: linear-gradient(120deg, var(--gold), var(--gold-soft));
    }
    .home-dashboard .btn-primary-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 24px rgba(200,134,42,0.25);
    }

    .home-dashboard .btn-logout {
        color: var(--cream);
        background: transparent;
        border: 1px solid rgba(255,255,255,0.16);
    }
    .home-dashboard .btn-logout:hover {
        border-color: var(--gold-soft);
        color: var(--gold-soft);
        transform: translateY(-2px);
    }

    @media (max-width: 560px) {
        .home-dashboard .info-grid { grid-template-columns: 1fr; }
    }

    @media (prefers-reduced-motion: reduce) {
        .home-dashboard *, .home-dashboard *::before, .home-dashboard *::after {
            animation: none !important; transition: none !important;
        }
    }
</style>

<div class="home-dashboard">
    <div class="topline">
        <div class="avatar"><?= htmlspecialchars($initial) ?></div>
        <div>
            <p class="greet-eyebrow">Selamat datang kembali</p>
            <h1 class="greet-name">Halo, <?= htmlspecialchars($nama) ?></h1>
            <span class="role-badge"><i class="bi bi-shield-check"></i> <?= htmlspecialchars($role) ?></span>
        </div>
    </div>

    <div class="info-grid">
        <div class="info-tile">
            <div class="info-label"><i class="bi bi-envelope"></i> Email</div>
            <div class="info-value"><?= htmlspecialchars($email) ?></div>
        </div>
        <div class="info-tile">
            <div class="info-label"><i class="bi bi-person-badge"></i> Peran</div>
            <div class="info-value"><?= htmlspecialchars($role) ?></div>
        </div>
    </div>

    <div class="action-row">
        <a href="dashboard.php" class="btn-action btn-primary-action">
            <i class="bi bi-grid-1x2"></i> Buka Dashboard
        </a>
        <a href="logout.php" class="btn-action btn-logout">
            <i class="bi bi-box-arrow-right"></i> Keluar
        </a>
    </div>
</div>