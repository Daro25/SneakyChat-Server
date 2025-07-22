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
        $stmt = $conex->prepare("SELECT Contra FROM usuario WHERE Nomb = ?");
        $stmt->bind_param("s",  $nombre);
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
        if (!$is_hash && $pass == $hash) {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $is_hash = true;
        }
        if ($is_hash && password_verify($pass, $hash)){
            $stmt = $conex->prepare("DELETE FROM usuario WHERE Nomb = ? AND Edad = ?");
            $stmt->bind_param("si", $nombre, $edad);
            $stmt->execute();
            
            // Verificar si se eliminó alguna fila
                if ($stmt->affected_rows > 0) {
                    echo 'Hola desconocido, tu quien eres? [Fuiste eliminado del sistema].';
                } else {
                    echo 'Ups! No logramos encontrar dicho usuario.';
                }
        } else {
            echo 'Creo que esa no era la contraseña o el usuario correcto, vuelve a intentarlo.';
        }
    } else echo '👀 Como que reciví unos parametros medio raros, vuelve intentarlo pero deja de hacer cosas raras.';
    // Cerrar conexión
    mysqli_close($conex);
} else {
    echo 'La comunicación no fue posible';
}
?>