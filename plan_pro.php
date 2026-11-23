<?php
session_start();

if(!isset($_SESSION['Usuario'])){
    header("Location: login.php");
    exit();
}

$esPro = false;

if($_SESSION['Usuario']['Plan'] == 'pro'){
    $esPro = true;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plan Pro - Cursolandia</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="Estilos/estilos.css?v=2" rel="stylesheet">
</head>

<body class="d-flex flex-column min-vh-100">

    <!-- HEADER -->
    
<header class="py-2 text-bg-dark sticky-top">
    <div class="container-fluid px-3">
        <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">

            <!-- LOGO -->
            <a href="index.php" class="d-flex align-items-center mb-2 mb-lg-0 me-lg-4 text-white text-decoration-none">
                <i class="bi bi-rocket-takeoff-fill text-warning fs-3 me-2"></i>
                <span class="fs-5 fw-semibold">Cursolandia</span>
            </a>

            <!-- NAVEGACIÓN -->
            <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
                <li>
                    <a href="index.php" class="nav-link px-3 text-white">Inicio</a>
                </li>
                <li>
                    <a href="cursos.php" class="nav-link px-3 text-white">Cursos</a>
                </li>
                <li>
                    <a href="index.php#como-funciona" class="nav-link px-3 text-white">Cómo funciona</a>
                </li>
                <li>
                    <a href="index.php#contacto" class="nav-link px-3 text-white">Contacto</a>
                </li>
            </ul>

            <!-- USUARIO -->
            <div class="text-end">
                <div class="d-flex align-items-center">

                    <?php
                    if($_SESSION['Usuario']['Foto'] != ""){
                    ?>
                        <img src="imagenes/<?php echo htmlspecialchars($_SESSION['Usuario']['Foto']); ?>"
                             class="rounded-circle"
                             width="40"
                             height="40"
                             style="object-fit: cover;">
                    <?php
                    }else{
                    ?>
                        <i class="bi bi-person-circle text-white fs-2"></i>
                    <?php
                    }
                    ?>

                    <span class="text-white fw-normal mx-2">
                        <?php
                        echo htmlspecialchars($_SESSION['Usuario']['Nombre'])." ".htmlspecialchars($_SESSION['Usuario']['Apellido']);
                        ?>
                    </span>
                    <a href='Mis_cursos.php' class='btn btn-warning btn-sm me-2'>Mis cursos</a>
                    <a href="cerrar_sesion.php" class="btn btn-outline-light btn-sm">
                        Cerrar sesión
                    </a>

                </div>
            </div>

        </div>
    </div>
</header>x


    <!-- CONTENIDO -->
    <main class="flex-grow-1 py-5 bg-soft">

        <div class="container">

            <!-- PRESENTACIÓN -->
            <div class="text-center mb-5">

                <span class="badge bg-warning text-dark px-3 py-2 mb-3">
                    <i class="bi bi-star-fill me-1"></i>
                    Cursolandia Pro
                </span>

                <h1 class="fw-bold display-5">
                    Llevá tu experiencia al siguiente nivel
                </h1>

                <p class="text-secondary fs-5 mx-auto" style="max-width: 700px;">
                    Aprovechá todo el potencial de Cursolandia.
                    Creá más cursos, compartí materiales y llegá a más estudiantes
                    sin las limitaciones del plan gratuito.
                </p>

            </div>


            <!-- TARJETA PRO -->
            <div class="row justify-content-center mb-5">

                <div class="col-lg-8">

                    <div class="card card-cl shadow-lg border-0">

                        <div class="card-body p-4 p-md-5">

                            <div class="text-center mb-4">

                                <i class="bi bi-star-fill text-warning display-3"></i>

                                <h2 class="fw-bold mt-3">
                                    Plan Pro
                                </h2>

                                <p class="text-secondary">
                                    Más posibilidades para enseñar y aprender.
                                </p>

                            </div>

                            <div class="row g-3">

                                <div class="col-md-6">
                                    <div class="d-flex gap-2">
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                        <span>Cursos activos ilimitados.</span>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="d-flex gap-2">
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                        <span>Archivos sin límite de cantidad.</span>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="d-flex gap-2">
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                        <span>Almacenamiento ilimitado.</span>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="d-flex gap-2">
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                        <span>Sin límite de tamaño por archivo.</span>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="d-flex gap-2">
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                        <span>Inscripciones ilimitadas.</span>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="d-flex gap-2">
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                        <span>Cursos destacados en Cursolandia.</span>
                                    </div>
                                </div>

                            </div>

                            <hr class="my-4">

                            <div class="text-center">

                                <?php if($esPro){ ?>

                                    <span class="badge bg-success fs-6 px-3 py-2">
                                        <i class="bi bi-check-circle-fill me-1"></i>
                                        Ya tenés el Plan Pro
                                    </span>

                                <?php }else{ ?>

                                    <h4 class="fw-bold mb-3">
                                        ¡Convertite en Pro!
                                    </h4>

                                    <a href="pagar_plan.php" class="btn btn-warning btn-lg px-5 fw-bold">
                                        <i class="bi bi-star-fill me-2"></i>
                                        Contratar Plan Pro
                                    </a>

                                <?php } ?>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- COMPARACIÓN -->
            <div class="mb-5">

                <h2 class="fw-bold text-center mb-4">
                    Compará nuestros planes
                </h2>

                <div class="table-responsive">

                    <table class="table table-bordered table-hover bg-white text-center align-middle shadow-sm">

                        <thead class="table-dark">
                            <tr>
                                <th>Características</th>
                                <th>Gratuito</th>
                                <th class="text-warning">Pro <i class="bi bi-star-fill"></i></th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td class="text-start">Cursos activos creados</td>
                                <td>3</td>
                                <td class="fw-bold">Ilimitados</td>
                            </tr>

                            <tr>
                                <td class="text-start">Archivos por curso</td>
                                <td>5</td>
                                <td class="fw-bold">Ilimitados</td>
                            </tr>

                            <tr>
                                <td class="text-start">Tamaño por archivo</td>
                                <td>5 MB</td>
                                <td class="fw-bold">Ilimitado</td>
                            </tr>

                            <tr>
                                <td class="text-start">Almacenamiento total</td>
                                <td>10 MB</td>
                                <td class="fw-bold">Ilimitado</td>
                            </tr>

                            <tr>
                                <td class="text-start">Inscripciones activas</td>
                                <td>3</td>
                                <td class="fw-bold">Ilimitadas</td>
                            </tr>

                            <tr>
                                <td class="text-start">Cursos destacados</td>
                                <td><i class="bi bi-x-circle text-danger"></i></td>
                                <td><i class="bi bi-check-circle-fill text-success"></i></td>
                            </tr>
                        </tbody>

                    </table>

                </div>

            </div>


            <!-- CONTRATACIÓN -->
            <div id="contratar" class="text-center py-4">

                <h2 class="fw-bold">
                    ¿Listo para dar el siguiente paso?
                </h2>

                <p class="text-secondary">
                    Elegí el Plan Pro y disfrutá de todas sus ventajas.
                </p>

                <?php if(!$esPro){ ?>

                    <p class="text-muted small">
                        Próximamente vas a poder completar tu contratación desde aquí.
                    </p>

                <?php } ?>

            </div>

        </div>

    </main>


    <!-- FOOTER -->
    <footer class="py-4 text-center text-white" style="background-color: #0E1B33;">
        <div class="container">
            <p class="mb-0">
                &copy; 2026 Cursolandia. Todos los derechos reservados.
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
