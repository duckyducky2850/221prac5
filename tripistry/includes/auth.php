<?php
/**
 * includes/auth.php
 * -----------------
 * Session helpers and access-control guards.
 * Call require_login() or require_role() at the top of any protected page.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,   // XSS mitigation
        'cookie_samesite' => 'Strict',
        'use_strict_mode' => true,   // prevent session fixation
    ]);
}

/** Returns true if a user is logged in */
function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

/** Returns the logged-in user's role ('traveller' | 'agency') or null */
function current_role(): ?string {
    return $_SESSION['role'] ?? null;
}

/** Returns the logged-in user's ID or null */
function current_user_id(): ?int {
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

/** Redirects to login if not logged in */
function require_login(): void {
    if (!is_logged_in()) {
        header('Location: ' . BASE_URL . '/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

/** Redirects to login / home if wrong role */
function require_role(string $role): void {
    require_login();
    if (current_role() !== $role) {
        header('Location: ' . BASE_URL . '/index.php?error=access_denied');
        exit;
    }
}

/**
 * Generates (or returns existing) CSRF token for the session.
 * Use csrf_field() in every form, validate with verify_csrf() on POST.
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function verify_csrf(): bool {
    $token = $_POST['csrf_token'] ?? '';
    return hash_equals(csrf_token(), $token);
}

/** Sanitise output — always use this when echoing user-supplied data */
function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Flash message helpers */
function set_flash(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function get_flash(): ?array {
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}
