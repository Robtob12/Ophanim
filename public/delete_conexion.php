<?php
session_start();
require_once "../db/conect.php";

$id = $_GET['id'] ?? null;

if (!$id) die("ID inválido");

$sql = "DELETE FROM conexiones WHERE id = :id";
$stmt = $Ophanim->prepare($sql);
$stmt->execute([':id' => $id]);

header("Location: ../public/dashboard.php");
exit;