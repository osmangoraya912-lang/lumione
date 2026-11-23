<?php
/**
 * LumiCore One - Shared helpers
 * Output escaping, money formatting (USD), CSRF protection, auth guards,
 * flash messages and the option lists used across the modules.
 */
require_once __DIR__ . '/db.php';

/* ---------- Output / formatting ---------- */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function money($amount): string
{
    return CURRENCY . number_format((float) $amount, 2);
}

function today(): string
{
    return date('Y-m-d');
}

/* ---------- Flash messages ---------- */
function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_show(): void
{
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="alert alert-' . e($f['type']) . ' alert-dismissible fade show" role="alert">'
            . e($f['message'])
            . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    }
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/* ---------- CSRF ---------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $sent = $_POST['csrf'] ?? '';
    if (!is_string($sent) || !hash_equals($_SESSION['csrf'] ?? '', $sent)) {
        http_response_code(403);
        exit('Invalid request. Please go back and try again.');
    }
}

/* ---------- Auth (single user) ---------- */
function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $stmt = db()->prepare('SELECT id, name, email FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        flash('warning', 'Please log in first.');
        redirect('login.php');
    }
    return $user;
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
}

/* ---------- Option lists ---------- */
function stores(): array
{
    return ['goraya244', 'mangoraya', 'WPBay', 'Other'];
}

/** Income platforms used by Sales + Income pages (must match exactly). */
function income_platforms(): array
{
    return ['Gumroad goraya244', 'Gumroad mangoraya', 'WPBay', 'Fiverr', 'Stock images', 'Other'];
}

function post_platforms(): array
{
    return ['Threads', 'Bluesky', 'Pinterest', 'Instagram', 'YouTube'];
}

function link_categories(): array
{
    return ['Store', 'Social', 'Work', 'Learning'];
}

function priorities(): array
{
    return ['High', 'Medium', 'Low'];
}

function priority_badge(string $priority): string
{
    $map = ['High' => 'danger', 'Medium' => 'warning', 'Low' => 'secondary'];
    $class = $map[$priority] ?? 'secondary';
    return '<span class="badge bg-' . $class . '">' . e($priority) . '</span>';
}

function status_badge(string $status): string
{
    $map = ['Live' => 'success', 'Draft' => 'secondary', 'Planned' => 'warning', 'Posted' => 'success'];
    $class = $map[$status] ?? 'secondary';
    return '<span class="badge bg-' . $class . '">' . e($status) . '</span>';
}
