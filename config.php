<?php
// ========== POOL INDIA — GLOBAL CONFIG ==========
define('API_BASE',    'https://api.greencar.ngo/api');
define('APP_NAME',    'Pool India');
define('APP_VERSION', '1.0.0');
define('SALT',        '@exam@');

// Session config — must be called before session_start()
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.gc_maxlifetime', 86400); // 24 hours

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Helper: proxy POST to GreenCar API ──────────────────────────────────────
function apiPost(string $endpoint, array $body): array {
    $url = API_BASE . '/' . ltrim($endpoint, '/');
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($body),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $raw  = curl_exec($ch);
    $err  = curl_error($ch);
    curl_close($ch);
    if ($err) return ['__curl_error' => $err];
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : ['__raw' => $raw];
}

// ── Helper: proxy GET to GreenCar API ───────────────────────────────────────
function apiGet(string $endpoint): array {
    $url = API_BASE . '/' . ltrim($endpoint, '/');
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $raw = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) return ['__curl_error' => $err];
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : ['__raw' => $raw];
}

// ── Helper: check logged in ──────────────────────────────────────────────────
function isLoggedIn(): bool {
    return !empty($_SESSION['user']);
}

// ── Helper: require login, else redirect ────────────────────────────────────
function requireLogin(): void {
    if (!isLoggedIn()) {
        $redirect = urlencode($_SERVER['REQUEST_URI']);
        header("Location: login.php?redirect=$redirect");
        exit;
    }
}

// ── Helper: get current user ─────────────────────────────────────────────────
function currentUser(): array {
    return $_SESSION['user'] ?? [];
}

// ── Helper: JSON response for AJAX ──────────────────────────────────────────
function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// ── Helper: redirect ─────────────────────────────────────────────────────────
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

// ── Helper: sanitize output ──────────────────────────────────────────────────
function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
