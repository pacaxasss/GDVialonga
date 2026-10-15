<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
$pageTitle       = 'Loja Oficial — Merchandising & Equipamentos';
$pageDescription = 'Loja oficial do Grupo Desportivo de Vialonga em parceria com a SAK Project. Equipamentos de jogo, treino e saída da época 2026/27.';

$productsFile = dirname(__DIR__) . '/data/products.json';
$allProducts  = file_exists($productsFile) ? json_decode(file_get_contents($productsFile), true) : [];

$categories = [
    'all'         => 'Todos os Artigos',
    'camisola'    => 'Camisolas',
    'kit'         => 'Kits Completos',
    'fato-treino' => 'Fatos de Treino',
    'agasalho'    => 'Casacos & Kispos',
    'polo'        => 'Polos & T-shirts',
    'acessorio'   => 'Mochilas & Acessórios',
    'meias'       => 'Meias de Jogo',
];

$activeCat = $_GET['cat'] ?? 'all';
if (!array_key_exists($activeCat, $categories)) {
    $activeCat = 'all';
}

$filteredProducts = $allProducts;
if ($activeCat !== 'all') {
    $filteredProducts = array_values(array_filter($allProducts, function($p) use ($activeCat) {
        if ($activeCat === 'polo') {
            return in_array($p['type'], ['polo', 'equipamento']) && stripos($p['title'], 't-shirt') !== false || $p['type'] === 'polo';
        }
        if ($activeCat === 'agasalho') {
            return in_array($p['type'], ['agasalho']) || stripos($p['title'], 'kispo') !== false || stripos($p['title'], 'corta vento') !== false;
        }
        return ($p['type'] ?? '') === $activeCat;
    }));
}

require_once dirname(__DIR__) . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container page-hero-inner">
        <nav class="breadcrumb">
            <a href="index.php">Início</a>
            <span class="breadcrumb-sep">/</span>
            <span>Loja Oficial</span>
        </nav>
        <h1>Loja Oficial GDV</h1>
        <p>Veste as cores de Vialonga. Coleção oficial de jogo, treino e saída da época 2026/27 produzida pela SAK Project.</p>
    </div>
</section>

<!-- Partner Notice Banner -->
<section style="background:var(--gray-900);border-bottom:1px solid rgba(255,255,255,.08);padding:1.1rem 0;color:#fff">
    <div class="container" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem">
        <div style="display:flex;align-items:center;gap:.85rem">
            <span style="background:var(--gdv-red);color:#fff;font-family:var(--font-display);font-size:.65rem;font-weight:800;letter-spacing:.08em;padding:.25rem .55rem;border-radius:4px;text-transform:uppercase">Parceiro Oficial</span>
            <span style="font-size:.85rem;color:rgba(255,255,255,.8)">Produção &amp; Encomendas geridas através da plataforma oficial <strong>SAK Project</strong></span>
        </div>
        <a href="https://sakproject.com/product-tag/gdvialonga/" target="_blank" rel="noopener noreferrer" class="btn btn-outline-white" style="font-size:.78rem;padding:.4rem .9rem;border-color:rgba(255,255,255,.3)">
            Ver Catálogo na SAK Project &nearr;
        </a>
    </div>
</section>

<!-- Filter Navigation -->
<section class="section section-white" style="padding:1.5rem 0">
    <div class="container">
        <div class="filter-bar" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center">
            <?php foreach ($categories as $catKey => $catLabel): ?>
            <a href="loja.php?cat=<?= urlencode($catKey) ?>"
               class="filter-btn <?= $activeCat===$catKey ? 'active' : '' ?>">
                <?= htmlspecialchars($catLabel) ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Product Grid Section -->
