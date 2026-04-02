<?php
session_start();
require_once "../db/conect.php";

$server = $_SESSION['server'];
$db = $_SESSION['db'];
$table = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['table']);
$id = $_GET['id'];

// conexión
$conection = new PDO(
    "mysql:host={$server['host']};dbname={$db}",
    $server['usuario'],
    openssl_decrypt($server['password'], "AES-128-ECB", "ophanim_secret")
);

// columnas
$stmt = $conection->prepare("DESCRIBE `$table`");
$stmt->execute();
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

// PK
$pk = null;
foreach ($columns as $col) {
    if ($col['Key'] === 'PRI') {
        $pk = $col['Field'];
    }
}

// obtener datos actuales
$stmt = $conection->prepare("SELECT * FROM `$table` WHERE `$pk` = :id");
$stmt->execute([':id' => $id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$error = "";

// UPDATE
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fields = [];
    $values = [];

    foreach ($columns as $col) {
        $name = $col['Field'];

        if ($name === $pk) continue;

        $fields[] = "`$name` = :$name";
        $values[":$name"] = $_POST[$name];
    }

    $values[":id"] = $id;

    $sql = "UPDATE `$table` SET " . implode(",", $fields) . " WHERE `$pk` = :id";

    $stmt = $conection->prepare($sql);
    $stmt->execute($values);

    header("Location: view_table.php?table=" . urlencode($table));
    exit;
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

<div class="back">
    <div class="cart"></div>

    <div class="create_table">
        <h1>Editar datos 
            <a href="view_table.php?table=<?= $table ?>" class="btn_volver">volver</a>
        </h1>

        <h2><span><?php echo $table; ?></span></h2>
        <p>Modifica los campos del registro</p>

        <?php if($error): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- 🧾 FORMULARIO -->
        <form method="POST">

            <div class="columns">

                <?php foreach($columns as $col): ?>
                    <?php
                        $type = strtolower($col['Type']);
                        $name = $col['Field'];

                        // 🚫 no editar PK
                        if ($name === $pk) continue;

                        $value = $row[$name] ?? "";
                    ?>

                    <div class="column">

                        <label>
                            <?php echo $name; ?>
                            <span class="type"><?php echo $col['Type']; ?></span>
                        </label>

                        <?php if(strpos($type, 'int') !== false): ?>
                            <input type="number" name="<?php echo $name; ?>" 
                                   value="<?php echo htmlspecialchars($value); ?>">

                        <?php elseif(strpos($type, 'text') !== false): ?>
                            <textarea name="<?php echo $name; ?>"><?php echo htmlspecialchars($value); ?></textarea>

                        <?php elseif(strpos($type, 'date') !== false && strpos($type, 'datetime') === false): ?>
                            <input type="date" name="<?php echo $name; ?>" 
                                   value="<?php echo substr($value, 0, 10); ?>">

                        <?php elseif(strpos($type, 'datetime') !== false): ?>
                            <input type="datetime-local" name="<?php echo $name; ?>" 
                                   value="<?php echo str_replace(' ', 'T', $value); ?>">

                        <?php elseif(strpos($type, 'tinyint(1)') !== false): ?>
                            <select name="<?php echo $name; ?>">
                                <option value="1" <?php echo ($value == 1) ? 'selected' : ''; ?>>True</option>
                                <option value="0" <?php echo ($value == 0) ? 'selected' : ''; ?>>False</option>
                            </select>

                        <?php else: ?>
                            <input type="text" name="<?php echo $name; ?>" 
                                   value="<?php echo htmlspecialchars($value); ?>">
                        <?php endif; ?>

                    </div>

                <?php endforeach; ?>

            </div>

            <!-- 🚀 BOTONES -->
            <div class="form-actions">
                <a href="view_table.php?table=<?= $table ?>" class="btn_volver">Cancelar</a>
                <input type="submit" value="Actualizar">
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
                            <td class="delete">
                                <a href="delete_row.php?table=<?php echo urlencode($table); ?>&id=<?php echo $row[$primary_key]; ?>"
                                onclick="return confirm('¿Eliminar este registro?')">
                                    <img src="image/delete.svg" alt="">
                                </a>
                            </td>
                            <td class="edit">
                                <a href="edit_row.php?table=<?php echo urlencode($table); ?>&id=<?php echo $row[$primary_key]; ?>">
                                    <img src="image/edit.svg" alt="">
                                </a>
                            </td>
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