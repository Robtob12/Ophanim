<?php 
session_start();
require_once '../db/conect.php';

if(isset($_SESSION['login']) && $_SESSION['login'] === true){
    header("location: dashboard.php");
    exit;
}

$errors = []; // Asegurarse de inicializar el array de errores

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST['name'] ?? "");
    $email =  trim($_POST['email'] ?? "");
    $password = trim($_POST['password'] ?? "");

    // Validar nombre
    if (strlen($name) < 3) {
        $errors[] = "El nombre debe tener al menos 3 caracteres";
    }

    // Validar email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email inválido";
    }

    // Validar contraseña
    if (strlen($password) < 8) {
        $errors[] = "La contraseña debe tener mínimo 8 caracteres";
    }

    if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors[] = "La contraseña debe contener letras y números";
    }

    // Si no hay errores, proceder con la creación de usuario
    if (empty($errors)) {

        // Verificar si el usuario ya existe
        $sql = "SELECT * FROM users WHERE email = :email";
        $conn = $Ophanim->prepare($sql);
        $conn->execute([":email" => $email]);
        $verify =  $conn->fetch(PDO::FETCH_ASSOC);

        if($verify){
            header("location: register.php?errors=El usuario ya existe");
            exit;
        }

        // Insertar nuevo usuario
        $sql = "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)";
        $conn = $Ophanim->prepare($sql);
        $conn->execute([
            ":name" => $name,
            ":email" => $email,
            ":password" => password_hash($password, PASSWORD_DEFAULT)
        ]);

        $_SESSION['user'] = [
            "name" => $name,
            "email" => $email,
            "login" => true,
            "newUser" => true
        ];

        header('location: dashboard.php');
        exit;

    }
}
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
            <h1 class="icon">< Ophanim > <a class="btn_volver" href="index">volver</a></h1>
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