<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
$pageTitle       = 'Futebol — Resultados & Classificação';
$pageDescription = 'Resultados, calendário e classificação do GD Vialonga na 1.ª Divisão da AF Lisboa.';

$competitions = getCompetitions();
$mainCompetition = null;
foreach ($competitions as $competition) {
    if ($competition['id'] === 'distrital-honra') {
        $mainCompetition = $competition;
        break;
    }
}
$standingsData = [
    'season' => SEASON,
    'updated_at' => date('Y-m-d'),
    'competition' => $mainCompetition['name'] ?? FPF_COMPETITION_NAME,
    'standings' => getStandings('distrital-honra'),
];
$activeComp = $_GET['comp'] ?? ($mainCompetition['id'] ?? ($competitions[0]['id'] ?? 'distrital-honra'));
require_once dirname(__DIR__) . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container page-hero-inner">
        <nav class="breadcrumb">
            <a href="index.php">Início</a>
            <span class="breadcrumb-sep">/</span>
            <span>Futebol</span>
        </nav>
        <h1>Resultados, Calendário & Classificação</h1>
        <p>Época <?= htmlspecialchars($standingsData['season']) ?> &middot; Atualizado <?= formatDate($standingsData['updated_at']) ?> &middot; <a href="<?= htmlspecialchars(FPF_BASE_URL . '/Competition/Details?competitionId=' . FPF_COMPETITION_ID . '&seasonId=' . FPF_SEASON_ID) ?>" target="_blank" rel="noopener noreferrer">Fonte: Resultados FPF</a> (dados oficiais após 15 dias)</p>
    </div>
</section>

<section class="section section-white" style="padding:1.75rem 0">
    <div class="container">
        <div class="comp-tabs">
            <?php foreach ($competitions as $comp): ?>
            <button class="comp-tab <?= $comp['id']===$activeComp?'active':'' ?>" data-target="<?= $comp['id'] ?>">
                <?= htmlspecialchars($comp['name']) ?>
            </button>
            <?php endforeach; ?>
        </div>
    </div>
</section>


<?php foreach ($competitions as $comp):
    $allCompMatches = getMatchesForPage($comp['id']);
    $finished    = array_values(array_filter($allCompMatches, fn($m) => $m['status']==='finished'));
    $upcoming    = array_values(array_filter($allCompMatches, fn($m) => $m['status']==='scheduled'));

    usort($finished, fn($a,$b) => strcmp($b['date'], $a['date']));
    usort($upcoming, fn($a,$b) => strcmp($a['date'], $b['date']));
