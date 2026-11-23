<?php
session_start();
include "Persistencia/Conexion.php";

$error = false;
$mensajeError = "";

if(!isset($_SESSION['Usuario'])){
    header("location:login.php");
    exit();
}
function formatearKb($kb){
    return $kb >= 1024 ? number_format($kb / 1024, 1, ',', '.')." MB" : $kb." KB";
}
$conexion = conectar();
mysqli_set_charset($conexion, 'utf8mb4');
$idUsuario = (int)$_SESSION['Usuario']['ID'];
$idCurso = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$hoy = date("Y-m-d");
$curso = null;
$etiquetas = false;
$textoEstado = "";
$claseEstado = "";
$listaMateriales = [];
$cantidadMateriales = 0;
$totalKb = 0;
$errorMaterial = "";
$preguntas = [];
$indiceRespuestas = [];
$errorRespuesta = "";
$idPreguntaError = 0;
$extensionesPermitidas = ['pdf','doc','docx','ppt','pptx','xls','xlsx','txt','zip','jpg','jpeg','png'];
$maxArchivos = 5;
$maxKbArchivo = 5 * 1024;   // 5 MB por archivo
$maxKbTotal = 10 * 1024;    // 10 MB en total
if($idCurso <= 0){
    $error = true;
    $mensajeError = "El identificador del curso no es válido.";
}else{

    $consulta = mysqli_query($conexion, "SELECT c.*, u.Plan AS plan_creador,
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

        // Solo el creador entra a esta página
        if($curso['ID_Creador'] != $idUsuario){
            mysqli_close($conexion);
            header("location:Detalles_curso.php?id=$idCurso");
            exit();
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

        $etiquetas = mysqli_query($conexion, "SELECT e.Nombre
                                              FROM etiquetas e
                                              INNER JOIN curso_etiqueta ce ON ce.ID_Etiqueta = e.ID
                                              WHERE ce.ID_Curso = $idCurso");
    }
    if(!$error){      // ===== INSCRIPCIONES =====
        if($_SERVER['REQUEST_METHOD'] == 'POST'){

            // Aceptar una solicitud pendiente
            if(isset($_POST['aceptar_inscripcion'])){
                $idEstudiante = (int)($_POST['id_usuario'] ?? 0);
                mysqli_query($conexion, "UPDATE inscripcion SET Estado = 'activa'
                                         WHERE ID_Usuario = $idEstudiante AND ID_Curso = $idCurso AND Estado = 'pendiente'");
                if(mysqli_affected_rows($conexion) > 0){
                    $textoNotifSql = mysqli_real_escape_string($conexion, "Tu inscripción a «".$curso['Titulo']."» fue aceptada.");
                    mysqli_query($conexion, "INSERT INTO notificaciones (Texto, ID_usuario)
                                             VALUES ('$textoNotifSql', $idEstudiante)");
                }
                mysqli_close($conexion);
                header("location:gestionar_curso.php?id=$idCurso#inscripciones");
                exit();
            }

            // Rechazar una solicitud pendiente
            if(isset($_POST['rechazar_inscripcion'])){
                $idEstudiante = (int)($_POST['id_usuario'] ?? 0);
                mysqli_query($conexion, "UPDATE inscripcion SET Estado = 'cancelada'
                                         WHERE ID_Usuario = $idEstudiante AND ID_Curso = $idCurso AND Estado = 'pendiente'");
                if(mysqli_affected_rows($conexion) > 0){
                    $textoNotifSql = mysqli_real_escape_string($conexion, "Tu inscripción a «".$curso['Titulo']."» fue rechazada.");
                    mysqli_query($conexion, "INSERT INTO notificaciones (Texto, ID_usuario)
                                             VALUES ('$textoNotifSql', $idEstudiante)");
                }
                mysqli_close($conexion);
                header("location:gestionar_curso.php?id=$idCurso#inscripciones");
                exit();
            }
        }

        $pendientes = mysqli_query($conexion, "SELECT u.ID, u.Nombre, u.Apellido, i.Fecha_Inscripcion
                                               FROM inscripcion i
                                               INNER JOIN usuarios u ON u.ID = i.ID_Usuario
                                               WHERE i.ID_Curso = $idCurso AND i.Estado = 'pendiente'
                                               ORDER BY i.Fecha_Inscripcion ASC");
        $cantPendientes = mysqli_num_rows($pendientes);

        $listaInscriptos = mysqli_query($conexion, "SELECT u.Nombre, u.Apellido, i.Fecha_Inscripcion
                                                    FROM inscripcion i
                                                    INNER JOIN usuarios u ON u.ID = i.ID_Usuario
                                                    WHERE i.ID_Curso = $idCurso AND i.Estado = 'activa'
                                                    ORDER BY i.Fecha_Inscripcion ASC");
        $cantInscriptos = mysqli_num_rows($listaInscriptos);
    
                // ===== MATERIALES =====
        if($curso['plan_creador'] == 'pro'){
            // AJUSTÁ estos valores a tu consigna: no sé los límites del plan pro
            $maxArchivos = null;          // null = sin límite
            $maxKbArchivo = 50 * 1024;    // 50 MB por archivo
            $maxKbTotal = null;           // null = sin límite
        }

        $resMateriales = mysqli_query($conexion, "SELECT * FROM materiales WHERE ID_Curso = $idCurso ORDER BY ID ASC");
        while($fila = mysqli_fetch_assoc($resMateriales)){
            $listaMateriales[] = $fila;
            $totalKb += (int)$fila['Tamaño'];
        }
        $cantidadMateriales = count($listaMateriales);

        // Si el archivo supera post_max_size, PHP descarta $_POST y $_FILES
        if($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST) && empty($_FILES)){
            $errorMaterial = "El archivo es demasiado grande para el servidor.";
        }

        if($_SERVER['REQUEST_METHOD'] == 'POST'){

            // Subir material
            if(isset($_POST['subir_material'])){
                $archivo = $_FILES['archivo'] ?? null;

                if(!$archivo || $archivo['error'] == UPLOAD_ERR_NO_FILE){
                    $errorMaterial = "Elegí un archivo.";
                }elseif($archivo['error'] == UPLOAD_ERR_INI_SIZE || $archivo['error'] == UPLOAD_ERR_FORM_SIZE){
                    $errorMaterial = "El archivo supera el tamaño máximo que acepta el servidor.";
                }elseif($archivo['error'] != UPLOAD_ERR_OK){
                    $errorMaterial = "No se pudo subir el archivo. Probá de nuevo.";
                }else{
                    $nombreOriginal = basename($archivo['name']);
                    $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
                    $kb = (int)ceil($archivo['size'] / 1024);

                    if(!in_array($extension, $extensionesPermitidas)){
                        $errorMaterial = "Tipo de archivo no permitido. Podés subir: ".implode(", ", $extensionesPermitidas).".";
                    }elseif($kb > $maxKbArchivo){
                        $errorMaterial = "El archivo supera el máximo de ".formatearKb($maxKbArchivo)." por archivo.";
                    }elseif($maxArchivos !== null && $cantidadMateriales >= $maxArchivos){
                        $errorMaterial = "Llegaste al máximo de $maxArchivos archivos para este curso.";
                    }elseif($maxKbTotal !== null && $totalKb + $kb > $maxKbTotal){
                        $errorMaterial = "No hay espacio: el total no puede superar ".formatearKb($maxKbTotal).".";
                    }else{
                        if(!is_dir(__DIR__."/materiales")){
                            mkdir(__DIR__."/materiales", 0755, true);
                        }
                        $nombreGuardado = bin2hex(random_bytes(8)).".".$extension;

                        if(move_uploaded_file($archivo['tmp_name'], __DIR__."/materiales/".$nombreGuardado)){
                            $nombreSql = mysqli_real_escape_string($conexion, mb_substr($nombreOriginal, 0, 100));
                            $tipoSql = strtoupper($extension);
                            $rutaSql = "materiales/".$nombreGuardado;
                            mysqli_query($conexion, "INSERT INTO materiales (Nombre, Tipo, Ruta, ID_Curso, `Tamaño`)
                                                     VALUES ('$nombreSql', '$tipoSql', '$rutaSql', $idCurso, '$kb')");
                            mysqli_close($conexion);
                            header("location:gestionar_curso.php?id=$idCurso#materiales");
                            exit();
                        }else{
                            $errorMaterial = "No se pudo guardar el archivo en el servidor.";
                        }
                    }
                }
            }

            // Borrar material
            if(isset($_POST['borrar_material'])){
                $idMaterial = (int)($_POST['id_material'] ?? 0);

                foreach($listaMateriales as $m){
                    if($m['ID'] == $idMaterial){
                        if(strpos($m['Ruta'], 'materiales/') === 0 && strpos($m['Ruta'], '..') === false){
                            $rutaDisco = __DIR__."/".$m['Ruta'];
                            if(is_file($rutaDisco)){
                                unlink($rutaDisco);
                            }
                        }
                        mysqli_query($conexion, "DELETE FROM materiales WHERE ID = $idMaterial AND ID_Curso = $idCurso");
                        mysqli_close($conexion);
                        header("location:gestionar_curso.php?id=$idCurso#materiales");
                        exit();
                    }
                }
            }
        }
                // ===== FORO =====
        $resPreguntas = mysqli_query($conexion, "SELECT p.*, u.Nombre, u.Apellido
                                                 FROM preguntas p
                                                 INNER JOIN usuarios u ON u.ID = p.ID_usuario
                                                 WHERE p.ID_curso = $idCurso
                                                 ORDER BY p.ID_pregunta DESC");
        while($fila = mysqli_fetch_assoc($resPreguntas)){
            $fila['respuestas'] = [];
            $preguntas[$fila['ID_pregunta']] = $fila;
        }

        // Todas las respuestas (visibles y ocultas) de las preguntas de ESTE curso
        $resRespuestas = mysqli_query($conexion, "SELECT r.*, u.Nombre, u.Apellido
                                                  FROM respuestas r
                                                  INNER JOIN usuarios u ON u.ID = r.ID_usuario
                                                  INNER JOIN preguntas p ON p.ID_pregunta = r.ID_pregunta
                                                  WHERE p.ID_curso = $idCurso
                                                  ORDER BY r.ID ASC");
        while($fila = mysqli_fetch_assoc($resRespuestas)){
            $preguntas[$fila['ID_pregunta']]['respuestas'][] = $fila;
            $indiceRespuestas[$fila['ID']] = $fila;
        }

        if($_SERVER['REQUEST_METHOD'] == 'POST'){

            // Responder como docente
            if(isset($_POST['responder'])){
                $idPregunta = (int)($_POST['id_pregunta'] ?? 0);
                $texto = trim($_POST['texto'] ?? '');

                if(isset($preguntas[$idPregunta])){
                    $idPreguntaError = $idPregunta;

                    if($preguntas[$idPregunta]['Estado'] == 'cerrada'){
                        $errorRespuesta = "La pregunta está cerrada. Reabrila para responder.";
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
                        $textoNotif = $_SESSION['Usuario']['Nombre']." ".$_SESSION['Usuario']['Apellido']." (docente) respondió tu pregunta «".$preguntas[$idPregunta]['Titulo']."» en «".$curso['Titulo']."».";
                        $textoNotifSql = mysqli_real_escape_string($conexion, $textoNotif);
                        mysqli_query($conexion, "INSERT INTO notificaciones (Texto, ID_usuario, ID_pregunta, ID_respuesta)
                                                 VALUES ('$textoNotifSql', $idAutorPregunta, $idPregunta, $idNuevaRespuesta)");
                    }                         
                        mysqli_query($conexion, "UPDATE preguntas SET Estado = 'respondida'
                                                 WHERE ID_pregunta = $idPregunta AND Estado = 'abierta'");
                        mysqli_close($conexion);
                        header("location:gestionar_curso.php?id=$idCurso#foro");
                        exit();
                    }
                }
            }

            // Ocultar / mostrar una respuesta
            if(isset($_POST['cambiar_visibilidad'])){
                $idRespuesta = (int)($_POST['id_respuesta'] ?? 0);

                if(isset($indiceRespuestas[$idRespuesta])){
                    $nuevo = ($indiceRespuestas[$idRespuesta]['Estado'] == 'visible') ? 'oculta' : 'visible';
                    mysqli_query($conexion, "UPDATE respuestas SET Estado = '$nuevo' WHERE ID = $idRespuesta");
                    mysqli_close($conexion);
                    header("location:gestionar_curso.php?id=$idCurso#foro");
                    exit();
                }
            }

            // Cerrar pregunta
            if(isset($_POST['cerrar_pregunta'])){
                $idPregunta = (int)($_POST['id_pregunta'] ?? 0);

                if(isset($preguntas[$idPregunta])){
                    mysqli_query($conexion, "UPDATE preguntas SET Estado = 'cerrada'
                                             WHERE ID_pregunta = $idPregunta AND ID_curso = $idCurso");
                    mysqli_close($conexion);
                    header("location:gestionar_curso.php?id=$idCurso#foro");
                    exit();
                }
            }

            // Reabrir pregunta
            if(isset($_POST['reabrir_pregunta'])){
                $idPregunta = (int)($_POST['id_pregunta'] ?? 0);

                if(isset($preguntas[$idPregunta])){
                    $nuevo = count($preguntas[$idPregunta]['respuestas']) > 0 ? 'respondida' : 'abierta';
                    mysqli_query($conexion, "UPDATE preguntas SET Estado = '$nuevo'
                                             WHERE ID_pregunta = $idPregunta AND ID_curso = $idCurso");
                    mysqli_close($conexion);
                    header("location:gestionar_curso.php?id=$idCurso#foro");
                    exit();
                }
            }

            // Borrar pregunta (sus respuestas se borran solas por el ON DELETE CASCADE)
            if(isset($_POST['borrar_pregunta'])){
                $idPregunta = (int)($_POST['id_pregunta'] ?? 0);

                if(isset($preguntas[$idPregunta])){
                    mysqli_query($conexion, "DELETE FROM preguntas
                                             WHERE ID_pregunta = $idPregunta AND ID_curso = $idCurso");
                    mysqli_close($conexion);
                    header("location:gestionar_curso.php?id=$idCurso#foro");
                    exit();
                }
            }
        
            }    
        }
    }   // <- cierra el if(!$error){
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar curso | Cursolandia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="estilos/estilos.css?v=10" rel="stylesheet">
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
        <div class="d-flex align-items-center">
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

                        <!-- Contenido -->
            <div class="col-12">

                <!-- Datos del curso -->
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

                        <p class="small text-secondary mb-0">
                            <i class="bi bi-calendar-event text-gold me-1"></i><?php echo date("d/m/Y", strtotime($curso['Fecha_Inicio'])); ?> al <?php echo date("d/m/Y", strtotime($curso['Fecha_Fin'])); ?>
                            <span class="mx-2">&middot;</span>
                            <i class="bi bi-people text-gold me-1"></i><?php echo $curso['inscriptos']; ?><?php echo $curso['Cupo'] > 0 ? ' de '.$curso['Cupo'].' lugares ocupados' : ' inscriptos (sin límite de cupo)'; ?>
                        </p>

                        <?php if(mysqli_num_rows($etiquetas) > 0){ ?>
                            <h2 class="h6 mt-4">Etiquetas</h2>
                            <?php while($etiqueta = mysqli_fetch_assoc($etiquetas)){ ?>
                                <span class="badge text-bg-dark"><?php echo htmlspecialchars($etiqueta['Nombre']); ?></span>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>
                <!-- Inscripciones -->
                <div class="card card-cl curso-card shadow-sm mb-4" id="inscripciones">
                    <div class="card-body p-4">

                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom border-warning border-3 pb-2 mb-3">
                            <h2 class="h5 mb-0">
                                <i class="bi bi-people text-warning me-2"></i>Inscripciones
                                <?php if($cantPendientes > 0){ ?>
                                    <span class="badge bg-warning text-dark ms-2"><?php echo $cantPendientes; ?> pendiente<?php echo $cantPendientes == 1 ? '' : 's'; ?></span>
                                <?php } ?>
                            </h2>
                            <span class="small text-secondary">
                                <?php echo $curso['inscriptos']; ?><?php echo $curso['Cupo'] > 0 ? ' de '.$curso['Cupo'].' lugares ocupados' : ' inscriptos (sin límite de cupo)'; ?>
                            </span>
                        </div>

                        <!-- Pendientes de confirmación -->
                        <h3 class="h6 mb-3">Pendientes de confirmación</h3>
                        <?php if($cantPendientes == 0){ ?>
                            <p class="text-secondary small mb-0">No hay inscripciones pendientes.</p>
                        <?php }else{ ?>
                            <ul class="list-group mb-2">
                                <?php while($pend = mysqli_fetch_assoc($pendientes)){ ?>
                                    <li class="list-group-item d-flex flex-wrap justify-content-between align-items-center gap-2">
                                        <span>
                                            <i class="bi bi-person text-warning me-1"></i><?php echo htmlspecialchars($pend['Nombre']." ".$pend['Apellido']); ?>
                                            <span class="d-block small text-secondary">Se inscribió el <?php echo date("d/m/Y", strtotime($pend['Fecha_Inscripcion'])); ?></span>
                                        </span>
                                        <div class="d-flex gap-2">
                                            <form method="post">
                                                <input type="hidden" name="id_usuario" value="<?php echo $pend['ID']; ?>">
                                                <button type="submit" class="btn btn-warning btn-sm" name="aceptar_inscripcion">
                                                    <i class="bi bi-check-lg me-1"></i>Aceptar
                                                </button>
                                            </form>
                                            <form method="post">
                                                <input type="hidden" name="id_usuario" value="<?php echo $pend['ID']; ?>">
                                                <button type="submit" class="btn btn-outline-dark btn-sm" name="rechazar_inscripcion">
                                                    <i class="bi bi-x-lg me-1"></i>Rechazar
                                                </button>
                                            </form>
                                        </div>
                                    </li>
                                <?php } ?>
                            </ul>
                        <?php } ?>

                        <!-- Inscriptos activos -->
                        <h3 class="h6 mt-4 mb-3">Inscriptos</h3>
                        <?php if($cantInscriptos == 0){ ?>
                            <p class="text-secondary small mb-0">Todavía no hay inscriptos.</p>
                        <?php }else{ ?>
                            <ul class="list-group list-group-flush">
                                <?php while($ins = mysqli_fetch_assoc($listaInscriptos)){ ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>
                                            <i class="bi bi-person text-warning me-1"></i><?php echo htmlspecialchars($ins['Nombre']." ".$ins['Apellido']); ?>
                                            <span class="d-block small text-secondary">Desde el <?php echo date("d/m/Y", strtotime($ins['Fecha_Inscripcion'])); ?></span>
                                        </span>
                                        <span class="badge bg-success">Activa</span>
                                    </li>
                                <?php } ?>
                            </ul>
                        <?php } ?>

                    </div>
                </div>
                <!-- Materiales -->
                                <!-- Materiales -->
                <div class="card card-cl curso-card shadow-sm mb-4" id="materiales">
                    <div class="card-body p-4">

                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom border-warning border-3 pb-2 mb-3">
                            <h2 class="h5 mb-0"><i class="bi bi-paperclip text-warning me-2"></i>Materiales</h2>
                            <span class="small text-secondary">
                                <?php echo $cantidadMateriales; ?><?php echo $maxArchivos !== null ? ' de '.$maxArchivos : ''; ?> archivos
                                &middot; <?php echo formatearKb($totalKb); ?><?php echo $maxKbTotal !== null ? ' de '.formatearKb($maxKbTotal) : ''; ?>
                            </span>
                        </div>

                        <!-- Subir archivo -->
                        <form method="post" enctype="multipart/form-data" class="mb-4">
                            <label for="archivo" class="form-label small fw-semibold">Subir archivo</label>
                            <div class="input-group has-validation">
                                <input type="file" class="form-control <?php echo $errorMaterial != '' ? 'is-invalid' : ''; ?>" id="archivo" name="archivo"
                                       accept="<?php echo '.'.implode(',.', $extensionesPermitidas); ?>">
                                <button type="submit" class="btn btn-warning" name="subir_material">
                                    <i class="bi bi-upload me-1"></i>Subir
                                </button>
                                <div class="invalid-feedback"><?php echo htmlspecialchars($errorMaterial); ?></div>
                            </div>
                            <div class="form-text">Máximo <?php echo formatearKb($maxKbArchivo); ?> por archivo.</div>
                        </form>

                        <!-- Lista -->
                        <?php if($cantidadMateriales == 0){ ?>
                            <p class="text-secondary mb-0">Todavía no subiste materiales a este curso.</p>
                        <?php }else{ ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach($listaMateriales as $m){ ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center gap-2">
                                        <a href="<?php echo htmlspecialchars($m['Ruta']); ?>" download="<?php echo htmlspecialchars($m['Nombre']); ?>" class="text-decoration-none text-dark">
                                            <i class="bi bi-file-earmark text-warning me-2"></i><?php echo htmlspecialchars($m['Nombre']); ?>
                                            <span class="badge text-bg-dark ms-1"><?php echo htmlspecialchars($m['Tipo']); ?></span>
                                        </a>
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="text-secondary small"><?php echo formatearKb((int)$m['Tamaño']); ?></span>
                                            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalBorrarMaterial-<?php echo $m['ID']; ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </li>
                                <?php } ?>
                            </ul>
                        <?php } ?>

                    </div>
                </div>

                <!-- Foro -->
                                <!-- Foro -->
                <div class="card card-cl shadow-sm" id="foro">
                    <div class="card-body p-4">

                        <h2 class="h5 border-bottom border-warning border-3 pb-2 mb-3">
                            <i class="bi bi-chat-dots text-warning me-2"></i>Foro del curso
                            <span class="badge text-bg-dark ms-2"><?php echo count($preguntas); ?></span>
                        </h2>

                        <?php if(count($preguntas) == 0){ ?>
                            <p class="text-secondary mb-0">Todavía no hay preguntas en este curso.</p>
                        <?php }else{ ?>

                        <div class="accordion" id="accordionForo">
                        <?php foreach($preguntas as $idPregunta => $p){
                            $respuestas = $p['respuestas'];
                            $cantidad = count($respuestas);
                            $abierta = ($idPreguntaError == $idPregunta);
                        ?>
                            <div class="accordion-item border rounded overflow-hidden mb-3">
                                <h3 class="accordion-header">
                                    <button class="accordion-button <?php echo $abierta ? '' : 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#pregunta-<?php echo $idPregunta; ?>" aria-expanded="<?php echo $abierta ? 'true' : 'false'; ?>">
                                        <div class="w-100 pe-3">
                                            <?php if($p['Estado'] == 'cerrada'){ ?>
                                                <span class="badge bg-secondary">Cerrada</span>
                                            <?php }elseif($p['Estado'] == 'respondida'){ ?>
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
                                <div id="pregunta-<?php echo $idPregunta; ?>" class="accordion-collapse collapse <?php echo $abierta ? 'show' : ''; ?>" data-bs-parent="#accordionForo">
                                    <div class="accordion-body">

                                        <p class="text-secondary"><?php echo nl2br(htmlspecialchars($p['Texto'])); ?></p>
                                        <hr>

                                        <?php if($cantidad == 0){ ?>
                                            <p class="text-secondary small">Todavía no hay respuestas.</p>
                                        <?php } ?>

                                        <?php foreach($respuestas as $r){
                                            $esDocente = ($r['ID_usuario'] == $curso['ID_Creador']);
                                            $oculta = ($r['Estado'] == 'oculta');
                                        ?>
                                            <div class="rounded p-3 mb-3 bg-light <?php echo $oculta ? 'opacity-50' : ''; ?>">
                                                <?php if($oculta){ ?>
                                                    <span class="badge bg-secondary mb-2"><i class="bi bi-eye-slash me-1"></i>Oculta para los estudiantes</span>
                                                <?php } ?>
                                                <p class="mb-1"><?php echo nl2br(htmlspecialchars($r['Texto'])); ?></p>
                                                <p class="small text-secondary mb-2">
                                                    <i class="bi <?php echo $esDocente ? 'bi-person-badge' : 'bi-person'; ?> me-1"></i><?php echo htmlspecialchars($r['Nombre']." ".$r['Apellido']); ?>
                                                    <?php if($esDocente){ ?><span class="badge text-bg-dark ms-1">Docente</span><?php } ?>
                                                </p>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="id_respuesta" value="<?php echo $r['ID']; ?>">
                                                    <button type="submit" class="btn btn-outline-dark btn-sm" name="cambiar_visibilidad">
                                                        <i class="bi <?php echo $oculta ? 'bi-eye' : 'bi-eye-slash'; ?> me-1"></i><?php echo $oculta ? 'Mostrar' : 'Ocultar'; ?>
                                                    </button>
                                                </form>
                                            </div>
                                        <?php } ?>
                                                                            <?php if($errorRespuesta != "" && $idPreguntaError == $idPregunta){ ?>
                                            <div class="alert alert-danger small"><?php echo htmlspecialchars($errorRespuesta); ?></div>
                                        <?php } ?>

                                        <!-- Responder (solo si no está cerrada) -->
                                        <?php if($p['Estado'] != 'cerrada'){ ?>
                                            <form method="post" class="mb-3">
                                                <input type="hidden" name="id_pregunta" value="<?php echo $idPregunta; ?>">
                                                <div class="input-group">
                                                    <textarea class="form-control" name="texto" rows="2" placeholder="Escribí tu respuesta como docente"><?php echo (isset($_POST['responder']) && $idPreguntaError == $idPregunta) ? htmlspecialchars($_POST['texto'] ?? '') : ''; ?></textarea>
                                                    <button type="submit" class="btn btn-dark" name="responder">Responder</button>
                                                </div>
                                            </form>
                                        <?php } ?>

                                        <!-- Acciones sobre la pregunta -->
                                        <div class="d-flex flex-wrap gap-2 border-top pt-3">
                                            <form method="post">
                                                <input type="hidden" name="id_pregunta" value="<?php echo $idPregunta; ?>">
                                                <?php if($p['Estado'] == 'cerrada'){ ?>
                                                    <button type="submit" class="btn btn-outline-dark btn-sm" name="reabrir_pregunta"><i class="bi bi-unlock me-1"></i>Reabrir</button>
                                                <?php }else{ ?>
                                                    <button type="submit" class="btn btn-outline-dark btn-sm" name="cerrar_pregunta"><i class="bi bi-lock me-1"></i>Cerrar</button>
                                                <?php } ?>
                                            </form>
                                            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalBorrarPregunta-<?php echo $idPregunta; ?>">
                                                <i class="bi bi-trash me-1"></i>Borrar
                                            </button>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                        </div>

                        <?php } ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
</main>

<!-- Modal: borrar material (uno por cada material) -->
<?php foreach($listaMateriales as $m){ ?>
<div class="modal fade" id="modalBorrarMaterial-<?php echo $m['ID']; ?>" tabindex="-1" aria-labelledby="tituloBorrarMaterial-<?php echo $m['ID']; ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 border-top border-warning border-4">
            <div class="modal-header">
                <h2 class="modal-title h5" id="tituloBorrarMaterial-<?php echo $m['ID']; ?>"><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Borrar material</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">¿Seguro que querés borrar <strong><?php echo htmlspecialchars($m['Nombre']); ?></strong>? Los estudiantes van a perder el acceso al archivo.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Cancelar</button>
                <form method="post" class="d-inline">
                    <input type="hidden" name="id_material" value="<?php echo $m['ID']; ?>">
                    <button type="submit" name="borrar_material" class="btn btn-dark">Sí, borrar</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php } ?>

<!-- Modal: borrar pregunta (en PHP va uno por cada pregunta, con su id oculto) -->
<!-- Modal: borrar pregunta (uno por cada pregunta) -->
<?php foreach($preguntas as $idPregunta => $p){ ?>
<div class="modal fade" id="modalBorrarPregunta-<?php echo $idPregunta; ?>" tabindex="-1" aria-labelledby="tituloBorrarPregunta-<?php echo $idPregunta; ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 border-top border-warning border-4">
            <div class="modal-header">
                <h2 class="modal-title h5" id="tituloBorrarPregunta-<?php echo $idPregunta; ?>"><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Borrar pregunta</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">¿Seguro que querés borrar <strong><?php echo htmlspecialchars($p['Titulo']); ?></strong>? Se van a borrar también todas sus respuestas.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Cancelar</button>
                <form method="post" class="d-inline">
                    <input type="hidden" name="id_pregunta" value="<?php echo $idPregunta; ?>">
                    <button type="submit" name="borrar_pregunta" class="btn btn-dark">Sí, borrar</button>
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