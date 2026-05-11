<?php
declare(strict_types=1);

// Système de connexion désactivé comme demandé.
// L'accès au back-office est maintenant direct.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$_SESSION['role'] = 'admin';
