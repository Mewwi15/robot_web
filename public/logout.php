<?php
declare(strict_types=1);

session_start();

// ล้าง session data
$_SESSION = [];

// ลบ cookie session
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        [
            'expires'  => time() - 3600,
            'path'     => $params['path'] ?? '/',
            'domain'   => $params['domain'] ?? '',
            'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'httponly' => true,
            'samesite' => 'Lax',
        ]
    );
}

// ทำลาย session ฝั่ง server
session_destroy();

// ไปหน้า login ทันที
header('Location: /robot_web/public/login.php');
exit;
