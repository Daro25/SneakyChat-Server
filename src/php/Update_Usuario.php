<?php 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$user = 'droa';
$server = 'localhost';
$database = 'mensajer_a';
$password = 'droaPluving$1';
$conex = mysqli_connect($server, $user, $password, $database);

if($conex) {
    // Saneamiento y validación
    $nombre = filter_input(INPUT_GET, 'nombre', FILTER_SANITIZE_STRING);
    $pass = filter_input(INPUT_GET, 'pass', FILTER_SANITIZE_STRING);
    $key = $_GET['key'];

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
            $stmt = $conex->prepare("UPDATE usuario SET Contra = ? WHERE Nomb = ?");
            $stmt->bind_param("ss", $hash, $nombre);
            $stmt->execute();
            $is_hash = true;
    }
    if ($is_hash && password_verify($pass, $hash)){
        $stmt = $conex->prepare("UPDATE usuario SET keyPublic = ? WHERE Nomb = ?");
        $stmt->bind_param("ss", $key, $nombre);
        $stmt->execute();
        
        // Obtener resultados
        $result = $stmt->get_result();
        echo json_encode(['Num'=>mysqli_affected_rows($conex)]);
    } else {
        echo json_encode(['error' => 'Esa contraseña no coincide.']);
    }
    // Cerrar conexión
    mysqli_close($conex);
} else {
    echo json_encode(['error' => 'La comunicación no fue posible']);
}
?>