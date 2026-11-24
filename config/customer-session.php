<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isCustomerLoggedIn(): bool
{
    return !empty($_SESSION['customer_id']);
}

function getCustomerId(): ?int
{
    return isCustomerLoggedIn()
        ? (int) $_SESSION['customer_id']
        : null;
}

function getCustomerName(): string
{
    return $_SESSION['customer_name'] ?? '';
}

function getCustomerEmail(): string
{
    return $_SESSION['customer_email'] ?? '';
}

function requireCustomerLogin(): void
{
    if (!isCustomerLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function getCustomerLogoutToken(): string
{
    if (empty($_SESSION['customer_logout_token'])) {
        $_SESSION['customer_logout_token'] =
            bin2hex(random_bytes(32));
    }

    return $_SESSION['customer_logout_token'];
}