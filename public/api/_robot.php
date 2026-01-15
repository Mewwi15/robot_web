<?php
declare(strict_types=1);
require_once __DIR__ . '/_db.php';

function require_robot_token(): void {
  $token = $_SERVER['HTTP_X_ROBOT_TOKEN'] ?? '';
  if (!hash_equals(ROBOT_TOKEN, $token)) {
    respond_json(["ok" => false, "error" => "unauthorized"], 401);
  }
}

function robot_id(PDO $pdo, string $name = DEFAULT_ROBOT_NAME): int {
  $st = $pdo->prepare('SELECT id FROM robots WHERE name=? LIMIT 1');
  $st->execute([$name]);
  $id = $st->fetchColumn();
  if ($id) return (int)$id;

  $ins = $pdo->prepare('INSERT INTO robots (name) VALUES (?)');
  $ins->execute([$name]);
  return (int)$pdo->lastInsertId();
}

function uuidv4(): string {
  $data = random_bytes(16);
  $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
  $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
  return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}
