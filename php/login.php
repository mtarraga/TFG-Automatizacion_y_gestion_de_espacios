<?php
// Configuracion de la base de datos
$host = "localhost";
$user = "root";
$pass = "";
$db = "teatro_control_db";


header('Content-Type: application/json');

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Fallo al conectar a MySQL: " . $conn->connect_error]));
}

// Captura el PIN de Q-SYS
if (isset($_GET['pin'])) {
    $pin_recibido = $_GET['pin'];

    // Encriptacion del PIN que entra para poder compararlo usando la encriptacion SHA-256.
    $pin_encriptado = hash('sha256', $pin_recibido);

    // Buscar
    $sql = "SELECT id_usuario, nombre_usuario, id_rol FROM usuarios WHERE clave_hash = '$pin_encriptado'";
    $result = $conn->query($sql);

    // Si coincide el hash
    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();

        echo json_encode([
            "status" => "success",
            "id_usuario" => $usuario['id_usuario'], // Envia el ID exacto
            "nombre" => $usuario['nombre_usuario'],
            "rol_id" => $usuario['id_rol'] // Devuelve el ID del rol (1, 2 o 3)
        ]);
    }
    else {
        echo json_encode(["status" => "error", "message" => "PIN incorrecto"]);
    }
}
else {
    echo json_encode(["status" => "error", "message" => "No se ha enviado ningún PIN"]);
}

$conn->close();
?>