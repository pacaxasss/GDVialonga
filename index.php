<?php
require_once 'config.php';
require_once 'includes/functions.php';
$pageTitle       = 'GD Vialonga — Orgulho de Vialonga desde 1961';
$pageDescription = 'Site oficial do GD Vialonga — resultados, noticias, planteis e inscricoes na formacao.';

$allStandings = getStandings('distrital-honra');
$gdvStanding  = array_values(array_filter($allStandings, static fn(array $row): bool => $row['is_gdv']))[0] ?? null;
$sponsors     = getSponsors();
$latestNews   = getNews('', 4);
$nextMatch    = getNextMatch();
$lastResult   = getLastResult();
$top5         = array_slice($allStandings, 0, 5);
$featured     = $latestNews[0] ?? null;
$secondary    = array_slice($latestNews, 1, 3);
$standings    = ['season' => SEASON, 'updated_at' => date('Y-m-d'), 'standings' => $allStandings];

require_once 'includes/header.php';
?>

<!-- HERO — full-bleed editorial, text anchored bottom -->
<section class="hero" aria-label="Banner principal">
    <div class="hero-bg"></div>
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <div class="container hero-inner">
            <div>
                <p class="hero-eyebrow">Grupo Desportivo de Vialonga &mdash; Fundado em 1961</p>
                <h1 class="hero-title">
                    Somos<br>de Vialonga.<br>
                    <strong>Sempre fomos.</strong>
                </h1>
                <p class="hero-tagline">
                    Desde 1961 que jogamos, crescemos e vivemos nesta terra.<br>O GDV não é só futebol — é o sítio onde Vialonga se encontra.
                </p>
                <div class="hero-actions">
                    <a href="futebol.php" class="btn btn-primary btn-lg">Resultados & Classificação</a>
                    <a href="formacao.php#inscricao" class="btn btn-outline-white btn-lg">Inscrever na Formação</a>
                </div>
            </div>

            <!-- Match panel — right column -->
            <div class="hero-panel">
                <?php if ($lastResult): ?>
                <p class="hero-panel-label">Último Resultado</p>
                <div class="hero-match">
                    <p class="hero-match-comp">
                        <?= htmlspecialchars(FPF_COMPETITION_NAME) ?> &middot; J<?= $lastResult['round'] ?>
                    </p>
                    <div class="hero-teams">
                        <span class="hero-team <?= $lastResult['home_team']['is_gdv'] ? 'is-gdv' : '' ?>">
                            <?= htmlspecialchars($lastResult['home_team']['name']) ?>
                        </span>
                        <span class="hero-score-display">
                            <?= $lastResult['home_score'] ?>&thinsp;–&thinsp;<?= $lastResult['away_score'] ?>
                        </span>
                        <span class="hero-team right <?= $lastResult['away_team']['is_gdv'] ? 'is-gdv' : '' ?>">
                            <?= htmlspecialchars($lastResult['away_team']['name']) ?>
                        </span>
                    </div>
                    <p class="hero-match-meta">
                        <?php
                        $gdvH = $lastResult['home_team']['is_gdv'];
                        $gS   = $gdvH ? $lastResult['home_score'] : $lastResult['away_score'];
                        $oS   = $gdvH ? $lastResult['away_score'] : $lastResult['home_score'];
                        $res  = $gS > $oS ? 'Vitória' : ($gS === $oS ? 'Empate' : 'Derrota');
                        echo "<strong>$res</strong> &middot; " . formatDateLong($lastResult['date']);
                        ?>
                    </p>
                </div>
                <?php endif; ?>

                <?php if ($nextMatch): ?>
                <hr class="hero-sep">
                <p class="hero-panel-label">Próximo Jogo</p>
                <div class="hero-match">
                    <p class="hero-match-comp"><?= htmlspecialchars(FPF_COMPETITION_NAME) ?> &middot; J<?= $nextMatch['round'] ?></p>
                    <div class="hero-teams">
                        <span class="hero-team <?= $nextMatch['home_team']['is_gdv'] ? 'is-gdv' : '' ?>">
                            <?= htmlspecialchars($nextMatch['home_team']['name']) ?>
                        </span>
                        <span class="hero-score-display vs">VS</span>
                        <span class="hero-team right <?= $nextMatch['away_team']['is_gdv'] ? 'is-gdv' : '' ?>">
                            <?= htmlspecialchars($nextMatch['away_team']['name']) ?>
                        </span>
                    </div>
                    <p class="hero-match-meta">
                        <strong><?= formatDateLong($nextMatch['date']) ?><?= !empty($nextMatch['time']) ? ', ' . htmlspecialchars($nextMatch['time']) . 'h' : '' ?></strong><br>
                        <?= htmlspecialchars($nextMatch['venue']) ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <span class="hero-scroll" aria-hidden="true">Scroll</span>
</section>

