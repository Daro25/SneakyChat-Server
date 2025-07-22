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

$user = 'droa';
$server = 'localhost';
$database = 'mensajer_a';
$password = 'droaPluving$1';
$conex = mysqli_connect($server, $user, $password, $database);

if($conex) {
    // Saneamiento y validación
    $nombre = filter_input(INPUT_GET, 'nombre', FILTER_SANITIZE_STRING);
    $pass = filter_input(INPUT_GET, 'pass', FILTER_SANITIZE_STRING);

    // Uso de sentencias preparadas
    $stmt = $conex->prepare("SELECT Contra FROM usuario WHERE Nomb = ?");
    $stmt->bind_param("s",  $nombre);
    $stmt->execute();
    $result = $stmt->get_result();
    $hash = '';
    while ($li = $result->fetch_assoc()) {
        $hash = $li['Contra'];
    }
    if (password_verify($pass, $hash)){
        $stmt = $conex->prepare("SELECT Id_User, Nomb, keyPublic FROM usuario WHERE Nomb = ?");
        $stmt->bind_param("s", $nombre);
        $stmt->execute();
        
        // Obtener resultados
        $result = $stmt->get_result();
        $tablasArray = [];
        
        while ($li = $result->fetch_assoc()) {
            $Id_User = $li['Id_User'];
            $Nomb = $li['Nomb']; 
            $keyPublic = $li['keyPublic'];
            $tablasArray[] = new Tabla($Id_User, $Nomb, $keyPublic);
        }

        echo json_encode($tablasArray);
    } else {
        echo json_encode(['error'=> 'La contraseña fue incorrecta.']);
    }
    // Cerrar conexión
    mysqli_close($conex);
} else {
    echo json_encode(['error' => 'La comunicación no fue posible']);
}
?>

