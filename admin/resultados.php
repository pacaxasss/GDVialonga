<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/fpf.php';
requireAuth();

$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);
$msg    = '';
$err    = '';

$competitions = dbFetchAll('SELECT * FROM competitions WHERE season=?', [SEASON]);
$compIds      = array_column($competitions, 'id');

// ── POST — Guardar jogo ────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verifyCsrfToken();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_match') {
  $deleteId = (int)($_POST['id'] ?? 0);
  $deletedMatch = $deleteId ? dbFetch('SELECT competition_id,season FROM matches WHERE id=?', [$deleteId]) : null;
  if ($deletedMatch) {
    dbRun('DELETE FROM matches WHERE id=?', [$deleteId]);
    recalculateStandings($deletedMatch['competition_id'], $deletedMatch['season']);
    $msg = 'Jogo eliminado.';
  }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'sync_fpf') {
  try {
    $syncResult = syncFpfResults();
    $msg = sprintf(
      'Sincronização FPF concluída: %d jogos (%d terminados), %d equipas na classificação.',
      $syncResult['matches'],
      $syncResult['finished'],
      $syncResult['teams']
    );
    $competitions = dbFetchAll('SELECT * FROM competitions WHERE season=?', [SEASON]);
    $compIds = array_column($competitions, 'id');
  } catch (Throwable $error) {
    $err = 'Não foi possível sincronizar com a FPF: ' . $error->getMessage();
  }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid        = (int)($_POST['id'] ?? 0);
    $compId     = $_POST['competition_id'] ?? 'distrital-honra';
    $round      = (int)($_POST['round'] ?? 1);
    $homeTeam   = trim($_POST['home_team'] ?? '');
    $homeShort  = strtoupper(trim($_POST['home_short'] ?? substr($homeTeam, 0, 3)));
    $homeGdv    = isset($_POST['home_is_gdv']) ? 1 : 0;
    $awayTeam   = trim($_POST['away_team'] ?? '');
    $awayShort  = strtoupper(trim($_POST['away_short'] ?? substr($awayTeam, 0, 3)));
    $awayGdv    = isset($_POST['away_is_gdv']) ? 1 : 0;
    $date       = trim($_POST['match_date'] ?? date('Y-m-d'));
    $time       = trim($_POST['match_time'] ?? '15:00');
    $venue      = trim($_POST['venue'] ?? '');
    $status     = $_POST['status'] ?? 'scheduled';
    $hScore     = $status === 'finished' ? (int)$_POST['home_score'] : null;
    $aScore     = $status === 'finished' ? (int)$_POST['away_score'] : null;
    $scorers    = json_encode(array_filter(array_map('trim', explode("\n", $_POST['scorers'] ?? ''))));
    $previousMatch = null;

    if (!$homeTeam || !$awayTeam || !$date) {
        $err = 'Preencha pelo menos as equipas e a data.';
    } else {
        if ($pid) {
        $previousMatch = dbFetch('SELECT competition_id,season FROM matches WHERE id=?', [$pid]);
            dbRun('UPDATE matches SET competition_id=?,round=?,home_team=?,home_short=?,home_is_gdv=?,
                   away_team=?,away_short=?,away_is_gdv=?,match_date=?,match_time=?,venue=?,
                   status=?,home_score=?,away_score=?,scorers=? WHERE id=?',
                  [$compId,$round,$homeTeam,$homeShort,$homeGdv,$awayTeam,$awayShort,$awayGdv,
                   $date,$time,$venue,$status,$hScore,$aScore,$scorers,$pid]);
            $msg = 'Jogo atualizado.';
        } else {
            dbExecute('INSERT INTO matches
                (competition_id,season,round,home_team,home_short,home_is_gdv,
                 away_team,away_short,away_is_gdv,match_date,match_time,venue,status,home_score,away_score,scorers)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                [$compId,SEASON,$round,$homeTeam,$homeShort,$homeGdv,$awayTeam,$awayShort,$awayGdv,
                 $date,$time,$venue,$status,$hScore,$aScore,$scorers]);
            $msg = 'Jogo criado.';
        }
          recalculateStandings($compId, SEASON);
          if ($previousMatch && ($previousMatch['competition_id'] !== $compId || $previousMatch['season'] !== SEASON)) {
            recalculateStandings($previousMatch['competition_id'], $previousMatch['season']);
          }
        $action = 'list';
    }
}

