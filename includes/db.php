<?php
require_once __DIR__ . '/../config.php';

function getDB(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $dir = dirname(DB_PATH);
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA journal_mode=WAL; PRAGMA foreign_keys=ON;');

    initSchema($pdo);
    seedIfEmpty($pdo);
    return $pdo;
}

function initSchema(PDO $pdo): void {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS news (
            id           INTEGER PRIMARY KEY AUTOINCREMENT,
            slug         TEXT UNIQUE NOT NULL,
            title        TEXT NOT NULL,
            summary      TEXT DEFAULT '',
            content      TEXT DEFAULT '',
            cover_image  TEXT DEFAULT 'assets/images/hero_stadium.png',
            category     TEXT DEFAULT 'clube',
            tags         TEXT DEFAULT '[]',
            published_at TEXT NOT NULL,
            author       TEXT DEFAULT 'Redação GDV',
            is_published INTEGER DEFAULT 1,
            created_at   TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS competitions (
            id     TEXT PRIMARY KEY,
            name   TEXT NOT NULL,
            short  TEXT,
            squad  TEXT,
            season TEXT
        );

        CREATE TABLE IF NOT EXISTS matches (
            id             INTEGER PRIMARY KEY AUTOINCREMENT,
            competition_id TEXT,
            season         TEXT DEFAULT '2026/27',
            round          INTEGER DEFAULT 1,
            home_team      TEXT NOT NULL,
            home_short     TEXT,
            home_is_gdv    INTEGER DEFAULT 0,
            away_team      TEXT NOT NULL,
            away_short     TEXT,
            away_is_gdv    INTEGER DEFAULT 0,
            match_date     TEXT NOT NULL,
            match_time     TEXT DEFAULT '15:00',
            venue          TEXT DEFAULT '',
            status         TEXT DEFAULT 'scheduled',
            home_score     INTEGER,
            away_score     INTEGER,
            scorers        TEXT DEFAULT '[]',
            FOREIGN KEY(competition_id) REFERENCES competitions(id)
        );

        CREATE TABLE IF NOT EXISTS standings (
            id             INTEGER PRIMARY KEY AUTOINCREMENT,
            competition_id TEXT DEFAULT 'distrital-honra',
            season         TEXT DEFAULT '2026/27',
            position       INTEGER,
            team           TEXT NOT NULL,
            short          TEXT,
            played         INTEGER DEFAULT 0,
            won            INTEGER DEFAULT 0,
            drawn          INTEGER DEFAULT 0,
            lost           INTEGER DEFAULT 0,
            goals_for      INTEGER DEFAULT 0,
            goals_against  INTEGER DEFAULT 0,
            points         INTEGER DEFAULT 0,
            form           TEXT DEFAULT '[]',
            is_gdv         INTEGER DEFAULT 0
        );

        CREATE TABLE IF NOT EXISTS sponsors (
            id      INTEGER PRIMARY KEY AUTOINCREMENT,
            name    TEXT NOT NULL,
            short   TEXT,
            tier    TEXT DEFAULT 'silver',
            website TEXT DEFAULT '',
            active  INTEGER DEFAULT 1
        );

        CREATE TABLE IF NOT EXISTS admin_users (
            id            INTEGER PRIMARY KEY AUTOINCREMENT,
            username      TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            must_change_password INTEGER NOT NULL DEFAULT 1,
            created_at    TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS login_attempts (
            ip_hash       TEXT PRIMARY KEY,
            attempts      INTEGER NOT NULL DEFAULT 0,
            window_started INTEGER NOT NULL
        );
    ");

    $adminColumns = array_column($pdo->query('PRAGMA table_info(admin_users)')->fetchAll(), 'name');
    if (!in_array('must_change_password', $adminColumns, true)) {
        $pdo->exec('ALTER TABLE admin_users ADD COLUMN must_change_password INTEGER NOT NULL DEFAULT 1');
        $invalidatedHash = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);
        $pdo->prepare('UPDATE admin_users SET password_hash=?,must_change_password=1')->execute([$invalidatedHash]);
    }
}

function seedIfEmpty(PDO $pdo): void {
    if ((int)$pdo->query('SELECT COUNT(*) FROM admin_users')->fetchColumn() === 0) {
        $initialUsername = trim(getenv('GDV_ADMIN_INITIAL_USERNAME') ?: 'admin');
        $initialPassword = getenv('GDV_ADMIN_INITIAL_PASSWORD');
        if ($initialPassword !== false && strlen($initialPassword) >= 14 && strlen($initialPassword) <= 1024
            && preg_match('/^[A-Za-z0-9_.-]{3,64}$/', $initialUsername)) {
            $hash = password_hash($initialPassword, PASSWORD_DEFAULT);
            $pdo->prepare('INSERT INTO admin_users (username,password_hash,must_change_password) VALUES (?,?,0)')
                ->execute([$initialUsername, $hash]);
        }
    }

    // Competições
    if ((int)$pdo->query('SELECT COUNT(*) FROM competitions')->fetchColumn() === 0) {
        $stmt = $pdo->prepare('INSERT OR IGNORE INTO competitions (id,name,short,squad,season) VALUES (?,?,?,?,?)');
        $stmt->execute(['distrital-honra', FPF_COMPETITION_NAME, 'AF Lisboa I', 'Seniores', SEASON]);
        $stmt->execute(['juniores-afl',    'Camp. Distrital Juniores',   'Juniores AFL', 'Sub-19', SEASON]);
    }

    // Notícias
    if ((int)$pdo->query('SELECT COUNT(*) FROM news')->fetchColumn() === 0) {
        $f = __DIR__ . '/../data/news.json';
        if (file_exists($f)) {
            $items = json_decode(file_get_contents($f), true) ?? [];
            $stmt  = $pdo->prepare('INSERT OR IGNORE INTO news
                (slug,title,summary,content,cover_image,category,tags,published_at,author,is_published)
                VALUES (?,?,?,?,?,?,?,?,?,1)');
            foreach ($items as $n) {
                $stmt->execute([
                    $n['slug'], $n['title'], $n['summary'] ?? '',
                    $n['content'] ?? '', $n['cover_image'] ?? 'assets/images/hero_stadium.png',
                    $n['category'] ?? 'clube', json_encode($n['tags'] ?? []),
                    $n['published_at'], $n['author'] ?? 'Redação GDV',
                ]);
            }
        }
    }

    // Patrocinadores
    if ((int)$pdo->query('SELECT COUNT(*) FROM sponsors')->fetchColumn() === 0) {
        $f = __DIR__ . '/../data/sponsors.json';
        if (file_exists($f)) {
            $items = json_decode(file_get_contents($f), true) ?? [];
            $stmt  = $pdo->prepare('INSERT INTO sponsors (name,short,tier,active) VALUES (?,?,?,1)');
            foreach ($items as $s) $stmt->execute([$s['name'], $s['short'], $s['tier'] ?? 'silver']);
        }
    }
}

// ── Query helpers ─────────────────────────────────
function dbFetchAll(string $sql, array $p = []): array {
    $st = getDB()->prepare($sql); $st->execute($p); return $st->fetchAll();
}
function dbFetch(string $sql, array $p = []): ?array {
    $st = getDB()->prepare($sql); $st->execute($p);
    $r = $st->fetch(); return $r ?: null;
}
function dbExecute(string $sql, array $p = []): int {
    $db = getDB(); $db->prepare($sql)->execute($p);
    return (int)$db->lastInsertId();
}
function dbRun(string $sql, array $p = []): bool {
    return getDB()->prepare($sql)->execute($p);
}
function dbCount(string $sql, array $p = []): int {
    $st = getDB()->prepare($sql); $st->execute($p);
    return (int)$st->fetchColumn();
}
