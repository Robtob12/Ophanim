<?php
$errores = [];

$name = trim($_POST['name'] ?? "nueva_coneccion");
$host = trim($_POST['host'] ?? "127.0.0.1");
$port = trim($_POST['port'] ?? "3306");
$usuario = trim($_POST['usuario'] ?? "root");
$password = trim($_POST['password'] ?? "root");
$database_name = trim($_POST['database_name'] ?? "");
$tipo = trim($_POST['tipo'] ?? "");

// Validaciones
if ($name === "") {
    $errores[] = "El nombre es obligatorio";
}

if ($tipo === "") {
    $errores[] = "El tipo de servidor es obligatorio";
}

// SQLite no necesita host/port/usuario
if ($tipo !== "sqlite") {
    if ($host === "") {
        $errores[] = "El host es obligatorio";
    }

    if ($port === "" || !is_numeric($port)) {
        $errores[] = "El puerto debe ser numérico";
    }

    if ($usuario === "") {
        $errores[] = "El usuario es obligatorio";
    }
}

if (empty($errores)) {
    try {
        switch ($tipo) {

            case "mysql":
                
                $conection = new PDO("mysql:host=$host;dbname=$database_name", $usuario, $password);
                break;

            case "pgsql":
                $dsn = "pgsql:host=$host;port=$port;dbname=$database_name";
                $conection = new PDO($dsn, $usuario, $password);
                break;

            case "sqlite":
                $dsn = "sqlite:$database_name";
                $conection = new PDO($dsn);
                break;

            default:
                throw new Exception("Tipo de base de datos no válido");
        }

        // Configuración extra
        $conection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        //guardar conecion

        $clave = "ophanim_secret";
        $password_encriptado = openssl_encrypt($password, "AES-128-ECB", $clave);

        $stmt = $Ophanim->prepare("
            INSERT INTO conexiones 
            (user_id, name, host, port, usuario, password, database_name, tipo, created_at)
            VALUES 
            (:user_id, :name, :host, :port, :usuario, :password, :database_name, :tipo, NOW())
        ");

        $stmt->execute([
            ':user_id' => $_SESSION['user']['id'], // asegúrate que existe
            ':name' => $name,
            ':host' => $host,
            ':port' => $port,
            ':usuario' => $usuario,
            ':password' => $password_encriptado,
            ':database_name' => $database_name,
            ':tipo' => $tipo
        ]);

        $_SESSION['server'] = [
            'host' => $host,
            'port' => $port,
            'usuario' => $usuario,
            'password' => openssl_encrypt($password, "AES-128-ECB", $clave),
            'database_name' => $database_name,
            'tipo' => $tipo
        ];

        // 🔥 FORZAR
        $_SESSION['user']['newUser'] = false;
        header('Location: dashboard.php?exito=create_conection');
        exit;


    } catch (PDOException $e) {
        echo "<p class='result'>❌ Error de conexión: " . $e->getMessage() . "</p>";
    }
}
