<?php
// gestion_usuarios.php
header('Content-Type: application/json');

$conn = new mysqli("127.0.0.1", "root", "", "teatro_control_db");

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Fallo de conexión a la BD"]);
    exit;
}

$accion = $_GET['accion'] ?? '';
$nombre_qsys = $_GET['nombre'] ?? '';
$pin_qsys = $_GET['pin'] ?? '';
$rol_qsys = $_GET['rol_id'] ?? '';

switch ($accion) {
    case 'create':
        if(empty($nombre_qsys) || empty($pin_qsys) || empty($rol_qsys)) {
            echo json_encode(["status" => "error", "message" => "Faltan datos obligatorios"]); exit;
        }
        $pin_encriptado = hash('sha256', $pin_qsys);
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre_usuario, clave_hash, id_rol) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $nombre_qsys, $pin_encriptado, $rol_qsys);
        break;

    case 'update':
        if(empty($nombre_qsys) || empty($pin_qsys) || empty($rol_qsys)) {
            echo json_encode(["status" => "error", "message" => "Faltan datos obligatorios"]); exit;
        }
        $pin_encriptado = hash('sha256', $pin_qsys);
        $stmt = $conn->prepare("UPDATE usuarios SET clave_hash = ?, id_rol = ? WHERE nombre_usuario = ?");
        $stmt->bind_param("sis", $pin_encriptado, $rol_qsys, $nombre_qsys);
        break;

    case 'delete':
        if(empty($nombre_qsys)) {
            echo json_encode(["status" => "error", "message" => "Falta el nombre del usuario"]); exit;
        }
        // Protección extra: No dejar que nadie borre al Admin original por accidente
        if(strtolower($nombre_qsys) === 'admin') {
            echo json_encode(["status" => "error", "message" => "No se puede borrar al administrador principal"]); exit;
        }
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE nombre_usuario = ?");
        $stmt->bind_param("s", $nombre_qsys);
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Acción no reconocida"]);
        exit;
}

if ($stmt->execute()) {
    if ($accion == 'create' || $stmt->affected_rows > 0) {
        echo json_encode(["status" => "success", "message" => "Operación completada con éxito"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Usuario no encontrado o sin cambios"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}

$stmt->close();
$conn->close();
?>