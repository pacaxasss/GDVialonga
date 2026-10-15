<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$pageTitle       = 'Patrocinadores & Parceiros';
$pageDescription = 'Conheça as empresas e instituições que apoiam o Grupo Desportivo de Vialonga e o desenvolvimento desportivo da nossa comunidade.';

$sponsors = getSponsors();

// Patrocinadores Oficiais
$tiers = [
    'principal' => [
        'title' => 'Patrocinadores Oficiais',
        'badge' => 'Patrocinador Oficial',
        'desc'  => 'As empresas e parceiros oficiais que apoiam o Grupo Desportivo de Vialonga na época desportiva 2026/2027.',
        'items' => []
    ],
];

foreach ($sponsors as $s) {
    $tiers['principal']['items'][] = $s;
}

require_once dirname(__DIR__) . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container page-hero-inner">
        <nav class="breadcrumb">
            <a href="<?= url() ?>">Início</a>
            <span class="breadcrumb-sep">/</span>
            <span>Patrocinadores</span>
        </nav>
        <h1>Patrocinadores &amp; Parceiros</h1>
        <p>As marcas, empresas e instituições que caminham lado a lado com o GD Vialonga e tornam o nosso desporto possível.</p>
    </div>
</section>

<!-- Callout do clube -->
<section style="background:var(--gray-900);color:#fff;padding:2.5rem 0;border-bottom:1px solid rgba(255,255,255,.08)">
    <div class="container" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:2rem">
        <div style="max-width:700px">
            <span style="background:var(--gdv-red);color:#fff;font-family:var(--font-display);font-size:.68rem;font-weight:800;letter-spacing:.08em;padding:.3rem .6rem;border-radius:4px;text-transform:uppercase;display:inline-block;margin-bottom:.6rem">Crescer Juntos</span>
            <h2 style="font-family:var(--font-display);font-size:clamp(1.2rem,2.5vw,1.6rem);font-weight:900;line-height:1.2;color:#fff;margin-bottom:.5rem">
                O futebol de Vialonga faz-se com o apoio das nossas pessoas e empresas.
            </h2>
            <p style="font-size:.88rem;color:rgba(255,255,255,.65);line-height:1.6">
                Mais de 200 atletas, 5 escalões competitivos e centenas de famílias aos fins de semana. Apoiar o GDV é investir diretamente na juventude, no desporto e no futuro da nossa vila.
            </p>
        </div>
        <div>
            <a href="<?= url('contactos') ?>" class="btn btn-primary" style="white-space:nowrap">
                Seja Patrocinador do GDV &rarr;
            </a>
        </div>
    </div>
</section>

