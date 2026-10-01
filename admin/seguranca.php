<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAuth();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $user = dbFetch('SELECT id,password_hash FROM admin_users WHERE id=?', [adminUser()['id']]);

    $mustChangePassword = !empty(adminUser()['must_change_password']);
    $initialPassword = getenv('GDV_ADMIN_INITIAL_PASSWORD');
    $currentPasswordValid = $mustChangePassword
      ? ($initialPassword !== false && $initialPassword !== '' && hash_equals($initialPassword, $currentPassword))
      : ($user && password_verify($currentPassword, $user['password_hash']));

    if (!$currentPasswordValid) {
        $error = 'A senha atual está incorreta.';
    } elseif (strlen($newPassword) < 14 || strlen($newPassword) > 1024) {
        $error = 'A nova senha deve ter entre 14 e 1024 caracteres.';
    } elseif (!hash_equals($newPassword, $confirmPassword)) {
        $error = 'A confirmação não corresponde à nova senha.';
    } elseif (hash_equals($currentPassword, $newPassword)) {
        $error = 'Escolha uma senha diferente da atual.';
    } else {
        dbRun('UPDATE admin_users SET password_hash=?,must_change_password=0 WHERE id=?', [
            password_hash($newPassword, PASSWORD_DEFAULT),
            $user['id'],
        ]);
        session_regenerate_id(true);
        unset($_SESSION['_csrf_token']);
        $_SESSION[ADMIN_SESSION_KEY]['must_change_password'] = false;
        header('Location: index.php');
        exit;
    }
}

$pageTitle = 'Segurança da conta';
$activeNav = 'seguranca';
include '_layout_head.php';
?>

<?php if ($error): ?><div class="alert alert-err"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="ph">
  <div>
    <div class="ph-title">Segurança da conta</div>
    <div class="ph-sub">Atualize a senha de acesso ao painel</div>
  </div>
</div>

<div class="card" style="max-width:560px">
  <div class="card-body">
    <form method="POST" action="seguranca.php">
      <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
      <div class="f-group">
        <label class="f-label" for="current_password">Senha atual</label>
        <input class="f-ctrl" type="password" id="current_password" name="current_password" required autocomplete="current-password">
      </div>
      <div class="f-group">
        <label class="f-label" for="new_password">Nova senha</label>
        <input class="f-ctrl" type="password" id="new_password" name="new_password" required minlength="14" maxlength="1024" autocomplete="new-password">
      </div>
      <div class="f-group">
        <label class="f-label" for="confirm_password">Confirmar nova senha</label>
        <input class="f-ctrl" type="password" id="confirm_password" name="confirm_password" required minlength="14" maxlength="1024" autocomplete="new-password">
      </div>
      <div class="form-footer">
        <button type="submit" class="btn btn-primary">Guardar nova senha</button>
      </div>
    </form>
  </div>
</div>

<?php include '_layout_foot.php'; ?>