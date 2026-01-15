<?php
declare(strict_types=1);

/**
 * Bootstrap สำหรับ API ทุกไฟล์
 * - start session
 * - JSON response helpers
 * - error logging พร้อม error_id
 */

session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function json_ok(array $data = [], int $code = 200): never {
  http_response_code($code);
  echo json_encode(['ok' => true] + $data, JSON_UNESCAPED_UNICODE);
  exit;
}

function json_fail(string $error, int $code = 400, array $extra = []): never {
  http_response_code($code);
  echo json_encode(['ok' => false, 'error' => $error] + $extra, JSON_UNESCAPED_UNICODE);
  exit;
}

/**
 * log server error แบบมี error_id ให้ไปไล่ใน Apache error.log
 */
function fail_server(Throwable $e, string $where = 'api'): never {
  $errorId = substr(bin2hex(random_bytes(8)), 0, 12);
  error_log("[$where][$errorId] " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
  json_fail('server error', 500, ['error_id' => $errorId]);
}
