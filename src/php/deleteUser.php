<?php 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Función para verificar si una cadena parece un hash bcrypt
function esHash($str) {
    return preg_match('/^\$2[aby]\$\d{2}\$[\.\/A-Za-z0-9]{53}$/', $str);
}

$server = 'localhost';
$user = 'droa';
$password = 'droaPluving$1';
$database = 'mensajer_a';

$conex = mysqli_connect($server, $user, $password, $database);

if (!$conex) {
    echo json_encode(['error' => 'La comunicación no fue posible']);
    exit;
}

// Saneamiento y validación
$nombre = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$pass = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$edad = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT);

if (!$nombre || strlen($nombre) > 20 || !$pass || strlen($pass) > 20 || $edad === false) {
    echo json_encode(['error' => '👀 Parámetros inválidos.']);
    exit;
}

// Buscar contraseña asociada al usuario
$stmt = $conex->prepare("SELECT Contra FROM usuario WHERE Nomb = ?");
$stmt->bind_param("s", $nombre);
$stmt->execute();
$result = $stmt->get_result();

$hash = '';
if ($row = $result->fetch_assoc()) {
    $hash = $row['Contra'];
}

$is_hash = esHash($hash);

// Migración: convertir contraseña antigua a hash si coincide
if (!$is_hash && $pass === $hash) {
    $nuevo_hash = password_hash($pass, PASSWORD_DEFAULT);
    $stmt = $conex->prepare("UPDATE usuario SET Contra = ? WHERE Nomb = ?");
    $stmt->bind_param("ss", $nuevo_hash, $nombre);
    $stmt->execute();
    $hash = $nuevo_hash;
    $is_hash = true;
}

// Verificación de contraseña
if ($is_hash && password_verify($pass, $hash)) {
    $stmt = $conex->prepare("DELETE FROM usuario WHERE Nomb = ? AND Edad = ?");
    $stmt->bind_param("si", $nombre, $edad);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['mensaje' => 'Hola desconocido, ¿tú quién eres? [Fuiste eliminado del sistema].']);
    } else {
        echo json_encode(['mensaje' => 'Ups! No logramos encontrar dicho usuario.']);
    }
} else {
    echo json_encode(['error' => 'Contraseña o usuario incorrecto.']);
}

mysqli_close($conex);
?>
