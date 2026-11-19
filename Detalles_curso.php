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
                    header("location:miscursos.php");
                    exit();
                }
            }
        }

        $materiales = mysqli_query($conexion, "SELECT *
                                               FROM materiales
                                               WHERE ID_Curso = $idCurso");

        $preguntas = mysqli_query($conexion, "SELECT p.*, u.Nombre, u.Apellido
                                              FROM preguntas p
                                              INNER JOIN usuarios u ON u.ID = p.ID_usuario
                                              WHERE p.ID_curso = $idCurso
                                              ORDER BY p.ID_pregunta DESC");

        $etiquetas = mysqli_query($conexion, "SELECT e.Nombre
                                              FROM etiquetas e
                                              INNER JOIN curso_etiqueta ce ON ce.ID_Etiqueta = e.ID
                                              WHERE ce.ID_Curso = $idCurso");
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
        <div class='d-flex align-items-center'>
        <?php if($_SESSION['Usuario']['Foto'] != ""){ ?>
            <img src="imagenes/<?php echo htmlspecialchars($_SESSION['Usuario']['Foto']); ?>" class="rounded-circle" width="40" height="40" style="object-fit: cover;">
        <?php }else{ ?>
            <i class="bi bi-person-circle text-white fs-2"></i>
        <?php } ?>
            <span class="text-white fw-normal mx-2"><?php echo htmlspecialchars($_SESSION['Usuario']['Nombre']." ".$_SESSION['Usuario']['Apellido']); ?></span>
            <a href="miscursos.php" class="btn btn-warning btn-sm me-2">Mis cursos</a>
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
            <div class="col-lg-8">

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
                    </div>
                </div>

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

                <div class="card card-cl curso-card shadow-sm" id="preguntas">
                    <div class="card-body p-4">
                        <h2 class="h5 border-bottom border-warning border-3 pb-2 mb-3"><i class="bi bi-chat-dots text-warning me-2"></i>Preguntas</h2>
                        <?php if(mysqli_num_rows($preguntas) == 0){ ?>
                            <p class="text-secondary mb-0">Todavía no hay preguntas en este curso.</p>
                        <?php }else{ ?>
                            <?php while($pregunta = mysqli_fetch_assoc($preguntas)){ ?>
                                <div class="border-bottom pb-3 mb-3">
                                    <h3 class="h6 mb-1"><?php echo htmlspecialchars($pregunta['Titulo']); ?></h3>
                                    <p class="text-secondary small mb-1"><?php echo nl2br(htmlspecialchars($pregunta['Texto'])); ?></p>
                                    <span class="small text-secondary"><i class="bi bi-person me-1"></i><?php echo htmlspecialchars($pregunta['Nombre']." ".$pregunta['Apellido']); ?></span>
                                    <span class="badge bg-secondary ms-2"><?php echo htmlspecialchars($pregunta['Estado']); ?></span>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>

            </div><!-- fin col-lg-8 -->

            <!-- Columna lateral -->
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

        </div>
    </div>
<?php } ?>
</main>
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