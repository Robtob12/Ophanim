<?php
session_start();
require_once "../db/conect.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['newUser'] === true) {
    header("location: create_conection.php");
    exit;
}

//OBTENER CONEXIONES DE USUARIO EXISTENTE
$sql = "SELECT * FROM conexiones WHERE user_id = :user_id";
$prepara = $Ophanim->prepare($sql);
$prepara->execute([':user_id' => $_SESSION['user']['id']]);
$conexiones = $prepara->fetchAll(PDO::FETCH_ASSOC);

//RECONSTRUIR LA CONEXION PRINCIPAL O LA QUE QUEDO
$server = $_SESSION['server'];

try {
    $conection = new PDO(
        "mysql:host={$server['host']};dbname={$server['database_name']}",
        $server['usuario'],
        openssl_decrypt($server['password'], "AES-128-ECB", "ophanim_secret")
    );

    $conection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Error de conexión");
}

//Obtener bancos del usuario
$sql = "SHOW DATABASES";
$prepara = $conection->prepare($sql);
$prepara->execute();
$bancos = $prepara->fetchAll(PDO::FETCH_ASSOC);

//OBTENER TABLAS DEL BANCO ACTUAL
$db_selected = $_SESSION['db'] ?? null;

$tablas = [];

if($db_selected){
    try {
        $sql = "SHOW TABLES FROM `$db_selected`";
        $stmt = $conection->prepare($sql);
        $stmt->execute();
        $tablas = $stmt->fetchAll(PDO::FETCH_NUM);
    } catch (PDOException $e) {
        $tablas = [];
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/dashboard.css">
    <title>Dashboard</title>
</head>
<body>
    <div class="menu">
        <div class="user">
            <h1 class="icon">< Ophanim ></h1>
            <a class="usericon" href="perfil.php">
                <img src="image/user.svg" alt="">
            </a>
        </div>

        <div class="conections">
            <h2 class="categoria">Conexiones</h2>
            <div class="divisor">
            <a class="add_c" href="create_conection.php">
                <img src="image/add_box.svg" alt="">
            </a>
            <details>
                <summary>
                    <?php 
                    if(empty($server['name'])){
                        echo "Conecion Actual";
                    }

                    else{
                        echo $server['name'];
                    }
                    ?>
                </summary>
                <ul>
                <?php foreach($conexiones as $con): ?>
                    <li><a href="../config/conectarConexion.php?id=<?php echo $con["id"] ?>"><?php echo $con["name"] ?></a></li>
                <?php endforeach?>
                </ul>
            </details>
            </div>
        </div>
        <details class="db">
            <summary>Bancos</summary>
            <div class="dbs">
                <ul>
                <?php foreach($bancos as $db): ?>
                    <li>
                        <a href="../config/conectarBanco.php?name=<?php echo urlencode($db['Database']); ?>">
                            <?php echo $db['Database']; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                </ul>
            </div>
        </details>
        <details class="tables">
            <summary>Tables</summary>
            <?php if($db_selected): ?>
                    <ul>
                        <?php foreach($tablas as $tabla): ?>
                            <li>
                                <a href="view_table.php?table=<?php echo urlencode($tabla[0]); ?>">
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
            <span class="context-badge active">Tabla: no seleccionado</span>
        </div>

        <div class="toolbar-right">
            <a href="sql_console.php" class="tool-btn">SQL</a>
            <a href="create_table.php" class="tool-btn">Crear</a>
        </div>
    </div>

        <!-- 📊 RESUMEN -->
        <div class="summary-panel">
            <div class="summary-card">
                <p class="summary-label">Base de datos</p>
                <h3><?php echo $db_selected; ?></h3>
            </div>

            <div class="summary-card">
                <p class="summary-label">Tabla</p>
                <h3>no seleccionado</h3>
            </div>

            <div class="summary-card">
                <p class="summary-label">Filas</p>
                <h3>0</h3>
            </div>

            <div class="summary-card">
                <p class="summary-label">Columnas</p>
                <h3>0</h3>
            </div>
        </div>
    </div>
</body>
</html>