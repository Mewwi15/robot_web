<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../private/_init.php';
require_once __DIR__ . '/../../../private/_auth.php';

require_method('GET');
require_web_login();

$robot_id = $_GET['robot_id'] ?? '';
if ($robot_id === '') json_out(['ok' => false, 'error' => 'missing_robot_id'], 400);

// ตัวอย่าง query (ปรับตาม schema จริงของคุณ)
$stmt = $pdo->prepare("SELECT robot_id, last_seen_at, status_json FROM robots WHERE robot_id = ?");
$stmt->execute([$robot_id]);
$row = $stmt->fetch();

if (!$row) json_out(['ok' => false, 'error' => 'not_found'], 404);

json_out(['ok' => true, 'robot' => $row]);
