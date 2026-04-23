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

// 1. VALIDACIÓN DE SOLAPAMIENTO (Solo para crear y editar)
if ($accion == 'create' || $accion == 'update') {
    $sql_check = "SELECT id_reserva FROM reservas WHERE 
                  (('$inicio' < fin_datetime) AND ('$fin' > inicio_datetime))";
    
    // Si estamos editando, ignoramos nuestra propia reserva actual
    if ($accion == 'update') { $sql_check .= " AND id_reserva != $id"; }

    $result = $conn->query($sql_check);
    if ($result && $result->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "CONFLICTO: Ya hay una reserva en ese horario."]);
        exit;
    }
}

// 2. EJECUCIÓN DE ACCIONES
switch ($accion) {
    case 'create':
        // PASO PREVIO: Buscamos un ID de usuario válido antes de insertar
        $res_user = $conn->query("SELECT id_usuario FROM usuarios LIMIT 1");
        
        if ($res_user && $res_user->num_rows > 0) {
            $user_row = $res_user->fetch_assoc();
            $id_admin = $user_row['id_usuario']; // Cogemos el ID del primer usuario que encontremos
        } else {
            // Si la tabla de usuarios está vacía, abortamos
            echo json_encode(["status" => "error", "message" => "Error DB: No hay usuarios creados para asignar la reserva."]);
            exit;
        }

        // Ahora sí, insertamos la reserva usando la variable de PHP $id_admin
        $sql = "INSERT INTO reservas (id_usuario, inicio_datetime, fin_datetime, descripcion) 
                VALUES ($id_admin, '$inicio', '$fin', '$desc')";
        break;
        
    case 'update':
        $sql = "UPDATE reservas SET inicio_datetime='$inicio', fin_datetime='$fin', descripcion='$desc' WHERE id_reserva=$id";
        break;
        
    case 'delete':
        $sql = "DELETE FROM reservas WHERE id_reserva=$id";
        break;
        
    default:
        echo json_encode(["status" => "error", "message" => "Acción no reconocida."]);
        exit;
}

if ($conn->query($sql)) {
    echo json_encode(["status" => "success", "message" => "¡Operación completada!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error SQL: " . $conn->error]);
}

$conn->close();
?>