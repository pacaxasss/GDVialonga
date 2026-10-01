<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireAuth();

$msg     = '';
$err     = '';
$season  = $_GET['season'] ?? SEASON;
if (!preg_match('/^\d{4}\/\d{2}$/', $season)) $season = SEASON;
$competitions = dbFetchAll('SELECT * FROM competitions WHERE season=?', [$season]);
$allowedCompetitionIds = array_column($competitions, 'id');
$compId = $_GET['comp'] ?? 'distrital-honra';
if (!in_array($compId, $allowedCompetitionIds, true)) $compId = $allowedCompetitionIds[0] ?? 'distrital-honra';

// ── POST — Guardar tabela ─────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verifyCsrfToken();
    $action_type = $_POST['action_type'] ?? 'update';

  if ($action_type === 'auto_calculate') {
    recalculateStandings($compId, $season);
    $msg = 'Tabela recalculada a partir dos resultados registados.';

  } elseif ($action_type === 'delete_row') {
        $delId = (int)($_POST['delete_id'] ?? 0);
    if ($delId) {
      dbRun('DELETE FROM standings WHERE id=? AND competition_id=? AND season=?', [$delId,$compId,$season]);
      $msg = 'Equipa removida.';
    }

    } elseif ($action_type === 'add_row') {
        $newTeam  = trim($_POST['new_team'] ?? '');
        $newShort = strtoupper(trim($_POST['new_short'] ?? ''));
        if ($newTeam) {
            $maxPos = dbFetch('SELECT MAX(position) as m FROM standings WHERE competition_id=? AND season=?', [$compId,$season]);
            $pos    = ((int)($maxPos['m'] ?? 0)) + 1;
            dbExecute('INSERT INTO standings (competition_id,season,position,team,short,is_gdv) VALUES (?,?,?,?,?,0)',
                      [$compId,$season,$pos,$newTeam,$newShort]);
            $msg = "Equipa \"$newTeam\" adicionada.";
        }

    } else {
        // Atualizar todas as linhas existentes
        $rows  = $_POST['rows'] ?? [];
        $db    = getDB();
        $db->beginTransaction();
        try {
            $stmt = $db->prepare('UPDATE standings SET position=?,team=?,short=?,played=?,won=?,drawn=?,lost=?,
                                  goals_for=?,goals_against=?,points=?,form=?,is_gdv=? WHERE id=? AND competition_id=? AND season=?');
            foreach ($rows as $rid => $r) {
                $form = json_encode(array_filter(array_map('trim', explode(',', $r['form'] ?? ''))));
                $stmt->execute([
                    (int)$r['position'], trim($r['team']), strtoupper(trim($r['short'])),
                    (int)$r['played'], (int)$r['won'], (int)$r['drawn'], (int)$r['lost'],
                    (int)$r['goals_for'], (int)$r['goals_against'], (int)$r['points'],
                    $form, isset($r['is_gdv']) ? 1 : 0, (int)$rid, $compId, $season
                ]);
            }
            $db->commit();
            $msg = 'Classificação atualizada com sucesso.';
        } catch (Exception $e) {
            $db->rollBack();
            $err = 'Erro ao guardar: ' . $e->getMessage();
        }
        // Re-ordenar por posição
        $allRows = dbFetchAll('SELECT * FROM standings WHERE competition_id=? AND season=? ORDER BY position ASC, points DESC', [$compId,$season]);
        foreach ($allRows as $i => $r) {
            dbRun('UPDATE standings SET position=? WHERE id=?', [$i+1, $r['id']]);
        }
    }
}

// Buscar linhas da tabela
$standings = dbFetchAll(
    'SELECT * FROM standings WHERE competition_id=? AND season=? ORDER BY position ASC',
    [$compId, $season]
);

$pageTitle = 'Classificação';
$activeNav = 'classificacao';
include '_layout_head.php';
?>

<?php if ($msg): ?><div class="alert alert-ok"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-err"><?= htmlspecialchars($err) ?></div><?php endif; ?>

<div class="ph">
  <div>
    <div class="ph-title">Classificação</div>
    <div class="ph-sub">Época <?= htmlspecialchars($season) ?> &middot; <?= count($standings) ?> equipas</div>
  </div>
  <div style="display:flex;gap:.5rem;flex-wrap:wrap">
    <form method="POST" action="?comp=<?= urlencode($compId) ?>&season=<?= urlencode($season) ?>" data-confirm="Recalcular a classificação a partir dos resultados?">
      <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
      <button type="submit" name="action_type" value="auto_calculate" class="btn btn-ghost">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
      Calcular Auto
      </button>
    </form>
  </div>
</div>

<!-- Filtros -->
<div class="toolbar" style="margin-bottom:1.1rem">
  <?php foreach ($competitions as $c): ?>
  <a href="?comp=<?= urlencode($c['id']) ?>&season=<?= urlencode($season) ?>"
     class="btn <?= $compId===$c['id']?'btn-primary':'btn-ghost' ?> btn-sm">
    <?= htmlspecialchars($c['name']) ?>
  </a>
  <?php endforeach; ?>
