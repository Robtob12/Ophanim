<?php 

$host = "127.0.0.1";
$root = "root";
$pass = "a1b2c3";
$dbname = "Ophanim";

try {
    $Ophanim = new PDO("mysql:host=$host;dbname=$dbname", $root, $pass);
    $Ophanim->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
