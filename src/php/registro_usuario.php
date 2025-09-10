<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

class Respuesta {
    public $res;
    function __construct($res) {
        $this->res = $res;
    }
}

$server = 'localhost';
$user = 'droa';
$password = 'droaPluving$1';
$database = 'mensajer_a';

$conex = mysqli_connect($server, $user, $password, $database);

if (!$conex) {
    echo json_encode(['resultado' => "La conexión a la base de datos falló: " . mysqli_connect_error()]);
    exit();
}

// Obtener y sanitizar inputs
$nomb = filter_input(INPUT_GET, 'nomb', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$contra = filter_input(INPUT_GET, 'contra', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$key = filter_input(INPUT_GET, 'key', FILTER_SANITIZE_STRING);
$sala_id = filter_input(INPUT_GET, 'sala_id', FILTER_VALIDATE_INT);
$edad = filter_input(INPUT_GET, 'edad', FILTER_VALIDATE_INT);
$keyHash = password_hash($contra, PASSWORD_DEFAULT);

// Validar campos
if (!$nomb || !$contra || !$key || $sala_id === false || $edad === false) {
    echo json_encode(['resultado' => "Todos los campos son obligatorios y sala_id y edad deben ser números válidos."]);
    exit();
}

// Insertar nuevo usuario
$stmt = $conex->prepare("INSERT INTO usuario (Id_User, Nomb, Contra, Edad, keyPublic) VALUES (null,?, ?, ?, ?)");
$stmt->bind_param("ssis", $nomb, $keyHash, $edad, $key);

if ($stmt->execute()) {
    $id = $conex->insert_id;

    // Relación con sala
    $stmt->close();
    $stmt = $conex->prepare('INSERT INTO `sala-usuario` (Id, Id_Sala, Id_Usuario) VALUES (null, ?, ?)');
    $stmt->bind_param("ii", $sala_id, $id);
    $stmt->execute();

    echo json_encode(['ID' => $id]);
} else {
    echo json_encode(['resultado' => "Error al insertar el registro: " . $stmt->error]);
    error_log("Error al insertar el registro: " . $stmt->error);
}

$stmt->close();
$conex->close();
?>
