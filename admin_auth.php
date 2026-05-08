<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_admin_logged_in(): bool
{
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function require_admin_login(): void
{
    if (!is_admin_logged_in()) {
        header('Location: admin_login.php');
        exit();
    }
}

function authenticate_admin(mysqli $conn, string $username, string $password): ?array
{
    $username = $conn->real_escape_string($username);
    $password = $conn->real_escape_string($password);

    $result = $conn->query("SELECT * FROM admin_users WHERE username = '$username' AND password = '$password' AND is_active = 1 LIMIT 1");

    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }

    return null;
}
