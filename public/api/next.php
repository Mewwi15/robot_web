<?php
declare(strict_types=1);
require __DIR__ . '/_robot.php';

require_method('POST');
require_robot_token();

$pdo = db();
$robotId = robot_id($pdo);

$est = (int)$pdo->query('SELECT enabled FROM estop WHERE id=1')->fetchColumn();
if ($est === 1) respond_json(["ok" => true, "command" => null, "message" => "estop"]);

$pdo->beginTransaction();
try {
  $st = $pdo->prepare('
    SELECT id, room
    FROM commands
    WHERE robot_id=? AND status="queued"
    ORDER BY created_at ASC
    LIMIT 1
    FOR UPDATE
  ');
  $st->execute([$robotId]);
  $cmd = $st->fetch();

  if (!$cmd) {
    $pdo->commit();
    respond_json(["ok" => true, "command" => null]);
  }

  $pdo->prepare('UPDATE commands SET status="sent", sent_at=NOW() WHERE id=? AND status="queued"')
      ->execute([$cmd['id']]);

  $pdo->prepare('UPDATE robots SET state="MOVING", active_command_id=?, last_target=?, updated_at=NOW() WHERE id=?')
      ->execute([$cmd['id'], (int)$cmd['room'], $robotId]);

  $pdo->commit();
} catch (Throwable $e) {
  $pdo->rollBack();
  log_line("next error: " . $e->getMessage());
  respond_json(["ok" => false, "error" => "db error"], 500);
}

respond_json(["ok" => true, "command" => ["id" => $cmd["id"], "room" => (int)$cmd["room"]]]);
