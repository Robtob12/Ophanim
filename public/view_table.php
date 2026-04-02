<?php
session_start();
require_once "../db/conect.php";

// 🔐 Validaciones básicas
if (!isset($_SESSION['user']) || $_SESSION['user']['newUser'] === true) {
    header("location: create_conection.php");
    exit;
}

if (!isset($_SESSION['server']) || !isset($_SESSION['db'])) {
    header("location: dashboard.php");
    exit;
}

// 📡 Datos base
$server = $_SESSION['server'];
$db_selected = $_SESSION['db'];
$table = $_GET['table'] ?? null;

// 🔐 Seguridad
$table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
$db_selected = preg_replace('/[^a-zA-Z0-9_]/', '', $db_selected);

// 🔌 Conexión (UNA SOLA VEZ)
try {
    $conection = new PDO(
        "mysql:host={$server['host']};dbname={$db_selected}",
        $server['usuario'],
        openssl_decrypt($server['password'], "AES-128-ECB", "ophanim_secret")
    );
    $conection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión");
}

// 📊 Obtener datos de tabla
$rows = [];
$columns = [];
$table_count = 0;

if ($table) {
    try {
        // datos
        $stmt = $conection->prepare("SELECT * FROM `$table` LIMIT 50");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // columnas
        $stmtCols = $conection->prepare("DESCRIBE `$table`");
        $stmtCols->execute();
        $columns = $stmtCols->fetchAll(PDO::FETCH_ASSOC);

        // count
        $stmtCount = $conection->prepare("SELECT COUNT(*) as total FROM `$table`");
        $stmtCount->execute();
        $table_count = (int)$stmtCount->fetch(PDO::FETCH_ASSOC)['total'];

    } catch (PDOException $e) {
        $rows = [];
    }
}

// 📡 Conexiones del usuario
$sql = "SELECT * FROM conexiones WHERE user_id = :user_id";
$prepara = $Ophanim->prepare($sql);
$prepara->execute([':user_id' => $_SESSION['user']['id']]);
$conexiones = $prepara->fetchAll(PDO::FETCH_ASSOC);

// 🗄️ Bases de datos
$stmt = $conection->prepare("SHOW DATABASES");
$stmt->execute();
$bancos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 📁 Tablas
$tablas = [];
if ($db_selected) {
    $stmt = $conection->prepare("SHOW TABLES FROM `$db_selected`");
    $stmt->execute();
    $tablas = $stmt->fetchAll(PDO::FETCH_NUM);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ophanim Dashboard</title>
    <link rel="stylesheet" href="styles/dashboard.css">
</head>
<body>

<div class="menu">

    <div class="user">
        <h1 class="icon">< Ophanim ></h1>
        <a class="usericon" href="perfil.php">
            <img src="image/user.svg">
        </a>
    </div>

    <div class="conections">
        <h2 class="categoria">Conexiones</h2>
        <div class="divisor">
            <a class="add_c" href="create_conection.php">
                <img src="image/add_box.svg">
            </a>

            <details>
                <summary>
                    <?php echo $server['name'] ?? "Conexión Actual"; ?>
                </summary>
                <ul>
                    <?php foreach($conexiones as $con): ?>
                        <li>
                            <a href="../config/conectarConexion.php?id=<?php echo $con["id"] ?>">
                                <?php echo $con["name"] ?>
                            </a>
                        </li>
                    <?php endforeach ?>
                </ul>
            </details>
        </div>
    </div>

    <details class="db">
        <summary>Bancos</summary>
        <ul>
            <?php foreach($bancos as $db): ?>
                <li>
                    <a href="../config/conectarBanco.php?name=<?php echo urlencode($db['Database']); ?>">
                        <?php echo $db['Database']; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </details>

    <details class="tables">
        <summary>Tablas</summary>
        <?php if($db_selected): ?>
            <ul>
                <?php foreach($tablas as $tabla): ?>
                    <li>
                        <a href="?table=<?php echo urlencode($tabla[0]); ?>">
                            <?php echo $tabla[0]; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Selecciona un banco</p>
        <?php endif; ?>
    </details>

</div>

<div class="actionbox">

    <!-- 🔥 TOOLBAR -->
    <div class="toolbar">
        <div class="toolbar-left">
            <span class="context-badge">DB: <?php echo $db_selected; ?></span>
            <?php if($table): ?>
                <span class="context-badge active">Tabla: <?php echo $table; ?></span>
            <?php endif; ?>
        </div>

        <div class="toolbar-right">
            <a href="sql_console.php" class="tool-btn">SQL</a>
            <a href="create_table.php" class="tool-btn">Crear</a>

            <?php if($table): ?>
                <a href="insert_row.php?table=<?php echo $table ?>" class="tool-btn">Insertar</a>
                <a href="alter_table.php?table=<?php echo $table ?>" class="tool-btn">Alterar</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- 📊 RESUMEN -->
    <div class="summary-panel">
        <div class="summary-card">
            <p class="summary-label">Base de datos</p>
            <h3><?php echo $db_selected; ?></h3>
        </div>

        <?php if($table): ?>
        <div class="summary-card">
            <p class="summary-label">Tabla</p>
            <h3><?php echo $table; ?></h3>
        </div>

        <div class="summary-card">
            <p class="summary-label">Filas</p>
            <h3><?php echo $table_count; ?></h3>
        </div>

        <div class="summary-card">
            <p class="summary-label">Columnas</p>
            <h3><?php echo count($columns); ?></h3>
        </div>
        <?php endif; ?>
    </div>

    <!-- 📋 TABLA -->
    <div class="table-wrap">

        <div class="table-head">
            <h2><?php echo $table ?? "Selecciona una tabla"; ?></h2>
        </div>

        <?php if(!empty($rows)): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <?php foreach(array_keys($rows[0]) as $col): ?>
                            <th><?php echo $col; ?></th>
                        <?php endforeach; ?>
                        <th colspan="2">EDITOR</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach($rows as $row): ?>
                        <tr>
                            <?php foreach($row as $value): ?>
                                <td><?php echo htmlspecialchars($value); ?></td>
                            <?php endforeach; ?>
                            <td class="delete"><a href=""> <img src="image/delete.svg" alt=""></a></td>
                            <td class="edit"><a href=""> <img src="image/edit.svg" alt=""></a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>

            <div class="empty-state">
                <h3>No hay datos</h3>
                <p>Selecciona una tabla</p>
            </div>
        <?php endif; ?>

    </div>

</div>

</body>
</html>