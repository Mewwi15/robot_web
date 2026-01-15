<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

try {
  $root = dirname(__DIR__, 2);
  require_once $root . '/private/_auth.php';
  require_once $root . '/private/_security.php';

  $body = read_json_body();

  $username = (string)($body['username'] ?? '');
  $password = (string)($body['password'] ?? '');

  // ถ้าคุณต้องการ “บังคับ CSRF ตอน login” ด้วยก็ทำได้
  // แต่โดยทั่วไป login มักยังไม่มี token ก็ได้
  // $csrf = (string)($body['csrf'] ?? '');
  // csrf_verify($csrf);

  $user = login_user($username, $password);

  // สร้าง csrf หลัง login (ดี)
  $token = csrf_token();

  json_ok([
    'user' => $user,
    'csrf' => $token,
  ]);

} catch (RuntimeException $e) {
  // errors ที่คาดไว้ ส่งกลับ 401/400 แบบไม่เป็น 500
  $msg = $e->getMessage();

  if ($msg === 'bad_json') json_fail('bad json', 400);
  if ($msg === 'missing_credentials') json_fail('missing credentials', 400);
  if ($msg === 'invalid_login') json_fail('invalid login', 401);

  // อื่นๆ ส่งเป็น 400 ไปก่อน
  json_fail($msg, 400);

} catch (Throwable $e) {
  fail_server($e, 'auth_login');
}
