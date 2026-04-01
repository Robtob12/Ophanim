<?php
session_start();
require_once '../db/conect.php';
include '../modules/verifyConection.php';

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

//Obtener conexiones
$sql = "SHOW DATABASES";
$prepara = $conection->prepare($sql);
$prepara->execute();
$bancos = $prepara->fetchAll(PDO::FETCH_ASSOC);

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
    <div class="back">
        <div class="cart"></div>
        <div class="create_conection">
            <h1>Crear Nueva Conecion</h1>
            <p>Crea y conecta un servidor a nuestra pagina para que puedas usar las herramientas de crud, esto hara que puedas editar el banco desde nuestra pagina asta el servidor</p>
            <?php
            if(!empty($errores)){
                foreach($errores as $error){
                    echo "<p style='color:red;' class='error'> # $error</p>";
                };
            }
            ?>
            <form action="" method="post">
                <label for="name">
                    Nombre de la conecion
                    <input type="text" name="name" id="" placeholder="mi_servidor" value="<?= $name ?>">
                </label>
                <label for="tipo">
                    Tipo de servidor
                    <select name="tipo" id="">
                        <option value="mysql">Mysql</option>
                        <option value="pgsql">Pgsql</option>
                        <option value="sqlite">Sqlite</option>
                    </select>
                </label>
                <div class="divisor">
                    <label for="host">
                        Direcion IP
                        <input type="text" name="host" id="" placeholder="127.0.0.1" value="<?= $host ?>">
                    </label>
                    <label for="port">
                        Puerto
                        <input type="text" name="port" id="" placeholder="3306" value="<?= $port ?>">
                    </label>
                </div>
                <label for="usuario">
                    Root
                    <input type="text" name="usuario" id="" placeholder="root" value="<?= $usuario ?>">
                </label>
                <label for="password">
                    Contraseña del Root
                    <input type="password" name="password" id="" placeholder="contraseña" value="<?= $password ?>">
                </label>
                <label for="database_name">
                    Nombre del banco (Opcional)
                    <input type="text" name="database_name" id="" placeholder="mi_banco" value="<?= $database_name ?>">
                </label>

                <input type="submit" value="Crear Coneccion">
            </form>
        </div>
    </div>

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
        <div class="db">
        <h2 class="categoria">Bancos</h2>
        <div class="dbs">
            <ul>
            <?php foreach($bancos as $db): ?>
                <li>
                    <a href="../config/conectarBanco.php?name=<?php echo $db['Database']; ?>">
                        <?php echo $db['Database']; ?>
                    </a>
                </li>
            <?php endforeach; ?>
            </ul>
        </div>
        </div>
    </div>

    <div class="actionbox">
        

    </div>
</body>
</html>
