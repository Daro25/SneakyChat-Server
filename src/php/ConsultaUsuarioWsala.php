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

if ($conex) {
    $id_sala = filter_input(INPUT_GET, 'id_sala', FILTER_VALIDATE_INT);

    if ($id_sala !== null && $id_sala !== false) {
        $tablasArray = [];

        $stmt1 = $conex->prepare("SELECT Id_Usuario FROM sala_usuario WHERE Id_Sala = ?");
        $stmt1->bind_param("i", $id_sala);
        $stmt1->execute();
        $result1 = $stmt1->get_result();

        while ($id = $result1->fetch_assoc()) {
            $stmt2 = $conex->prepare("SELECT Id_User, Nomb, keyPublic FROM usuario WHERE Id_User = ?");
            $stmt2->bind_param("i", $id['Id_Usuario']);
            $stmt2->execute();
            $result2 = $stmt2->get_result();

            while ($li = $result2->fetch_assoc()) {
                $tablasArray[] = new Tabla($li['Id_User'], $li['Nomb'], $li['keyPublic']);
            }

            $stmt2->close();
        }

        $stmt1->close();
        mysqli_close($conex);

        if (empty($tablasArray)) {
            echo json_encode(['mensaje' => 'No se encontraron usuarios para esa sala.']);
        } else {
            echo json_encode($tablasArray);
        }

    } else {
        echo json_encode(['error' => 'ID de sala inválido']);
    }
} else {
    echo json_encode(['error' => 'No se pudo conectar a la base de datos']);
}
?>
