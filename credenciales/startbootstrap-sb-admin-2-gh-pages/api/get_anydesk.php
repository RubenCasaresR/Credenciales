<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require '../includes/crypto.php';

try {
    $stmt = $pdo->query("SELECT id, name, ip_address, password, gmail_email, gmail_password, nordvpn, nordvpn_password, nord_dedicada_ip, anydesk_email, anydesk_email_password, notes FROM anydesk_accounts WHERE deleted_at IS NULL ORDER BY created_at DESC");
    $accounts = $stmt->fetchAll();
    
    foreach ($accounts as &$account) {
        $account['password_decrypted'] = decryptData($account['password']);
        $account['gmail_password_decrypted'] = decryptData($account['gmail_password']);
        $account['nordvpn_password_decrypted'] = decryptData($account['nordvpn_password']);
        $account['anydesk_email_password_decrypted'] = decryptData($account['anydesk_email_password']);
        unset($account['password']);
        unset($account['gmail_password']);
        unset($account['nordvpn_password']);
        unset($account['anydesk_email_password']);
    }
    
    echo json_encode(['success' => true, 'data' => $accounts]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>
