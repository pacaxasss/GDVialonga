<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAuth();

$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);
$msg    = '';
$err    = '';

// ── Criar slug único ────────────────────────────
function makeSlug(string $text): string {
    $text = mb_strtolower(trim($text));
    $map  = ['á'=>'a','à'=>'a','â'=>'a','ã'=>'a','é'=>'e','ê'=>'e','í'=>'i',
             'ó'=>'o','ô'=>'o','õ'=>'o','ú'=>'u','ü'=>'u','ç'=>'c','ñ'=>'n'];
    $text = strtr($text, $map);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', trim($text));
    return substr($text, 0, 80);
}

// ── POST — Guardar notícia ──────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verifyCsrfToken();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_news') {
  $deleteId = (int)($_POST['id'] ?? 0);
  if ($deleteId) {
    dbRun('DELETE FROM news WHERE id=?', [$deleteId]);
    $msg = 'Notícia eliminada.';
  }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid    = (int)($_POST['id'] ?? 0);
    $title  = trim($_POST['title'] ?? '');
    $cat    = trim($_POST['category'] ?? 'clube');
    $author = trim($_POST['author'] ?? 'Redação GDV');
    $date   = trim($_POST['published_at'] ?? date('Y-m-d'));
    $summ   = trim($_POST['summary'] ?? '');
    $cont   = sanitizeNewsHtml(trim($_POST['content'] ?? ''));
    $cover  = safeNewsImagePath($_POST['cover_image'] ?? 'assets/images/hero_stadium.png');
    $tags   = json_encode(array_filter(array_map('trim', explode(',', $_POST['tags'] ?? ''))));
    $pub    = isset($_POST['is_published']) ? 1 : 0;

    if (!$title) {
        $err = 'O título é obrigatório.';
    } elseif (!in_array($cat, ['clube','seniores','formacao','regiao','comunicado'], true)) {
      $err = 'Categoria inválida.';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)
      || !checkdate((int)substr($date, 5, 2), (int)substr($date, 8, 2), (int)substr($date, 0, 4))) {
      $err = 'Data de publicação inválida.';
    } else {
        $slug = makeSlug($title);
        if ($pid) {
            dbRun('UPDATE news SET title=?,slug=?,summary=?,content=?,cover_image=?,category=?,tags=?,published_at=?,author=?,is_published=? WHERE id=?',
                  [$title,$slug,$summ,$cont,$cover,$cat,$tags,$date,$author,$pub,$pid]);
            $msg = 'Notícia atualizada com sucesso.';
        } else {
            // Garante slug único
            $base = $slug; $i = 1;
            while (dbCount('SELECT COUNT(*) FROM news WHERE slug=?', [$slug])) {
                $slug = $base . '-' . $i++;
            }
            dbExecute('INSERT INTO news (slug,title,summary,content,cover_image,category,tags,published_at,author,is_published)
                       VALUES (?,?,?,?,?,?,?,?,?,?)',
                      [$slug,$title,$summ,$cont,$cover,$cat,$tags,$date,$author,$pub]);
            $msg = 'Notícia criada com sucesso.';
        }
        $action = 'list';
    }
}

// ── Dados para edição ────────────────────────────
$post = null;
if (($action === 'edit') && $id) {
    $post = dbFetch('SELECT * FROM news WHERE id=?', [$id]);
    if (!$post) { $err = 'Notícia não encontrada.'; $action = 'list'; }
}

// ── Lista ─────────────────────────────────────────
$news = dbFetchAll('SELECT id,title,category,published_at,is_published FROM news ORDER BY published_at DESC');

$pageTitle = 'Notícias';
$activeNav = 'noticias';
include '_layout_head.php';
?>

<?php if ($msg): ?><div class="alert alert-ok"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-err"><?= htmlspecialchars($err) ?></div><?php endif; ?>

<?php if ($action === 'list'): ?>
<!-- ═══ LISTA ═══ -->
<div class="ph">
  <div>
    <div class="ph-title">Notícias</div>
    <div class="ph-sub"><?= count($news) ?> artigos no total</div>
  </div>
  <a href="?action=create" class="btn btn-primary">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Nova Notícia
  </a>
</div>
<div class="card">
  <table class="tbl">
    <thead>
      <tr>
        <th>Título</th>
        <th>Categoria</th>
        <th>Data</th>
        <th>Estado</th>
        <th style="width:120px">Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($news as $n): ?>
      <tr>
        <td style="max-width:300px">
          <span style="font-weight:600;color:#1e293b"><?= htmlspecialchars($n['title']) ?></span>
        </td>
        <td><span class="badge badge-blue"><?= htmlspecialchars($n['category']) ?></span></td>
        <td style="white-space:nowrap"><?= substr($n['published_at'], 0, 10) ?></td>
        <td>
          <?php if ($n['is_published']): ?>
          <span class="badge badge-green">Publicado</span>
          <?php else: ?>
          <span class="badge badge-gray">Rascunho</span>
          <?php endif; ?>
        </td>
        <td>
          <div style="display:flex;gap:.3rem">
            <a href="?action=edit&id=<?= $n['id'] ?>" class="btn btn-ghost btn-xs">Editar</a>
            <form method="POST" action="noticias.php" data-confirm="Eliminar esta notícia?">
              <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
              <input type="hidden" name="id" value="<?= (int)$n['id'] ?>">
              <button type="submit" name="action" value="delete_news" class="btn btn-danger btn-xs">Del</button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($news)): ?>
      <tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:2rem">Sem notícias. <a href="?action=create">Criar a primeira →</a></td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php else: ?>
