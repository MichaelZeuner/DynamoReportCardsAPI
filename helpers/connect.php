<?php

function isLocal() {
     return in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1', '192.168.64.2'));
}

if(isLocal()) {
     $db   = 'report_cards';
     $user = 'root';
     $pass = '';
} else {
     $db   = 'dynamoca_report_cards';
     $user = 'dynamoca_api';
     $pass = 'LeB4c%^e9rcN';
}
$host = 'localhost';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}