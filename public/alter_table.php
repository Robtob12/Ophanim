<?php
session_start();
require_once "../db/conect.php";

// 🔐 Validaciones
if (!isset($_SESSION['user']) || $_SESSION['user']['newUser'] === true) {
    header("location: create_conection.php");
    exit;
}

if (!isset($_SESSION['server']) || !isset($_SESSION['db'])) {
    header("location: dashboard.php");
    exit;
}

// 📡 Datos
$server = $_SESSION['server'];
$db_selected = $_SESSION['db'];
$table = $_GET['table'] ?? "";

// 🔐 Seguridad
$table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);

if (!$table) die("Tabla inválida");

// 🔌 Conexión
try {
    $conection = new PDO(
        "mysql:host={$server['host']};dbname={$db_selected}",
        $server['usuario'],
        openssl_decrypt($server['password'], "AES-128-ECB", "ophanim_secret")
    );
    $conection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error conexión");
}

// 📊 Columnas actuales
$stmt = $conection->prepare("DESCRIBE `$table`");
$stmt->execute();
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 🚀 ALTER TABLE
$success = false;
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $queries = [];

    // 🔁 MODIFICAR / ELIMINAR
    foreach ($_POST['old_name'] as $i => $old_name) {

        $new_name = $_POST['name'][$i];
        $type = $_POST['type'][$i];
        $null = isset($_POST['null'][$i]) ? "NULL" : "NOT NULL";
        $default = $_POST['default'][$i];

        // eliminar
        if (isset($_POST['delete']) && in_array($old_name, $_POST['delete'])) {
            $queries[] = "DROP COLUMN `$old_name`";
            continue;
        }

        // modificar
        $sql = "CHANGE `$old_name` `$new_name` $type $null";

        if ($default !== "") {
            $sql .= " DEFAULT '$default'";
        }

        $queries[] = $sql;
    }

    // ➕ NUEVAS COLUMNAS
    if (!empty($_POST['new_name'])) {
        foreach ($_POST['new_name'] as $i => $name) {

            $type = $_POST['new_type'][$i];

            if ($name && $type) {
                $queries[] = "ADD COLUMN `$name` $type";
            }
        }
    }

    // 🧱 Ejecutar
    if (!empty($queries)) {
        try {
            $sql = "ALTER TABLE `$table` " . implode(", ", $queries);
            $stmt = $conection->prepare($sql);
            $stmt->execute();

            $success = true;

        } catch (PDOException $e) {
            $error = "Error al modificar tabla";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar Tabla</title>
    <link rel="stylesheet" href="styles/dashboard.css">
</head>
<body>

<div class="back">
    <div class="cart"></div>

    <div class="create_table">
        <h1>Alterar tabla <a href="view_table.php?table=<?php echo $table; ?>" class="btn_volver">Volver</a></h1>
        <h2><span><?php echo $table; ?></span> </h2>
        <p>Modifica la estructura de la tabla</p>

        <?php if($success): ?>
            <div class="alert success">Tabla modificada correctamente</div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">

            <h3>Columnas actuales</h3>

            <div class="columns">

                <?php foreach($columns as $i => $col): ?>
                <div class="column">

                    <input type="hidden" name="old_name[]" value="<?php echo $col['Field']; ?>">

                    <!-- nombre -->
                    <input type="text" name="name[]" value="<?php echo $col['Field']; ?>">

                    <!-- tipo -->
                    <input type="text" name="type[]" value="<?php echo $col['Type']; ?>">

                    <!-- NULL -->
                    <label>
                        <input type="checkbox" name="null[<?php echo $i; ?>]"
                        <?php echo ($col['Null'] === 'YES') ? 'checked' : ''; ?>>
                        NULL
                    </label>

                    <!-- DEFAULT -->
                    <input type="text" name="default[]" 
                        value="<?php echo $col['Default']; ?>" placeholder="DEFAULT">

                    <!-- eliminar -->
                    <label>
                        <input type="checkbox" name="delete[]" value="<?php echo $col['Field']; ?>">
                        Eliminar
                    </label>

                </div>
                <?php endforeach; ?>

            </div>

            <!-- ➕ NUEVAS COLUMNAS -->
            <h3>Nuevas columnas</h3>

            <div id="newColumns">

                <div class="column new-col">
                    <input type="text" name="new_name[]" placeholder="nombre">
                    <input type="text" name="new_type[]" placeholder="tipo">

                    <button type="button" class="removeColumn">❌</button>
                </div>

            </div>

            <button type="button" id="addColumn">+ Agregar columna</button>

            <!-- 🚀 -->
            <div class="form-actions">
                <input type="submit" value="Guardar cambios">
            </div>

        </form>
    </div>
</div>
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

<script src="../config/add_columns_alter.js"></script>

</body>
</html>