?>
<div data-panel="<?= $comp['id'] ?>" <?= $comp['id']!==$activeComp ? 'hidden' : '' ?>>

    <section class="section section-gray" id="<?= $comp['id']==='distrital-honra' ? 'resultados' : 'resultados-'.$comp['id'] ?>">
        <div class="container">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:2.5rem" class="fixtures-cols">

                <div>
                    <h2 style="font-family:var(--font-display);font-size:1.05rem;font-weight:800;color:var(--gray-900);margin-bottom:1rem;padding-bottom:.5rem;border-bottom:2px solid var(--gray-100)">
                        Últimos resultados
                    </h2>
                    <?php if (empty($finished)): ?>
                    <p class="text-muted" style="font-size:.88rem">Ainda não há resultados registados.</p>
                    <?php else: ?>
                    <div class="fixtures-list">
                        <?php foreach (array_slice($finished, 0, 6) as $match): ?>
                        <div class="fixture-card <?= ($match['home_team']['is_gdv']||$match['away_team']['is_gdv'])?'gdv-match':'' ?>">
                            <div class="fixture-team">
                                <div class="fixture-badge <?= $match['home_team']['is_gdv']?'gdv':'' ?>"><?= htmlspecialchars($match['home_team']['short']) ?></div>
                                <span class="fixture-team-name <?= $match['home_team']['is_gdv']?'is-gdv':'' ?>"><?= htmlspecialchars($match['home_team']['name']) ?></span>
                            </div>
                            <div class="fixture-center">
                                <div class="fixture-score"><?= $match['home_score'] ?><span class="fixture-score-sep">–</span><?= $match['away_score'] ?></div>
                                <div class="fixture-date-time"><?= formatDate($match['date']) ?></div>
                                <div class="fixture-round">Jornada <?= $match['round'] ?></div>
                            </div>
                            <div class="fixture-team away">
                                <div class="fixture-badge <?= $match['away_team']['is_gdv']?'gdv':'' ?>"><?= htmlspecialchars($match['away_team']['short']) ?></div>
                                <span class="fixture-team-name <?= $match['away_team']['is_gdv']?'is-gdv':'' ?>"><?= htmlspecialchars($match['away_team']['name']) ?></span>
                            </div>
                        </div>
                        <?php if (!empty($match['scorers'])): ?>
                        <div style="font-size:.72rem;color:var(--gray-400);padding:.1rem .75rem .35rem">
                            <?= implode(' &middot; ', array_map('htmlspecialchars', $match['scorers'])) ?>
                        </div>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div id="calendario">
                    <h2 style="font-family:var(--font-display);font-size:1.05rem;font-weight:800;color:var(--gray-900);margin-bottom:1rem;padding-bottom:.5rem;border-bottom:2px solid var(--gray-100)">
                        Próximos jogos
                    </h2>
                    <?php if (empty($upcoming)): ?>
                    <p class="text-muted" style="font-size:.88rem">Sem jogos marcados de momento.</p>
                    <?php else: ?>
                    <div class="fixtures-list">
                        <?php foreach (array_slice($upcoming, 0, 6) as $match): ?>
                        <div class="fixture-card <?= ($match['home_team']['is_gdv']||$match['away_team']['is_gdv'])?'gdv-match':'' ?>">
                            <div class="fixture-team">
                                <div class="fixture-badge <?= $match['home_team']['is_gdv']?'gdv':'' ?>"><?= htmlspecialchars($match['home_team']['short']) ?></div>
                                <span class="fixture-team-name <?= $match['home_team']['is_gdv']?'is-gdv':'' ?>"><?= htmlspecialchars($match['home_team']['name']) ?></span>
                            </div>
                            <div class="fixture-center">
                                <div class="fixture-vs">VS</div>
                                <div class="fixture-upcoming-date"><?= formatDate($match['date']) ?><?= !empty($match['time']) ? ' &middot; ' . htmlspecialchars($match['time']) . 'h' : '' ?></div>
                                <div class="fixture-round">Jornada <?= $match['round'] ?></div>
                            </div>
                            <div class="fixture-team away">
                                <div class="fixture-badge <?= $match['away_team']['is_gdv']?'gdv':'' ?>"><?= htmlspecialchars($match['away_team']['short']) ?></div>
                                <span class="fixture-team-name <?= $match['away_team']['is_gdv']?'is-gdv':'' ?>"><?= htmlspecialchars($match['away_team']['name']) ?></span>
                            </div>
                        </div>
                        <div style="font-size:.7rem;color:var(--gray-400);padding:.1rem .75rem .35rem">
                            <?= htmlspecialchars($match['venue']) ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <?php if ($comp['id']==='distrital-honra'): ?>
    <section class="section section-white" id="classificacao">
        <div class="container">
            <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:1.75rem;flex-wrap:wrap;gap:1rem">
                <div>
                    <span class="section-label">Tabela Classificativa</span>
                    <h2 style="font-family:var(--font-display);font-weight:900;font-size:clamp(1.3rem,3vw,1.85rem);color:var(--gray-900);margin-top:.3rem">
                        <?= htmlspecialchars($standingsData['competition']) ?>
                    </h2>
                </div>
                <div style="display:flex;gap:1.25rem;font-size:.76rem;color:var(--gray-500)">
                    <span><span style="display:inline-block;width:10px;height:10px;background:#059669;border-radius:2px;margin-right:.3rem"></span>Promoção</span>
                    <span><span style="display:inline-block;width:10px;height:10px;background:#d97706;border-radius:2px;margin-right:.3rem"></span>Play-off</span>
                    <span><span style="display:inline-block;width:10px;height:10px;background:var(--gdv-red);border-radius:2px;margin-right:.3rem"></span>Descida</span>
                </div>
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
                            <th title="Golos Marcados">GM</th>
                            <th title="Golos Sofridos">GS</th>
                            <th title="Diferença de Golos">DG</th>
                            <th class="team-form-col">Forma</th>
                            <th title="Pontos">PTS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($standingsData['standings'] as $row):
                            $pos = $row['position'];
                            $posClass = $pos===1?'pos-promo':($pos===2?'pos-playoff':($pos>=9?'pos-releg':''));
                        ?>
                        <tr class="<?= $row['is_gdv']?'gdv-row':'' ?>">
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
                            <td><?= $row['goals_for'] ?></td>
                            <td><?= $row['goals_against'] ?></td>
                            <td><?= ($row['goal_diff']>0?'+':'').$row['goal_diff'] ?></td>
                            <td class="team-form-col">
                                <div class="form-badges">
                                    <?php foreach (array_slice($row['form'],-5) as $f): ?>
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
    <?php endif; ?>
</div>
<?php endforeach; ?>

<style>
@media (max-width: 768px) { .fixtures-cols { grid-template-columns: 1fr !important; } }
</style>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