<!-- NUMBERS — horizontal strip, not 4 identical boxes -->
<div class="club-numbers">
    <div class="club-number-item">
        <div class="club-number-val">1961</div>
        <div class="club-number-label">Ano de fundação</div>
    </div>
    <div class="club-number-item">
        <div class="club-number-val blue">289</div>
        <div class="club-number-label">Atletas inscritos<br>esta época</div>
    </div>
    <div class="club-number-item">
        <div class="club-number-val">7</div>
        <div class="club-number-label">Escalões de<br>formação ativos</div>
    </div>
    <div class="club-number-item">
        <div class="club-number-val red"><?= $gdvStanding ? (int)$gdvStanding['position'] . 'º' : '—' ?></div>
        <div class="club-number-label">Lugar — AF Lisboa<br>SABSEG</div>
    </div>
</div>

<!-- NOTÍCIAS — featured first article, rest in 2-col -->
<section class="section section-white">
    <div class="container">
        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:1.75rem; flex-wrap:wrap; gap:1rem">
            <div>
                <span class="section-label">Do Clube</span>
                <h2 style="font-family:var(--font-display);font-weight:900;font-size:clamp(1.5rem,3.5vw,2.1rem);color:var(--gray-900);margin-top:.3rem">
                    O que se passa no GDV
                </h2>
            </div>
            <a href="noticias.php" class="btn-ghost">
                Ver todas
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
        </div>

        <?php if ($featured): ?>
        <!-- Featured article — full-width, editorial -->
        <a href="noticia.php?slug=<?= urlencode($featured['slug']) ?>" class="news-featured" style="display:grid">
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

        <!-- Secondary articles — 2-col -->
        <?php if (!empty($secondary)): ?>
        <div class="news-secondary">
            <?php foreach ($secondary as $post): ?>
            <article class="news-card">
                <div class="news-card-img">
                    <img src="<?= htmlspecialchars(safeNewsImagePath($post['cover_image']), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($post['title']) ?>" loading="lazy">
                    <div class="news-card-cat">
                        <span class="badge <?= categoryClass($post['category']) ?>"><?= categoryLabel($post['category']) ?></span>
                    </div>
                </div>
                <div class="news-card-body">
                    <div class="news-card-date"><?= formatDateLong($post['published_at']) ?></div>
                    <h3 class="news-card-title"><?= htmlspecialchars($post['title']) ?></h3>
                    <p class="news-card-summary"><?= htmlspecialchars(truncate($post['summary'], 100)) ?></p>
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
        <?php endif; ?>
    </div>
</section>

<!-- ÚLTIMO RESULTADO + PRÓXIMO JOGO — dark strip -->
<div class="match-strip">
    <div class="container">
        <div class="match-strip-inner">
            <?php if ($lastResult): ?>
            <div class="match-strip-section">
                <p class="match-strip-label">Último Resultado &middot; <?= htmlspecialchars(FPF_COMPETITION_NAME) ?></p>
                <div class="match-strip-teams">
                    <span class="match-strip-team <?= $lastResult['home_team']['is_gdv'] ? 'is-gdv' : '' ?>">
                        <?= htmlspecialchars($lastResult['home_team']['name']) ?>
                    </span>
                    <span class="match-strip-score">
                        <?= $lastResult['home_score'] ?>&thinsp;–&thinsp;<?= $lastResult['away_score'] ?>
                    </span>
                    <span class="match-strip-team right <?= $lastResult['away_team']['is_gdv'] ? 'is-gdv' : '' ?>">
                        <?= htmlspecialchars($lastResult['away_team']['name']) ?>
                    </span>
                </div>
                <p class="match-strip-meta">
                    <strong>J<?= $lastResult['round'] ?></strong> &middot;
                    <?= formatDateLong($lastResult['date']) ?>
                    <?php if (!empty($lastResult['scorers'])): ?>
                    &middot; <?= implode(', ', array_map('htmlspecialchars', array_slice($lastResult['scorers'], 0, 2))) ?>
                    <?php endif; ?>
                </p>
            </div>
            <?php endif; ?>

            <div class="match-strip-div"></div>

            <?php if ($nextMatch): ?>
            <div class="match-strip-section">
                <p class="match-strip-label">Próximo Jogo &middot; Em Casa</p>
                <div class="match-strip-teams">
                    <span class="match-strip-team <?= $nextMatch['home_team']['is_gdv'] ? 'is-gdv' : '' ?>">
                        <?= htmlspecialchars($nextMatch['home_team']['name']) ?>
                    </span>
                    <span class="match-strip-score vs">VS</span>
                    <span class="match-strip-team right <?= $nextMatch['away_team']['is_gdv'] ? 'is-gdv' : '' ?>">
                        <?= htmlspecialchars($nextMatch['away_team']['name']) ?>
                    </span>
                </div>
                <p class="match-strip-meta">
                    <strong><?= formatDateLong($nextMatch['date']) ?><?= !empty($nextMatch['time']) ? ', ' . htmlspecialchars($nextMatch['time']) . 'h' : '' ?></strong> &middot;
                    <?= htmlspecialchars($nextMatch['venue']) ?>
                </p>
            </div>
            <?php else: ?>
            <div class="match-strip-section">
                <p class="match-strip-label">Próximo Jogo</p>
                <p style="color:rgba(255,255,255,.4);font-size:.88rem">Calendário a confirmar</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- CLASSIFICAÇÃO — top 5 -->
