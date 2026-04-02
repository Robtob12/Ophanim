<?php
session_start();
require_once "../db/conect.php";

// validar sesión
if (!isset($_SESSION['server']) || !isset($_SESSION['db'])) {
    die("Acceso inválido");
}

$server = $_SESSION['server'];
$db = $_SESSION['db'];

$table = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['table'] ?? '');
$id = $_GET['id'] ?? '';

if (!$table || !$id) die("Datos inválidos");

// conexión
$conection = new PDO(
    "mysql:host={$server['host']};dbname={$db}",
    $server['usuario'],
    openssl_decrypt($server['password'], "AES-128-ECB", "ophanim_secret")
);

// detectar PK
$stmt = $conection->prepare("DESCRIBE `$table`");
$stmt->execute();
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pk = null;
foreach ($columns as $col) {
    if ($col['Key'] === 'PRI') {
        $pk = $col['Field'];
        break;
    }
}

if (!$pk) die("No PK encontrada");

// eliminar
$sql = "DELETE FROM `$table` WHERE `$pk` = :id";
$stmt = $conection->prepare($sql);
$stmt->execute([':id' => $id]);

// redirect
header("Location: ../public/view_table.php?table=" . urlencode($table));
exit;