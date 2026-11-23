<?php
function conectar() {
    $archivo = file_exists(__DIR__ . "/config.php") ? "config.php" : "config.example.php";
    $c = include __DIR__ . "/" . $archivo;

    if (!is_array($c)) {
        die("El archivo $archivo no tiene el formato correcto (falta return [...]).");
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try {
        $conexion = new mysqli($c["host"], $c["user"], $c["pass"], $c["db"]);
        $conexion->set_charset("utf8mb4");
        return $conexion;
    } catch (mysqli_sql_exception $e) {
        die("Error al comunicarse con la base de datos! ");
    }
}

?>