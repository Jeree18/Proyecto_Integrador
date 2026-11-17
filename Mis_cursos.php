<?php
session_start();
if(!isset($_SESSION['Usuario'])){
    header("location:login.php");
    exit();
}


?>




<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis cursos | Cursolandia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="Estilos/estilos.css?v=3" rel="stylesheet">
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
            echo "<a href='cerrar_sesion.php' class='btn btn-outline-light btn-sm'>Cerrar sesión</a>";
            echo "</div>";
        }
        ?>
      </div>

    </div>
  </div>
</header>

<main class="bg-soft flex-grow-1 py-5">
    <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Mis cursos</h1>
            <a href="crear_curso.php" class="btn btn-warning"><i class="bi bi-plus-lg me-1"></i>Crear curso</a>
        </div>

        <!--cursos creados -->
        <h2 class="h5 border-bottom border-warning border-3 pb-2 mb-4"><i class="bi bi-mortarboard-fill text-warning me-2"></i>Cursos creados</h2>

        <div class="row g-4 mb-5">

            <!-- Tarjeta de ejemplo 1: activo -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card card-cl curso-card shadow-sm h-100 border-0 border-top border-warning border-4">
                    <img src="imagenes/imagen1.jpg" class="card-img-top" alt="" style="height: 160px; object-fit: cover;">
                    <div class="card-body d-flex flex-column">
                        <div class="mb-2">
                            <span class="badge bg-warning text-dark">Activo</span>
                            <span class="badge border border-dark text-dark">Público</span>
                        </div>
                        <h3 class="h5">Título del curso</h3>
                        <p class="text-secondary small flex-grow-1">Descripción corta del curso, que se recorta a unos 100 caracteres...</p>
                        <ul class="list-unstyled small mb-3">
                            <li><i class="bi bi-calendar-event text-warning me-1"></i>01/12/2026 al 31/12/2026</li>
                            <li><i class="bi bi-cash text-warning me-1"></i>Gratuito</li>
                            <li><i class="bi bi-people text-warning me-1"></i>3 inscriptos de 20</li>
                        </ul>
                        <a href="curso.php" class="btn btn-dark btn-sm">Ver curso</a>
                    </div>
                </div>
            </div>

            <!-- Tarjeta de ejemplo 2: próximo -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card card-cl curso-card shadow-sm h-100 border-0 border-top border-warning border-4">
                    <img src="imagenes/imagen1.jpg" class="card-img-top" alt="" style="height: 160px; object-fit: cover;">
                    <div class="card-body d-flex flex-column">
                        <div class="mb-2">
                            <span class="badge text-bg-dark">Próximo</span>
                            <span class="badge border border-dark text-dark">Privado</span>
                        </div>
                        <h3 class="h5">Otro curso</h3>
                        <p class="text-secondary small flex-grow-1">Otra descripción de ejemplo para ver cómo se acomodan las tarjetas...</p>
                        <ul class="list-unstyled small mb-3">
                            <li><i class="bi bi-calendar-event text-warning me-1"></i>15/01/2027 al 15/02/2027</li>
                            <li><i class="bi bi-cash text-warning me-1"></i>$5.000,00</li>
                            <li><i class="bi bi-people text-warning me-1"></i>0 inscriptos (sin límite de cupo)</li>
                        </ul>
                       <a href="curso.php" class="btn btn-dark btn-sm">Ver curso</a>
                    </div>
                </div>
            </div>

        </div>

        <div class="alert alert-secondary mb-5">Todavía no creaste ningún curso.</div>
                <!-- cursos inscriptos -->
        <h2 class="h5 border-bottom border-warning border-3 pb-2 mb-4"><i class="bi bi-journal-bookmark-fill text-warning me-2"></i>Cursos en los que estoy inscripto</h2>

        <div class="row g-4">

            <div class="col-12 col-md-6 col-lg-4">
                <div class="card card-cl curso-card shadow-sm h-100 border-0 border-top border-warning border-4">
                    <img src="imagenes/imagen1.jpg" class="card-img-top" alt="" style="height: 160px; object-fit: cover;">
                    <div class="card-body d-flex flex-column">
                        <div class="mb-2">
                            <span class="badge bg-warning text-dark">Inscripción activa</span>
                            <span class="badge border border-dark text-dark">Público</span>
                        </div>
                        <h3 class="h5">Curso de bases de datos</h3>
                        <p class="text-secondary small flex-grow-1">Descripción corta del curso al que estoy inscripto...</p>
                        <ul class="list-unstyled small mb-3">
                            <li><i class="bi bi-person text-warning me-1"></i>Creado por Ana Pérez</li>
                            <li><i class="bi bi-calendar-event text-warning me-1"></i>01/12/2026 al 31/12/2026</li>
                            <li><i class="bi bi-cash text-warning me-1"></i>Gratuito</li>
                            <li><i class="bi bi-check2-circle text-warning me-1"></i>Inscripto desde el 20/11/2026</li>
                        </ul>
                        <a href="curso.php" class="btn btn-dark btn-sm">Ir al curso</a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-4">
                <div class="card card-cl curso-card shadow-sm h-100 border-0 border-top border-warning border-4">
                    <img src="imagenes/imagen1.jpg" class="card-img-top" alt="" style="height: 160px; object-fit: cover;">
                    <div class="card-body d-flex flex-column">
                        <div class="mb-2">
                            <span class="badge bg-secondary">Finalizada</span>
                            <span class="badge border border-dark text-dark">Público</span>
                        </div>
                        <h3 class="h5">Introducción a PHP</h3>
                        <p class="text-secondary small flex-grow-1">Otro curso de ejemplo ya terminado...</p>
                        <ul class="list-unstyled small mb-3">
                            <li><i class="bi bi-person text-warning me-1"></i>Creado por Luis Gómez</li>
                            <li><i class="bi bi-calendar-event text-warning me-1"></i>01/09/2026 al 30/09/2026</li>
                            <li><i class="bi bi-cash text-warning me-1"></i>$3.000,00</li>
                            <li><i class="bi bi-check2-circle text-warning me-1"></i>Inscripto desde el 02/09/2026</li>
                        </ul>
                        <a href="curso.php" class="btn btn-dark btn-sm">Ir al curso</a>
                    </div>
                </div>
            </div>

        </div>

        <div class="alert alert-secondary mt-4">Todavía no te inscribiste en ningún curso.</div>
    </div>
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
</body>
</html>