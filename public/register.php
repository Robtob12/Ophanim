<?php 
session_start();
require_once '../db/conect.php';

if(isset($_SESSION['user']['login']) && $_SESSION['user']['login'] === true){
    header("location: dashboard.php");
    exit;
}

include '../modules/verifyRegister.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta</title>
    <link rel="stylesheet" href="styles/register.css">
</head>
<body>
    <div class="cart"></div>
    <div class="divisor">
        <div class="box1">
            <h1 class="icon">< Ophanim > <a class="btn_volver" href="index.php">volver</a></h1>
            <h2>Crear una cuenta</h2>
            <?php 
            if(!empty($errors)){
                foreach($errors as $error){
                    echo "<p class='error'> # $error</p>";
                };
            } else {
                echo "<p>Al crear una cuenta se guardaran todos tus datos para fines de seguridad y utilidad del sistema como coneciones y bancos dentro de tu mismo usuario</p>";
            }
            ?>
            <form action="" method="POST">
                <label for="name">
                    Nombre de usuario
                    <input type="text" name="name" id="" placeholder="Jonh Doe" value="<?= $name ?? "" ?>">
                </label>
                <label for="email">
                    Correo Electronico
                    <input type="email" name="email" id="" placeholder="exemple@email.com" value="<?= $email ?? "" ?>">
                </label>
                <label for="password">
                    Contraseña
                    <input type="password" name="password" id="" placeholder="contraseña">
                </label>
                <input type="submit" value="Registrarse">
                <p class="login">Ya tienes cuenta en nuestra herramienta? <a href="login.php">Entrar</a></p>
            </form>
        </div>
        <img src="image/gestor.jpg" alt="">
    </div>
</body>
</html>