</div>

<div class="alert alert-info" style="margin-bottom:1.1rem">
  <strong>Dica:</strong> Clique em qualquer campo da tabela abaixo para editar diretamente.
  Use <em>"Calcular Auto"</em> para recalcular a partir dos jogos registados.
  Pode também ajustar manualmente qualquer valor e guardar.
</div>

<form method="POST" action="?comp=<?= urlencode($compId) ?>&season=<?= urlencode($season) ?>">
<input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
<input type="hidden" name="action_type" value="update">

<div class="card" style="overflow-x:auto">
  <table class="tbl stand-ed">
    <thead>
      <tr>
        <th>#</th>
        <th>Equipa</th>
        <th>Abrev.</th>
        <th title="Jogos">J</th>
        <th title="Vitórias">V</th>
        <th title="Empates">E</th>
        <th title="Derrotas">D</th>
        <th title="Golos Marcados">GM</th>
        <th title="Golos Sofridos">GS</th>
        <th title="Pontos">PTS</th>
        <th title="Forma (W,D,L separados por vírgula)">Forma</th>
        <th>GDV</th>
        <th style="width:50px">Del</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($standings as $r): ?>
      <tr class="<?= $r['is_gdv'] ? 'gdv-tr' : '' ?>">
        <td>
          <input type="number" name="rows[<?= $r['id'] ?>][position]"
                 value="<?= $r['position'] ?>" style="width:42px">
        </td>
        <td>
          <input type="text" name="rows[<?= $r['id'] ?>][team]"
                 value="<?= htmlspecialchars($r['team']) ?>" style="width:160px">
        </td>
        <td>
          <input type="text" name="rows[<?= $r['id'] ?>][short]"
                 value="<?= htmlspecialchars($r['short']) ?>" style="width:50px">
        </td>
        <td><input type="number" name="rows[<?= $r['id'] ?>][played]"       value="<?= $r['played'] ?>"></td>
        <td><input type="number" name="rows[<?= $r['id'] ?>][won]"          value="<?= $r['won'] ?>"></td>
        <td><input type="number" name="rows[<?= $r['id'] ?>][drawn]"        value="<?= $r['drawn'] ?>"></td>
        <td><input type="number" name="rows[<?= $r['id'] ?>][lost]"         value="<?= $r['lost'] ?>"></td>
        <td><input type="number" name="rows[<?= $r['id'] ?>][goals_for]"    value="<?= $r['goals_for'] ?>"></td>
        <td><input type="number" name="rows[<?= $r['id'] ?>][goals_against]" value="<?= $r['goals_against'] ?>"></td>
        <td><input type="number" name="rows[<?= $r['id'] ?>][points]"       value="<?= $r['points'] ?>"></td>
        <td>
          <input type="text" name="rows[<?= $r['id'] ?>][form]"
                 value="<?= htmlspecialchars(implode(',', json_decode($r['form'] ?? '[]', true))) ?>"
                 style="width:80px" placeholder="W,D,L">
        </td>
        <td style="text-align:center">
          <input type="checkbox" name="rows[<?= $r['id'] ?>][is_gdv]" value="1"
                 <?= $r['is_gdv'] ? 'checked' : '' ?> style="width:14px;height:14px">
        </td>
        <td>
            <button type="submit" name="action_type" value="delete_row" data-delete-id="<?= (int)$r['id'] ?>" data-confirm="Remover esta equipa?"
                  class="btn btn-danger btn-xs">Del</button>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <input type="hidden" name="delete_id" value="">
</div>

<div class="form-footer" style="margin-top:1rem">
  <button type="submit" name="action_type" value="update" class="btn btn-primary">
    Guardar Tabela
  </button>
  <span style="font-size:.76rem;color:#94a3b8">Clique para guardar todos os valores editados acima.</span>
</div>
</form>

<!-- Adicionar equipa -->
<div class="card" style="margin-top:1.25rem">
  <div class="card-head"><span class="card-title">Adicionar Equipa à Tabela</span></div>
  <div class="card-body">
    <form method="POST" action="?comp=<?= urlencode($compId) ?>&season=<?= urlencode($season) ?>">
      <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="action_type" value="add_row">
      <div style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end">
        <div class="f-group" style="min-width:220px">
          <label class="f-label" for="new_team">Nome da equipa</label>
          <input class="f-ctrl" type="text" id="new_team" name="new_team" required placeholder="Ex: SC Sacavenense">
        </div>
        <div class="f-group" style="min-width:100px">
          <label class="f-label" for="new_short">Abreviatura</label>
          <input class="f-ctrl" type="text" id="new_short" name="new_short" maxlength="5" placeholder="Ex: SCS">
        </div>
        <button type="submit" class="btn btn-primary">Adicionar</button>
      </div>
    </form>
  </div>
</div>

<?php include '_layout_foot.php'; ?>
