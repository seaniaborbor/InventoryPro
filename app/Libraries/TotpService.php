<?php

namespace App\Libraries;

class TotpService
{
    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public function generateSecret(int $length = 32): string
    {
        $secret = '';
        $bytes = random_bytes($length);

        for ($index = 0; $index < $length; $index++) {
            $secret .= self::BASE32_ALPHABET[ord($bytes[$index]) % 32];
        }

        return $secret;
    }

    public function verifyCode(string $secret, string $code, int $window = 1, int $timestamp = null): bool
    {
        $timestamp ??= time();
        $code = preg_replace('/\D/', '', $code);

        if ($code === null || strlen($code) !== 6) {
            return false;
        }

        for ($offset = -$window; $offset <= $window; $offset++) {
            if (hash_equals($this->getCode($secret, $timestamp + ($offset * 30)), $code)) {
                return true;
            }
        }

        return false;
    }

    public function getProvisioningUri(string $label, string $secret, string $issuer): string
    {
        return 'otpauth://totp/' . rawurlencode($issuer . ':' . $label)
            . '?secret=' . rawurlencode($secret)
            . '&issuer=' . rawurlencode($issuer)
            . '&algorithm=SHA1&digits=6&period=30';
    }

    private function getCode(string $secret, int $timestamp): string
    {
        $counter = pack('N*', 0) . pack('N*', floor($timestamp / 30));
        $hash = hash_hmac('sha1', $counter, $this->base32Decode($secret), true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $truncatedHash = substr($hash, $offset, 4);
        $value = unpack('N', $truncatedHash)[1] & 0x7FFFFFFF;

        return str_pad((string) ($value % 1000000), 6, '0', STR_PAD_LEFT);
    }

    private function base32Decode(string $secret): string
    {
        $secret = strtoupper(preg_replace('/[^A-Z2-7]/', '', $secret) ?? '');
        $buffer = 0;
        $bitsLeft = 0;
        $decoded = '';

        foreach (str_split($secret) as $character) {
            $value = strpos(self::BASE32_ALPHABET, $character);
            if ($value === false) {
                continue;
            }

            $buffer = ($buffer << 5) | $value;
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $decoded .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }

        return $decoded;
    }
}
