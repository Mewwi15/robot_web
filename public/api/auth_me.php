<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

try {
  $root = dirname(__DIR__, 2);
  require_once $root . '/private/_auth.php';
  require_once $root . '/private/_security.php';

  // user อาจเป็น null ถ้ายังไม่ล็อกอิน (ตามที่คุณต้องการ)
  json_ok([
    'user' => current_user(),
    'csrf' => csrf_token(),
  ]);

} catch (Throwable $e) {
  fail_server($e, 'auth_me');
}
