<?php
session_start();

$server = $_SESSION['server'];
$db = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['name'] ?? '');

if (!$db) die("DB inválida");

$conection = new PDO(
    "mysql:host={$server['host']}",
    $server['usuario'],
    openssl_decrypt($server['password'], "AES-128-ECB", "ophanim_secret")
);

// ⚠️ PELIGRO
$sql = "DROP DATABASE `$db`";
$conection->exec($sql);

header("Location: ../public/dashboard.php");
exit;