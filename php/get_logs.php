<?php
header('Content-Type: application/json');

$servidor = "127.0.0.1";
$usuario  = "root";
$password = "";
$db       = "teatro_control_db";

$conn = new mysqli($servidor, $usuario, $password, $db);

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Fallo de conexión"]);
    exit;
}

// Consulta la tabla logs_eventos descrita en tu memoria
$sql = "SELECT id_log, fecha_hora, tipo_evento, accion_detalle, nivel_alerta FROM logs_eventos ORDER BY id_log DESC LIMIT 15";
$result = $conn->query($sql);

$logs = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
}

echo json_encode($logs);
$conn->close();
?>