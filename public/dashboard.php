<?php
/*====================================*\ 
[#]         CONFIGURACION            [#]
\*====================================*/

//[1] ACTIVAR SESSION
session_start();

//[2] PEDIR BANCO IMPORTANTE
require_once "../db/conect.php";

//[3] SI EL USUARIO EXISTE Y ES NUEVO QUE CREÈ SU PRIMERA CONEXION
if (!isset($_SESSION['user']) || $_SESSION['user']['newUser'] === true) {
    header("location: create_conection.php");
    exit;
}


/*====================================*\ 
[#]       OBTENCION DE DATOS         [#]
\*====================================*/

//[1] OBTENER CONEXIONES DEL USUARIO EXISTENTE
$sql = "SELECT * FROM conexiones WHERE user_id = :user_id";
$prepara = $Ophanim->prepare($sql);
$prepara->execute([':user_id' => $_SESSION['user']['id']]);
$conexiones = $prepara->fetchAll(PDO::FETCH_ASSOC);

//[2] RECONSTRUIR LA CONEXION PRINCIPAL O LA QUE QUEDO
$server = $_SESSION['server'] ?? null;  # SI NO HAY SERVIDOR SELECIONADO ES NULL
$conection = null;                      # LA CONEXION ES NULA POR DEFECTO PARA EVITAR ERRORES
$connection_error = null;               # NO HAY ERRORES DE CONEXION ASTA QUE SE COMPREVE QUE HAY

//[3] SI HAY CONEXION ENTONCES:
if($server){ 
    try {
        // CREA LA CONEXION DEL BANCO CON
        $conection = new PDO(                                                       # Server -> HOST
            "mysql:host={$server['host']};dbname={$server['database_name']}",       # Server -> DBNAME si hay
            $server['usuario'],                                                     # Server -> ROOT
            openssl_decrypt($server['password'], "AES-128-ECB", "ophanim_secret")   # Server -> PASSWORD
        ); 

        $conection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);        # CREAR ATIRBUTOS DE LA CONEXION

    } catch (PDOException $e) { // EN CASO DE ERROR AL CONECTAR
        $connection_error = $e->getMessage();                   # LA CONEXION TIENE ERRORES Y SE GUARDA
        unset($_SESSION['server']);                             # ELIMINAMOS EL SERVIDOR YA QUE NO HAY
        $server = null;                                         # DEJAMOS EL SERVIDOR NULO ESTO ES INPORTANTE
    }
}

//[4] OBTENER LOS BANCOS DE LA CONEXION
$bancos = [];                                       # CREAMOS UN ARRAY PARA LOS BANCOS

if($conection){                                     # SI EL SERVIDOR ESTA FUNCIONANDO OBTENERMOS LOS BANCOS DE ESTE
    $sql = "SHOW DATABASES";                        # CODIGO SQL PARA MOSTRAR LOS BANCOS
    $prepara = $conection->prepare($sql);           # PREPARAMOS EL CODIGO EN EL SERVIDOR
    $prepara->execute();                            # EJECUTAMOS EL CODIGO
    $bancos = $prepara->fetchAll(PDO::FETCH_ASSOC); # DEVOLVEMOS EL RESULTADO EN EL ARRAY DE BANCOS
}

//[5] OBTENER LAS TABLAS DEL BANCO SELECCIONADO
$db_selected = $_SESSION['db'] ?? null;             # SI YA EXISTE UN BANCO SELECIONADO USAMOS SU NOMBRE
$tablas = [];                                       # GUARDAMOS LAS TABLAS EN UN ARRAY COMO LO HICIMOS CON LOS BANCOS

if($db_selected && $conection){                     # SI EXISTE UN BANCO SELECIONADO Y LA CONEXION FUNCIONA
    try {                                           
        $sql = "SHOW TABLES FROM `$db_selected`";   # CODIGO SQL PARA SELECIONAR LAS TABLAS DEL BANCO SELECIONADO
        $stmt = $conection->prepare($sql);          # PREPARAMOS EL CODIGO EN EL SERVIDOR
        $stmt->execute();                           # EJECUTAMOS EL CODIGO
        $tablas = $stmt->fetchAll(PDO::FETCH_NUM);  # DEVOLVEMOS EL RESULTADO EN EL ARRAY DE TABLAS
    } catch (PDOException $e) {                     # EN CASO DE ERRORES O DE QUE NO FUNCIONE EL COMANDO DEJAMOS LAS TABLAS VACIAS
        $tablas = [];
    }
}

/*=====================================*\
[#] CODIGO HTML Y ESTRUCTURA DE LA UI [#]
\*=====================================*/

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
    <!-- MENU LATERAL -->
    <div class="menu">
            
        <!-- AREA DE USUARIO -->
        <div class="user">
            <h1 class="icon">< Ophanim ></h1>
            <a class="usericon" href="perfil.php">
                <img src="image/user.svg" alt="">
            </a>
        </div>

        <!-- AREA DE CONEXIONES -->
        <div class="conections">
            <h2 class="categoria">Conexiones</h2>
            <div class="divisor">
            <a class="add_c" href="create_conection.php">
                <img src="image/add_box.svg" alt="">
            </a>
            <details>
                <summary>
                    <?php 
                    if(!$server || empty($server['name'])){
                        echo "Sin conexión";
                    } else {
                        echo $server['name'];
                    }
                    ?>
                </summary>
                <ul>
                <?php foreach($conexiones as $con): ?>
                    <li class="conexion_link">
                        <a href="../config/conectarConexion.php?id=<?php echo $con["id"] ?>"><?php echo $con["name"] ?></a>
                        <a href="delete_conexion.php?id=<?php echo $con["id"] ?>"onclick="return confirm('Eliminar conexión?')">
                            <img src="image/delete.svg" alt="">
                        </a>
                    </li>
                <?php endforeach?>
                </ul>
            </details>
            </div>
        </div>

        <!-- AREA DE BANCOS-->
        <details class="db">
            <summary>Bancos</summary>
            <div class="dbs">
                <ul>
                <?php foreach($bancos as $db): ?>
                    <li class="db_link">
                        <a href="../config/conectarBanco.php?name=<?php echo urlencode($db['Database']); ?>">
                            <?php echo $db['Database']; ?>
                        </a>
                        <div class="edits">
                            <a href="delete_database.php?name=<?php echo urlencode($db['Database']); ?>" onclick="return confirm('Eliminar base de datos?')">
                                <img src="image/delete.svg" alt="">
                            </a>
                            <a href="#"></a>
                                <img src="image/edit.svg" alt="">
                            </a>
                        </div>
                    </li>
                <?php endforeach; ?>
                </ul>
            </div>
        </details>

        <!-- AREA DE TABLAS -->
        <details class="tables">
            <summary>Tables</summary>
            <?php if($db_selected): ?>
                    <ul>
                        <?php foreach($tablas as $tabla): ?>
                            <li class="table_link">
                                <a href="view_table.php?table=<?php echo urlencode($tabla[0]); ?>">
                                    <?php echo $tabla[0]; ?>
                                </a>
                                <div class="edits">
                                    <a href="delete_table.php?table=<?php echo urlencode($tabla[0]); ?>" onclick="return confirm('Eliminar tabla?')">
                                        <img src="image/delete.svg" alt="">
                                    </a>
                                    <a href="#">
                                        <img src="image/edit.svg" alt="">
                                    </a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
            <?php else: ?>
                <p>Selecciona un banco</p>
            <?php endif; ?>

        </details>

    </div>
    
    <!-- AREA DE ACCIONES Y HERRAMIENTAS-->
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