<?php
require_once '../../config/session.php';
$user = requireAuth(['admin']);
header('Location: ipcr-form.php');
exit;
