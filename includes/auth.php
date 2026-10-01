<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';

function isLoggedIn(): bool {
    return !empty($_SESSION[ADMIN_SESSION_KEY]);
}
function requireAuth(): void {
    if (!isLoggedIn()) {
        header('Location: ' . (strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? 'login.php' : '/admin/login.php'));
        exit;
    }

    $sessionUser = adminUser();
    if (time() - (int)($sessionUser['at'] ?? 0) > 1800) {
        doLogout();
        header('Location: login.php');
        exit;
    }
    $_SESSION[ADMIN_SESSION_KEY]['at'] = time();

    $account = dbFetch('SELECT username,must_change_password FROM admin_users WHERE id=?', [(int)$sessionUser['id']]);
    if (!$account) {
        doLogout();
        header('Location: login.php');
        exit;
    }
    $_SESSION[ADMIN_SESSION_KEY]['username'] = $account['username'];
    $_SESSION[ADMIN_SESSION_KEY]['must_change_password'] = (bool)$account['must_change_password'];

    $page = basename($_SERVER['SCRIPT_NAME'] ?? '');
    if (!empty($account['must_change_password']) && !in_array($page, ['seguranca.php', 'logout.php'], true)) {
        header('Location: seguranca.php');
        exit;
    }
}
function csrfToken(): string {
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}
function verifyCsrfToken(): void {
    $submitted = $_POST['_csrf_token'] ?? '';
    $stored = $_SESSION['_csrf_token'] ?? '';
    if (!is_string($submitted) || !is_string($stored) || $stored === '' || !hash_equals($stored, $submitted)) {
        http_response_code(403);
        exit('Pedido inválido. Atualize a página e tente novamente.');
    }
}
function attemptLogin(string $username, string $password): bool {
    $ipHash = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $now = time();
    dbRun('DELETE FROM login_attempts WHERE window_started<?', [$now - 86400]);
    $attempt = dbFetch('SELECT attempts,window_started FROM login_attempts WHERE ip_hash=?', [$ipHash]);
    if ($attempt && $now - (int)$attempt['window_started'] < 900 && (int)$attempt['attempts'] >= 5) {
        return false;
    }
    if ($attempt && $now - (int)$attempt['window_started'] >= 900) {
        dbRun('DELETE FROM login_attempts WHERE ip_hash=?', [$ipHash]);
        $attempt = null;
    }

    $user = null;
    if (strlen($username) <= 128 && strlen($password) <= 1024) {
        $user = dbFetch('SELECT * FROM admin_users WHERE username = ?', [$username]);
    }
    $mustChangePassword = $user && !empty($user['must_change_password']);
    $initialPassword = getenv('GDV_ADMIN_INITIAL_PASSWORD');
    $passwordValid = $user && ($mustChangePassword
        ? ($initialPassword !== false && $initialPassword !== '' && hash_equals($initialPassword, $password))
        : password_verify($password, $user['password_hash']));
    if (!$passwordValid) {
        if ($attempt) {
            dbRun('UPDATE login_attempts SET attempts=attempts+1 WHERE ip_hash=?', [$ipHash]);
        } else {
            dbRun('INSERT INTO login_attempts (ip_hash,attempts,window_started) VALUES (?,1,?)', [$ipHash, $now]);
        }
        return false;
    }

    dbRun('DELETE FROM login_attempts WHERE ip_hash=?', [$ipHash]);
    if (!$mustChangePassword && password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
        dbRun('UPDATE admin_users SET password_hash=? WHERE id=?', [password_hash($password, PASSWORD_DEFAULT), $user['id']]);
    }
    session_regenerate_id(true);
    unset($_SESSION['_csrf_token']);
    $_SESSION[ADMIN_SESSION_KEY] = [
        'id' => $user['id'],
        'username' => $user['username'],
        'must_change_password' => (bool)$user['must_change_password'],
        'at' => time(),
    ];
    return true;
}
function doLogout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Lax',
        ]);
    }
    session_destroy();
}
function adminUser(): ?array { return $_SESSION[ADMIN_SESSION_KEY] ?? null; }
