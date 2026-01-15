<?php
declare(strict_types=1);
require __DIR__ . '/_auth.php';
require __DIR__ . '/_robot.php';

require_method('POST');
require_login();
require_csrf();

$pdo = db();
$robotId = robot_id($pdo);

$pdo->beginTransaction();
try {
  $pdo->prepare('
    UPDATE commands
    SET status="canceled", message="cleared by admin", reported_at=NOW()
    WHERE robot_id=? AND status IN ("queued","sent")
  ')->execute([$robotId]);

  $pdo->prepare('UPDATE robots SET state="READY", active_command_id=NULL, updated_at=NOW() WHERE id=?')
      ->execute([$robotId]);

  $pdo->commit();
} catch (Throwable $e) {
  $pdo->rollBack();
  log_line("clear error: " . $e->getMessage());
  respond_json(["ok" => false, "error" => "db error"], 500);
}

respond_json(["ok" => true]);
