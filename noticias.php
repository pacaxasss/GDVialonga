<?php
require_once 'config.php';
require_once 'includes/functions.php';
$pageTitle       = 'Notícias';
$pageDescription = 'Resultados, historias e comunicados do GD Vialonga — o clube de Vialonga, Vila Franca de Xira.';

$activeFilter = $_GET['cat'] ?? 'all';
$allNews      = getNews($activeFilter !== 'all' ? $activeFilter : '', 100);
$featured     = $allNews[0] ?? null;
$rest         = array_slice($allNews, 1);
require_once 'includes/header.php';
?>

<section class="page-hero">
    <div class="container page-hero-inner">
        <nav class="breadcrumb">
            <a href="index.php">Início</a>
            <span class="breadcrumb-sep">/</span>
            <span>Notícias</span>
        </nav>
        <h1>Notícias do GDV</h1>
        <p>Do campo, da formação, do clube e da comunidade de Vialonga.</p>
    </div>
</section>

<section class="section section-white">
    <div class="container">
        <div class="filter-bar">
            <button class="filter-btn <?= $activeFilter==='all'?'active':'' ?>" data-filter="all">Todas</button>
            <button class="filter-btn <?= $activeFilter==='seniores'?'active':'' ?>" data-filter="seniores">Seniores</button>
            <button class="filter-btn <?= $activeFilter==='formacao'?'active':'' ?>" data-filter="formacao">Formação</button>
            <button class="filter-btn <?= $activeFilter==='clube'?'active':'' ?>" data-filter="clube">Clube</button>
            <button class="filter-btn <?= $activeFilter==='regiao'?'active':'' ?>" data-filter="regiao">Vialonga & Região</button>
            <button class="filter-btn <?= $activeFilter==='comunicado'?'active':'' ?>" data-filter="comunicado">Comunicados</button>
        </div>

        <!-- Featured -->
        <?php if ($featured && ($activeFilter==='all' || $featured['category']===$activeFilter)): ?>
        <a href="noticia.php?slug=<?= urlencode($featured['slug']) ?>" class="news-featured" data-category="<?= htmlspecialchars($featured['category'], ENT_QUOTES, 'UTF-8') ?>" style="display:grid;margin-bottom:2.5rem">
            <div class="news-featured-img">
                <img src="<?= htmlspecialchars(safeNewsImagePath($featured['cover_image']), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($featured['title']) ?>" loading="eager">
            </div>
            <div class="news-featured-body">
                <div class="news-featured-tag">
                    <span class="badge <?= categoryClass($featured['category']) ?>"><?= categoryLabel($featured['category']) ?></span>
                </div>
                <div class="news-featured-meta"><?= formatDateLong($featured['published_at']) ?> &middot; <?= htmlspecialchars($featured['author'] ?? '') ?></div>
                <h2 class="news-featured-title"><?= htmlspecialchars($featured['title']) ?></h2>
                <p class="news-featured-summary"><?= htmlspecialchars(truncate($featured['summary'], 160)) ?></p>
                <span class="btn-ghost" style="color:rgba(255,255,255,.65)">
                    Ler artigo
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </span>
            </div>
        </a>
        <?php endif; ?>

        <!-- Rest -->
        <div class="news-secondary" data-filterable>
            <?php foreach ($rest as $post): ?>
            <article class="news-card" data-category="<?= htmlspecialchars($post['category'], ENT_QUOTES, 'UTF-8') ?>"
                <?= ($activeFilter!=='all' && $post['category']!==$activeFilter) ? 'style="display:none"' : '' ?>>
                <div class="news-card-img">
                    <img src="<?= htmlspecialchars(safeNewsImagePath($post['cover_image']), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($post['title']) ?>" loading="lazy">
                    <div class="news-card-cat">
                        <span class="badge <?= categoryClass($post['category']) ?>"><?= categoryLabel($post['category']) ?></span>
                    </div>
                </div>
                <div class="news-card-body">
                    <div class="news-card-date"><?= formatDateLong($post['published_at']) ?><?= $post['author'] ? ' &middot; ' . htmlspecialchars($post['author']) : '' ?></div>
                    <h2 class="news-card-title"><?= htmlspecialchars($post['title']) ?></h2>
                    <p class="news-card-summary"><?= htmlspecialchars(truncate($post['summary'], 110)) ?></p>
                    <div class="news-card-footer">
                        <a href="noticia.php?slug=<?= urlencode($post['slug']) ?>" class="news-read-more">
                            Ler mais
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
