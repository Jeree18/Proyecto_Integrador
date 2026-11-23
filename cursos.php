
<?php
session_start();
include "Persistencia/Conexion.php";

$conexion = conectar();

$notificaciones = [];
$idUsuarioNotif = 0;

if(isset($_SESSION['Usuario'])){
    $idUsuarioNotif = (int)$_SESSION['Usuario']['ID'];

    $resNotif = mysqli_query($conexion, "SELECT n.ID, n.Texto, n.ID_pregunta, n.ID_respuesta, p.ID_curso, c.ID_Creador
                                         FROM notificaciones n
                                         LEFT JOIN preguntas p ON p.ID_pregunta = n.ID_pregunta
                                         LEFT JOIN cursos c ON c.ID = p.ID_curso
                                         WHERE n.ID_usuario = $idUsuarioNotif
                                         ORDER BY n.ID DESC");

    while($fila = mysqli_fetch_assoc($resNotif)){
        $notificaciones[] = $fila;
    }
}

$cantNotificaciones = count($notificaciones);
$hoy = date("Y-m-d");

// Buscador
$busqueda = trim($_GET['buscar'] ?? "");
$busquedaSegura = mysqli_real_escape_string($conexion, $busqueda);

// Obtener todos los cursos públicos
$sql = "SELECT cursos.*, creador.Plan AS plan_creador
        FROM cursos
        JOIN usuarios AS creador ON creador.ID = cursos.ID_Creador
        WHERE cursos.Publico = 'si'
        AND (
            '$busquedaSegura' = ''
            OR cursos.Titulo LIKE '%$busquedaSegura%'
            OR cursos.Descripcion LIKE '%$busquedaSegura%'
            OR EXISTS (
                SELECT 1
                FROM curso_etiqueta ce
                JOIN etiquetas e ON e.ID = ce.ID_Etiqueta
                WHERE ce.ID_Curso = cursos.ID
                AND e.Nombre LIKE '%$busquedaSegura%'
            )
        )
        ORDER BY
            CASE WHEN creador.Plan = 'pro' THEN 0 ELSE 1 END,
            cursos.Fecha_Inicio ASC";

$cursos = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todos los cursos | Cursolandia</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="Estilos/estilos.css?v=2" rel="stylesheet">
</head>

<body>

<header class="py-2 text-bg-dark sticky-top">
    <div class="container-fluid px-3">
        <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">

            <a href="index.php" class="d-flex align-items-center mb-2 mb-lg-0 me-lg-4 text-white text-decoration-none">
                <i class="bi bi-rocket-takeoff-fill text-warning fs-3 me-2"></i>
                <span class="fs-5 fw-semibold">Cursolandia</span>
            </a>

            <ul class="nav col-12 col-lg-auto mb-2 justify-content-center mb-md-0">
                <li><a href="index.php" class="nav-link px-3 text-white">Inicio</a></li>
                <li><a href="cursos.php" class="nav-link px-3 text-white">Cursos</a></li>
                <li><a href="index.php#como-funciona" class="nav-link px-3 text-white">Cómo funciona</a></li>
                <li><a href="index.php#contacto" class="nav-link px-3 text-white">Contacto</a></li>
            </ul>

            <form action="cursos.php" method="GET" class="col-12 col-lg-auto mb-3 mb-lg-0 mx-lg-auto" role="search" style="max-width: 300px;">
                <input
                    type="search"
                    name="buscar"
                    class="form-control form-control-dark text-bg-dark"
                    placeholder="Buscar..."
                    aria-label="Buscar"
                    value="<?= htmlspecialchars($busqueda) ?>">
            </form>

            <div class="text-end">
                <?php
                if(!isset($_SESSION['Usuario'])){
                    echo "<a href='login.php' class='btn btn-outline-light me-2'>Iniciar sesión</a>";
                    echo "<a href='registro.php' class='btn btn-warning'>Registrarse</a>";
                }else{
                    echo "<div class='d-flex align-items-center'>";

                    if($_SESSION['Usuario']['Foto'] != ""){
                        echo "<img src='imagenes/".$_SESSION['Usuario']['Foto']."' class='rounded-circle' width='40' height='40' style='object-fit: cover;'>";
                    }else{
                        echo "<i class='bi bi-person-circle text-white fs-2'></i>";
                    }

                    echo "<span class='text-white fw-normal mx-2'>".htmlspecialchars($_SESSION['Usuario']['Nombre'])." ".htmlspecialchars($_SESSION['Usuario']['Apellido'])."</span>";
                ?>

                    <!-- Campana de notificaciones -->
                    <div class="dropdown me-2">
                        <button type="button" class="btn btn-outline-light btn-sm position-relative" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="Notificaciones">
                            <i class="bi bi-bell-fill"></i>

                            <?php if($cantNotificaciones > 0){ ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark">
                                    <?= $cantNotificaciones ?>
                                </span>
                            <?php } ?>
                        </button>

                        <div class="dropdown-menu dropdown-menu-end p-0 shadow" style="width: 360px; max-width: 90vw;">

                            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                                <span class="fw-semibold">Notificaciones</span>

                                <form method="post" action="notificaciones_accion.php">
                                    <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-none" name="limpiar_notificaciones">
                                        Marcar todas como leídas
                                    </button>
                                </form>
                            </div>

                            <?php if($cantNotificaciones == 0){ ?>
                                <p class="text-center text-secondary small my-3">No tenés notificaciones.</p>
                            <?php }else{ ?>

                                <div style="max-height: 360px; overflow-y: auto;">
                                    <?php foreach($notificaciones as $notif){

                                        if($notif['ID_respuesta'] !== null){
                                            $icono = "bi-reply";
                                        }elseif($notif['ID_pregunta'] !== null){
                                            $icono = "bi-chat-dots";
                                        }else{
                                            $icono = "bi-person-plus";
                                        }

                                        if($notif['ID_pregunta'] !== null && $notif['ID_curso'] !== null){
                                            $pagina = ($notif['ID_Creador'] == $idUsuarioNotif) ? "gestionar_curso.php" : "Detalles_curso.php";
                                            $enlace = $pagina."?id=".$notif['ID_curso']."#pregunta-".$notif['ID_pregunta'];
                                        }else{
                                            $enlace = "Mis_cursos.php";
                                        }
                                    ?>
                                        <div class="d-flex align-items-start gap-2 px-3 py-2 border-bottom">
                                            <i class="bi <?= $icono ?> text-warning fs-5"></i>

                                            <a href="<?= $enlace ?>" class="flex-grow-1 small text-decoration-none text-dark">
                                                <?= htmlspecialchars($notif['Texto']) ?>
                                            </a>

                                            <form method="post" action="notificaciones_accion.php">
                                                <input type="hidden" name="id_notificacion" value="<?= $notif['ID'] ?>">
                                                <button type="submit" class="btn-close btn-sm" name="descartar_notificacion" aria-label="Descartar"></button>
                                            </form>
                                        </div>
                                    <?php } ?>
                                </div>

                            <?php } ?>
                        </div>
                    </div>

                <?php
                    echo "<a href='Mis_cursos.php' class='btn btn-warning btn-sm me-2'>Mis cursos</a>";
                    echo "<a href='cerrar_sesion.php' class='btn btn-outline-light btn-sm'>Cerrar sesión</a>";
                    echo "</div>";
                }
                ?>
            </div>

        </div>
    </div>
</header>

<main>

    <section class="py-5">
        <div class="container">

            <div class="mb-4">
                <h1 class="fw-bold mb-1">
                    <i class="bi bi-collection-play-fill text-gold me-2"></i>
                    Explorá todos los cursos
                </h1>

                <p class="text-secondary mb-0">
                    Encontrá el curso ideal para aprender algo nuevo.
                </p>
            </div>

            <?php if($busqueda != ""){ ?>
                <p class="text-secondary">
                    Resultados para: <strong><?= htmlspecialchars($busqueda) ?></strong>
                    <a href="cursos.php" class="ms-2 text-gold">Limpiar búsqueda</a>
                </p>
            <?php } ?>

            <!-- Carrusel de cursos -->
            <div id="carruselCursos" class="carousel slide" data-bs-ride="false">

                <div class="carousel-inner">

                    <?php
                    $contador = 0;

                    while($c = $cursos->fetch_assoc()){

                        if($hoy < $c["Fecha_Inicio"]){
                            $textoEstado = "Próximo";
                            $claseEstado = "text-bg-dark";
                        }elseif($hoy > $c["Fecha_Fin"]){
                            $textoEstado = "Finalizado";
                            $claseEstado = "bg-secondary";
                        }else{
                            $textoEstado = "Activo";
                            $claseEstado = "bg-success";
                        }

                        $imagenCurso = !empty($c["Imagen"]) ? $c["Imagen"] : "imagenes/cursos/default.jpg";

                        // Cada tres cursos comienza una nueva diapositiva
                        if($contador % 9 == 0){
                            if($contador > 0){
                                echo '</div></div>';
                            }
                    ?>

                            <div class="carousel-item <?= $contador == 0 ? 'active' : '' ?>">
                                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">

                    <?php } ?>

                            <div class="col">
                                <div class="card card-cl curso-card h-100 shadow">

                                    <div class="position-relative">
                                        <img src="<?= htmlspecialchars($imagenCurso) ?>" class="card-img-top" alt="" style="height: 180px; object-fit: cover;">

                                        <div class="position-absolute top-0 start-0 m-2">

                                            <?php if($c["plan_creador"] == "pro"){ ?>
                                                <span class="badge bg-warning text-dark shadow-sm">
                                                    <i class="bi bi-star-fill me-1"></i>Destacado
                                                </span>
                                            <?php } ?>

                                            <span class="badge <?= $claseEstado ?> shadow-sm">
                                                <?= $textoEstado ?>
                                            </span>

                                        </div>

                                        <span class="position-absolute bottom-0 end-0 m-2 badge text-bg-dark fs-6 shadow-sm">
                                            <?= $c["Costo"] > 0 ? '$'.number_format($c["Costo"], 2, ',', '.') : 'Gratuito' ?>
                                        </span>
                                    </div>

                                    <div class="card-body d-flex flex-column">

                                        <h2 class="h5 card-title">
                                            <?= htmlspecialchars($c["Titulo"]) ?>
                                        </h2>

                                        <p class="card-text text-secondary small">
                                            <?= htmlspecialchars(mb_strimwidth($c["Descripcion"] ?? "", 0, 100, "...")) ?>
                                        </p>

                                        <ul class="list-unstyled small text-secondary mb-3">
                                            <li>
                                                <i class="bi bi-calendar-event text-gold me-1"></i>
                                                <?= date("d/m/Y", strtotime($c["Fecha_Inicio"])) ?>
                                                al
                                                <?= date("d/m/Y", strtotime($c["Fecha_Fin"])) ?>
                                            </li>

                                            <li>
                                                <i class="bi bi-people text-gold me-1"></i>
                                                <?= $c["Cupo"] > 0 ? $c["Cupo"].' lugares' : 'Sin límite de cupo' ?>
                                            </li>
                                        </ul>

                                        <a href="Inscripcion.php?id=<?= $c["ID"] ?>" class="btn btn-dark mt-auto">
                                            Ver curso
                                        </a>

                                    </div>
                                </div>
                            </div>

                    <?php
                        $contador++;
                    }

                    // Cerrar la última diapositiva
                    if($contador > 0){
                        echo '</div></div>';
                    }
                    ?>

                </div>

                <?php if($contador > 3){ ?>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carruselCursos" data-bs-slide="prev" style="width: 5%;">
                        <span class="carousel-control-prev-icon bg-dark rounded-circle p-3" aria-hidden="true"></span>
                        <span class="visually-hidden">Anterior</span>
                    </button>

                    <button class="carousel-control-next" type="button" data-bs-target="#carruselCursos" data-bs-slide="next" style="width: 5%;">
                        <span class="carousel-control-next-icon bg-dark rounded-circle p-3" aria-hidden="true"></span>
                        <span class="visually-hidden">Siguiente</span>
                    </button>
                <?php } ?>

            </div>

            <?php if($contador == 0){ ?>
                <div class="text-center py-5">
                    <i class="bi bi-search display-4 text-secondary"></i>
                    <h2 class="h4 mt-3">No encontramos cursos</h2>
                    <p class="text-secondary">Probá con otra búsqueda.</p>
                    <a href="cursos.php" class="btn btn-dark">Ver todos los cursos</a>
                </div>
            <?php } ?>

        </div>
    </section>

</main>

<footer id="contacto" class="text-bg-dark py-5 border-top border-warning border-4">
    <div class="container">

        <div class="row g-4">

            <div class="col-md-5">
                <a href="index.php" class="text-white text-decoration-none d-inline-flex align-items-center mb-2">
                    <i class="bi bi-rocket-takeoff-fill fs-3 me-2"></i>
                    <span class="fs-5 fw-semibold">Cursolandia</span>
                </a>

                <p class="text-secondary">
                    Una plataforma para crear cursos e inscribirte en ellos.
                </p>
            </div>

            <div class="col-6 col-md-3">
                <h3 class="h6">Navegación</h3>

                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a href="index.php" class="nav-link p-0 mb-2 text-secondary">Inicio</a>
                    </li>

                    <li class="nav-item">
                        <a href="index.php#como-funciona" class="nav-link p-0 mb-2 text-secondary">Cómo funciona</a>
                    </li>

                    <li class="nav-item">
                        <a href="cursos.php" class="nav-link p-0 mb-2 text-secondary">Cursos</a>
                    </li>
                </ul>
            </div>

            <div class="col-6 col-md-4">
                <h3 class="h6">Contacto</h3>

                <ul class="list-unstyled text-secondary">
                    <li class="mb-2">
                        <i class="bi bi-envelope me-2"></i>
                        Jeremiasbarroso01@gmail.com
                    </li>

                    <li class="mb-2">
                        <i class="bi bi-geo-alt me-2"></i>
                        San Luis, Argentina
                    </li>
                </ul>
            </div>

        </div>

        <hr class="border-secondary my-4">

        <p class="text-secondary small mb-0">
            &copy; 2026 Cursolandia. Todos los derechos reservados.
        </p>

    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
