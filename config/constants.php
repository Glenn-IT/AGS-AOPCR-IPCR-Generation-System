<?php
// Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'csu_piat_aopcr_ipcr');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// App
define('APP_NAME', 'CSU-Piat AOPCR/IPCR System');
define('APP_VERSION', '2.0.0');
define('BASE_URL', 'http://localhost/AGS-AOPCR-IPCR-Generation-System/');

// Uploads
define('UPLOAD_PATH', dirname(__DIR__) . '/uploads/evidence/');
define('UPLOAD_URL', BASE_URL . 'uploads/evidence/');
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_EXTENSIONS', ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'xlsx']);
define('ALLOWED_MIME_TYPES', [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'image/jpeg',
    'image/png',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
]);

// Roles
define('ROLE_SUPERADMIN', 'superadmin');
define('ROLE_ADMIN', 'admin');
define('ROLE_USER', 'user');

// Login lockout
define('MAX_LOGIN_ATTEMPTS', 3);
define('LOCKOUT_SECONDS', 30);
