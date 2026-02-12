<?php

function trz_random_token(int $bytes = 32): string
{
    return bin2hex(random_bytes($bytes));
}

function trz_password_algo(): string|int
{
    if (defined('PASSWORD_ARGON2ID')) {
        return PASSWORD_ARGON2ID;
    }

    return PASSWORD_DEFAULT;
}

function trz_password_options(string|int $algo): array
{
    if (defined('PASSWORD_BCRYPT') && $algo === PASSWORD_BCRYPT) {
        return ['cost' => 12];
    }

    return [];
}

function trz_password_hash(string $password): string
{
    $algo = trz_password_algo();
    return password_hash($password, $algo, trz_password_options($algo));
}

function trz_password_needs_rehash(string $hash): bool
{
    $algo = trz_password_algo();
    return password_needs_rehash($hash, $algo, trz_password_options($algo));
}

function trz_allow_legacy_md5_passwords(): bool
{
    $v = getenv('TRAVIANZ_ALLOW_LEGACY_MD5');
    if ($v === false) {
        return true;
    }
    $v = strtolower(trim((string) $v));
    return $v === '1' || $v === 'true' || $v === 'yes' || $v === 'on';
}

function trz_csp_nonce(): string
{
    static $nonce = null;
    if (!is_string($nonce)) {
        $raw = random_bytes(16);
        $nonce = rtrim(base64_encode($raw), '=');
    }
    return $nonce;
}

function trz_csp_nonce_attr(): string
{
    $nonce = trz_csp_nonce();
    return ' nonce="' . htmlspecialchars($nonce, ENT_QUOTES, 'UTF-8') . '"';
}
