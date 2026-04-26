<?php

function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

function ensure_csrf_token(): void
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

function csrf_token(): string
{
    start_secure_session();
    ensure_csrf_token();

    return $_SESSION['csrf_token'];
}

function verify_csrf_token(?string $token): bool
{
    start_secure_session();

    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function require_login(string $redirect = 'login.php'): void
{
    start_secure_session();
    ensure_csrf_token();

    if (!isset($_SESSION['user_id'])) {
        header("Location: {$redirect}");
        exit();
    }
}

function require_login_json(): void
{
    start_secure_session();
    ensure_csrf_token();

    if (!isset($_SESSION['user_id'])) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }
}

function current_user_role(): string
{
    start_secure_session();

    $role = strtolower(trim((string) ($_SESSION['role'] ?? 'user')));

    return $role === '' ? 'user' : $role;
}

function has_role(string ...$roles): bool
{
    $currentRole = current_user_role();
    $normalizedRoles = array_map(
        static fn(string $role): string => strtolower(trim($role)),
        $roles
    );

    return in_array($currentRole, $normalizedRoles, true);
}

function require_roles(array $roles, bool $json = false, string $redirect = 'dashboard.php', string $message = 'Access denied'): void
{
    if ($json) {
        require_login_json();
    } else {
        $loginRedirect = str_starts_with($redirect, '../') ? '../login.php' : 'login.php';
        require_login($loginRedirect);
    }

    $normalizedRoles = array_map(
        static fn(string $role): string => strtolower(trim($role)),
        $roles
    );

    if (!in_array(current_user_role(), $normalizedRoles, true)) {
        if ($json) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => $message]);
        } else {
            $_SESSION['response'] = [
                'success' => false,
                'message' => $message,
            ];
            header("Location: {$redirect}");
        }
        exit();
    }
}

function require_admin(bool $json = false, string $redirect = 'dashboard.php'): void
{
    require_roles(['admin'], $json, $redirect, $json ? 'Access denied' : 'Access denied - admin only');
}

function require_post_csrf(bool $json = false, ?string $redirect = null): void
{
    $token = $_POST['csrf_token'] ?? null;

    if (!verify_csrf_token($token)) {
        if ($json) {
            header('Content-Type: application/json');
            http_response_code(419);
            echo json_encode(['success' => false, 'message' => 'Invalid security token']);
        } else {
            $_SESSION['response'] = [
                'success' => false,
                'message' => 'Invalid security token',
            ];

            if ($redirect !== null) {
                header("Location: {$redirect}");
            }
        }
        exit();
    }
}

function require_json_csrf(array $payload): void
{
    $token = $payload['csrf_token'] ?? null;

    if (!verify_csrf_token(is_string($token) ? $token : null)) {
        http_response_code(419);
        die('Invalid security token');
    }
}
