<?php
/**
 * CipherUI - تشفير البيانات وحفظها
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/crypto.php';

requireLogin();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'طريقة غير مسموحة']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$plainText = $input['plain_text'] ?? $input['data'] ?? '';

if (empty(trim($plainText))) {
    echo json_encode(['success' => false, 'message' => 'النص فارغ']);
    exit;
}

$result = encryptData($plainText);
if ($result === false) {
    echo json_encode(['success' => false, 'message' => 'فشل التشفير']);
    exit;
}

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare('INSERT INTO encrypted_data (user_id, data, iv, created_at) VALUES (?, ?, ?, NOW())');
    $stmt->execute([getCurrentUserId(), $result['encrypted'], $result['iv']]);
    $id = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'تم التشفير والحفظ بنجاح',
        'encrypted' => $result['encrypted'],
        'id' => (int)$id
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'فشل حفظ البيانات']);
}
?>
