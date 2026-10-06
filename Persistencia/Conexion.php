<?php
function conectar():mysqli{
    
    try{
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $conexion= new mysqli("localhost","root","","cursolandia");
        $conexion->set_charset("utf8mb4");
        return $conexion;
    }catch(mysqli_sql_exception $e){
        echo "Error al comunciase con la base de datos!";
        exit();
    }
    
}





?>