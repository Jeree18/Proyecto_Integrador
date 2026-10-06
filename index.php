<?php
session_start();
include "Persistencia/Conexion.php";
$conexion=conectar();
$sql = "SELECT c.*, COUNT(i.ID_Usuario) AS inscriptos
        FROM cursos c
        LEFT JOIN inscripcion i ON i.ID_Curso = c.ID AND i.Estado = 'activa'
        GROUP BY c.ID
        ORDER BY inscriptos DESC, c.ID DESC
        LIMIT 3";
$destacados = $conexion->query($sql);





?>








<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cursolandia</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="Estilos/estilos.css?v=2" rel="stylesheet">>
</head>
<body>

<header class="py-3 text-bg-dark">
  <div class="container-fluid px-3">
    <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
      <a href="index.php" class="d-flex align-items-center mb-2 mb-lg-0 text-white text-decoration-none">
        <i class="bi bi-rocket-takeoff-fill text-warning fs-3 me-2"></i>
        <span class="fs-4 fw-semibold">Cursolandia</span>
      </a>
      <!-- Solo links que existen, todos del mismo color -->
      <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
        <li><a href="index.php" class="nav-link px-2 text-white" aria-current="page">Inicio</a></li>
        <li><a href="#cursos" class="nav-link px-2 text-white">Cursos</a></li>
        <li><a href="#como-funciona" class="nav-link px-2 text-white">Cómo funciona</a></li>
        <li><a href="#contacto" class="nav-link px-2 text-white">Contacto</a></li>
      </ul>
      <form class="col-12 col-lg-auto mb-3 mb-lg-0 me-lg-3" role="search">
        <input type="search" class="form-control form-control-dark text-bg-dark" placeholder="Buscar..." aria-label="Buscar">
      </form>
      <div class="text-end">
        <button type="button" class="btn btn-outline-light me-2">Iniciar sesión</button>
        <button type="button" class="btn btn-warning">Registrarse</button>
      </div>
    </div>
  </div>
</header>