// ── Dados para edição ─────────────────────────────
$match = null;
if ($action === 'edit' && $id) {
    $match = dbFetch('SELECT * FROM matches WHERE id=?', [$id]);
    if (!$match) { $err = 'Jogo não encontrado.'; $action = 'list'; }
}

// ── Lista por competição ───────────────────────────
$filterComp = $_GET['comp'] ?? '';
$matches = $filterComp
    ? dbFetchAll('SELECT * FROM matches WHERE competition_id=? AND season=? ORDER BY match_date DESC', [$filterComp, SEASON])
    : dbFetchAll('SELECT * FROM matches WHERE season=? ORDER BY match_date DESC', [SEASON]);

$pageTitle = 'Jogos & Resultados';
$activeNav = 'resultados';
include '_layout_head.php';
?>

<?php if ($msg): ?><div class="alert alert-ok"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-err"><?= htmlspecialchars($err) ?></div><?php endif; ?>

<?php if ($action === 'list'): ?>
<!-- ═══ LISTA ═══ -->
<div class="ph">
  <div>
    <div class="ph-title">Jogos & Resultados</div>
    <div class="ph-sub">Época <?= SEASON ?> &middot; <?= count($matches) ?> jogos</div>
  </div>
  <div style="display:flex;gap:.5rem;flex-wrap:wrap">
    <form method="POST" action="resultados.php" data-confirm="A sincronização vai substituir os jogos e a classificação desta competição pelos dados da FPF. Continuar?">
      <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
      <button type="submit" name="action" value="sync_fpf" class="btn btn-ghost">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 7v5h-5M4 17v-5h5"/><path d="M5.6 9a7 7 0 0 1 11.7-2L20 12M4 12l2.7 5a7 7 0 0 0 11.7-2"/></svg>
        Sincronizar FPF
      </button>
    </form>
    <a href="?action=create" class="btn btn-primary">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Novo Jogo
    </a>
  </div>
</div>

<!-- Filtro competição -->
<div class="toolbar">
  <a href="resultados.php" class="btn <?= !$filterComp?'btn-primary':'btn-ghost' ?> btn-sm">Todos</a>
  <?php foreach ($competitions as $c): ?>
  <a href="?comp=<?= urlencode($c['id']) ?>" class="btn <?= $filterComp===$c['id']?'btn-primary':'btn-ghost' ?> btn-sm">
    <?= htmlspecialchars($c['short']) ?>
  </a>
  <?php endforeach; ?>
</div>

<div class="card">
  <table class="tbl">
    <thead>
      <tr>
        <th>J</th>
        <th>Casa</th>
        <th style="text-align:center">Result.</th>
        <th>Fora</th>
        <th>Data</th>
        <th>Estado</th>
        <th style="width:100px">Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($matches as $m): ?>
      <tr style="<?= ($m['home_is_gdv'] || $m['away_is_gdv']) ? 'background:#fffbf0' : '' ?>">
        <td style="color:#94a3b8;font-size:.78rem"><?= $m['round'] ?></td>
        <td style="font-weight:<?= $m['home_is_gdv']?'700':'400' ?>">
          <?= htmlspecialchars($m['home_team']) ?>
        </td>
        <td style="text-align:center;font-weight:800;font-size:.95rem">
          <?php if ($m['status'] === 'finished'): ?>
            <?= $m['home_score'] ?> – <?= $m['away_score'] ?>
          <?php else: ?>
            <span style="color:#94a3b8;font-size:.78rem">vs</span>
          <?php endif; ?>
        </td>
        <td style="font-weight:<?= $m['away_is_gdv']?'700':'400' ?>">
          <?= htmlspecialchars($m['away_team']) ?>
        </td>
        <td style="white-space:nowrap;color:#64748b;font-size:.8rem">
          <?= htmlspecialchars($m['match_date']) ?> <?= htmlspecialchars($m['match_time']) ?>
        </td>
        <td>
          <?php
          $badgeClass = match($m['status']) {
              'finished'  => 'badge-green',
              'scheduled' => 'badge-blue',
              'cancelled' => 'badge-red',
              default     => 'badge-gray'
          };
          $badgeText = match($m['status']) {
              'finished'  => 'Terminado',
              'scheduled' => 'Agendado',
              'cancelled' => 'Cancelado',
              default     => $m['status']
          };
          ?>
          <span class="badge <?= $badgeClass ?>"><?= $badgeText ?></span>
        </td>
        <td>
          <div style="display:flex;gap:.3rem">
            <a href="?action=edit&id=<?= $m['id'] ?>" class="btn btn-ghost btn-xs">Editar</a>
            <form method="POST" action="resultados.php" data-confirm="Eliminar este jogo?">
              <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
              <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
              <button type="submit" name="action" value="delete_match" class="btn btn-danger btn-xs">Del</button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($matches)): ?>
      <tr><td colspan="7" style="text-align:center;color:#94a3b8;padding:2rem">Sem jogos. <a href="?action=create">Criar o primeiro →</a></td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php else: ?>
