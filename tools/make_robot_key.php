<?php
declare(strict_types=1);

$token = $argv[1] ?? '';
if ($token === '') {
  fwrite(STDERR, "Usage: php make_robot_key.php <token>\n");
  exit(1);
}
echo password_hash($token, PASSWORD_DEFAULT) . PHP_EOL;
