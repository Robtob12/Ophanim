<?php
session_start();
require_once '../db/conect.php';

$server_id = $_GET['id'];
$sql = "SELECT * FROM conexiones WHERE id = :id";
$com = $Ophanim->prepare($sql);
$com->execute([':id' => $server_id]);
$_SESSION['server'] = $com->fetch(PDO::FETCH_ASSOC);
header('location: ../public/dashboard.php');
?>