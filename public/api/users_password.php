<?php
declare(strict_types=1);
require __DIR__ . '/_auth.php';

require_method('POST');
$me = require_login();
require_csrf();

$pdo = db();
$body = get_json_body(); if (!$body) $body = $_POST;

$userId = (int)($body['user_id'] ?? $me['id']);
$newPass = (string)($body['new_password'] ?? '');

if (strlen($newPass) < 8) respond_json(["ok" => false, "error" => "password min 8 chars"], 422);

// ถ้าไม่ใช่ admin ห้ามเปลี่ยนของคนอื่น
if ($userId !== (int)$me['id'] && ($me['role'] ?? '') !== 'admin') {
  respond_json(["ok" => false, "error" => "forbidden"], 403);
}

$hash = password_hash($newPass, PASSWORD_DEFAULT);
$st = $pdo->prepare('UPDATE users SET password_hash=? WHERE id=?');
$st->execute([$hash, $userId]);

respond_json(["ok" => true]);
