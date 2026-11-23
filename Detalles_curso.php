<?php
session_start();
include "Persistencia/Conexion.php";
$error = false;
$mensajeError = "";
if(!isset($_SESSION['Usuario'])){
    header("location:login.php");
    exit();
}

$conexion = conectar();
mysqli_set_charset($conexion, 'utf8mb4');
$idUsuario = (int)$_SESSION['Usuario']['ID'];
$idCurso = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$hoy = date("Y-m-d");

$curso = null;
$esProfesor = false;
$esEstudiante = false;
$miInscripcion = null;
$materiales = false;
$preguntas = false;
$etiquetas = false;
$mensaje = "";
$textoEstado = "";
$claseEstado = "";

if($idCurso <= 0){
    $error = true;
    $mensajeError = "El identificador del curso no es válido.";
}else{

    $consulta = mysqli_query($conexion, "SELECT c.*, u.Nombre, u.Apellido, u.Plan AS plan_creador,
                                        (SELECT COUNT(*) FROM inscripcion i
                                         WHERE i.ID_Curso = c.ID AND i.Estado IN ('activa','pendiente')) AS inscriptos
                                        FROM cursos c
                                        INNER JOIN usuarios u ON u.ID = c.ID_Creador
                                        WHERE c.ID = $idCurso");

    if(mysqli_num_rows($consulta) == 0){
        $error = true;
        $mensajeError = "El curso que estás buscando no existe.";
    }else{
        $curso = mysqli_fetch_assoc($consulta);

        $esProfesor = ($curso['ID_Creador'] == $idUsuario);
        if($esProfesor){
            mysqli_close($conexion);
            header("location:gestionar_curso.php?id=$idCurso");
            exit();
        }

        if(!$esProfesor){
            $consultaInscripcion = mysqli_query($conexion, "SELECT Estado
                                                            FROM inscripcion
                                                            WHERE ID_Usuario = $idUsuario
                                                            AND ID_Curso = $idCurso");
            $miInscripcion = mysqli_fetch_assoc($consultaInscripcion);

            if($miInscripcion && $miInscripcion['Estado'] == 'activa'){
                $esEstudiante = true;
            }
        }

        // Estado del curso según las fechas
        if($hoy < $curso['Fecha_Inicio']){
            $textoEstado = "Próximo";
            $claseEstado = "text-bg-dark";
        }elseif($hoy > $curso['Fecha_Fin']){
            $textoEstado = "Finalizado";
            $claseEstado = "bg-secondary";
        }else{
            $textoEstado = "Activo";
            $claseEstado = "bg-success";
        }

        // Inscribirme
        if(isset($_POST['inscribirme']) && !$esProfesor){
            if($textoEstado == "Finalizado"){
                $mensaje = "Este curso ya finalizó.";
            }elseif($miInscripcion && ($miInscripcion['Estado'] == 'activa' || $miInscripcion['Estado'] == 'pendiente')){
                $mensaje = "Ya estás inscripto en este curso.";
            }elseif($curso['Cupo'] > 0 && $curso['inscriptos'] >= $curso['Cupo']){
                $mensaje = "El curso no tiene lugares disponibles.";
            }else{
                if(($_SESSION['Usuario']['Plan'] ?? 'gratuito') != 'pro'){
                    $res = mysqli_query($conexion, "SELECT COUNT(*) AS total
                                                    FROM inscripcion i
                                                    INNER JOIN cursos c ON c.ID = i.ID_Curso
                                                    WHERE i.ID_Usuario = $idUsuario
                                                    AND i.Estado IN ('activa','pendiente')
                                                    AND c.Fecha_Fin >= CURDATE()");
                    $fila = mysqli_fetch_assoc($res);
                    if($fila['total'] >= 3){
                        $mensaje = "Con el plan gratuito solo podés tener hasta 3 inscripciones activas.";
                    }
                }

                if($mensaje == ""){
                    $nuevoEstado = $curso['Costo'] > 0 ? 'pendiente' : 'activa';

                    if($miInscripcion){
                        mysqli_query($conexion, "UPDATE inscripcion SET Estado = '$nuevoEstado', Fecha_Inscripcion = CURDATE()
                                                 WHERE ID_Usuario = $idUsuario AND ID_Curso = $idCurso");
                    }else{
                        mysqli_query($conexion, "INSERT INTO inscripcion (ID_Usuario, ID_Curso, Estado, Fecha_Inscripcion)
                                                 VALUES ($idUsuario, $idCurso, '$nuevoEstado', CURDATE())");
                    }
                                        $idCreador = (int)$curso['ID_Creador'];
                    $nombreAlumno = $_SESSION['Usuario']['Nombre']." ".$_SESSION['Usuario']['Apellido'];
                    if($nuevoEstado == 'pendiente'){
                        $textoNotif = $nombreAlumno." solicitó inscribirse a «".$curso['Titulo']."».";
                    }else{
                        $textoNotif = $nombreAlumno." se inscribió a «".$curso['Titulo']."».";
                    }
                    $textoNotifSql = mysqli_real_escape_string($conexion, $textoNotif);
                    mysqli_query($conexion, "INSERT INTO notificaciones (Texto, ID_usuario)
                                             VALUES ('$textoNotifSql', $idCreador)");
                    header("location:Mis_cursos.php");
                    exit();
                }
            }
        }

        $materiales = mysqli_query($conexion, "SELECT *
                                               FROM materiales
                                               WHERE ID_Curso = $idCurso");

        $etiquetas = mysqli_query($conexion, "SELECT e.Nombre
                                              FROM etiquetas e
                                              INNER JOIN curso_etiqueta ce ON ce.ID_Etiqueta = e.ID
                                              WHERE ce.ID_Curso = $idCurso");
    }
            // Abandonar el curso
    if(isset($_POST['abandonar']) && $miInscripcion && in_array($miInscripcion['Estado'], ['activa', 'pendiente'])){
        mysqli_query($conexion, "UPDATE inscripcion SET Estado = 'cancelada'
                                 WHERE ID_Usuario = $idUsuario AND ID_Curso = $idCurso
                                 AND Estado IN ('activa','pendiente')");
        mysqli_close($conexion);
        header("location:Mis_cursos.php");
        exit();
    }
}
        // ===== FORO =====
    $preguntas = [];
    $indiceRespuestas = [];
    $errorTitulo = "";
    $errorTexto = "";
    $errorRespuesta = "";
    $idPreguntaError = 0;
    $idRespuestaError = 0;
    $abrirFormPregunta = false;

    if(!$error && $esEstudiante){

        // Preguntas del curso
        $resPreguntas = mysqli_query($conexion, "SELECT p.*, u.Nombre, u.Apellido
                                                 FROM preguntas p
                                                 INNER JOIN usuarios u ON u.ID = p.ID_usuario
                                                 WHERE p.ID_curso = $idCurso
                                                 ORDER BY p.ID_pregunta DESC");
        while($fila = mysqli_fetch_assoc($resPreguntas)){
            $fila['respuestas'] = [];
            $fila['cerrada'] = ($fila['Estado'] == 'cerrada');
            $preguntas[$fila['ID_pregunta']] = $fila;
        }

        // Respuestas visibles de esas preguntas
        $resRespuestas = mysqli_query($conexion, "SELECT r.*, u.Nombre, u.Apellido
                                                  FROM respuestas r
                                                  INNER JOIN usuarios u ON u.ID = r.ID_usuario
                                                  INNER JOIN preguntas p ON p.ID_pregunta = r.ID_pregunta
                                                  WHERE p.ID_curso = $idCurso
                                                  AND r.Estado = 'visible'
                                                  ORDER BY r.ID ASC");
        while($fila = mysqli_fetch_assoc($resRespuestas)){
            $preguntas[$fila['ID_pregunta']]['respuestas'][] = $fila;
            $indiceRespuestas[$fila['ID']] = $fila;
        }

        // Acciones del foro
        if($_SERVER['REQUEST_METHOD'] == 'POST'){

            // Crear pregunta
            if(isset($_POST['crear_pregunta'])){
                $titulo = trim($_POST['titulo'] ?? '');
                $texto = trim($_POST['texto'] ?? '');

                if($titulo == ""){
                    $errorTitulo = "Escribí un título.";
                }elseif(mb_strlen($titulo) > 150){
                    $errorTitulo = "El título no puede superar los 150 caracteres.";
                }
                if($texto == ""){
                    $errorTexto = "Escribí tu pregunta.";
                }elseif(mb_strlen($texto) > 1000){
                    $errorTexto = "La pregunta no puede superar los 1000 caracteres.";
                }

                if($errorTitulo == "" && $errorTexto == ""){
                    $tituloSql = mysqli_real_escape_string($conexion, $titulo);
                    $textoSql = mysqli_real_escape_string($conexion, $texto);
                    mysqli_query($conexion, "INSERT INTO preguntas (ID_curso, ID_usuario, Titulo, Texto)
                                             VALUES ($idCurso, $idUsuario, '$tituloSql', '$textoSql')");
                    $idNuevaPregunta = mysqli_insert_id($conexion);
                    $idCreador = (int)$curso['ID_Creador'];
                    $textoNotif = $_SESSION['Usuario']['Nombre']." ".$_SESSION['Usuario']['Apellido']." hizo una pregunta en «".$curso['Titulo']."».";
                    $textoNotifSql = mysqli_real_escape_string($conexion, $textoNotif);
                    mysqli_query($conexion, "INSERT INTO notificaciones (Texto, ID_usuario, ID_pregunta)
                                             VALUES ('$textoNotifSql', $idCreador, $idNuevaPregunta)");                         
                    mysqli_close($conexion);
                    header("location:Detalles_curso.php?id=$idCurso#preguntas");
                    exit();
                }
                $abrirFormPregunta = true;
            }

            // Responder
            if(isset($_POST['responder'])){
                $idPregunta = (int)($_POST['id_pregunta'] ?? 0);
                $texto = trim($_POST['texto'] ?? '');

                if(isset($preguntas[$idPregunta])){
                    $idPreguntaError = $idPregunta;
                    $respuestas = $preguntas[$idPregunta]['respuestas'];
                    $ultima = count($respuestas) > 0 ? end($respuestas) : false;

                    if($preguntas[$idPregunta]['cerrada']){
                        $errorRespuesta = "La pregunta está cerrada.";
                    }elseif($ultima && $ultima['ID_usuario'] == $idUsuario){
                        $errorRespuesta = "Esperá a que otro usuario responda o editá tu última respuesta.";
                    }elseif($texto == ""){
                        $errorRespuesta = "Escribí tu respuesta.";
                    }elseif(mb_strlen($texto) > 1000){
                        $errorRespuesta = "La respuesta no puede superar los 1000 caracteres.";
                    }else{
                        $textoSql = mysqli_real_escape_string($conexion, $texto);
                        mysqli_query($conexion, "INSERT INTO respuestas (ID_pregunta, ID_usuario, Texto)
                                                 VALUES ($idPregunta, $idUsuario, '$textoSql')");
                                                $idNuevaRespuesta = mysqli_insert_id($conexion);
                        $idAutorPregunta = (int)$preguntas[$idPregunta]['ID_usuario'];
                        if($idAutorPregunta != $idUsuario){
                            $textoNotif = $_SESSION['Usuario']['Nombre']." ".$_SESSION['Usuario']['Apellido']." respondió tu pregunta «".$preguntas[$idPregunta]['Titulo']."».";
                            $textoNotifSql = mysqli_real_escape_string($conexion, $textoNotif);
                            mysqli_query($conexion, "INSERT INTO notificaciones (Texto, ID_usuario, ID_pregunta, ID_respuesta)
                                                     VALUES ('$textoNotifSql', $idAutorPregunta, $idPregunta, $idNuevaRespuesta)");
                        }
                        mysqli_query($conexion, "UPDATE preguntas SET Estado = 'respondida'
                                                 WHERE ID_pregunta = $idPregunta AND Estado = 'abierta'");
                        mysqli_close($conexion);
                        header("location:Detalles_curso.php?id=$idCurso#pregunta-$idPregunta");
                        exit();
                    }
                }
            }

            // Editar mi última respuesta
            if(isset($_POST['editar_respuesta'])){
                $idRespuesta = (int)($_POST['id_respuesta'] ?? 0);
                $texto = trim($_POST['texto'] ?? '');

                if(isset($indiceRespuestas[$idRespuesta])){
                    $idPregunta = $indiceRespuestas[$idRespuesta]['ID_pregunta'];
                    $idPreguntaError = $idPregunta;
                    $idRespuestaError = $idRespuesta;
                    $respuestas = $preguntas[$idPregunta]['respuestas'];
                    $ultima = end($respuestas);

                    if($preguntas[$idPregunta]['cerrada']){
                        $errorRespuesta = "La pregunta está cerrada.";
                    }elseif($ultima['ID'] != $idRespuesta || $ultima['ID_usuario'] != $idUsuario){
                        $errorRespuesta = "Solo podés editar tu última respuesta.";
                    }elseif($texto == ""){
                        $errorRespuesta = "La respuesta no puede quedar vacía.";
                    }elseif(mb_strlen($texto) > 1000){
                        $errorRespuesta = "La respuesta no puede superar los 1000 caracteres.";
                    }else{
                        $textoSql = mysqli_real_escape_string($conexion, $texto);
                        mysqli_query($conexion, "UPDATE respuestas SET Texto = '$textoSql'
                                                 WHERE ID = $idRespuesta AND ID_usuario = $idUsuario");
                        mysqli_close($conexion);
                        header("location:Detalles_curso.php?id=$idCurso#pregunta-$idPregunta");
                        exit();
                    }
                }
            }

            // Cerrar pregunta (solo su autor, y si ya le respondió otro usuario)
            if(isset($_POST['cerrar_pregunta'])){
                $idPregunta = (int)($_POST['id_pregunta'] ?? 0);

                if(isset($preguntas[$idPregunta])){
                    $idPreguntaError = $idPregunta;
                    $hayOtra = false;
                    foreach($preguntas[$idPregunta]['respuestas'] as $resp){
                        if($resp['ID_usuario'] != $idUsuario){
                            $hayOtra = true;
                        }
                    }

                    if($preguntas[$idPregunta]['cerrada']){
                        $errorRespuesta = "La pregunta ya está cerrada.";
                    }elseif($preguntas[$idPregunta]['ID_usuario'] != $idUsuario){
                        $errorRespuesta = "Solo quien hizo la pregunta puede cerrarla.";
                    }elseif(!$hayOtra){
                        $errorRespuesta = "Todavía no te respondió otro usuario.";
                    }else{
                        mysqli_query($conexion, "UPDATE preguntas SET Estado = 'cerrada'
                                                 WHERE ID_pregunta = $idPregunta AND ID_usuario = $idUsuario");
                        mysqli_close($conexion);
                        header("location:Detalles_curso.php?id=$idCurso#pregunta-$idPregunta");
                        exit();
                    }
                }
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del curso | Cursolandia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="Estilos/estilos.css?v=10" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">

<header class="py-2 text-bg-dark sticky-top">
  <div class="container-fluid px-3">
    <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">

      <a href="index.php" class="d-flex align-items-center mb-2 mb-lg-0 me-lg-4 text-white text-decoration-none">
        <i class="bi bi-rocket-takeoff-fill text-warning fs-3 me-2"></i>
        <span class="fs-5 fw-semibold">Cursolandia</span>
      </a>

       <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
        <li><a href="index.php" class="nav-link px-3 text-white">Inicio</a></li>
        <li><a href="index.php#cursos" class="nav-link px-3 text-white">Cursos</a></li>
        <li><a href="index.php#como-funciona" class="nav-link px-3 text-white">Cómo funciona</a></li>
        <li><a href="index.php#contacto" class="nav-link px-3 text-white">Contacto</a></li>
      </ul>

      <div class="text-end">
        <div class='d-flex align-items-center'>
        <?php if($_SESSION['Usuario']['Foto'] != ""){ ?>
            <img src="imagenes/<?php echo htmlspecialchars($_SESSION['Usuario']['Foto']); ?>" class="rounded-circle" width="40" height="40" style="object-fit: cover;">
        <?php }else{ ?>
            <i class="bi bi-person-circle text-white fs-2"></i>
        <?php } ?>
            <span class="text-white fw-normal mx-2"><?php echo htmlspecialchars($_SESSION['Usuario']['Nombre']." ".$_SESSION['Usuario']['Apellido']); ?></span>
            <a href="Mis_cursos.php" class="btn btn-warning btn-sm me-2">Mis cursos</a>
            <a href="cerrar_sesion.php" class="btn btn-outline-light btn-sm">Cerrar sesión</a>
        </div>
      </div>

    </div>
  </div>
</header>

<main class="bg-soft flex-grow-1 py-5">
    <?php if($error){ ?>
        <div class="container mt-4">
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($mensajeError); ?>
            </div>
            <a href="index.php" class="btn btn-dark">Volver al inicio</a>
        </div>
    <?php }else{ ?>
    <div class="container">
        <div class="row g-4">

            <!-- Columna principal -->
            <div class="<?php echo $esEstudiante ? 'col-12' : 'col-lg-8'; ?>">

                    <?php if($esEstudiante){ ?>
                        <div class="alert alert-success d-flex align-items-center mb-4">
                        <i class="bi bi-check-circle-fill me-2"></i>Estás inscripto en este curso.
                    </div>
                    <?php } ?>
                    <div class="card card-cl curso-card shadow-sm mb-4">
                    <div class="position-relative">
                        <img src="<?php echo htmlspecialchars(!empty($curso['Imagen']) ? $curso['Imagen'] : 'imagenes/cursos/default.jpg'); ?>" class="card-img-top" alt="" style="height: 300px; object-fit: cover;">
                        <div class="position-absolute top-0 start-0 m-3">
                            <?php if($curso['plan_creador'] == 'pro'){ ?>
                                <span class="badge bg-warning text-dark shadow-sm"><i class="bi bi-star-fill me-1"></i>Destacado</span>
                            <?php } ?>
                            <span class="badge <?php echo $claseEstado; ?> shadow-sm"><?php echo $textoEstado; ?></span>
                            <span class="badge text-bg-dark shadow-sm"><?php echo $curso['Publico'] == 'si' ? 'Público' : 'Privado'; ?></span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <h1 class="h3 mb-3"><?php echo htmlspecialchars($curso['Titulo']); ?></h1>
                        <p class="text-secondary"><?php echo nl2br(htmlspecialchars($curso['Descripcion'])); ?></p>

                        <?php if(mysqli_num_rows($etiquetas) > 0){ ?>
                            <h2 class="h6 mt-4">Etiquetas</h2>
                            <?php while($etiqueta = mysqli_fetch_assoc($etiquetas)){ ?>
                                <span class="badge text-bg-dark"><?php echo htmlspecialchars($etiqueta['Nombre']); ?></span>
                            <?php } ?>
                        <?php } ?>
                        <?php if($miInscripcion && in_array($miInscripcion['Estado'], ['activa', 'pendiente'])){ ?>
                        <hr>
                        <div class="text-end">
                        <button type="button" class="btn btn-outline-dark btn-sm" data-bs-toggle="modal" data-bs-target="#modalAbandonar">
                            <i class="bi bi-box-arrow-left me-1"></i>Abandonar curso
                        </button>
                    </div>
                    <?php } ?>
                    </div>
                </div>
                <?php if($esEstudiante){ ?>
                <div class="card card-cl curso-card shadow-sm mb-4" id="materiales">
                    <div class="card-body p-4">
                        <h2 class="h5 border-bottom border-warning border-3 pb-2 mb-3"><i class="bi bi-paperclip text-warning me-2"></i>Materiales</h2>
                        <?php if(mysqli_num_rows($materiales) == 0){ ?>
                            <p class="text-secondary mb-0">Todavía no hay materiales en este curso.</p>
                        <?php }else{ ?>
                        <ul class="list-group list-group-flush">
                            <?php while($material = mysqli_fetch_assoc($materiales)){ ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php if($esEstudiante){ ?>
                                        <a href="<?php echo htmlspecialchars($material['Ruta']); ?>" download class="text-decoration-none text-dark">
                                            <i class="bi bi-file-earmark text-gold me-2"></i><?php echo htmlspecialchars($material['Nombre']); ?>
                                        </a>
                                    <?php }else{ ?>
                                        <span class="text-secondary"><i class="bi bi-lock text-gold me-2"></i><?php echo htmlspecialchars($material['Nombre']); ?></span>
                                    <?php } ?>
                                    <span class="text-secondary small"><?php echo htmlspecialchars($material['Tamaño']); ?></span>
                                </li>
                            <?php } ?>
                        </ul>
                        <?php } ?>
                    </div>
                </div>
                
                
            <div class="card card-cl shadow-sm" id="preguntas">
            <div class="card-body p-4">

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom border-warning border-3 pb-2 mb-3">
                    <h2 class="h5 mb-0">
                        <i class="bi bi-chat-dots text-warning me-2"></i>Preguntas
                        <span class="badge text-bg-dark ms-2"><?php echo count($preguntas); ?></span>
                    </h2>
                    <button class="btn btn-warning btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#formPregunta">
                        <i class="bi bi-plus-lg me-1"></i>Hacer una pregunta
                    </button>
                </div>

                <!-- Formulario para preguntar -->
                <div class="collapse mb-4 <?php echo $abrirFormPregunta ? 'show' : ''; ?>" id="formPregunta">
                    <form method="post" class="border rounded p-3 bg-white">
                        <div class="mb-3">
                            <label for="titulo" class="form-label small fw-semibold">Título *</label>
                            <input type="text" class="form-control <?php echo $errorTitulo != '' ? 'is-invalid' : ''; ?>" id="titulo" name="titulo" maxlength="150"
                                value="<?php echo isset($_POST['crear_pregunta']) ? htmlspecialchars($_POST['titulo'] ?? '') : ''; ?>">
                            <div class="invalid-feedback"><?php echo htmlspecialchars($errorTitulo); ?></div>
                        </div>
                        <div class="mb-3">
                            <label for="texto" class="form-label small fw-semibold">Tu pregunta *</label>
                            <textarea class="form-control <?php echo $errorTexto != '' ? 'is-invalid' : ''; ?>" id="texto" name="texto" rows="3"><?php echo isset($_POST['crear_pregunta']) ? htmlspecialchars($_POST['texto'] ?? '') : ''; ?></textarea>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($errorTexto); ?></div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-warning btn-sm" name="crear_pregunta">Publicar pregunta</button>
                            <button type="button" class="btn btn-outline-dark btn-sm" data-bs-toggle="collapse" data-bs-target="#formPregunta">Cancelar</button>
                        </div>
                    </form>
                </div>

                <?php if(count($preguntas) == 0){ ?>
                    <p class="text-secondary mb-0">Todavía no hay preguntas en este curso.</p>
                <?php }else{ ?>

                <div class="accordion" id="accordionPreguntas">
                <?php foreach($preguntas as $idPregunta => $p){
                    $respuestas = $p['respuestas'];
                    $cantidad = count($respuestas);
                    $ultima = $cantidad > 0 ? end($respuestas) : false;
                    $ultimaEsMia = $ultima && $ultima['ID_usuario'] == $idUsuario;
                    $abierta = ($idPreguntaError == $idPregunta);
                    $hayOtra = false;
                    foreach($respuestas as $resp){
                        if($resp['ID_usuario'] != $idUsuario){
                            $hayOtra = true;
                        }
                    }
                ?>
                    <div class="accordion-item border rounded overflow-hidden mb-3">
                        <h3 class="accordion-header">
                            <button class="accordion-button <?php echo $abierta ? '' : 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#pregunta-<?php echo $idPregunta; ?>" aria-expanded="<?php echo $abierta ? 'true' : 'false'; ?>">
                                <div class="w-100 pe-3">
                                    <?php if($p['cerrada']){ ?>
                                        <span class="badge bg-secondary">Cerrada</span>
                                    <?php }elseif($cantidad > 0){ ?>
                                        <span class="badge text-bg-dark">Respondida</span>
                                    <?php }else{ ?>
                                        <span class="badge bg-warning text-dark">Abierta</span>
                                    <?php } ?>
                                    <div class="fw-semibold mt-1"><?php echo htmlspecialchars($p['Titulo']); ?></div>
                                    <div class="small text-secondary">
                                        <?php echo htmlspecialchars($p['Nombre']." ".$p['Apellido']); ?> &middot;
                                        <?php echo $cantidad == 0 ? 'Sin respuestas' : ($cantidad == 1 ? '1 respuesta' : $cantidad.' respuestas'); ?>
                                    </div>
                                </div>
                            </button>
                        </h3>
                        <div id="pregunta-<?php echo $idPregunta; ?>" class="accordion-collapse collapse <?php echo $abierta ? 'show' : ''; ?>" data-bs-parent="#accordionPreguntas">
                            <div class="accordion-body">

                                <p class="text-secondary"><?php echo nl2br(htmlspecialchars($p['Texto'])); ?></p>
                                <hr>

                                <?php if($cantidad == 0){ ?>
                                    <p class="text-secondary small">Todavía no hay respuestas.</p>
                                <?php } ?>

                                <?php foreach($respuestas as $r){
                                    $esDocente = ($r['ID_usuario'] == $curso['ID_Creador']);
                                    $esUltimaMia = $ultimaEsMia && $r['ID'] == $ultima['ID'];
                                ?>
                                    <div class="rounded p-3 mb-3 bg-light">
                                        <p class="mb-1"><?php echo nl2br(htmlspecialchars($r['Texto'])); ?></p>
                                        <p class="small text-secondary mb-2">
                                            <i class="bi <?php echo $esDocente ? 'bi-person-badge' : 'bi-person'; ?> me-1"></i><?php echo htmlspecialchars($r['Nombre']." ".$r['Apellido']); ?>
                                            <?php if($esDocente){ ?><span class="badge text-bg-dark ms-1">Docente</span><?php } ?>
                                        </p>

                                        <?php if(!$p['cerrada'] && $esUltimaMia){ ?>
                                            <button class="btn btn-outline-dark btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#editar-<?php echo $r['ID']; ?>">
                                                <i class="bi bi-pencil me-1"></i>Editar
                                            </button>
                                            <div class="collapse mt-2 <?php echo $idRespuestaError == $r['ID'] ? 'show' : ''; ?>" id="editar-<?php echo $r['ID']; ?>">
                                                <form method="post">
                                                    <input type="hidden" name="id_respuesta" value="<?php echo $r['ID']; ?>">
                                                    <textarea class="form-control mb-2" name="texto" rows="2"><?php echo htmlspecialchars($idRespuestaError == $r['ID'] ? ($_POST['texto'] ?? '') : $r['Texto']); ?></textarea>
                                                    <button type="submit" class="btn btn-dark btn-sm" name="editar_respuesta">Guardar cambios</button>
                                                </form>
                                            </div>
                                        <?php } ?>
                                    </div>
                                <?php } ?>

                                <?php if($errorRespuesta != "" && $idPreguntaError == $idPregunta){ ?>
                                    <div class="alert alert-danger small"><?php echo htmlspecialchars($errorRespuesta); ?></div>
                                <?php } ?>

                                <?php if($p['cerrada']){ ?>
                                    <p class="small text-secondary mt-3 mb-0"><i class="bi bi-lock me-1"></i>Pregunta cerrada.</p>
                                <?php }else{ ?>

                                    <?php if($p['ID_usuario'] == $idUsuario && $hayOtra){ ?>
                                        <form method="post" class="mb-3">
                                            <input type="hidden" name="id_pregunta" value="<?php echo $idPregunta; ?>">
                                            <button type="submit" class="btn btn-dark btn-sm" name="cerrar_pregunta">
                                                <i class="bi bi-check2 me-1"></i>Cerrar pregunta (ya me respondieron)
                                            </button>
                                        </form>
                                    <?php } ?>

                                    <?php if($ultimaEsMia){ ?>
                                        <p class="small text-secondary mb-0"><i class="bi bi-hourglass-split me-1"></i>Esperá a que otro usuario responda o editá tu última respuesta.</p>
                                    <?php }else{ ?>
                                        <form method="post">
                                            <input type="hidden" name="id_pregunta" value="<?php echo $idPregunta; ?>">
                                            <div class="input-group">
                                                <textarea class="form-control" name="texto" rows="2" placeholder="Escribí tu respuesta"><?php echo (isset($_POST['responder']) && $idPreguntaError == $idPregunta) ? htmlspecialchars($_POST['texto'] ?? '') : ''; ?></textarea>
                                                <button type="submit" class="btn btn-dark" name="responder">Responder</button>
                                            </div>
                                        </form>
                                    <?php } ?>

                                <?php } ?>

                            </div>
                        </div>
                    </div>
                <?php } ?>
                </div>

                <?php } ?>

            </div>
        </div>
    <?php } ?>
            </div><!-- fin col-lg-8 -->

            <!-- Columna lateral -->
             <?php if(!$esEstudiante){ ?>
            <div class="col-lg-4">
                <div class="card card-cl shadow-sm sticky-lg-top" style="top: 90px;">
                    <div class="card-body p-4">

                        <p class="display-6 fw-bold mb-3"><?php echo $curso['Costo'] > 0 ? '$'.number_format($curso['Costo'], 2, ',', '.') : 'Gratuito'; ?></p>

                        <ul class="list-unstyled mb-4">
                            <li class="mb-2"><i class="bi bi-calendar-event text-gold me-2"></i><?php echo date("d/m/Y", strtotime($curso['Fecha_Inicio'])); ?> al <?php echo date("d/m/Y", strtotime($curso['Fecha_Fin'])); ?></li>
                            <li class="mb-2"><i class="bi bi-people text-gold me-2"></i><?php echo $curso['inscriptos']; ?> inscriptos<?php echo $curso['Cupo'] > 0 ? ' de '.$curso['Cupo'] : ' (sin límite de cupo)'; ?></li>
                            <li class="mb-2"><i class="bi bi-person text-gold me-2"></i>Dictado por <?php echo htmlspecialchars($curso['Nombre']." ".$curso['Apellido']); ?></li>
                        </ul>

                        <?php if($mensaje != ""){ ?>
                            <div class="alert alert-danger small"><?php echo $mensaje; ?></div>
                        <?php } ?>

                        <?php if($miInscripcion && $miInscripcion['Estado'] == 'pendiente'){ ?>
                            <div class="alert alert-warning mb-0 small"><i class="bi bi-hourglass-split me-1"></i>Tu inscripción está pendiente de confirmación.</div>

                        <?php }elseif($esEstudiante){ ?>
                            <div class="alert alert-success mb-0 small"><i class="bi bi-check-circle me-1"></i>Ya estás inscripto en este curso.</div>

                        <?php }elseif($textoEstado == "Finalizado"){ ?>
                            <button class="btn btn-secondary btn-lg w-100" disabled>Curso finalizado</button>

                        <?php }elseif($curso['Cupo'] > 0 && $curso['inscriptos'] >= $curso['Cupo']){ ?>
                            <button class="btn btn-secondary btn-lg w-100" disabled>Sin lugares disponibles</button>

                        <?php }else{ ?>
                            <form method="post">
                                <button type="submit" name="inscribirme" class="btn btn-warning btn-lg w-100">Inscribirme</button>
                            </form>
                            <?php if($curso['Costo'] > 0){ ?>
                                <p class="text-secondary small mt-2 mb-0">Es un curso pago: tu inscripción quedará pendiente hasta que el creador la confirme.</p>
                            <?php } ?>
                        <?php } ?>

                    </div>
                </div>
            </div>
            <?php } ?>

        </div>
    </div>
<?php } ?>
</main>
<!--Modal para confirmacion de abandono del curso-->
<?php if($miInscripcion && in_array($miInscripcion['Estado'], ['activa', 'pendiente'])){ ?>
<div class="modal fade" id="modalAbandonar" tabindex="-1" aria-labelledby="tituloAbandonar" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 border-top border-warning border-4">
            <div class="modal-header">
                <h2 class="modal-title h5" id="tituloAbandonar"><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Abandonar curso</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">¿Seguro que quieres abandonar <strong><?php echo htmlspecialchars($curso['Titulo']); ?></strong>? Vas a perder el acceso a los materiales y a las preguntas del curso.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Cancelar</button>
                <form method="post" class="d-inline">
                    <button type="submit" name="abandonar" class="btn btn-dark">Sí, abandonar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php } ?>
<footer id="contacto" class="text-bg-dark py-5 border-top border-warning border-4">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-5">
        <a href="index.php" class="text-white text-decoration-none d-inline-flex align-items-center mb-2">
          <i class="bi bi-rocket-takeoff-fill fs-3 me-2"></i><span class="fs-5 fw-semibold">Cursolandia</span>
        </a>
        <p class="text-secondary">Una plataforma para crear cursos e inscribirte en ellos.</p>
      </div>
      <div class="col-6 col-md-3">
        <h3 class="h6">Navegación</h3>
        <ul class="nav flex-column">
          <li class="nav-item"><a href="index.php" class="nav-link p-0 mb-2 text-secondary">Inicio</a></li>
          <li class="nav-item"><a href="index.php#como-funciona" class="nav-link p-0 mb-2 text-secondary">Cómo funciona</a></li>
          <li class="nav-item"><a href="index.php#cursos" class="nav-link p-0 mb-2 text-secondary">Cursos</a></li>
        </ul>
      </div>
      <div class="col-6 col-md-4">
        <h3 class="h6">Contacto</h3>
        <ul class="list-unstyled text-secondary">
          <li class="mb-2"><i class="bi bi-envelope me-2"></i>Jeremias01@gmail.com</li>
          <li class="mb-2"><i class="bi bi-geo-alt me-2"></i>San Luis, Argentina</li>
        </ul>
      </div>
    </div>
    <hr class="border-secondary my-4">
    <p class="text-secondary small mb-0">&copy; 2026 Cursolandia. Todos los derechos reservados.</p>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<?php mysqli_close($conexion); ?>
</body>
</html>        