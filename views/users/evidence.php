<?php
require_once '../../config/session.php';
$user = requireAuth(['user']);
header('Location: ipcr-form.php');
exit;
