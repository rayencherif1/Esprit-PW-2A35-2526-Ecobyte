<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Entrée directe au backoffice (dashboard)
header('Location: ../view/back%20office/argon-dashboard-tailwind-1.0.1/argon-dashboard-tailwind-1.0.1/build/index.html');
exit;
