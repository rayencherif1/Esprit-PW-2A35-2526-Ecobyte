<?php
/**
 * Session navigateur : jeton stable pour rattacher les programmes « perso » (sans compte).
 */

declare(strict_types=1);

final class AppSession
{
    private const SESSION_KEY = 'nf_owner_prog';

    public static function ensureStarted(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function userProgramOwnerToken(): string
    {
        self::ensureStarted();
        if (empty($_SESSION[self::SESSION_KEY]) || !is_string($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return (string) $_SESSION[self::SESSION_KEY];
    }
}
