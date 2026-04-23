<?php
header('Content-Type: application/json');
$conn = new mysqli("127.0.0.1", "root", "", "teatro_control_db");

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Fallo de conexión"]);
    exit;
}

// Consultamos las reservas futuras o en curso
$sql = "SELECT id_reserva, inicio_datetime, fin_datetime, descripcion FROM reservas ORDER BY inicio_datetime ASC LIMIT 5";
$result = $conn->query($sql);

$reservas = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $reservas[] = $row;
    }
}

echo json_encode($reservas);
$conn->close();
?>