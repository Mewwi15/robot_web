<?php
declare(strict_types=1);
require __DIR__ . '/_robot.php';

require_method('POST');
require_robot_token();

$pdo = db();
$robotId = robot_id($pdo);

$pdo->prepare('UPDATE robots SET last_seen_at=NOW(), updated_at=NOW() WHERE id=?')
    ->execute([$robotId]);

respond_json(["ok" => true]);