<section class="section section-gray">
    <div class="container">
        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:1.75rem; flex-wrap:wrap; gap:1rem">
            <div>
                <span class="section-label">Tabela</span>
                <h2 style="font-family:var(--font-display);font-weight:900;font-size:clamp(1.4rem,3vw,2rem);color:var(--gray-900);margin-top:.3rem">
                    <?= htmlspecialchars(FPF_COMPETITION_NAME) ?>
                </h2>
                <p style="font-size:.8rem;color:var(--gray-400);margin-top:.25rem">Época <?= htmlspecialchars($standings['season'] ?? '2026/27') ?> &middot; Atualizado <?= formatDate($standings['updated_at'] ?? date('Y-m-d')) ?></p>
            </div>
            <a href="futebol.php#classificacao" class="btn-ghost">
                Tabela completa
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="standings-wrap">
            <table class="standings-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th class="team-col">Clube</th>
                        <th title="Jogos">J</th>
                        <th title="Vitórias">V</th>
                        <th title="Empates">E</th>
                        <th title="Derrotas">D</th>
                        <th title="Diferença">DG</th>
                        <th class="team-form-col">Forma</th>
                        <th title="Pontos">PTS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($top5 as $row):
                        $pos = $row['position'];
                        $posClass = $pos === 1 ? 'pos-promo' : ($pos === 2 ? 'pos-playoff' : '');
                    ?>
                    <tr class="<?= $row['is_gdv'] ? 'gdv-row' : '' ?>">
                        <td><span class="<?= $posClass ?>"><?= $pos ?></span></td>
                        <td class="team-name-cell">
                            <div class="team-cell">
                                <div class="team-avatar"><?= htmlspecialchars($row['short']) ?></div>
                                <span><?= htmlspecialchars($row['team']) ?></span>
                            </div>
                        </td>
                        <td><?= $row['played'] ?></td>
                        <td><?= $row['won'] ?></td>
                        <td><?= $row['drawn'] ?></td>
                        <td><?= $row['lost'] ?></td>
                        <td><?= ($row['goal_diff'] > 0 ? '+' : '') . $row['goal_diff'] ?></td>
                        <td class="team-form-col">
                            <div class="form-badges">
                                <?php foreach (array_slice($row['form'], -5) as $f): ?>
                                <span class="form-badge <?= formBadgeClass($f) ?>"><?= formBadgeText($f) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </td>
                        <td class="pts-cell"><?= $row['points'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- FORMAÇÃO CTA — editorial, sem gradiente a cobrir tudo -->
<section class="formacao-cta">
    <div class="formacao-cta-inner container" style="max-width:100%;padding:0">
        <div class="formacao-cta-text" style="padding:4rem clamp(1rem,4vw,4rem)">
            <span style="font-family:var(--font-display);font-size:.7rem;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:rgba(255,255,255,.45);display:block;margin-bottom:.85rem">Formação GDV</span>
            <h2 style="font-family:var(--font-display);font-size:clamp(1.75rem,4vw,2.5rem);font-weight:900;color:#fff;line-height:1.1;margin-bottom:.75rem">
                O teu filho<br>tem lugar aqui.
            </h2>
            <p style="color:rgba(255,255,255,.6);font-size:.95rem;line-height:1.75;margin-bottom:1.25rem">
                Não é preciso experiência. Não é preciso equipamento.<br>É preciso aparecer — e nós tratamos do resto.
            </p>
            <div class="escalao-tags">
                <span class="escalao-tag">Sub-7 Petizes</span>
                <span class="escalao-tag">Sub-9 Mini</span>
                <span class="escalao-tag">Sub-11 Traquinas</span>
                <span class="escalao-tag">Sub-13 Infantis</span>
                <span class="escalao-tag">Sub-15 Iniciados</span>
                <span class="escalao-tag">Sub-17 Juvenis</span>
                <span class="escalao-tag">Sub-19 Juniores</span>
            </div>
            <a href="formacao.php#inscricao" class="btn btn-outline-white btn-lg">
                Inscrição gratuita
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="formacao-cta-image">
            <img src="assets/images/player_silhouette.png" alt="Formação GD Vialonga">
        </div>
    </div>
</section>

<!-- PATROCINADORES -->
<section class="sponsors-section section-white">
    <div class="container">
        <p style="font-family:var(--font-display);font-size:.68rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--gray-400);margin-bottom:1.25rem">Com o apoio de</p>
        <div class="sponsors-track-wrap">
            <div class="sponsors-track">
                <?php foreach (array_merge($sponsors, $sponsors) as $s): ?>
                <div class="sponsor-item <?= $s['tier'] === 'principal' ? 'principal' : '' ?>">
                    <?= htmlspecialchars($s['short']) ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
