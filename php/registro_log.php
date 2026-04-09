<?php
$servidor = "127.0.0.1";
$usuario_db = "root";

$password_db = "";

$nombre_db = "teatro_control_db";

$conn = new mysqli($servidor, $usuario_db, $password_db, $nombre_db);

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Conexion fallida"]));
}

// Si el usuario es 0 o no llega, lo convertimos a NULL para que MySQL no se queje
$id_usuario = (isset($_GET['id_usuario']) && $_GET['id_usuario'] != '0') ? (int)$_GET['id_usuario'] : null;

// Si llega un id_equipo por la URL lo guardamos, si no, lo dejamos en null
$id_equipo = (isset($_GET['id_equipo']) && $_GET['id_equipo'] != '0') ? (int)$_GET['id_equipo'] : null;


$tipo_evento = isset($_GET['evento']) ? $_GET['evento'] : 'LOGIN';
$resultado = isset($_GET['resultado']) ? $_GET['resultado'] : 'EXITO';
$accion_detalle = isset($_GET['detalle']) ? $_GET['detalle'] : 'Acceso al sistema';

// 2. El Nivel de Alerta es numerico (1, 2 o 3)
$nivel_alerta = isset($_GET['alerta']) ? (int)$_GET['alerta'] : 1;


$sql = "INSERT INTO logs_eventos (id_usuario, id_equipo, tipo_evento, resultado, accion_detalle, nivel_alerta) 
        VALUES (?, ?, ?, ?, ?, ?)";


$stmt = $conn->prepare($sql);

if ($stmt) {
    // iisssi = Entero(User), Entero(Equipo), String, String, String, Entero(Alerta)
    $stmt->bind_param("iisssi", $id_usuario, $id_equipo, $tipo_evento, $resultado, $accion_detalle, $nivel_alerta);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Evento registrado"]);
    }
    else {
        echo json_encode(["status" => "error", "message" => "Error MySQL: " . $stmt->error]);
    }
    $stmt->close();
}
else {
    echo json_encode(["status" => "error", "message" => "Error Preparando Consulta"]);
}

$conn->close();
?>