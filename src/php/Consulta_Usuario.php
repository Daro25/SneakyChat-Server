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
    if ($nombre && $pass){
        // Uso de sentencias preparadas
        $stmt = $conex->prepare("SELECT Contra FROM usuario WHERE Nomb = ?");
        $stmt->bind_param("s", $nombre);
        $stmt->execute();
        $result = $stmt->get_result();
        $hash = '';
        while ($li = $result->fetch_assoc()) {
            $hash = $li['Contra'];
        }
        function esHash($str) {
            return preg_match('/^\$2[aby]\$\d{2}\$[\.\/A-Za-z0-9]{53}$/', $str);
        }
        $is_hash = esHash($hash);
        if (!$is_hash && $_GET['pass'] == $hash) {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $stmt = $conex->prepare("UPDATE usuario SET Contra = ? WHERE Nomb = ?");
                $stmt->bind_param("ss", $hash, $_GET['nombre']);
                $stmt->execute();
                $is_hash = true;
        }
        if ($is_hash && password_verify($pass, $hash)){
            $stmt = $conex->prepare("SELECT Id_User, Nomb, keyPublic FROM usuario WHERE Nomb = ?");
            $stmt->bind_param("s", $GET['nombre']);
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

            echo json_encode($tablasArray);}
    } else {
        echo json_encode(['error'=> 'La contraseña fue incorrecta.']);
    }
    // Cerrar conexión
    mysqli_close($conex);
} else {
    echo json_encode(['error' => 'La comunicación no fue posible']);
}
?>

