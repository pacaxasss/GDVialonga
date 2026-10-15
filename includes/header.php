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
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>

<header class="site-header" id="site-header">
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
    <div class="container header-inner">
        <a href="<?= url() ?>" class="logo-link">
              <img src="<?= url('assets/images/logo.png') ?>" alt="GD Vialonga" class="logo-img" data-logo-fallback>
            <div class="logo-text">
                <span class="logo-name">GD Vialonga</span>
                <span class="logo-sub">Desde 1961 &mdash; Vila Franca de Xira</span>
            </div>
        </a>

        <nav class="main-nav" aria-label="Navegação principal">
            <ul class="nav-list">
                <li><a href="<?= url() ?>" class="nav-link <?= in_array($currentPage, ['index',''])?'active':'' ?>">Início</a></li>
                <li><a href="<?= url('o-clube') ?>" class="nav-link <?= $currentPage==='o-clube'?'active':'' ?>">O Clube</a></li>
                <li><a href="<?= url('noticias') ?>" class="nav-link <?= in_array($currentPage, ['noticias','noticia'])?'active':'' ?>">Notícias</a></li>
                <li class="nav-item-dropdown">
                    <a href="<?= url('futebol') ?>" class="nav-link <?= in_array($currentPage, ['futebol','plantel','formacao'])?'active':'' ?>">
                        Futebol
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
                    </a>
                    <div class="nav-dropdown">
                        <a href="<?= url('futebol') ?>" class="nav-dropdown-link">Resultados &amp; Calendário</a>
                        <a href="<?= url('futebol#classificacao') ?>" class="nav-dropdown-link">Classificação</a>
                        <a href="<?= url('plantel') ?>" class="nav-dropdown-link">Plantéis &amp; Atletas</a>
                        <a href="<?= url('formacao') ?>" class="nav-dropdown-link">Formação &amp; Inscrições</a>
                    </div>
                </li>
                <li><a href="<?= url('patrocinadores') ?>" class="nav-link <?= $currentPage==='patrocinadores'?'active':'' ?>">Patrocinadores</a></li>
                <li><a href="<?= url('loja') ?>" class="nav-link <?= $currentPage==='loja'?'active':'' ?>">Loja</a></li>
                <li><a href="<?= url('contactos') ?>" class="nav-link <?= $currentPage==='contactos'?'active':'' ?>">Contactos</a></li>
            </ul>
        </nav>

        <div class="header-actions">
            <a href="<?= url('formacao#inscricao') ?>" class="btn btn-cta">Inscrever na Formação</a>
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
                <li><a href="<?= url() ?>" class="mobile-nav-link">Início</a></li>
                <li><a href="<?= url('o-clube') ?>" class="mobile-nav-link">O Clube</a></li>
                <li><a href="<?= url('noticias') ?>" class="mobile-nav-link">Notícias</a></li>
                <li style="padding:.5rem 1rem .2rem;font-size:.7rem;font-weight:800;letter-spacing:.08em;color:rgba(255,255,255,.4);text-transform:uppercase">Futebol</li>
                <li><a href="<?= url('futebol') ?>" class="mobile-nav-link" style="padding-left:1.5rem">&bull; Resultados &amp; Jogos</a></li>
                <li><a href="<?= url('plantel') ?>" class="mobile-nav-link" style="padding-left:1.5rem">&bull; Plantéis (Todos os Escalões)</a></li>
                <li><a href="<?= url('formacao') ?>" class="mobile-nav-link" style="padding-left:1.5rem">&bull; Formação &amp; Inscrições</a></li>
                <li><a href="<?= url('patrocinadores') ?>" class="mobile-nav-link">Patrocinadores</a></li>
                <li><a href="<?= url('loja') ?>" class="mobile-nav-link">Loja</a></li>
                <li><a href="<?= url('contactos') ?>" class="mobile-nav-link">Contactos</a></li>
            </ul>
            <div style="margin-top:1.25rem">
                <a href="<?= url('formacao#inscricao') ?>" class="btn btn-cta btn-full">Inscrever na Formação</a>
            </div>
        </nav>
    </div>
</header>
