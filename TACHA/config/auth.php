<?php
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Africa/Douala');

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function format_fcfa(int $amount): string
{
    return number_format($amount, 0, ',', ' ') . ' FCFA';
}

function sanitize_redirect(string $redirect, string $fallback = 'index.php'): string
{
    $redirect = trim($redirect);
    if ($redirect === '') {
        return $fallback;
    }

    if (preg_match('/[\r\n]/', $redirect)) {
        return $fallback;
    }

    $parts = parse_url($redirect);
    if ($parts === false) {
        return $fallback;
    }

    // Refuse absolute/external redirects.
    if (isset($parts['scheme']) || isset($parts['host'])) {
        return $fallback;
    }

    if (str_starts_with($redirect, '//')) {
        return $fallback;
    }

    if (str_starts_with($redirect, '/')) {
        $redirect = ltrim($redirect, '/');
    }

    if (!preg_match('#^[A-Za-z0-9_\-./?=&%]+$#', $redirect)) {
        return $fallback;
    }

    return $redirect;
}

function app_prefix(): string
{
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    if (strpos($script, '/org/') !== false || strpos($script, '/admin/') !== false) {
        return '../';
    }
    return '';
}

function app_base_url(): string
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    $path = preg_replace('#/(org|admin)$#', '', $path);

    return $https . '://' . $host . ($path ?: '');
}

function image_path(string $filename, string $fallback = 'Baniere_accuil.png'): string
{
    static $cache = null;

    if ($cache === null) {
        $cache = [];
        $dir = __DIR__ . '/../img';
        if (is_dir($dir)) {
            foreach (scandir($dir) as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $cache[strtolower($file)] = $file;
            }
        }
    }

    $name = strtolower($filename);
    if (isset($cache[$name])) {
        return 'img/' . $cache[$name];
    }

    $fallbackKey = strtolower($fallback);
    if (isset($cache[$fallbackKey])) {
        return 'img/' . $cache[$fallbackKey];
    }

    return 'img/' . $fallback;
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token(?string $token): bool
{
    return !empty($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function get_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

function current_user(): ?array
{
    if (!is_logged_in()) {
        return null;
    }

    static $cachedUser = null;
    if ($cachedUser && (int) $cachedUser['id'] === (int) $_SESSION['user_id']) {
        return $cachedUser;
    }

    $stmt = db()->prepare('SELECT id, name, phone, email, role, shop_name, shop_city, shop_phone, created_at FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([(int) $_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        logout_user();
        return null;
    }

    $cachedUser = $user;
    return $cachedUser;
}

function login_user(string $email, string $password): bool
{
    $stmt = db()->prepare('SELECT id, email, password_hash FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return false;
    }

    $_SESSION['user_id'] = (int) $user['id'];
    session_regenerate_id(true);
    return true;
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function require_login(): void
{
    if (!is_logged_in()) {
        $current = $_SERVER['REQUEST_URI'] ?? (app_prefix() . 'index.php');
        header('Location: ' . app_prefix() . 'login.php?redirect=' . urlencode($current));
        exit;
    }
}

function require_role(string $role): void
{
    if (!is_logged_in()) {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $loginPath = app_prefix() . 'login.php';
        if (strpos($script, '/org/') !== false && $role === 'organizer') {
            $loginPath = 'login.php';
        }
        if (strpos($script, '/admin/') !== false && $role === 'admin') {
            $loginPath = 'login.php';
        }
        $current = $_SERVER['REQUEST_URI'] ?? (app_prefix() . 'index.php');
        header('Location: ' . $loginPath . '?redirect=' . urlencode($current));
        exit;
    }

    $user = current_user();
    if (!$user || $user['role'] !== $role) {
        set_flash('danger', 'Acces refuse: role insuffisant.');
        header('Location: ' . app_prefix() . 'index.php');
        exit;
    }
}

function generate_ticket_token(): string
{
    do {
        $token = 'TCH-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(3)));
        $stmt = db()->prepare('SELECT id FROM tickets WHERE token = ? LIMIT 1');
        $stmt->execute([$token]);
    } while ($stmt->fetch());

    return $token;
}

function ticket_holder_name(array $ticket): string
{
    $first = trim((string) ($ticket['buyer_firstname'] ?? ''));
    $last = trim((string) ($ticket['buyer_lastname'] ?? ''));
    $full = trim($first . ' ' . $last);

    if ($full !== '') {
        return $full;
    }

    if (!empty($ticket['participant'])) {
        return (string) $ticket['participant'];
    }

    if (!empty($ticket['holder_name'])) {
        return (string) $ticket['holder_name'];
    }

    return 'Acheteur';
}
?>

