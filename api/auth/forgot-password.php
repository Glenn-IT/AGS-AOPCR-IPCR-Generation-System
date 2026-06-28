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

    // Do NOT reveal the security question to the client
    echo json_encode(['success' => true]);
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

    $question = trim($input['question'] ?? '');
    $answer   = strtolower(trim($input['answer'] ?? ''));

    if (!$question || !$answer) {
        echo json_encode(['success' => false, 'error' => 'Please select a question and enter your answer.']);
        exit;
    }

    $stmt = $db->prepare('SELECT security_question, security_answer FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$_SESSION['fp_username']]);
    $row = $stmt->fetch();

    // Verify both question and answer — use same generic message to avoid revealing which is wrong
    if (!$row
        || $row['security_question'] !== $question
        || !password_verify($answer, $row['security_answer'])
    ) {
        echo json_encode(['success' => false, 'error' => 'Incorrect security question or answer. Please try again.']);
        exit;
    }

    $_SESSION['fp_verified'] = true;
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid action.']);
