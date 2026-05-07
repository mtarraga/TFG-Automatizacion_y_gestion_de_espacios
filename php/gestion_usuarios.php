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
        $stmt = $conn->prepare("INSERT INTO usuario (nombre_usuario, clave_hash, id_rol) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $nombre_qsys, $pin_encriptado, $rol_qsys);
        $mensaje_exito = "¡Usuario '$nombre_qsys' creado correctamente!";
        break;

    case 'update':
        if(empty($nombre_qsys) || empty($pin_qsys) || empty($rol_qsys)) {
            echo json_encode(["status" => "error", "message" => "Faltan datos obligatorios"]); exit;
        }
        $pin_encriptado = hash('sha256', $pin_qsys);
        $stmt = $conn->prepare("UPDATE usuario SET clave_hash = ?, id_rol = ? WHERE nombre_usuario = ?");
        $stmt->bind_param("sis", $pin_encriptado, $rol_qsys, $nombre_qsys);
        $mensaje_exito = "¡Datos de '$nombre_qsys' actualizados!";
        break;

    case 'delete':
        if(empty($nombre_qsys)) {
            echo json_encode(["status" => "error", "message" => "Falta el nombre del usuario"]); exit;
        }
        if(strtolower($nombre_qsys) === 'admin') {
            echo json_encode(["status" => "error", "message" => "No se puede borrar al administrador principal"]); exit;
        }
        $stmt = $conn->prepare("DELETE FROM usuario WHERE nombre_usuario = ?");
        $stmt->bind_param("s", $nombre_qsys);
        $mensaje_exito = "¡Usuario '$nombre_qsys' eliminado con éxito!";
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Acción no reconocida"]);
        exit;
}

if ($stmt->execute()) {
    // Si es creación, o si se ha afectado alguna fila (en update/delete)
    if ($accion == 'create' || $stmt->affected_rows > 0) {
        echo json_encode(["status" => "success", "message" => $mensaje_exito]);
    } else {
        echo json_encode(["status" => "error", "message" => "Usuario no encontrado o sin cambios"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}

$stmt->close();
$conn->close();
?>