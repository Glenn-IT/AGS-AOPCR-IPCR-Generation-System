<?php
require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Strict']);
    session_start();
}

function requireAuth(array $roles = []): array {
    $isApi = str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/');

    if (!isset($_SESSION['user'])) {
        if ($isApi) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Session expired. Please log in again.']);
            exit;
        }
        header('Location: ' . getBasePath() . 'index.php');
        exit;
    }
    if (!empty($roles) && !in_array($_SESSION['user']['role'], $roles, true)) {
        if ($isApi) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Access denied.']);
            exit;
        }
        redirectByRole($_SESSION['user']['role']);
        exit;
    }
    return $_SESSION['user'];
}

function getSessionUser(): ?array {
    return $_SESSION['user'] ?? null;
}

function setSessionUser(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user'] = $user;
}

function clearSession(): void {
    session_unset();
    session_destroy();
}

function isLoggedIn(): bool {
    return isset($_SESSION['user']);
}

function redirectByRole(string $role): void {
    $base = getBasePath();
    $map = [
        ROLE_SUPERADMIN => 'views/superadmin/dashboard.php',
        ROLE_ADMIN      => 'views/admin/dashboard.php',
        ROLE_USER       => 'views/users/dashboard.php',
    ];
    header('Location: ' . $base . ($map[$role] ?? 'index.php'));
    exit;
}

function getBasePath(): string {
    $self = $_SERVER['PHP_SELF'] ?? '';
    if (str_contains($self, '/views/superadmin/')) return '../../';
    if (str_contains($self, '/views/admin/'))      return '../../';
    if (str_contains($self, '/views/users/'))      return '../../';
    if (str_contains($self, '/api/auth/'))         return '../../';
    if (str_contains($self, '/api/'))              return '../../';
    return '';
}

function addLog(int $userId, string $activity): void {
    try {
        $db = getDB();
        $stmt = $db->prepare(
            'INSERT INTO activity_logs (user_id, activity, ip_address) VALUES (?, ?, ?)'
        );
        $stmt->execute([$userId, $activity, $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
    } catch (Exception $e) {
        // Log silently — never break the app for a logging failure
    }
}

function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