<!-- ═══ FORMULÁRIO ═══ -->
<div class="ph">
  <div>
    <div class="ph-title"><?= $action === 'edit' ? 'Editar Jogo' : 'Novo Jogo' ?></div>
    <div class="ph-sub"><?= $action === 'edit' ? 'ID #'.$match['id'] : 'Preencha os detalhes do jogo' ?></div>
  </div>
  <a href="resultados.php" class="btn btn-ghost">← Voltar à lista</a>
</div>

<div class="card">
  <div class="card-body">
  <form method="POST" action="resultados.php">
    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
    <?php if ($action === 'edit'): ?>
    <input type="hidden" name="id" value="<?= $match['id'] ?>">
    <?php endif; ?>

    <div class="fg">
      <!-- Competição e Jornada -->
      <div class="f-group">
        <label class="f-label" for="competition_id">Competição</label>
        <select class="f-ctrl" id="competition_id" name="competition_id">
          <?php foreach ($competitions as $c): ?>
          <option value="<?= $c['id'] ?>" <?= ($match['competition_id'] ?? 'distrital-honra')===$c['id']?'selected':'' ?>>
            <?= htmlspecialchars($c['name']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="f-group">
        <label class="f-label" for="round">Jornada</label>
        <input class="f-ctrl" type="number" id="round" name="round" min="1" max="99"
               value="<?= $match['round'] ?? 1 ?>">
      </div>

      <!-- Equipa da Casa -->
      <div class="f-group">
        <label class="f-label" for="home_team">Equipa da Casa *</label>
        <input class="f-ctrl" type="text" id="home_team" name="home_team" required
               value="<?= htmlspecialchars($match['home_team'] ?? '') ?>"
               placeholder="Ex: GD Vialonga">
      </div>
      <div class="f-group">
        <label class="f-label" for="home_short">Abreviatura Casa</label>
        <input class="f-ctrl" type="text" id="home_short" name="home_short" maxlength="5"
               value="<?= htmlspecialchars($match['home_short'] ?? '') ?>" placeholder="Ex: GDV">
      </div>

      <!-- Equipa de Fora -->
      <div class="f-group">
        <label class="f-label" for="away_team">Equipa Visitante *</label>
        <input class="f-ctrl" type="text" id="away_team" name="away_team" required
               value="<?= htmlspecialchars($match['away_team'] ?? '') ?>"
               placeholder="Ex: FC Alverca B">
      </div>
      <div class="f-group">
        <label class="f-label" for="away_short">Abreviatura Fora</label>
        <input class="f-ctrl" type="text" id="away_short" name="away_short" maxlength="5"
               value="<?= htmlspecialchars($match['away_short'] ?? '') ?>" placeholder="Ex: FCA">
      </div>

      <!-- GDV flags -->
      <div class="f-group" style="flex-direction:row;align-items:center;gap:.5rem;padding-top:.25rem">
        <input type="checkbox" id="home_is_gdv" name="home_is_gdv" value="1"
               <?= !empty($match['home_is_gdv']) ? 'checked' : '' ?> style="width:15px;height:15px">
        <label for="home_is_gdv" style="font-size:.82rem">Casa = GDV</label>
      </div>
      <div class="f-group" style="flex-direction:row;align-items:center;gap:.5rem;padding-top:.25rem">
        <input type="checkbox" id="away_is_gdv" name="away_is_gdv" value="1"
               <?= !empty($match['away_is_gdv']) ? 'checked' : '' ?> style="width:15px;height:15px">
        <label for="away_is_gdv" style="font-size:.82rem">Fora = GDV</label>
      </div>

      <!-- Data, Hora, Local -->
      <div class="f-group">
        <label class="f-label" for="match_date">Data *</label>
        <input class="f-ctrl" type="date" id="match_date" name="match_date" required
               value="<?= htmlspecialchars($match['match_date'] ?? date('Y-m-d')) ?>">
      </div>
      <div class="f-group">
        <label class="f-label" for="match_time">Hora</label>
        <input class="f-ctrl" type="time" id="match_time" name="match_time"
               value="<?= htmlspecialchars($match['match_time'] ?? '15:00') ?>">
      </div>
      <div class="f-group full">
        <label class="f-label" for="venue">Local / Estádio</label>
        <input class="f-ctrl" type="text" id="venue" name="venue"
               value="<?= htmlspecialchars($match['venue'] ?? '') ?>"
               placeholder="Ex: Complexo Desportivo de Vialonga">
      </div>

      <!-- Estado e resultado -->
      <div class="f-group">
        <label class="f-label" for="status">Estado do Jogo</label>
        <select class="f-ctrl" id="status" name="status" data-toggle-score>
          <option value="scheduled" <?= ($match['status']??'scheduled')==='scheduled'?'selected':'' ?>>Agendado</option>
          <option value="finished"  <?= ($match['status']??'')==='finished' ?'selected':'' ?>>Terminado</option>
          <option value="cancelled" <?= ($match['status']??'')==='cancelled'?'selected':'' ?>>Cancelado</option>
        </select>
      </div>
      <div class="f-group" id="score-group" style="<?= ($match['status']??'scheduled')!=='finished'?'display:none':'' ?>">
        <label class="f-label">Resultado Final</label>
        <div class="score-row">
          <span style="font-size:.72rem;color:#94a3b8;text-align:right"><?= htmlspecialchars(substr($match['home_team'] ?? 'Casa', 0, 10)) ?></span>
          <input class="f-ctrl score-inp" type="number" name="home_score" min="0" max="99"
                 value="<?= $match['home_score'] ?? 0 ?>">
          <span class="score-sep">–</span>
          <input class="f-ctrl score-inp" type="number" name="away_score" min="0" max="99"
                 value="<?= $match['away_score'] ?? 0 ?>">
          <span style="font-size:.72rem;color:#94a3b8"><?= htmlspecialchars(substr($match['away_team'] ?? 'Fora', 0, 10)) ?></span>
        </div>
      </div>

      <!-- Marcadores -->
      <div class="f-group full" id="scorers-group" style="<?= ($match['status']??'scheduled')!=='finished'?'display:none':'' ?>">
        <label class="f-label" for="scorers">Marcadores <small>um por linha: "Nome 23'"</small></label>
        <textarea class="f-ctrl f-textarea" id="scorers" name="scorers" rows="3"
                  placeholder="Fábio Tavares 23'&#10;Jordan Mendes 74'"><?php
          $sc = json_decode($match['scorers'] ?? '[]', true);
          echo htmlspecialchars(implode("\n", $sc));
        ?></textarea>
      </div>
    </div>

    <div class="form-footer">
      <button type="submit" class="btn btn-primary">
        <?= $action === 'edit' ? 'Guardar alterações' : 'Criar jogo' ?>
      </button>
      <a href="resultados.php" class="btn btn-ghost">Cancelar</a>
    </div>
  </form>
  </div>
</div>

<?php endif; ?>

<?php include '_layout_foot.php'; ?>
