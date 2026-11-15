<?php
session_start();
if(!isset($_SESSION['Usuario'])){
header("location:login.php");
exit();
}
if(isset($_POST['crear'])){












}


?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear curso | Cursolandia</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="estilos/estilos.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">

<header class="py-3 text-bg-dark sticky-top">
  <div class="container-fluid px-3">
    <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
      <a href="index.php" class="d-flex align-items-center mb-2 mb-lg-0 text-white text-decoration-none">
        <i class="bi bi-rocket-takeoff-fill text-warning fs-3 me-2"></i>
        <span class="fs-4 fw-semibold">Cursolandia</span>
      </a>
      <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
        <li><a href="index.php" class="nav-link px-2 text-white">Inicio</a></li>
        <li><a href="index.php#cursos" class="nav-link px-2 text-white">Cursos</a></li>
        <li><a href="index.php#como-funciona" class="nav-link px-2 text-white">Cómo funciona</a></li>
        <li><a href="index.php#contacto" class="nav-link px-2 text-white">Contacto</a></li>
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
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">

                <div class="card card-cl shadow-sm">
                    <div class="card-body p-4 p-md-5">

                        <div class="text-center mb-4">
                            <span class="icon-circle mb-3"><i class="bi bi-journal-plus"></i></span>
                            <h1 class="h3 mb-1">Crear nuevo curso</h1>
                            <p class="text-secondary mb-0">Completá los datos de tu curso para publicarlo.<br>(Los campos con * son obligatorios)</p>
                        </div>

                        <form action="crear_curso.php" method="post" novalidate>
                            <div class="row g-3">

                                <div class="col-12">
                                    <label for="titulo" class="form-label">Título *</label>
                                    <input type="text" class="form-control" id="titulo" name="titulo" maxlength="100" required>
                                    
                                </div>

                                <div class="col-12">
                                    <label for="descripcion" class="form-label">Descripción *</label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="4" required></textarea>
                                    
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="fecha_inicio" class="form-label">Fecha de inicio *</label>
                                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                                    >
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="fecha_fin" class="form-label">Fecha de fin *</label>
                                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
                                    
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="costo" class="form-label">Costo <span class="text-secondary"></span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="costo" name="costo" min="0" step="0.01">
                                    </div>
                                    
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="cupo" class="form-label">Cupo <span class="text-secondary"></span></label>
                                    <input type="number" class="form-control" id="cupo" name="cupo" min="1">
                                    
                                </div>

                                <div class="col-12">
                                    <label class="form-label d-block">Acceso *</label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="publico" id="acceso_publico" value="si" checked>
                                        <label class="form-check-label" for="acceso_publico">Público</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="publico" id="acceso_privado" value="no">
                                        <label class="form-check-label" for="acceso_privado">Privado</label>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label for="etiquetas" class="form-label">Etiquetas <span class="text-secondary">(opcional)</span></label>
                                    <input type="text" class="form-control" id="etiquetas" name="etiquetas" placeholder="Separadas por comas">
                                    <div class="form-text">Ayudan a que otros usuarios encuentren tu curso.</div>
                                    
                                </div>

                                <div class="col-12 d-grid mt-4">
                                    <button type="submit" class="btn btn-warning btn-lg" name="crear">Crear curso</button>
                                </div>

                            </div>
                        </form>

                        <p class="text-center mt-4 mb-0">
                            <a href="index.php" class="text-gold fw-semibold">Volver al inicio</a>
                        </p>

                    </div>
                </div>

            </div>
        </div>
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
          <li class="mb-2"><i class="bi bi-envelope me-2"></i>Jeremiasbarroso01@gmail.com</li>
          <li class="mb-2"><i class="bi bi-geo-alt me-2"></i>San Luis, Argentina</li>
        </ul>
      </div>
    </div>
    <hr class="border-secondary my-4">
    <p class="text-secondary small mb-0">&copy; 2026 Cursolandia. Todos los derechos reservados.</p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>