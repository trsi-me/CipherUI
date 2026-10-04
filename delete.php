<?php
/**
 * CipherUI - حذف البيانات المشفرة
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

requireLogin();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'طريقة غير مسموحة']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$id = (int)($input['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'معرف غير صالح']);
    exit;
}

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare('DELETE FROM encrypted_data WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, getCurrentUserId()]);
    
    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'البيانات غير موجودة أو غير مصرح لك بالحذف']);
        exit;
    }
    
    echo json_encode(['success' => true, 'message' => 'تم الحذف بنجاح']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء الحذف']);
}
