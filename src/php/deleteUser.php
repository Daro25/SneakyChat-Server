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
    // Saneamiento y validación
    $nombre = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $pass = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
    $edad = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT);
    if ($nombre && strlen($nombre) <= 20 && $pass && strlen($pass) <= 20 && $edad !== false)
    {
        // Uso de sentencias preparadas
        $stmt = $conex->prepare("DELETE FROM usuario WHERE Contra = ? AND Nomb = ? AND Edad = ?");
        $stmt->bind_param("ssi", $pass, $nombre, $edad);
        $stmt->execute();
        
        // Verificar si se eliminó alguna fila
            if ($stmt->affected_rows > 0) {
                echo 'Hola desconocido, tu quien eres? [Fuiste eliminado del sistema].';
            } else {
                echo 'Ups! No logramos encontrar dicho usuario.';
            }
    } else echo '👀 Como que reciví unos parametros medio raros, vuelve intentarlo pero deja de hacer cosas raras.';
    // Cerrar conexión
    mysqli_close($conex);
} else {
    echo 'La comunicación no fue posible';
}
?>