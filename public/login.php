<?php 
session_start();
require_once '../db/conect.php';

if(isset($_SESSION['user']['login']) && $_SESSION['user']['login'] === true){
    header("location: dashboard.php");
    exit;
}

include '../modules/verifyLogin.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta</title>
    <link rel="stylesheet" href="styles/login.css">
</head>
<body>
    <div class="box1">
        <h1 class="icon">< Ophanim > <a class="btn_volver" href="index.php">volver</a></h1>
        <h2>Iniciar Sesion</h2>
        <?php 
        if(!empty($errors)){
            foreach($errors as $error){
                echo "<p class='error'> # $error</p>";
            };
        } else {
            echo "<p>Usa tu cuenta para recuperar y usar tus coneciones a servidores que ya tubiste, aprovecha de las herramientas que te ofrece nuestros servicios y usalos a tu favor para facilitar tu vida</p>";
        }
        ?>
        <form action="" method="POST">
            <label for="email">
                Correo Electronico
                <input type="email" name="email" id="" placeholder="exemple@email.com" value="<?= $email ?? "" ?>">
            </label>
            <label for="password">
                Contraseña
                <input type="password" name="password" id="" placeholder="contraseña">
            </label>
            <input type="submit" value="Entrar">
            <p class="login">No tienes una cuenta? <a href="register.php">Registrate</a></p>
        </form>
    </div>
    <img src="image/data.jpg" alt="">
</body>
</html>