<?php
require_once __DIR__ . '/../../config/session.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

$input  = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$action = $input['action'] ?? '';
$db     = getDB();

// ------------------------------------------------------------------
// Step 1: verify username — return security question
// ------------------------------------------------------------------
if ($action === 'verify_username') {
    $username = trim($input['username'] ?? '');
    if (!$username) {
        echo json_encode(['success' => false, 'error' => 'Please enter your username.']);
        exit;
    }

    $stmt = $db->prepare('SELECT id, security_question FROM users WHERE username = ? AND status = ? LIMIT 1');
    $stmt->execute([$username, 'active']);
    $user = $stmt->fetch();

    if (!$user) {
        // Generic message — don't reveal whether username exists
        echo json_encode(['success' => false, 'error' => 'Username not found or account is not active.']);
        exit;
    }

    // Store in session — never send user ID to the client between steps
    $_SESSION['fp_username'] = $username;
    unset($_SESSION['fp_verified']);

    echo json_encode([
        'success'           => true,
        'security_question' => $user['security_question'],
    ]);
    exit;
}

// ------------------------------------------------------------------
// Step 2: verify security answer
// ------------------------------------------------------------------
if ($action === 'verify_answer') {
    if (empty($_SESSION['fp_username'])) {
        echo json_encode(['success' => false, 'error' => 'Session expired. Please start over.']);
        exit;
    }

    $answer = strtolower(trim($input['answer'] ?? ''));
    if (!$answer) {
        echo json_encode(['success' => false, 'error' => 'Please enter your answer.']);
        exit;
    }

    $stmt = $db->prepare('SELECT security_answer FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$_SESSION['fp_username']]);
    $row = $stmt->fetch();

    if (!$row || !password_verify($answer, $row['security_answer'])) {
        echo json_encode(['success' => false, 'error' => 'Incorrect answer. Please try again.']);
        exit;
    }

    $_SESSION['fp_verified'] = true;
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid action.']);