<!-- ═══ FORMULÁRIO (create / edit) ═══ -->
<div class="ph">
  <div>
    <div class="ph-title"><?= $action === 'edit' ? 'Editar Notícia' : 'Nova Notícia' ?></div>
    <div class="ph-sub"><?= $action === 'edit' ? 'ID #'.$post['id'] : 'Preencha os campos abaixo' ?></div>
  </div>
  <a href="noticias.php" class="btn btn-ghost">← Voltar à lista</a>
</div>

<div class="card">
  <div class="card-body">
  <form method="POST" action="noticias.php">
    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
    <?php if ($action === 'edit'): ?>
    <input type="hidden" name="id" value="<?= $post['id'] ?>">
    <?php endif; ?>

    <div class="fg">
      <div class="f-group full">
        <label class="f-label" for="title">Título *</label>
        <input class="f-ctrl" type="text" id="title" name="title" required
               value="<?= htmlspecialchars($post['title'] ?? '') ?>" placeholder="Ex: Vitória em Loures!">
      </div>

      <div class="f-group">
        <label class="f-label" for="category">Categoria</label>
        <select class="f-ctrl" id="category" name="category">
          <?php foreach (['clube','seniores','formacao','regiao','comunicado'] as $c): ?>
          <option value="<?= $c ?>" <?= ($post['category'] ?? 'clube')===$c?'selected':'' ?>><?= ucfirst($c) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="f-group">
        <label class="f-label" for="published_at">Data de publicação</label>
        <input class="f-ctrl" type="date" id="published_at" name="published_at"
               value="<?= htmlspecialchars($post['published_at'] ?? date('Y-m-d')) ?>">
      </div>

      <div class="f-group">
        <label class="f-label" for="author">Autor</label>
        <input class="f-ctrl" type="text" id="author" name="author"
               value="<?= htmlspecialchars($post['author'] ?? 'Redação GDV') ?>">
      </div>

      <div class="f-group">
        <label class="f-label" for="cover_image">Imagem (caminho relativo)</label>
        <input class="f-ctrl" type="text" id="cover_image" name="cover_image"
               value="<?= htmlspecialchars($post['cover_image'] ?? 'assets/images/hero_stadium.png') ?>">
        <span class="f-hint">Ex: assets/images/hero_stadium.png</span>
      </div>

      <div class="f-group full">
        <label class="f-label" for="tags">Tags <small>separadas por vírgula</small></label>
        <input class="f-ctrl" type="text" id="tags" name="tags"
               value="<?= htmlspecialchars(implode(', ', json_decode($post['tags'] ?? '[]', true))) ?>"
               placeholder="Ex: Seniores, Vitória, AF Lisboa">
      </div>

      <div class="f-group full">
        <label class="f-label" for="summary">Resumo <small>aparece nos cards de notícias</small></label>
        <textarea class="f-ctrl f-textarea" id="summary" name="summary" rows="2"
                  placeholder="2-3 frases resumindo a notícia..."><?= htmlspecialchars($post['summary'] ?? '') ?></textarea>
      </div>

      <div class="f-group full">
        <label class="f-label" for="content">Conteúdo <small>HTML básico aceite (&lt;p&gt;, &lt;strong&gt;, &lt;em&gt;, &lt;ul&gt;, &lt;li&gt;)</small></label>
        <textarea class="f-ctrl f-textarea" id="content" name="content" rows="12"
                  style="min-height:260px;font-family:monospace;font-size:.8rem"><?= htmlspecialchars($post['content'] ?? '') ?></textarea>
      </div>

      <div class="f-group full" style="flex-direction:row;align-items:center;gap:.6rem">
        <input type="checkbox" id="is_published" name="is_published" value="1"
               <?= ($post['is_published'] ?? 1) ? 'checked' : '' ?> style="width:16px;height:16px;cursor:pointer">
        <label for="is_published" style="font-size:.85rem;font-weight:500;cursor:pointer">Publicar no site</label>
      </div>
    </div>

    <div class="form-footer">
      <button type="submit" class="btn btn-primary">
        <?= $action === 'edit' ? 'Guardar alterações' : 'Criar notícia' ?>
      </button>
      <a href="noticias.php" class="btn btn-ghost">Cancelar</a>
    </div>
  </form>
  </div>
</div>
<?php endif; ?>

<?php include '_layout_foot.php'; ?>
