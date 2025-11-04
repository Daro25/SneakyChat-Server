<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Habilitar reporte de errores
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$server = 'localhost';
$user = 'droa';
$password = 'droaPluving$1';
$database = 'mensajer_a';

$conex = mysqli_connect($server, $user, $password, $database);

if (!$conex) {
    echo json_encode(['resultado' => "La conexión falló: " . mysqli_connect_error()]);
    exit();
}

// ✅ Sanitización correcta y segura
$nomb = filter_input(INPUT_GET, 'nomb', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$contra = filter_input(INPUT_GET, 'contra', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$key = filter_input(INPUT_GET, 'key', FILTER_SANITIZE_STRING);
$sala_id = filter_input(INPUT_GET, 'sala_id', FILTER_VALIDATE_INT);
$edad = filter_input(INPUT_GET, 'edad', FILTER_VALIDATE_INT);

// ✅ Validar existencia y tipos
if (!$nomb || !$contra || !$key || $sala_id === false || $edad === false) {
    echo json_encode(['resultado' => "Campos inválidos"]);
    exit();
}

// ✅ Hash seguro de contraseña
$keyHash = password_hash($contra, PASSWORD_DEFAULT);

// ✅ Insert usuario
$stmt = $conex->prepare("INSERT INTO `usuario` (`Nomb`, `Contra`, `Edad`, `keyPublic`) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssis", $nomb, $keyHash, $edad, $key);
$stmt->execute();
$id = $conex->insert_id;
$stmt->close();

// ✅ Relación con sala
$stmt = $conex->prepare("INSERT INTO `sala-usuario` (`Id_Sala`, `Id_Usuario`) VALUES (?, ?)");
$stmt->bind_param("ii", $sala_id, $id);
$stmt->execute();
$stmt->close();

echo json_encode(['ID' => $id]);

$conex->close();
?>