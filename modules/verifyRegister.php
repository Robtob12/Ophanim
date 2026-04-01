<?php
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
            $errors[] = "El usuario ya existe";
        }

        else{
            // Insertar nuevo usuario
            $sql = "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)";
            $conn = $Ophanim->prepare($sql);
            $conn->execute([
                ":name" => $name,
                ":email" => $email,
                ":password" => password_hash($password, PASSWORD_DEFAULT)
            ]);

            $_SESSION['user'] = [
                "id" => $Ophanim->lastInsertId(),
                "name" => $name,
                "email" => $email,
                "login" => true,
                "newUser" => true
            ];

            header('location: dashboard.php');
            exit;
        }

    }
}
?>