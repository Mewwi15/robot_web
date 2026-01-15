<?php
declare(strict_types=1);
require __DIR__ . '/_auth.php';

require_method('POST');
require_role('admin');
require_csrf();

$pdo = db();
$body = get_json_body(); if (!$body) $body = $_POST;

$username = trim((string)($body['username'] ?? ''));
$password = (string)($body['password'] ?? '');
$role = (string)($body['role'] ?? 'viewer');

if ($username === '' || strlen($username) > 50) respond_json(["ok" => false, "error" => "bad username"], 422);
if (strlen($password) < 8) respond_json(["ok" => false, "error" => "password min 8 chars"], 422);
if (!in_array($role, ['admin','viewer'], true)) respond_json(["ok" => false, "error" => "bad role"], 422);

$hash = password_hash($password, PASSWORD_DEFAULT);
try {
  $st = $pdo->prepare('INSERT INTO users (username, password_hash, role) VALUES (?,?,?)');
  $st->execute([$username, $hash, $role]);
} catch (Throwable $e) {
  respond_json(["ok" => false, "error" => "create failed (username maybe exists)"], 409);
}

respond_json(["ok" => true]);
