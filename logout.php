<?php

// ==========================================
// SHANU HAVEN - CUSTOMER LOGOUT
// ==========================================

require_once __DIR__ . '/config/customer-session.php';

// Allow POST requests only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    http_response_code(405);
    exit('Method not allowed.');

}

// Validate logout security token
$token = $_POST['csrf_token'] ?? '';

if (
    !is_string($token) ||
    empty($_SESSION['customer_logout_token']) ||
    !hash_equals(
        $_SESSION['customer_logout_token'],
        $token
    )
) {

    http_response_code(403);
    exit('Invalid logout request.');

}

// Remove customer session data only
unset(
    $_SESSION['customer_id'],
    $_SESSION['customer_name'],
    $_SESSION['customer_email'],
    $_SESSION['customer_logout_token'],
    $_SESSION['login_token']
);

// Regenerate session ID
session_regenerate_id(true);

// Redirect to home page
header('Location: index.php');
exit;