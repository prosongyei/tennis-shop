<?php
$dbFile = __DIR__ . '/database/database.sqlite';
if (file_exists($dbFile)) {
    unlink($dbFile);
}
$pdo = new PDO('sqlite:' . $dbFile);
$pdo->exec('PRAGMA user_version = 0;');
echo "Clean SQLite database initialized!\n";
