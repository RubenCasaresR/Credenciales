<?php
require_once __DIR__ . '/../config.php';

function encryptPaymentData($data) {
    if (empty($data)) return $data;
    $key = hex2bin(PAYMENT_ENCRYPTION_KEY);
    if (strlen($key) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
        $key = hash('sha256', PAYMENT_ENCRYPTION_KEY, true);
    }
    $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
    $ciphertext = sodium_crypto_secretbox($data, $nonce, $key);
    sodium_memzero($key);
    return base64_encode($nonce . $ciphertext);
}

function decryptPaymentData($data) {
    if (empty($data)) return $data;
    $key = hex2bin(PAYMENT_ENCRYPTION_KEY);
    if (strlen($key) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
        $key = hash('sha256', PAYMENT_ENCRYPTION_KEY, true);
    }
    $decoded = base64_decode($data);
    if ($decoded === false || strlen($decoded) < SODIUM_CRYPTO_SECRETBOX_NONCEBYTES) {
        sodium_memzero($key);
        return '';
    }
    $nonce = substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
    $ciphertext = substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
    $plaintext = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);
    sodium_memzero($key);
    return $plaintext !== false ? $plaintext : '';
}
