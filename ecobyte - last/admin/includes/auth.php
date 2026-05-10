<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Entrée directe au backoffice (bypass login)
if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'admin';
}
