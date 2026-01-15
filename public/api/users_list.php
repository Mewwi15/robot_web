<?php
declare(strict_types=1);
require __DIR__ . '/_auth.php';

require_role('admin');

$rows = db()->query('SELECT id, username, role, created_at, last_login_at FROM users ORDER BY id ASC')->fetchAll();
respond_json(["ok" => true, "users" => $rows]);
