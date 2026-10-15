<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
$slug    = $_GET['slug'] ?? '';
$post    = getNewsBySlug($slug);
$allNews = getNews('', 10);

if (!$post) {
    header('HTTP/1.0 404 Not Found');
    $pageTitle = 'Notícia não encontrada';
    require_once dirname(__DIR__) . '/includes/header.php';
    echo '<div class="container section" style="min-height:50vh"><h2 style="margin-bottom:1rem;font-family:var(--font-display)">Notícia não encontrada.</h2><a href="' . url('noticias') . '" class="btn btn-primary">Voltar às notícias</a></div>';
    require_once dirname(__DIR__) . '/includes/footer.php';
    exit;
}

$pageTitle       = $post['title'];
$pageDescription = truncate($post['summary'], 155);
$related         = array_slice(array_values(array_filter($allNews, fn($n) => $n['slug'] !== $slug && $n['category'] === $post['category'])), 0, 3);
require_once dirname(__DIR__) . '/includes/header.php';
?>

<section class="page-hero" style="padding:2.25rem 0">
    <div class="container page-hero-inner">
        <nav class="breadcrumb">
            <a href="<?= url() ?>">Início</a>
            <span class="breadcrumb-sep">/</span>
            <a href="<?= url('noticias') ?>">Notícias</a>
            <span class="breadcrumb-sep">/</span>
            <span class="badge <?= categoryClass($post['category']) ?>"><?= categoryLabel($post['category']) ?></span>
        </nav>
        <h1 style="font-size:clamp(1.4rem,3.5vw,2.1rem)"><?= htmlspecialchars($post['title']) ?></h1>
        <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;margin-top:.65rem;color:rgba(255,255,255,.45);font-size:.82rem">
            <span><?= formatDateLong($post['published_at']) ?></span>
            <?php if ($post['author']): ?><span>&middot; <?= htmlspecialchars($post['author']) ?></span><?php endif; ?>
        </div>
    </div>
</section>

<section class="section section-white">
    <div class="container">
        <div style="display:grid;grid-template-columns:1fr 280px;gap:3rem;align-items:start" class="article-layout">

            <main>
                <div class="article-cover">
                    <img src="<?= url(htmlspecialchars(safeNewsImagePath($post['cover_image']), ENT_QUOTES, 'UTF-8')) ?>" alt="<?= htmlspecialchars($post['title']) ?>">
                </div>

                <p style="font-size:1.05rem;font-weight:500;color:var(--gray-700);line-height:1.7;margin-bottom:1.75rem;padding-left:1rem;border-left:3px solid var(--gdv-blue)">
                    <?= htmlspecialchars($post['summary']) ?>
                </p>

                <div class="article-body">
                    <?= sanitizeNewsHtml($post['content']) ?>
                </div>

                <?php if (!empty($post['tags'])): ?>
                <div style="display:flex;gap:.45rem;flex-wrap:wrap;margin-top:1.5rem">
                    <?php foreach ($post['tags'] as $tag): ?>
                    <span style="background:var(--gray-100);color:var(--gray-600);padding:.25rem .7rem;font-size:.72rem;font-family:var(--font-display);font-weight:700">#<?= htmlspecialchars($tag) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div class="share-bar">
                    <span class="share-label">Partilhar:</span>
                    <a href="#" class="share-btn fb">Facebook</a>
                    <a href="#" class="share-btn wa">WhatsApp</a>
                    <a href="#" class="share-btn tw">X</a>
                </div>
            </main>

            <aside style="position:sticky;top:120px">
                <a href="<?= url('noticias') ?>" style="display:inline-flex;align-items:center;gap:.4rem;color:var(--gdv-blue);font-weight:700;font-size:.82rem;font-family:var(--font-display);margin-bottom:1.5rem">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    Voltar às notícias
                </a>

                <?php if (!empty($related)): ?>
                <div style="border:1px solid var(--gray-200);padding:1.25rem">
                    <h3 style="font-family:var(--font-display);font-size:.75rem;font-weight:800;color:var(--gray-900);border-bottom:1px solid var(--gray-100);padding-bottom:.5rem;margin-bottom:1rem">
                        Também pode interessar
                    </h3>
                    <div style="display:flex;flex-direction:column;gap:.85rem">
                        <?php foreach ($related as $rel): ?>
                        <a href="<?= url('noticia?slug=' . urlencode($rel['slug'])) ?>" style="display:flex;gap:.75rem;color:inherit">
                            <img src="<?= url(htmlspecialchars(safeNewsImagePath($rel['cover_image']), ENT_QUOTES, 'UTF-8')) ?>" alt="" style="width:60px;height:48px;object-fit:cover;flex-shrink:0">
                            <div>
                                <div style="font-size:.68rem;color:var(--gray-400);margin-bottom:.15rem"><?= formatDate($rel['published_at']) ?></div>
                                <div style="font-family:var(--font-display);font-size:.78rem;font-weight:700;color:var(--gray-800);line-height:1.3"><?= htmlspecialchars($rel['title']) ?></div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div style="background:var(--gray-900);padding:1.5rem;margin-top:1.25rem">
                    <h3 style="font-family:var(--font-display);font-size:.8rem;font-weight:800;color:#fff;margin-bottom:.85rem">Acesso rápido</h3>
                    <div style="display:flex;flex-direction:column;gap:.5rem">
                        <a href="<?= url('futebol') ?>" style="font-size:.82rem;color:rgba(255,255,255,.55);transition:color .2s">Resultados & Classificação</a>
                        <a href="<?= url('plantel') ?>" style="font-size:.82rem;color:rgba(255,255,255,.55)">Plantéis</a>
                        <a href="<?= url('formacao#inscricao') ?>" style="font-size:.82rem;color:rgba(255,255,255,.55)">Inscrever na Formação</a>
                        <a href="<?= url('contactos') ?>" style="font-size:.82rem;color:rgba(255,255,255,.55)">Contactos</a>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>

<style>
@media (max-width: 900px) { .article-layout { grid-template-columns: 1fr !important; } }
</style>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
