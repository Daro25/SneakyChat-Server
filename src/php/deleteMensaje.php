<?php 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");

$user = 'droa';
$server = 'localhost';
$database = 'mensajer_a';
$password = 'droaPluving$1';
$conex = mysqli_connect($server, $user, $password, $database);

if($conex) {
    
    $mensajeId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if ($mensajeId !== false && $mensajeId !== null) {
        
        $stmt = $conex->prepare("DELETE FROM mensaje WHERE Id = ?");
        $stmt->bind_param("i", $mensajeId);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo 'El mensaje fue eliminado exitosamente.';
        } else {
            echo 'No se encontró ningún mensaje.'+$mensajeId;
        }

        $stmt->close();
    } else {
        echo 'Parámetro inválido.';
    }

    mysqli_close($conex);
} else {
    echo 'La comunicación no fue posible con la base de datos.';
}
?>