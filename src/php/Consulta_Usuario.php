<?php 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

class Tabla 
{
    public $Id_User, $Nomb, $KeyPublic;
    
    function __construct($Id_User, $Nomb, $KeyPublic)
    {
        $this->Id_User = $Id_User;
        $this->Nomb = $Nomb;
        $this->KeyPublic = $KeyPublic;
    }
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

// Validar y sanitizar entrada
$nombre = filter_input(INPUT_GET, 'nombre', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$pass = filter_input(INPUT_GET, 'pass', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

if (!$nombre || !$pass) {
    echo json_encode(['error' => 'Faltan datos requeridos']);
    exit;
}

// Buscar usuario
$stmt = $conex->prepare("SELECT Contra FROM usuario WHERE Nomb = ?");
$stmt->bind_param("s", $nombre);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $hash = $row['Contra'];

    // Validar si es hash de bcrypt
    $esHash = preg_match('/^\$2[aby]\$\d{2}\$[\.\/A-Za-z0-9]{53}$/', $hash);

    // Si aún no es hash, actualizar con hash real
    if (!$esHash && $pass === $hash) {
        $nuevoHash = password_hash($pass, PASSWORD_DEFAULT);
        $update = $conex->prepare("UPDATE usuario SET Contra = ? WHERE Nomb = ?");
        $update->bind_param("ss", $nuevoHash, $nombre);
        $update->execute();
        $hash = $nuevoHash;
        $esHash = true;
    }

    // Validar contraseña
    if ($esHash && password_verify($pass, $hash)) {
        $stmt = $conex->prepare("SELECT Id_User, Nomb, keyPublic FROM usuario WHERE Nomb = ?");
        $stmt->bind_param("s", $nombre);
        $stmt->execute();
        $result = $stmt->get_result();

        $tablasArray = [];
        while ($li = $result->fetch_assoc()) {
            $tablasArray[] = new Tabla($li['Id_User'], $li['Nomb'], $li['keyPublic']);
        }

        echo json_encode($tablasArray);
    } else {
        echo json_encode(['error' => 'La contraseña fue incorrecta.']);
    }
} else {
    echo json_encode(['error' => 'Usuario no encontrado.']);
}

mysqli_close($conex);
?>
