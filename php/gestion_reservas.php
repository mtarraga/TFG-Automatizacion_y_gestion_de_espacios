<?php
header('Content-Type: application/json');
$conn = new mysqli("127.0.0.1", "root", "", "teatro_control_db");

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Fallo de conexión a la BD"]);
    exit;
}

$accion = $_GET['accion'] ?? '';
$id = $_GET['id'] ?? '';
$inicio = $_GET['inicio'] ?? '';
$fin = $_GET['fin'] ?? '';
$desc = $_GET['desc'] ?? '';

// 1. VALIDACIÓN DE SOLAPAMIENTO
if ($accion == 'create' || $accion == 'update') {
    $sql_check = "SELECT id_reserva FROM reserva WHERE 
                  (('$inicio' < fin_datetime) AND ('$fin' > inicio_datetime))";
    
    if ($accion == 'update') { $sql_check .= " AND id_reserva != $id"; }

    $result = $conn->query($sql_check);
    if ($result && $result->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "ERROR: El horario ya está reservado."]);
        exit;
    }
}

// 2. EJECUCIÓN DE ACCIONES
switch ($accion) {
    case 'create':
        $res_user = $conn->query("SELECT id_usuario FROM usuario LIMIT 1");
        $id_admin = ($res_user && $res_user->num_rows > 0) ? $res_user->fetch_assoc()['id_usuario'] : 1;

        $sql = "INSERT INTO reserva (id_usuario, inicio_datetime, fin_datetime, descripcion) 
                VALUES ($id_admin, '$inicio', '$fin', '$desc')";
        $mensaje_exito = "¡Reserva guardada correctamente!";
        break;
        
    case 'update':
        $sql = "UPDATE reserva SET inicio_datetime='$inicio', fin_datetime='$fin', descripcion='$desc' WHERE id_reserva=$id";
        $mensaje_exito = "¡Reserva modificada con éxito!";
        break;
        
    case 'delete':
        $sql = "DELETE FROM reserva WHERE id_reserva=$id";
        $mensaje_exito = "¡Reserva eliminada!";
        break;
        
    default:
        echo json_encode(["status" => "error", "message" => "Acción no reconocida."]);
        exit;
}

if ($conn->query($sql)) {
    echo json_encode(["status" => "success", "message" => $mensaje_exito]);
} else {
    echo json_encode(["status" => "error", "message" => "Error SQL: " . $conn->error]);
}

$conn->close();
?>