<!-- Lista de Patrocinadores por Categoria -->
<section class="section section-gray">
    <div class="container">

        <?php foreach ($tiers as $tierKey => $tier): 
            if (empty($tier['items'])) continue;
        ?>
        <div style="margin-bottom:3.5rem">
            <div style="margin-bottom:1.75rem;border-bottom:2px solid var(--gray-200);padding-bottom:1rem">
                <div style="display:flex;align-items:center;gap:.75rem">
                    <h2 style="font-family:var(--font-display);font-size:1.45rem;font-weight:900;color:var(--gray-900)">
                        <?= htmlspecialchars($tier['title']) ?>
                    </h2>
                    <span style="background:var(--gdv-blue-bg);color:var(--gdv-blue-dark);font-family:var(--font-display);font-size:.65rem;font-weight:800;padding:.2rem .55rem;border-radius:20px;text-transform:uppercase">
                        <?= count($tier['items']) ?> parceiros
                    </span>
                </div>
                <p style="font-size:.85rem;color:var(--gray-500);margin-top:.3rem;max-width:700px">
                    <?= htmlspecialchars($tier['desc']) ?>
                </p>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(min(100%, 300px), 1fr));gap:1.75rem">
                <?php foreach ($tier['items'] as $item): ?>
                <div class="sponsor-card" style="background:#fff;border-radius:var(--radius-lg);border:1px solid var(--gray-200);display:flex;flex-direction:column;justify-content:space-between;box-shadow:var(--shadow-sm);overflow:hidden;transition:var(--transition)">
                    <div>
                        <!-- Header do Card com Badge e Logótipo -->
                        <div style="padding:1.5rem 1.5rem 1.25rem;background:linear-gradient(180deg, var(--gray-50) 0%, #fff 100%);border-bottom:1px solid var(--gray-100)">
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;margin-bottom:1rem;flex-wrap:wrap">
                                <span style="font-size:.65rem;font-family:var(--font-display);font-weight:700;letter-spacing:.05em;padding:.25rem .65rem;border-radius:20px;text-transform:uppercase;background:var(--gdv-blue-bg);color:var(--gdv-blue-dark)">
                                    <?= htmlspecialchars($item['category'] ?: $tier['badge']) ?>
                                </span>
                                <span style="font-size:.62rem;font-family:var(--font-display);font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--gray-400)">
                                    <?= htmlspecialchars($tier['badge']) ?>
                                </span>
                            </div>

                            <div style="height:84px;display:flex;align-items:center;justify-content:center;background:#fff;border:1px solid var(--gray-200);border-radius:var(--radius-md);padding:.75rem 1.25rem;box-shadow:inset 0 1px 2px rgba(0,0,0,.02)">
                                <?php if (!empty($item['logo_image']) && file_exists(dirname(__DIR__) . '/' . $item['logo_image'])): ?>
                                    <img src="<?= url(htmlspecialchars($item['logo_image'])) ?>" alt="Logótipo <?= htmlspecialchars($item['name']) ?>" style="max-height:64px;max-width:100%;width:auto;height:auto;object-fit:contain;display:block">
                                <?php else: ?>
                                    <span style="font-family:var(--font-display);font-weight:900;font-size:1.05rem;color:var(--gdv-blue-dark);letter-spacing:.03em;text-align:center">
                                        <?= htmlspecialchars($item['logo_text'] ?: ($item['short'] ?: $item['name'])) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Conteúdo Informativo -->
                        <div style="padding:1.5rem 1.5rem 0.5rem">
                            <h3 style="font-family:var(--font-display);font-size:1.15rem;font-weight:800;color:var(--gray-900);line-height:1.3;margin-bottom:.4rem;word-break:break-word">
                                <?= htmlspecialchars($item['name']) ?>
                            </h3>

                            <?php if (!empty($item['tagline'])): ?>
                            <p style="font-size:.82rem;font-weight:600;color:var(--gdv-blue);line-height:1.4;margin-bottom:.85rem">
                                &ldquo;<?= htmlspecialchars($item['tagline']) ?>&rdquo;
                            </p>
                            <?php endif; ?>

                            <p style="font-size:.85rem;color:var(--gray-600);line-height:1.65;margin-bottom:1rem">
                                <?= htmlspecialchars($item['description'] ?? 'Parceiro oficial do Grupo Desportivo de Vialonga.') ?>
                            </p>
                        </div>
                    </div>

                    <!-- Rodapé do Card com Localização e Botão de Ação -->
                    <div style="padding:1rem 1.5rem 1.25rem;border-top:1px solid var(--gray-100);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;background:#fff">
                        <?php if (!empty($item['location'])): ?>
                        <span style="font-size:.75rem;color:var(--gray-500);display:inline-flex;align-items:center;gap:.35rem">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:160px" title="<?= htmlspecialchars($item['location']) ?>"><?= htmlspecialchars($item['location']) ?></span>
                        </span>
                        <?php else: ?>
                        <span></span>
                        <?php endif; ?>

                        <?php if (!empty($item['website'])): ?>
                        <a href="<?= htmlspecialchars($item['website']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline" style="font-size:.72rem;padding:.38rem .8rem;display:inline-flex;align-items:center;gap:.35rem">
                            <span><?= strpos($item['website'], 'sakproject') !== false ? 'Loja de Equipamentos' : 'Visitar Website' ?></span>
                            <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14L21 3"/></svg>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Caixa de Parceria / Novo Patrocinador -->
        <div style="background:#fff;border-radius:var(--radius-lg);padding:3rem 2.5rem;border:2px dashed var(--gray-300);text-align:center;max-width:800px;margin:2rem auto 0">
            <span style="font-family:var(--font-display);font-size:.75rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:var(--gdv-red);display:block;margin-bottom:.5rem">Junte-se à Nossa Família</span>
            <h3 style="font-family:var(--font-display);font-size:1.6rem;font-weight:900;color:var(--gray-900);line-height:1.2;margin-bottom:.75rem">
                Dê Visibilidade à Sua Marca em Vialonga
            </h3>
            <p style="font-size:.9rem;color:var(--gray-600);line-height:1.7;max-width:580px;margin:0 auto 1.75rem">
                Dispomos de vários pacotes de patrocínio à medida: placas de publicidade no estádio, destaque nos equipamentos de jogo, carrinhas do clube e presença digital nos nossos canais e website.
            </p>
            <div style="display:flex;justify-content:center;gap:1rem;flex-wrap:wrap">
                <a href="<?= url('contactos') ?>" class="btn btn-primary btn-lg">Falar com a Direção</a>
                <a href="mailto:geral@gdvialonga.pt?subject=Patrocínio%20GD%20Vialonga" class="btn btn-outline btn-lg">Enviar Email Direto</a>
            </div>
        </div>

    </div>
</section>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
