<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (isLoggedIn()) { header('Location: index.php'); exit; }

$adminConfigured = dbCount('SELECT COUNT(*) FROM admin_users') > 0;
$adminAccount = $adminConfigured ? dbFetch('SELECT must_change_password FROM admin_users LIMIT 1') : null;
$needsBootstrapPassword = $adminAccount && !empty($adminAccount['must_change_password'])
  && (!getenv('GDV_ADMIN_INITIAL_PASSWORD') || getenv('GDV_ADMIN_INITIAL_PASSWORD') === '');
$error = !$adminConfigured
  ? 'Painel ainda não provisionado. Configure GDV_ADMIN_INITIAL_PASSWORD no Apache.'
  : ($needsBootstrapPassword ? 'Para desbloquear a troca de senha, configure GDV_ADMIN_INITIAL_PASSWORD no ambiente do Apache.' : '');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verifyCsrfToken();
  if ($adminConfigured && !$needsBootstrapPassword && attemptLogin(trim($_POST['username'] ?? ''), $_POST['password'] ?? '')) {
        header('Location: index.php'); exit;
    }
  if ($adminConfigured && !$needsBootstrapPassword) $error = 'Utilizador ou senha incorretos.';
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin — GD Vialonga</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>
<div class="login-wrap">
  <div class="login-box">
    <div class="login-logo">
      <div class="login-mark">GDV</div>
      <h1>Painel de Administração</h1>
      <p>Grupo Desportivo de Vialonga</p>
    </div>
    <?php if ($error): ?>
    <div class="alert alert-err"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
      <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
      <div class="f-group" style="margin-bottom:.85rem">
        <label class="f-label" for="u">Utilizador</label>
        <input class="f-ctrl" type="text" id="u" name="username" required autofocus
               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
      </div>
      <div class="f-group" style="margin-bottom:1.25rem">
        <label class="f-label" for="p">Senha</label>
        <input class="f-ctrl" type="password" id="p" name="password" required>
      </div>
      <button class="btn btn-primary" style="width:100%;justify-content:center;padding:.7rem">Entrar</button>
    </form>
    <p style="text-align:center;margin-top:1.1rem;font-size:.73rem;color:#94a3b8">
      <a href="../index.php">← Voltar ao site</a>
    </p>
  </div>
</div>
</body>
</html>
