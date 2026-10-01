<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAuth();

$pageTitle = 'Dashboard';
$activeNav = 'dashboard';

$nNews    = dbCount('SELECT COUNT(*) FROM news WHERE is_published=1');
$nDraft   = dbCount('SELECT COUNT(*) FROM news WHERE is_published=0');
$nMatches = dbCount('SELECT COUNT(*) FROM matches WHERE season=?', [SEASON]);
$nTeams   = dbCount('SELECT COUNT(*) FROM standings WHERE season=?', [SEASON]);

$recentNews    = dbFetchAll('SELECT id,title,category,published_at FROM news ORDER BY published_at DESC LIMIT 5');
$recentMatches = dbFetchAll(
    "SELECT * FROM matches WHERE season=? ORDER BY match_date DESC LIMIT 6",
    [SEASON]
);
$nextGame = dbFetch(
    "SELECT * FROM matches WHERE status='scheduled' AND (home_is_gdv=1 OR away_is_gdv=1) AND match_date>=? ORDER BY match_date ASC LIMIT 1",
    [date('Y-m-d')]
);

include '_layout_head.php';
?>

<div class="stats">
  <div class="stat">
    <div class="stat-val blue"><?= $nNews ?></div>
    <div class="stat-lbl">Notícias publicadas</div>
  </div>
  <div class="stat">
    <div class="stat-val"><?= $nDraft ?></div>
    <div class="stat-lbl">Rascunhos</div>
  </div>
  <div class="stat">
    <div class="stat-val blue"><?= $nMatches ?></div>
    <div class="stat-lbl">Jogos registados</div>
  </div>
  <div class="stat">
    <div class="stat-val"><?= $nTeams ?></div>
    <div class="stat-lbl">Equipas na tabela</div>
  </div>
</div>

<?php if ($nextGame): ?>
<div class="alert alert-info" style="margin-bottom:1.5rem">
  <strong>Próximo jogo —</strong>
  <?= htmlspecialchars($nextGame['home_team']) ?> vs <?= htmlspecialchars($nextGame['away_team']) ?>
  &nbsp;&middot;&nbsp; <?= htmlspecialchars($nextGame['match_date']) ?> <?= htmlspecialchars($nextGame['match_time']) ?>h
  &nbsp;&middot;&nbsp; <?= htmlspecialchars($nextGame['venue']) ?>
  &nbsp; <a href="resultados.php?action=edit&id=<?= $nextGame['id'] ?>">→ Registar resultado</a>
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem">

  <!-- Últimas notícias -->
  <div class="card">
    <div class="card-head">
      <span class="card-title">Últimas Notícias</span>
      <a href="noticias.php?action=create" class="btn btn-primary btn-sm">+ Nova</a>
    </div>
    <table class="tbl">
      <thead><tr><th>Título</th><th>Cat.</th><th>Data</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($recentNews as $n): ?>
        <tr>
          <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($n['title']) ?></td>
          <td><span class="badge badge-blue"><?= htmlspecialchars($n['category']) ?></span></td>
          <td><?= substr($n['published_at'], 0, 10) ?></td>
          <td><a href="noticias.php?action=edit&id=<?= $n['id'] ?>" class="btn btn-ghost btn-xs">Editar</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div style="padding:.75rem 1.1rem;border-top:1px solid #f1f5f9">
      <a href="noticias.php" class="btn btn-ghost btn-sm">Ver todas</a>
    </div>
  </div>

  <!-- Últimos jogos -->
  <div class="card">
    <div class="card-head">
      <span class="card-title">Jogos Recentes</span>
      <a href="resultados.php?action=create" class="btn btn-primary btn-sm">+ Novo</a>
    </div>
    <table class="tbl">
      <thead><tr><th>Casa</th><th>Res.</th><th>Fora</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($recentMatches as $m): ?>
        <tr>
          <td style="font-weight:<?= $m['home_is_gdv'] ? '700' : '400' ?>"><?= htmlspecialchars($m['home_short'] ?: substr($m['home_team'],0,10)) ?></td>
          <td style="text-align:center;font-weight:700;font-size:.88rem">
            <?php if ($m['status'] === 'finished'): ?>
              <?= $m['home_score'] ?>&ndash;<?= $m['away_score'] ?>
            <?php else: ?>
              <span style="color:#94a3b8">vs</span>
            <?php endif; ?>
          </td>
          <td style="font-weight:<?= $m['away_is_gdv'] ? '700' : '400' ?>"><?= htmlspecialchars($m['away_short'] ?: substr($m['away_team'],0,10)) ?></td>
          <td><a href="resultados.php?action=edit&id=<?= $m['id'] ?>" class="btn btn-ghost btn-xs">Editar</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div style="padding:.75rem 1.1rem;border-top:1px solid #f1f5f9">
      <a href="resultados.php" class="btn btn-ghost btn-sm">Ver todos</a>
    </div>
  </div>

</div>

<div style="margin-top:1.25rem" class="card">
  <div class="card-head">
    <span class="card-title">Ações Rápidas</span>
  </div>
  <div class="card-body" style="display:flex;gap:.75rem;flex-wrap:wrap">
    <a href="noticias.php?action=create" class="btn btn-primary">+ Nova Notícia</a>
    <a href="resultados.php?action=create" class="btn btn-primary">+ Novo Jogo</a>
    <a href="classificacao.php" class="btn btn-ghost">Editar Classificação</a>
    <a href="../index.php" target="_blank" rel="noopener noreferrer" class="btn btn-ghost">Ver Site →</a>
  </div>
</div>

<?php include '_layout_foot.php'; ?>
