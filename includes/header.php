<?php
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($pageDescription ?? 'Website oficial do Grupo Desportivo de Vialonga') ?>">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | GD Vialonga' : 'GD Vialonga — Orgulho de Vialonga' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $basePath ?? '' ?>assets/css/style.css">
</head>
<body>

<div class="topbar">
    <div class="container topbar-inner">
        <span>Complexo Desportivo de Vialonga &middot; Rua do Desportivo, Vila Franca de Xira</span>
        <div class="topbar-social">
            <a href="https://facebook.com/gdvialonga" target="_blank" rel="noopener noreferrer" class="topbar-social-link">Facebook</a>
            <a href="https://instagram.com/gdvialonga" target="_blank" rel="noopener noreferrer" class="topbar-social-link">Instagram</a>
            <a href="https://youtube.com/@gdvialonga" target="_blank" rel="noopener noreferrer" class="topbar-social-link">YouTube</a>
        </div>
    </div>
</div>

<header class="site-header" id="site-header">
    <div class="container header-inner">
        <a href="<?= $basePath ?? '' ?>index.php" class="logo-link">
              <img src="<?= $basePath ?? '' ?>assets/images/logo.png" alt="GD Vialonga" class="logo-img" data-logo-fallback>
            <div class="logo-text">
                <span class="logo-name">GD Vialonga</span>
                <span class="logo-sub">Desde 1961 &mdash; Vila Franca de Xira</span>
            </div>
        </a>

        <nav class="main-nav" aria-label="Navegação principal">
            <ul class="nav-list">
                <li><a href="<?= $basePath ?? '' ?>index.php" class="nav-link <?= $currentPage==='index'?'active':'' ?>">Início</a></li>
                <li><a href="<?= $basePath ?? '' ?>o-clube.php" class="nav-link <?= $currentPage==='o-clube'?'active':'' ?>">O Clube</a></li>
                <li><a href="<?= $basePath ?? '' ?>noticias.php" class="nav-link <?= $currentPage==='noticias'?'active':'' ?>">Notícias</a></li>
                <li class="nav-item-dropdown">
                    <a href="<?= $basePath ?? '' ?>futebol.php" class="nav-link <?= $currentPage==='futebol'?'active':'' ?>">
                        Futebol
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
                    </a>
                    <div class="nav-dropdown">
                        <a href="<?= $basePath ?? '' ?>futebol.php#resultados" class="nav-dropdown-link">Resultados</a>
                        <a href="<?= $basePath ?? '' ?>futebol.php#classificacao" class="nav-dropdown-link">Classificação</a>
                        <a href="<?= $basePath ?? '' ?>futebol.php#calendario" class="nav-dropdown-link">Calendário</a>
                    </div>
                </li>
                <li><a href="<?= $basePath ?? '' ?>plantel.php" class="nav-link <?= $currentPage==='plantel'?'active':'' ?>">Plantéis</a></li>
                <li><a href="<?= $basePath ?? '' ?>formacao.php" class="nav-link <?= $currentPage==='formacao'?'active':'' ?>">Formação</a></li>
                <li><a href="<?= $basePath ?? '' ?>contactos.php" class="nav-link <?= $currentPage==='contactos'?'active':'' ?>">Contactos</a></li>
            </ul>
        </nav>

        <div class="header-actions">
            <a href="<?= $basePath ?? '' ?>formacao.php#inscricao" class="btn btn-cta">Inscrever na Formação</a>
            <button class="mobile-toggle" id="mobile-toggle" aria-label="Menu" aria-expanded="false">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
        </div>
    </div>

    <div class="mobile-menu" id="mobile-menu" aria-hidden="true">
        <nav>
            <ul class="mobile-nav-list">
                <li><a href="<?= $basePath ?? '' ?>index.php" class="mobile-nav-link">Início</a></li>
                <li><a href="<?= $basePath ?? '' ?>o-clube.php" class="mobile-nav-link">O Clube</a></li>
                <li><a href="<?= $basePath ?? '' ?>noticias.php" class="mobile-nav-link">Notícias</a></li>
                <li><a href="<?= $basePath ?? '' ?>futebol.php" class="mobile-nav-link">Futebol</a></li>
                <li><a href="<?= $basePath ?? '' ?>plantel.php" class="mobile-nav-link">Plantéis</a></li>
                <li><a href="<?= $basePath ?? '' ?>formacao.php" class="mobile-nav-link">Formação</a></li>
                <li><a href="<?= $basePath ?? '' ?>contactos.php" class="mobile-nav-link">Contactos</a></li>
            </ul>
            <div style="margin-top:1.25rem">
                <a href="<?= $basePath ?? '' ?>formacao.php#inscricao" class="btn btn-cta btn-full">Inscrever na Formação</a>
            </div>
        </nav>
    </div>
</header>
<div class="header-spacer"></div>
