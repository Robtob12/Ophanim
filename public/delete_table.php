<?php
session_start();

// 🔐 Validar sesión
if (!isset($_SESSION['server']) || !isset($_SESSION['db'])) {
    die("Acceso no autorizado");
}

$server = $_SESSION['server'];
$db_selected = $_SESSION['db'];
$table = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['table'] ?? '');

if (!$table) die("Tabla inválida");

try {

    $conection = new PDO(
        "mysql:host={$server['host']};dbname={$db_selected}",
        $server['usuario'],
        openssl_decrypt($server['password'], "AES-128-ECB", "ophanim_secret")
    );

    $conection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 🔍 Detectar dependencias (FOREIGN KEYS)
    $stmt = $conection->prepare("
        SELECT TABLE_NAME, COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE REFERENCED_TABLE_NAME = :table
        AND TABLE_SCHEMA = :db
    ");

    $stmt->execute([
        ':table' => $table,
        ':db' => $db_selected
    ]);

    $dependencies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 🚫 Si hay dependencias → bloquear
    if (!empty($dependencies)) {

        echo "<h2>No puedes eliminar esta tabla</h2>";
        echo "<p>Está siendo usada por:</p><ul>";

        foreach ($dependencies as $dep) {
            echo "<li>{$dep['TABLE_NAME']} → {$dep['COLUMN_NAME']}</li>";
        }

        echo "</ul>";
        echo "<a href='../public/dashboard.php'>Volver</a>";
        exit;
    }

    // 🗑️ Eliminar tabla
    $sql = "DROP TABLE `$table`";
    $conection->exec($sql);

    header("Location: ../public/dashboard.php");
    exit;

} catch (PDOException $e) {

    // 🔥 Manejo de error específico
    if ($e->getCode() == 23000) {
        die("No puedes eliminar la tabla por restricciones de integridad");
    }

    die("Error: " . $e->getMessage());
}