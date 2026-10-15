<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
$pageTitle       = 'Plantéis';
$pageDescription = 'Jogadores e equipas tecnicas do GD Vialonga — Seniores, Juniores e restantes escaloes.';

$playersData   = loadData('players.json');
$squads        = $playersData['squads'] ?? [];
$activeSquad   = $_GET['squad'] ?? 'all';
$positionOrder = ['goalkeeper','defender','midfielder','forward','coach','staff'];
require_once dirname(__DIR__) . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container page-hero-inner">
        <nav class="breadcrumb">
            <a href="<?= url() ?>">Início</a>
            <span class="breadcrumb-sep">/</span>
            <span>Plantéis</span>
        </nav>
        <h1>Os nossos plantéis</h1>
        <p>Época <?= htmlspecialchars($squads[0]['competition'] ?? '2026/27') ?> — jogadores e equipas técnicas do GDV</p>
    </div>
</section>

<section class="section section-white" style="padding:1.75rem 0">
    <div class="container">
        <div class="filter-bar">
            <button class="filter-btn <?= $activeSquad==='all'?'active':'' ?>" data-filter="all">Todos os escalões</button>
            <?php foreach ($squads as $squad): ?>
            <button class="filter-btn <?= $activeSquad===$squad['id']?'active':'' ?>" data-filter="<?= $squad['id'] ?>">
                <?= htmlspecialchars($squad['name']) ?>
            </button>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section-gray">
    <div class="container">
        <?php foreach ($squads as $squad):
            $visible = ($activeSquad === 'all' || $activeSquad === $squad['id']);
        ?>
        <div data-squad="<?= $squad['id'] ?>" <?= !$visible ? 'style="display:none"' : '' ?>>
            <div style="background:var(--gray-900);color:#fff;padding:1.5rem 2rem;margin-bottom:1.75rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem">
                <div>
                    <h2 style="font-family:var(--font-display);font-size:1.25rem;font-weight:900"><?= htmlspecialchars($squad['name']) ?></h2>
                    <p style="color:rgba(255,255,255,.5);font-size:.82rem;margin-top:.2rem">
                        <?= htmlspecialchars($squad['competition']) ?> &middot; Mister: <strong style="color:rgba(255,255,255,.8)"><?= htmlspecialchars($squad['coach']) ?></strong>
                    </p>
                </div>
                <span style="font-family:var(--font-display);font-size:.75rem;font-weight:700;color:rgba(255,255,255,.4)">
                    <?= count($squad['players']) ?> jogadores inscritos
                </span>
            </div>

            <?php foreach ($positionOrder as $pos):
                $posPlayers = array_values(array_filter($squad['players'], fn($p) => $p['position']===$pos));
                if (empty($posPlayers)) continue;
            ?>
            <div class="squad-section">
                <h3 class="squad-position-title"><?= positionLabel($pos) ?></h3>
                <div class="players-grid">
                    <?php foreach ($posPlayers as $player):
                        $isStaff      = ($pos==='coach' || $pos==='staff');
                        $playerPhoto  = !empty($player['photo']) ? url($player['photo']) : '';
                        $playerNumber = (int)($player['number'] ?? 0);
                        $playerGoals  = (int)($player['goals'] ?? 0);
                        $playerAssist = (int)($player['assists'] ?? 0);
                        $payload = json_encode([
                            'name'        => $player['name'] ?? '',
                            'number'      => $playerNumber,
                            'position'    => positionLabel($player['position'] ?? ''),
                            'nationality' => nationalityLabel($player['nationality'] ?? ''),
                            'age'         => $player['age'] ?? null,
                            'goals'       => $playerGoals,
                            'assists'     => $playerAssist,
                            'clubs'       => array_values($player['clubs'] ?? []),
                            'bio'         => $player['bio'] ?? '',
                            'photo'       => $playerPhoto,
                            'squad'       => $squad['name'] ?? '',
                        ], JSON_UNESCAPED_UNICODE);
                    ?>
                    <div class="player-card <?= $isStaff ? 'is-coach' : '' ?>"
                         role="button" tabindex="0"
                         aria-label="Ver ficha de <?= htmlspecialchars($player['name']) ?>"
                         data-player="<?= htmlspecialchars($payload, ENT_QUOTES, 'UTF-8') ?>">
                        <div class="player-card-photo">
                            <div class="player-placeholder-icon">
                                <?= $pos==='goalkeeper' ? '🧤' : ($isStaff ? '📋' : '👤') ?>
                            </div>
                            <?php if ($playerPhoto !== ''): ?>
                            <img class="player-photo" src="<?= htmlspecialchars($playerPhoto) ?>"
                                 alt="<?= htmlspecialchars($player['name']) ?>" loading="lazy" data-hide-on-error>
                            <?php endif; ?>
                            <?php if ($playerNumber > 0): ?>
                            <div class="player-number"><?= $playerNumber ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="player-card-info">
                            <div class="player-name"><?= htmlspecialchars($player['name']) ?></div>
                            <div class="player-position"><?= positionLabel($player['position']) ?></div>
                            <div class="player-nationality"><?= nationalityLabel($player['nationality'] ?? '') ?></div>
                            <div class="player-stats">
                                <span title="Golos marcados">⚽ <?= $playerGoals ?></span>
                                <span title="Assistências">🅰️ <?= $playerAssist ?></span>
                            </div>
                            <?php if (!empty($player['age'])): ?>
                            <div class="player-age"><?= (int)$player['age'] ?> anos</div>
                            <?php endif; ?>
                            <span class="player-card-cta">Ver ficha</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <div class="divider"></div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<div class="player-modal" id="player-modal" hidden aria-hidden="true">
    <div class="player-modal-backdrop" data-modal-close></div>
    <div class="player-modal-card" role="dialog" aria-modal="true" aria-labelledby="player-modal-name">
        <button type="button" class="player-modal-close" data-modal-close aria-label="Fechar ficha">&times;</button>
        <div class="player-modal-photo">
            <div class="player-placeholder-icon">👤</div>
            <img id="player-modal-img" src="" alt="" data-hide-on-error>
            <div class="player-modal-number" id="player-modal-number"></div>
        </div>
        <div class="player-modal-body">
            <span class="player-modal-squad" id="player-modal-squad"></span>
            <h3 id="player-modal-name"></h3>
            <p class="player-modal-role" id="player-modal-role"></p>
            <div class="player-modal-stats">
                <div><span class="player-modal-stat-value" id="player-modal-goals">0</span><span class="player-modal-stat-label">Golos</span></div>
                <div><span class="player-modal-stat-value" id="player-modal-assists">0</span><span class="player-modal-stat-label">Assistências</span></div>
                <div><span class="player-modal-stat-value" id="player-modal-age">—</span><span class="player-modal-stat-label">Idade</span></div>
            </div>
            <dl class="player-modal-meta">
                <dt>Nacionalidade</dt>
                <dd id="player-modal-nationality">—</dd>
                <dt>Percurso</dt>
                <dd><ul id="player-modal-clubs"></ul></dd>
            </dl>
            <p class="player-modal-bio" id="player-modal-bio"></p>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
