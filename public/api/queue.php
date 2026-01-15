<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../private/_init.php';
require_once __DIR__ . '/../../private/_auth.php';

if (function_exists('require_login')) {
  require_login();
}

try {
  /** @var PDO $pdo */
  if (!isset($pdo)) {
    throw new RuntimeException('PDO not initialized');
  }

  // Resolve robot
  $robotId = null;

  $robot_key = isset($_GET['robot_key']) ? trim((string)$_GET['robot_key']) : '';
  $robot_id_q = isset($_GET['robot_id']) ? trim((string)$_GET['robot_id']) : '';

  if ($robot_id_q !== '' && ctype_digit($robot_id_q)) {
    $robotId = (int)$robot_id_q;
  } elseif ($robot_key !== '') {
    $st = $pdo->prepare("SELECT id FROM robots WHERE name = ? LIMIT 1");
    $st->execute([$robot_key]);
    $robotId = (int)($st->fetchColumn() ?: 0);
  } else {
    if (function_exists('robot_id')) {
      $robotId = (int)robot_id($pdo);
    }
  }

  if (!$robotId) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'missing_or_unknown_robot'], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // Initialize counts to keep stable keys
  $counts = [
    'queued' => 0,
    'sent' => 0,
    'done' => 0,
    'error' => 0,
    'canceled' => 0,
  ];

  $st = $pdo->prepare("
    SELECT status, COUNT(*) AS c
    FROM commands
    WHERE robot_id = ?
    GROUP BY status
  ");
  $st->execute([$robotId]);

  while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
    $k = (string)$r['status'];
    if (array_key_exists($k, $counts)) {
      $counts[$k] = (int)$r['c'];
    }
  }

  echo json_encode([
    'ok' => true,
    'robot_id' => $robotId,
    'counts' => $counts,
  ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'server_error'], JSON_UNESCAPED_UNICODE);
}
