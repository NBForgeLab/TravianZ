<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class LegacySecurityTest extends TestCase
{
    public function testRandomTokenLength(): void
    {
        $token = trz_random_token(16);
        $this->assertSame(32, strlen($token));
        $this->assertMatchesRegularExpression('/^[0-9a-f]+$/', $token);
    }

    public function testPasswordHashAndRehashCheck(): void
    {
        $hash = trz_password_hash('secret');
        $this->assertIsString($hash);
        $this->assertNotSame('', $hash);

        $this->assertFalse(trz_password_needs_rehash($hash));
    }

    public function testLegacyMd5FlagParsing(): void
    {
        $old = getenv('TRAVIANZ_ALLOW_LEGACY_MD5');

        putenv('TRAVIANZ_ALLOW_LEGACY_MD5=0');
        $this->assertFalse(trz_allow_legacy_md5_passwords());

        putenv('TRAVIANZ_ALLOW_LEGACY_MD5=yes');
        $this->assertTrue(trz_allow_legacy_md5_passwords());

        if ($old === false) {
            putenv('TRAVIANZ_ALLOW_LEGACY_MD5');
        } else {
            putenv('TRAVIANZ_ALLOW_LEGACY_MD5=' . $old);
        }
    }

    public function testCspNonceIsStablePerRequest(): void
    {
        $nonce1 = trz_csp_nonce();
        $nonce2 = trz_csp_nonce();
        $this->assertSame($nonce1, $nonce2);

        $attr = trz_csp_nonce_attr();
        $this->assertStringContainsString($nonce1, $attr);
        $this->assertStringStartsWith(' nonce="', $attr);
        $this->assertStringEndsWith('"', $attr);
    }
}

