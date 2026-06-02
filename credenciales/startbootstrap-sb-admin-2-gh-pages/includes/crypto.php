<?php
require_once __DIR__ . '/../config.php';

function encryptData($data) {
    if (empty($data)) return $data;
    $method = 'AES-256-CBC';
    $key = hash('sha256', ENCRYPTION_KEY);
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
    $encrypted = openssl_encrypt($data, $method, $key, 0, $iv);
    return base64_encode($iv . $encrypted);
}

function decryptData($data) {
    if (empty($data)) return $data;
    $method = 'AES-256-CBC';
    $key = hash('sha256', ENCRYPTION_KEY);
    $data = base64_decode($data);
    $ivLength = openssl_cipher_iv_length($method);
    
    // Check if data is valid before decrypting
    if (strlen($data) <= $ivLength) return '';
    
    $iv = substr($data, 0, $ivLength);
    $encrypted = substr($data, $ivLength);
    $decrypted = openssl_decrypt($encrypted, $method, $key, 0, $iv);
    return $decrypted !== false ? $decrypted : '';
}
?>
