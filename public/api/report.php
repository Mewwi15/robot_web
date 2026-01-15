<?php
declare(strict_types=1);
require __DIR__ . '/_robot.php';

require_method('POST');
require_robot_token();

$pdo = db();
$robotId = robot_id($pdo);

$body = get_json_body(); if (!$body) $body = $_POST;

$id = (string)($body['id'] ?? '');
$result = (string)($body['result'] ?? '');
$message = $body['message'] ?? null;

if ($id === '') respond_json(["ok" => false, "error" => "missing id"], 422);
if (!in_array($result, ["done","error"], true)) respond_json(["ok" => false, "error" => "result must be done|error"], 422);
if ($message !== null && !is_string($message)) $message = (string)$message;

$pdo->beginTransaction();
try {
  $st = $pdo->prepare('
    UPDATE commands
    SET status=?, message=?, reported_at=NOW()
    WHERE id=? AND robot_id=? AND status="sent"
  ');
  $st->execute([$result, $message, $id, $robotId]);

  if ($st->rowCount() === 0) {
    $pdo->rollBack();
    respond_json(["ok" => false, "error" => "command not found or not in sent state"], 409);
  }

  $newState = ($result === 'done') ? 'ARRIVED' : 'ERROR';
  $pdo->prepare('UPDATE robots SET state=?, active_command_id=NULL, updated_at=NOW() WHERE id=? AND active_command_id=?')
      ->execute([$newState, $robotId, $id]);

  $pdo->commit();
} catch (Throwable $e) {
  $pdo->rollBack();
  log_line("report error: " . $e->getMessage());
  respond_json(["ok" => false, "error" => "db error"], 500);
}

respond_json(["ok" => true]);