<main>

  <!-- 1. CARRUSEL: PRESENTACIÓN + CURSOS DESTACADOS -->
  <section aria-label="Cursos destacados">
    <div id="carruselCursos" class="carousel slide" data-bs-ride="carousel">
      <div class="carousel-indicators">
        <button type="button" data-bs-target="#carruselCursos" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Presentación"></button>
        <?php for ($k = 1; $k <= $destacados->num_rows; $k++): ?>
        <button type="button" data-bs-target="#carruselCursos" data-bs-slide-to="<?= $k ?>" aria-label="Curso <?= $k ?>"></button>
        <?php endfor; ?>


          
        
        
        
        
        
        ?>
      </div>
      <div class="carousel-inner">

        <!-- Slide 0: presentación (lleva el h1 de la página). Misma estructura que las otras slides -->
        <div class="carousel-item active slide-curso slide-0">
          <div class="container carousel-caption-custom">
            <div class="row align-items-center">
              <div class="col-md-8">
                <h1 class="display-5 fw-bold">Aprendé y enseñá en un solo lugar</h1>
                <p class="lead">Cursolandia es una plataforma de cursos online: inscribite en los que ya existen o creá los tuyos para que otros se inscriban.</p>
                <div class="d-flex flex-wrap gap-2">
                  <a href="#cursos" class="btn btn-warning btn-lg">Ver cursos</a>
                  <a href="#" class="btn btn-outline-light btn-lg">Crear mi cuenta</a>
                </div>
              </div>
              <div class="col-md-4 d-none d-md-block text-center">
                <!-- Poné tu imagen en Imagenes/portada.jpg (o cambiá el nombre en src) -->
                <img src="Imagenes/imagen1.jpg" alt="Personas aprendiendo en Cursolandia" class="slide-imagen rounded shadow">
              </div>
            </div>
          </div>
        </div>
        <?php  $n=0; while($c=$destacados->fetch_assoc()):$n++;?>    
        <div class="carousel-item slide-curso slide-<?$n?>">
          <div class="container carousel-caption-custom">
            <div class="row align-items-center">
              <div class="col-md-8">
                <span class="badge text-bg-warning mb-3">Destacados</span>
                <h2 class="display-6 fw-bold"><?= htmlspecialchars($c["Titulo"])?></h2>
                <p class="lead"><?= htmlspecialchars($c["Descripcion"])??""?></p>
                <p class="mb-3">Del <?= htmlspecialchars($c["Fecha_Inicio"])?>  &nbsp;  Al &nbsp;   <?= htmlspecialchars($c["Fecha_Fin"])?></p>
                <a href="#" class="btn btn-warning btn-lg">Inscribirme</a>
              </div>
              <div class="col-md-4 d-none d-md-block text-center">
                <img src="<?=$c["Imagen"]?>" alt="Imagen" class="slide-imagen rounded shadow">
              </div>
            </div>
          </div>
        </div>
        <?php endwhile;?>  
        

        

      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#carruselCursos" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">Anterior</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#carruselCursos" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">Siguiente</span>
      </button>
    </div>
  </section>

  <!-- 2. QUÉ PODÉS HACER (justo después del carrusel) -->
  <section id="que-puedo-hacer" class="py-5 bg-soft">
    <div class="container">
      <h2 class="fw-bold text-center mb-5">Qué podés hacer</h2>
      <div class="row g-4">
        <div class="col-md-6">
          <div class="card card-cl step-card h-100 shadow-sm">
            <div class="card-body p-4">
              <h3 class="h4"><i class="bi bi-mortarboard me-2 text-gold"></i>Si querés aprender</h3>
              <ul class="list-unstyled mt-3 mb-4">
                <li class="mb-2"><i class="bi bi-check-lg text-gold me-2"></i>Explorar el catálogo y buscar por tema</li>
                <li class="mb-2"><i class="bi bi-check-lg text-gold me-2"></i>Inscribirte en uno o varios cursos</li>
                <li class="mb-2"><i class="bi bi-check-lg text-gold me-2"></i>Ver tus cursos desde tu cuenta</li>
              </ul>
              <a href="#cursos" class="btn btn-outline-dark">Ver cursos</a>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card card-cl step-card h-100 shadow-sm">
            <div class="card-body p-4">
              <h3 class="h4"><i class="bi bi-person-video3 me-2 text-gold"></i>Si querés enseñar</h3>
              <ul class="list-unstyled mt-3 mb-4">
                <li class="mb-2"><i class="bi bi-check-lg text-gold me-2"></i>Crear un curso con título, descripción y duración</li>
                <li class="mb-2"><i class="bi bi-check-lg text-gold me-2"></i>Publicarlo para que otros se inscriban</li>
                <li class="mb-2"><i class="bi bi-check-lg text-gold me-2"></i>Ver quiénes se inscribieron</li>
              </ul>
              <a href="#" class="btn btn-warning">Crear un curso</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 3. CÓMO FUNCIONA (antes del listado) -->
  <section id="como-funciona" class="py-5">
    <div class="container">
      <h2 class="fw-bold text-center mb-5">Cómo funciona</h2>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="card step-card text-bg-dark h-100 border-0 shadow">
            <div class="card-body p-4 text-center">
              <span class="icon-circle mb-3"><i class="bi bi-person-plus"></i></span>
              <h3 class="h5">1. Creá tu cuenta</h3>
              <p class="text-white-50 mb-0">Registrate con tu correo para guardar tus cursos y tu progreso.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card step-card text-bg-dark h-100 border-0 shadow">
            <div class="card-body p-4 text-center">
              <span class="icon-circle mb-3"><i class="bi bi-search"></i></span>
              <h3 class="h5">2. Elegí un curso</h3>
              <p class="text-white-50 mb-0">Explorá el catálogo o buscá por tema desde la barra superior.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card step-card text-bg-dark h-100 border-0 shadow">
            <div class="card-body p-4 text-center">
              <span class="icon-circle mb-3"><i class="bi bi-check2-circle"></i></span>
              <h3 class="h5">3. Inscribite y empezá</h3>
              <p class="text-white-50 mb-0">Confirmá tu inscripción y accedé al contenido de inmediato.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 4. LISTADO DE CURSOS -->
  <section id="cursos" class="py-5 bg-soft">
    <div class="container">
      <div class="d-flex flex-wrap justify-content-between align-items-end mb-4">
        <div>
          <h2 class="fw-bold mb-1">Todos los cursos</h2>
          <p class="text-secondary mb-0">Elegí uno e inscribite en un par de clics.</p>
        </div>
        <a href="#" class="btn btn-dark mt-3 mt-md-0"><i class="bi bi-plus-circle me-1"></i> Crear un curso</a>
      </div>

      <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <div class="col"><div class="card card-cl h-100 shadow-sm"><div class="card-body d-flex flex-column">
          <span class="curso-icono mb-3"><i class="bi bi-code-slash"></i></span>
          <h3 class="h5 card-title">Desarrollo web desde cero</h3>
          <p class="card-text text-secondary">HTML, CSS y JavaScript para armar tu primer sitio completo.</p>
          <div class="mt-auto d-flex justify-content-between align-items-center">
            <small class="text-secondary"><i class="bi bi-clock me-1"></i>8 semanas</small>
            <a href="#" class="btn btn-outline-dark btn-sm">Inscribirme</a>
          </div></div></div></div>

        <div class="col"><div class="card card-cl h-100 shadow-sm"><div class="card-body d-flex flex-column">
          <span class="curso-icono mb-3"><i class="bi bi-database"></i></span>
          <h3 class="h5 card-title">Bases de datos con MySQL</h3>
          <p class="card-text text-secondary">Tablas, relaciones y consultas para tus proyectos.</p>
          <div class="mt-auto d-flex justify-content-between align-items-center">
            <small class="text-secondary"><i class="bi bi-clock me-1"></i>6 semanas</small>
            <a href="#" class="btn btn-outline-dark btn-sm">Inscribirme</a>
          </div></div></div></div>

        <div class="col"><div class="card card-cl h-100 shadow-sm"><div class="card-body d-flex flex-column">
          <span class="curso-icono mb-3"><i class="bi bi-filetype-php"></i></span>
          <h3 class="h5 card-title">Programación en PHP</h3>
          <p class="card-text text-secondary">Formularios, sesiones y acceso a base de datos del lado del servidor.</p>
          <div class="mt-auto d-flex justify-content-between align-items-center">
            <small class="text-secondary"><i class="bi bi-clock me-1"></i>10 semanas</small>
            <a href="#" class="btn btn-outline-dark btn-sm">Inscribirme</a>
          </div></div></div></div>

        <div class="col"><div class="card card-cl h-100 shadow-sm"><div class="card-body d-flex flex-column">
          <span class="curso-icono mb-3"><i class="bi bi-phone"></i></span>
          <h3 class="h5 card-title">Diseño responsive con Bootstrap</h3>
          <p class="card-text text-secondary">Páginas que se adaptan a cualquier pantalla.</p>
          <div class="mt-auto d-flex justify-content-between align-items-center">
            <small class="text-secondary"><i class="bi bi-clock me-1"></i>4 semanas</small>
            <a href="#" class="btn btn-outline-dark btn-sm">Inscribirme</a>
          </div></div></div></div>

        <div class="col"><div class="card card-cl h-100 shadow-sm"><div class="card-body d-flex flex-column">
          <span class="curso-icono mb-3"><i class="bi bi-git"></i></span>
          <h3 class="h5 card-title">Git y GitHub</h3>
          <p class="card-text text-secondary">Guardá versiones de tu código y trabajá en equipo.</p>
          <div class="mt-auto d-flex justify-content-between align-items-center">
            <small class="text-secondary"><i class="bi bi-clock me-1"></i>2 semanas</small>
            <a href="#" class="btn btn-outline-dark btn-sm">Inscribirme</a>
          </div></div></div></div>

        <div class="col"><div class="card card-cl h-100 shadow-sm"><div class="card-body d-flex flex-column">
          <span class="curso-icono mb-3"><i class="bi bi-braces"></i></span>
          <h3 class="h5 card-title">JavaScript avanzado</h3>
          <p class="card-text text-secondary">Funciones, DOM, eventos y consumo de APIs.</p>
          <div class="mt-auto d-flex justify-content-between align-items-center">
            <small class="text-secondary"><i class="bi bi-clock me-1"></i>8 semanas</small>
            <a href="#" class="btn btn-outline-dark btn-sm">Inscribirme</a>
          </div></div></div></div>
      </div>
    </div>
  </section>

  <!-- 5. POR QUÉ PODÉS CONFIAR -->
  <section id="confianza" class="py-5">
    <div class="container">
      <h2 class="fw-bold text-center mb-1">Por qué podés confiar</h2>
      <p class="text-secondary text-center mb-5">Esto es lo que hacemos para cuidar tu cuenta y tu aprendizaje.</p>
      <div class="row g-4 text-center mb-5">
        <div class="col-md-4">
          <span class="icon-circle"><i class="bi bi-shield-lock"></i></span>
          <h3 class="h5 mt-3">Tus datos protegidos</h3>
          <p class="text-secondary">Tu contraseña se guarda cifrada y no compartimos tus datos con terceros.</p>
        </div>
        <div class="col-md-4">
          <span class="icon-circle"><i class="bi bi-patch-check"></i></span>
          <h3 class="h5 mt-3">Cursos con información clara</h3>
          <p class="text-secondary">Cada curso muestra su contenido, duración y nivel antes de inscribirte.</p>
        </div>
        <div class="col-md-4">
          <span class="icon-circle"><i class="bi bi-headset"></i></span>
          <h3 class="h5 mt-3">Contacto directo</h3>
          <p class="text-secondary">Si tenés dudas, escribinos y te respondemos por correo.</p>
        </div>
      </div>

      <h3 class="h5 text-center mb-4">Lo que dicen quienes ya lo usaron</h3>
      <!-- Reseñas de ejemplo: reemplazalas por opiniones reales cuando las tengas -->
      <div class="row row-cols-1 row-cols-md-3 g-4">
        <div class="col"><figure class="card card-cl-left shadow-sm h-100 p-3 mb-0">
          <blockquote class="blockquote fs-6">Me inscribí en minutos y pude empezar el mismo día.</blockquote>
          <figcaption class="blockquote-footer mb-0">Estudiante (reseña de ejemplo)</figcaption></figure></div>
        <div class="col"><figure class="card card-cl-left shadow-sm h-100 p-3 mb-0">
          <blockquote class="blockquote fs-6">Publicar mi curso fue simple y ya tengo inscriptos.</blockquote>
          <figcaption class="blockquote-footer mb-0">Docente (reseña de ejemplo)</figcaption></figure></div>
        <div class="col"><figure class="card card-cl-left shadow-sm h-100 p-3 mb-0">
          <blockquote class="blockquote fs-6">Los cursos explican bien qué voy a aprender antes de empezar.</blockquote>
          <figcaption class="blockquote-footer mb-0">Estudiante (reseña de ejemplo)</figcaption></figure></div>
      </div>
    </div>
  </section>

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
          <li class="nav-item"><a href="#como-funciona" class="nav-link p-0 mb-2 text-secondary">Cómo funciona</a></li>
          <li class="nav-item"><a href="#cursos" class="nav-link p-0 mb-2 text-secondary">Cursos</a></li>
        </ul>
      </div>
      <div class="col-6 col-md-4">
        <h3 class="h6">Contacto</h3>
        <ul class="list-unstyled text-secondary">
          <li class="mb-2"><i class="bi bi-envelope me-2"></i>contacto@ejemplo.com</li>
          <li class="mb-2"><i class="bi bi-geo-alt me-2"></i>San Luis, Argentina</li>
        </ul>
      </div>
    </div>
    <hr class="border-secondary my-4">
    <p class="text-secondary small mb-0">&copy; 2026 Cursolandia. Todos los derechos reservados.</p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>