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
function insertar_usuario($datos,$conexion){

    $nombre= mysqli_real_escape_string($conexion, $datos['nombre']);
    $apellido=mysqli_real_escape_string($conexion, $datos['apellido']);
    $mail= mysqli_real_escape_string($conexion, $datos['mail']);
    $clave= mysqli_real_escape_string($conexion, $datos['clave']);
    $dni= mysqli_real_escape_string($conexion, $datos['dni']);
    $nacimiento= mysqli_real_escape_string($conexion, $datos['nacimiento']);
    $telefono= mysqli_real_escape_string($conexion, $datos['telefono']);
    $foto= mysqli_real_escape_string($conexion, $datos['foto']);
    $intereses= mysqli_real_escape_string($conexion, $datos['intereses']);
    $antecedentes= mysqli_real_escape_string($conexion, $datos['antecedentes']);
    $sql = "INSERT INTO usuarios(Nombre, Apellido, Mail, `Contraseña`, Dni, Nacimiento, Telefono, Foto, Intereses, Antecedentes, Rol, Plan)
            VALUES('$nombre', '$apellido', '$mail', '$clave', '$dni', '$nacimiento', '$telefono', '$foto', '$intereses', '$antecedentes', 'usuario', 'gratuito')";
    return mysqli_query($conexion,$sql);        
}
function mail_existente($mail,$conexion){
    $mailseguro = mysqli_real_escape_string($conexion, $mail);
    $sql="SELECT Mail
                FROM Usuarios
                WHERE Mail = '$mailseguro'";
    $resultado=mysqli_query($conexion, $sql);
    if($fila = mysqli_fetch_assoc($resultado)){
            if(strtolower($fila['Mail']) === strtolower($mail)){
                return true;
            }
            
        }
    return false;

}
function dni_existente($dni,$conexion){
    $dniseguro  = mysqli_real_escape_string($conexion, $dni);
    $sql="SELECT Dni
                FROM Usuarios
                WHERE Dni = '$dniseguro'";
    $resultado=mysqli_query($conexion, $sql);
    if($fila = mysqli_fetch_assoc($resultado)){
            if((string)$fila['Dni'] === $dni){
                return true;
            }
            
        }
    return false;

}

?>