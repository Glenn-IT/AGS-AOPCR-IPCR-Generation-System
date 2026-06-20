<?php
require_once __DIR__ . '/../../config/session.php';

$user = getSessionUser();
if ($user) {
    addLog($user['id'], 'Logged out');
}

clearSession();
header('Location: ' . BASE_URL . 'index.php');
exit;
