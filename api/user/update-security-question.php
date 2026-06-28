<?php
require_once '../../config/session.php';
header('Content-Type: application/json');

$user  = requireAuth();
$input = json_decode(file_get_contents('php://input'), true) ?? [];

$currentAnswer   = trim($input['current_answer'] ?? '');
$newQuestion     = trim($input['security_question'] ?? '');
$newAnswer       = trim($input['new_answer'] ?? '');
$confirmAnswer   = trim($input['confirm_answer'] ?? '');

$allowedQuestions = [
    "What is your mother's maiden name?",
    "What city were you born in?",
    "What is your pet's name?",
    "What was the name of your first school?",
    "What is your favorite book?",
];

if (!$currentAnswer || !$newQuestion || !$newAnswer || !$confirmAnswer) {
    echo json_encode(['success' => false, 'error' => 'All fields are required.']);
    exit;
}

if (!in_array($newQuestion, $allowedQuestions, true)) {
    echo json_encode(['success' => false, 'error' => 'Invalid security question selected.']);
    exit;
}

if ($newAnswer !== $confirmAnswer) {
    echo json_encode(['success' => false, 'error' => 'New answers do not match.']);
    exit;
}

if (strlen($newAnswer) < 2) {
    echo json_encode(['success' => false, 'error' => 'Security answer must be at least 2 characters.']);
    exit;
}

$db   = getDB();
$stmt = $db->prepare('SELECT security_answer FROM users WHERE id = ?');
$stmt->execute([$user['id']]);
$row  = $stmt->fetch();

if (!$row || !password_verify(strtolower($currentAnswer), $row['security_answer'])) {
    echo json_encode(['success' => false, 'error' => 'Current security answer is incorrect.']);
    exit;
}

$hashedAnswer = password_hash(strtolower($newAnswer), PASSWORD_BCRYPT);
$upd = $db->prepare('UPDATE users SET security_question = ?, security_answer = ? WHERE id = ?');
$upd->execute([$newQuestion, $hashedAnswer, $user['id']]);

addLog($user['id'], 'Updated security question');
echo json_encode(['success' => true]);
