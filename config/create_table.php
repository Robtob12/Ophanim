<?php
session_start();
require_once "../db/conect.php";

/* 🔐 VALIDAR SESIÓN */
if (!isset($_SESSION['user']) || !isset($_SESSION['server']) || !isset($_SESSION['db'])) {
    header("Location: ../public/dashboard.php");
    exit;
}

$server = $_SESSION['server'];
$db = $_SESSION['db'];

$errors = [];

/* 🔹 CONEXIÓN SEGURA */
try {
    $conection = new PDO(
        "mysql:host={$server['host']};dbname={$db}",
        $server['usuario'],
        openssl_decrypt($server['password'], "AES-128-ECB", "ophanim_secret")
    );
    $conection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión");
}

/* 🔹 RECIBIR DATOS */
$table_name = $_POST['table_name'] ?? '';
$columns = $_POST['columns'] ?? [];
$fks = $_POST['fk'] ?? [];

/* 🔐 SANITIZAR NOMBRE TABLA */
$table_name = preg_replace('/[^a-zA-Z0-9_]/', '', $table_name);

if ($table_name === '') {
    $errors[] = "Nombre de tabla inválido";
}

/* 🔹 VALIDAR COLUMNAS */
if (empty($columns)) {
    $errors[] = "Debe haber al menos una columna";
}

$valid_types = ['INT','VARCHAR','TEXT','DATE','DATETIME','BOOLEAN'];

$sql_columns = [];
$primary_keys = [];

foreach ($columns as $i => $col) {

    $name = preg_replace('/[^a-zA-Z0-9_]/', '', $col['name'] ?? '');
    $type = strtoupper($col['type'] ?? '');
    $length = $col['length'] ?? null;

    if ($name === '') {
        $errors[] = "Columna #$i sin nombre";
        continue;
    }

    if (!in_array($type, $valid_types)) {
        $errors[] = "Tipo inválido en columna $name";
        continue;
    }

    /* 🔹 ARMAR TIPO */
    $sql = "`$name` $type";

    if ($type === 'VARCHAR' && $length) {
        $sql .= "($length)";
    }

    /* 🔹 NULL */
    if (!isset($col['null'])) {
        $sql .= " NOT NULL";
    }

    /* 🔹 DEFAULT */
    if (!empty($col['default'])) {
        $default = addslashes($col['default']);
        $sql .= " DEFAULT '$default'";
    }

    /* 🔹 AUTO INCREMENT */
    if (isset($col['ai']) && $type === 'INT') {
        $sql .= " AUTO_INCREMENT";
    }

    /* 🔹 PK */
    if (isset($col['pk'])) {
        $primary_keys[] = $name;
    }

    $sql_columns[] = $sql;
}

/* 🔹 PRIMARY KEY */
if (!empty($primary_keys)) {
    $pk = implode(",", array_map(fn($c) => "`$c`", $primary_keys));
    $sql_columns[] = "PRIMARY KEY ($pk)";
}

/* 🔹 FOREIGN KEYS */
foreach ($fks as $fk) {
    $col = preg_replace('/[^a-zA-Z0-9_]/', '', $fk['column'] ?? '');
    $ref_table = preg_replace('/[^a-zA-Z0-9_]/', '', $fk['ref_table'] ?? '');
    $ref_col = preg_replace('/[^a-zA-Z0-9_]/', '', $fk['ref_column'] ?? '');

    if ($col && $ref_table && $ref_col) {
        $sql_columns[] = "FOREIGN KEY (`$col`) REFERENCES `$ref_table`(`$ref_col`)";
    }
}

/* ❌ SI HAY ERRORES */
if (!empty($errors)) {
    echo "<pre>";
    print_r($errors);
    echo "</pre>";
    exit;
}

/* 🔥 CREAR SQL FINAL */
$sql = "CREATE TABLE `$table_name` (\n";
$sql .= implode(",\n", $sql_columns);
$sql .= "\n) ENGINE=InnoDB;";

/* 🚀 EJECUTAR */
try {
    $conection->exec($sql);

    header("Location: ../public/dashboard.php?success=table_created");
    exit;

} catch (PDOException $e) {
    echo "<h3>Error al crear tabla:</h3>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}