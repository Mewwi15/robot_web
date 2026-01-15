<?php
declare(strict_types=1);
require __DIR__ . '/_auth.php';
require __DIR__ . '/_robot.php';

require_method('POST');
require_login();
require_csrf();
rate_limit('enqueue', 60, 60);

$pdo = db();
$body = get_json_body(); if (!$body) $body = $_POST;

$room = $body['room'] ?? null;
$room = is_numeric($room) ? (int)$room : 0;
if ($room < 1 || $room > 4) respond_json(["ok" => false, "error" => "room must be 1..4"], 422);

$est = (int)$pdo->query('SELECT enabled FROM estop WHERE id=1')->fetchColumn();
if ($est === 1) respond_json(["ok" => false, "error" => "E-STOP is enabled"], 409);

$robotId = robot_id($pdo);
$cmdId = uuidv4();

$pdo->beginTransaction();
try {
  $pdo->prepare('INSERT INTO commands (id, robot_id, room, status) VALUES (?, ?, ?, "queued")')
      ->execute([$cmdId, $robotId, $room]);

  $pdo->prepare('UPDATE robots SET state="QUEUED", last_target=?, updated_at=NOW() WHERE id=?')
      ->execute([$room, $robotId]);

  $pdo->commit();
} catch (Throwable $e) {
  $pdo->rollBack();
  log_line("enqueue error: " . $e->getMessage());
  respond_json(["ok" => false, "error" => "db error"], 500);
}

respond_json(["ok" => true, "command" => ["id" => $cmdId, "room" => $room]]);
