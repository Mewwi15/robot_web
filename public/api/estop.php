<?php
declare(strict_types=1);
require __DIR__ . '/_auth.php';
require __DIR__ . '/_robot.php';

require_method('POST');
require_login();
require_csrf();

$pdo = db();
$body = get_json_body(); if (!$body) $body = $_POST;

$enabled = $body['enabled'] ?? null;
$enabled = filter_var($enabled, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
if ($enabled === null) respond_json(["ok" => false, "error" => "enabled must be true/false"], 422);

$pdo->beginTransaction();
try {
  $pdo->prepare('UPDATE estop SET enabled=?, updated_at=NOW() WHERE id=1')
      ->execute([$enabled ? 1 : 0]);

  $robotId = robot_id($pdo);
  $pdo->prepare('UPDATE robots SET state=?, updated_at=NOW() WHERE id=?')
      ->execute([$enabled ? 'E-STOP' : 'READY', $robotId]);

  if ($enabled) {
    $pdo->prepare('
      UPDATE commands
      SET status="canceled", message="canceled by E-STOP", reported_at=NOW()
      WHERE robot_id=? AND status IN ("queued","sent")
    ')->execute([$robotId]);
  }

  $pdo->commit();
} catch (Throwable $e) {
  $pdo->rollBack();
  log_line("estop error: " . $e->getMessage());
  respond_json(["ok" => false, "error" => "db error"], 500);
}

respond_json(["ok" => true, "enabled" => (bool)$enabled]);
