<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

try {
  $root = dirname(__DIR__, 2);
  require_once $root . '/private/_auth.php';
  require_once $root . '/private/_security.php';

  // ถ้าจะ “กัน CSRF ตอน logout” แนะนำให้เปิดใช้งาน:
  // $body = read_json_body();
  // csrf_verify((string)($body['csrf'] ?? ''));

  logout_user();
  json_ok();

} catch (Throwable $e) {
  fail_server($e, 'auth_logout');
}
