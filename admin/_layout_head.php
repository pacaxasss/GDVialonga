<?php
/**
 * Admin layout include.
 * Requer: $pageTitle, $activeNav
 */
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($pageTitle ?? 'Admin') ?> — GDV Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= $adminBase ?? '' ?>assets/admin.css">
</head>
<body>
<div class="admin-wrap">

  <!-- Sidebar -->
  <nav class="sidebar">
    <div class="sidebar-logo">
      <div class="sidebar-mark">GDV</div>
      <div class="sidebar-name">Admin<small>GD Vialonga</small></div>
    </div>
    <div class="sidebar-nav">
      <div class="nav-section">Geral</div>
      <a href="<?= $adminBase ?? '' ?>index.php"
         class="nav-link <?= ($activeNav??'')==='dashboard'?'active':'' ?>">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        Dashboard
      </a>

      <div class="nav-section" style="margin-top:.5rem">Conteúdo</div>
      <a href="<?= $adminBase ?? '' ?>noticias.php"
         class="nav-link <?= ($activeNav??'')==='noticias'?'active':'' ?>">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h7"/></svg>
        Notícias
      </a>

      <div class="nav-section" style="margin-top:.5rem">Futebol</div>
      <a href="<?= $adminBase ?? '' ?>resultados.php"
         class="nav-link <?= ($activeNav??'')==='resultados'?'active':'' ?>">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
        Jogos & Resultados
      </a>
      <a href="<?= $adminBase ?? '' ?>classificacao.php"
         class="nav-link <?= ($activeNav??'')==='classificacao'?'active':'' ?>">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
        Classificação
      </a>
      <a href="<?= $adminBase ?? '' ?>seguranca.php"
         class="nav-link <?= ($activeNav??'')==='seguranca'?'active':'' ?>">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        Segurança
      </a>

      <div class="nav-section" style="margin-top:.5rem">Site</div>
      <a href="../index.php" target="_blank" rel="noopener noreferrer" class="nav-link">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        Ver Site
      </a>
    </div>
    <div class="sidebar-foot">
      Sessão: <strong><?= htmlspecialchars(adminUser()['username'] ?? '') ?></strong><br>
      <form method="POST" action="<?= $adminBase ?? '' ?>logout.php">
        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit" class="sidebar-logout">Terminar sessão</button>
      </form>
    </div>
  </nav>

  <!-- Main -->
  <main class="admin-main">
    <header class="topbar">
      <span class="topbar-title"><?= htmlspecialchars($pageTitle ?? '') ?></span>
      <div class="topbar-user">
        <span><?= htmlspecialchars(adminUser()['username'] ?? '') ?></span>
        <form method="POST" action="<?= $adminBase ?? '' ?>logout.php">
          <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
          <button type="submit" class="topbar-logout">Sair</button>
        </form>
      </div>
    </header>
    <div class="page-body">
