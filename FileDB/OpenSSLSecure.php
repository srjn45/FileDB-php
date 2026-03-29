<?php

require_once("Secure.php");

class OpenSSLSecure implements Secure {

    private static $cipher = 'AES-256-CBC';

    public static function getKey(): string {
        $raw = getenv('FILEDB_SECRET_KEY') ?: 'yourSecretKeyLen';
        // Normalize any string to exactly 32 bytes for AES-256
        return substr(hash('sha256', $raw, true), 0, 32);
    }

    private function safe_b64encode(string $string): string {
        $data = base64_encode($string);
        $data = str_replace(['+', '/', '='], ['-', '_', ''], $data);
        return $data;
    }

    private function safe_b64decode(string $string): string {
        $data = str_replace(['-', '_'], ['+', '/'], $string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }

    public function encode($value) {
        if (!$value) { return false; }
        $key   = self::getKey();
        $ivLen = openssl_cipher_iv_length(self::$cipher);
        $iv    = random_bytes($ivLen);
        $encrypted = openssl_encrypt($value, self::$cipher, $key, OPENSSL_RAW_DATA, $iv);
        if ($encrypted === false) { return false; }
        // Prepend IV to ciphertext so decode() can extract it
        return $this->safe_b64encode($iv . $encrypted);
    }

    public function decode($value) {
        if (!$value) { return false; }
        $raw   = $this->safe_b64decode($value);
        $ivLen = openssl_cipher_iv_length(self::$cipher);
        if (strlen($raw) <= $ivLen) { return false; }
        $iv         = substr($raw, 0, $ivLen);
        $ciphertext = substr($raw, $ivLen);
        $decrypted  = openssl_decrypt($ciphertext, self::$cipher, self::getKey(), OPENSSL_RAW_DATA, $iv);
        return $decrypted === false ? false : $decrypted;
    }
}
