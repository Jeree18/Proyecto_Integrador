<?php
session_start();
include "Persistencia/Conexion.php";

if(!isset($_SESSION['Usuario']) || $_SERVER['REQUEST_METHOD'] != 'POST'){
    header("location:index.php");
    exit();
}

$conexion = conectar();
$idUsuario = (int)$_SESSION['Usuario']['ID'];

// Descartar una notificación (solo si es del usuario)
if(isset($_POST['descartar_notificacion'])){
    $idNotificacion = (int)($_POST['id_notificacion'] ?? 0);
    mysqli_query($conexion, "DELETE FROM notificaciones WHERE ID = $idNotificacion AND ID_usuario = $idUsuario");
}

// Marcar todas como leídas
if(isset($_POST['limpiar_notificaciones'])){
    mysqli_query($conexion, "DELETE FROM notificaciones WHERE ID_usuario = $idUsuario");
}

mysqli_close($conexion);

// Volver a la página donde estaba el usuario (solo dentro de este mismo sitio)
$volver = "index.php";
if(!empty($_SERVER['HTTP_REFERER'])){
    $partes = parse_url($_SERVER['HTTP_REFERER']);
    $hostPropio = explode(':', $_SERVER['HTTP_HOST'])[0];
    if(isset($partes['host']) && $partes['host'] == $hostPropio){
        $volver = $_SERVER['HTTP_REFERER'];
    }
}
header("location:".$volver);
exit();