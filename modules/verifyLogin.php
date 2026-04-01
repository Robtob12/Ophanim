<?php
$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email =  trim($_POST['email'] ?? "");
    $password = trim($_POST['password'] ?? "");

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email inválido";
    }

    if (strlen($password) < 8) {
        $errors[] = "La contraseña debe tener mínimo 8 caracteres";
    }

    if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors[] = "La contraseña debe contener letras y números";
    }

    if (empty($errors)) {

        $sql = "SELECT * FROM users WHERE email = :email";
        $conn = $Ophanim->prepare($sql);
        $conn->execute([":email" => $email]);
        $verify =  $conn->fetch(PDO::FETCH_ASSOC);

        if(empty($verify)){
            $errors[] = "Esta cuenta no existe";
        } else {

            // 🔴 NUEVO: verificar contraseña real
            if (!password_verify($password, $verify['password'])) {
                $errors[] = "Contraseña incorrecta";
            }

            if(empty($errors)){

                $sql = "SELECT * FROM conexiones WHERE user_id = :user_id limit 1";
                $conn = $Ophanim->prepare($sql);
                $conn->execute([":user_id" => $verify['id']]);
                $conexion = $conn->fetch(PDO::FETCH_ASSOC);

                // 🔴 NUEVO: definir siempre
                $newUser = true;

                // 🔴 NUEVO: validar que exista conexión
                if($conexion && is_array($conexion)){

                    $newUser = false;

                    try {

                        $conection = new PDO(
                            "mysql:host={$conexion['host']};dbname={$conexion['database_name']}",
                            $conexion['usuario'],
                            // 🔴 CORREGIDO
                            openssl_decrypt($conexion['password'], "AES-128-ECB","ophanim_secret")
                        );

                        $conection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    } catch (PDOException $e) {
                        $errors[] = "Error al conectar al servidor";
                    }
                }

                // 🔴 SOLO si no hay errores
                if(empty($errors)){
                    $_SESSION['user'] = [
                        "id" => $verify['id'],
                        "name" => $verify['name'],
                        "email" => $verify['email'],
                        "login" => true,
                        "newUser" => $newUser
                    ];

                    $_SESSION['server'] = $conexion;

                    header('location: dashboard.php');
                    exit;
                }
            }
        }
    }
}
?>