<section class="section section-gray">
    <div class="container">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;flex-wrap:wrap;gap:1rem">
            <div>
                <h2 style="font-family:var(--font-display);font-weight:900;font-size:1.4rem;color:var(--gray-900)">
                    <?= htmlspecialchars($categories[$activeCat]) ?>
                </h2>
                <p style="font-size:.85rem;color:var(--gray-500);margin-top:.2rem">
                    <?= count($filteredProducts) ?> artigo(s) disponível(eis) para encomenda online
                </p>
            </div>
            <div style="font-size:.8rem;color:var(--gray-500)">
                Entregas ao domicílio ou levantamento de encomendas
            </div>
        </div>

        <?php if (empty($filteredProducts)): ?>
        <div style="background:#fff;padding:3rem 2rem;border-radius:var(--radius-md);text-align:center;color:var(--gray-500)">
            <p>Não foram encontrados produtos nesta categoria.</p>
            <a href="loja.php" class="btn btn-primary" style="margin-top:1rem">Ver Todos os Produtos</a>
        </div>
        <?php else: ?>
        <div class="store-grid" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(260px, 1fr));gap:1.75rem">
            <?php foreach ($filteredProducts as $item): 
                $imgSrc = !empty($item['local_image']) ? $item['local_image'] : $item['image'];
            ?>
            <div class="store-card" style="background:#fff;border-radius:var(--radius-md);border:1px solid var(--gray-200);overflow:hidden;display:flex;flex-direction:column;transition:var(--transition);box-shadow:var(--shadow-sm)">
                <div style="position:relative;background:#f8fafc;padding:1.5rem;display:flex;align-items:center;justify-content:center;aspect-ratio:1/1;overflow:hidden">
                    <img src="<?= htmlspecialchars($imgSrc) ?>" 
                         alt="<?= htmlspecialchars($item['title']) ?>"
                         loading="lazy"
                         style="max-width:100%;max-height:100%;object-fit:contain;transition:transform .3s ease">
                    <?php if (stripos($item['title'], '75 Anos') !== false): ?>
                    <span style="position:absolute;top:.75rem;left:.75rem;background:#ffd166;color:#1e293b;font-family:var(--font-display);font-size:.62rem;font-weight:800;padding:.2rem .45rem;border-radius:3px;text-transform:uppercase">
                        Edição Especial
                    </span>
                    <?php elseif (stripos($item['title'], '26/27') !== false): ?>
                    <span style="position:absolute;top:.75rem;left:.75rem;background:var(--gdv-blue);color:#fff;font-family:var(--font-display);font-size:.62rem;font-weight:800;padding:.2rem .45rem;border-radius:3px;text-transform:uppercase">
                        Época 2026/27
                    </span>
                    <?php endif; ?>
                </div>
                <div style="padding:1.25rem;display:flex;flex-direction:column;flex-grow:1;justify-content:space-between">
                    <div>
                        <span style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;color:var(--gray-400);font-weight:700">SAK x GD Vialonga</span>
                        <h3 style="font-family:var(--font-display);font-size:.95rem;font-weight:700;color:var(--gray-900);line-height:1.35;margin-top:.25rem;margin-bottom:.5rem">
                            <?= htmlspecialchars($item['title']) ?>
                        </h3>
                    </div>
                    <div style="margin-top:1rem;display:flex;align-items:center;justify-content:space-between;padding-top:.85rem;border-top:1px solid var(--gray-100)">
                        <span style="font-family:var(--font-display);font-size:1.15rem;font-weight:900;color:var(--gdv-blue-darker)">
                            <?= htmlspecialchars($item['price']) ?>
                        </span>
                        <a href="<?= htmlspecialchars($item['url']) ?>" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="btn btn-primary" 
                           style="font-size:.75rem;padding:.45rem .85rem;display:inline-flex;align-items:center;gap:.35rem">
                            <span>Comprar</span>
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Information / FAQ Strip -->
        <div style="margin-top:3.5rem;background:#fff;border-radius:var(--radius-lg);padding:2.5rem;border:1px solid var(--gray-200);display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:2.5rem">
            <div>
                <div style="font-size:1.8rem;margin-bottom:.6rem">👕</div>
                <h4 style="font-family:var(--font-display);font-weight:800;font-size:1rem;color:var(--gray-900);margin-bottom:.35rem">Personalização de Camisolas</h4>
                <p style="font-size:.82rem;color:var(--gray-500);line-height:1.6">Na página de encomenda da SAK Project podes escolher o tamanho e personalizar o equipamento oficial com número e nome à escolha.</p>
            </div>
            <div>
                <div style="font-size:1.8rem;margin-bottom:.6rem">🚚</div>
                <h4 style="font-family:var(--font-display);font-weight:800;font-size:1rem;color:var(--gray-900);margin-bottom:.35rem">Envio &amp; Levantamento</h4>
                <p style="font-size:.82rem;color:var(--gray-500);line-height:1.6">As encomendas são enviadas por transportadora diretamente para a tua morada, com tracking e suporte da equipa da SAK Project.</p>
            </div>
            <div>
                <div style="font-size:1.8rem;margin-bottom:.6rem">🛡️</div>
                <h4 style="font-family:var(--font-display);font-weight:800;font-size:1rem;color:var(--gray-900);margin-bottom:.35rem">Material Oficial Licenciado</h4>
                <p style="font-size:.82rem;color:var(--gray-500);line-height:1.6">Todos os artigos são desenvolvidos com tecnologia têxtil de alto rendimento, respirabilidade e resistência homologada pelo clube.</p>
            </div>
        </div>
    </div>
</section>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
