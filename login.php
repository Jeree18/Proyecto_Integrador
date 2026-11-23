<?php
session_start();
include "Persistencia/Conexion.php";
$mail="";
$clave="";
$mjecorreo="";
$mjeclave="";
$errorlogin="";
$error=false;

if(isset($_POST['Ingresar'])){
    extract($_POST);
    $mail = trim($mail);

    if(!filter_var($mail, FILTER_VALIDATE_EMAIL)){
        $mjecorreo = "Ingresá un correo válido.";
        $error = true;
    }

    if($clave == ""){
        $mjeclave = "Ingresá tu contraseña.";
        $error = true;
    }

    if($error == false){
        $conexion = conectar();
        $mailSeguro = mysqli_real_escape_string($conexion, $mail);

        $resultado = mysqli_query($conexion, "SELECT * FROM usuarios WHERE Mail = '$mailSeguro'");
        $fila = mysqli_fetch_assoc($resultado);
        mysqli_close($conexion);

        if($fila && password_verify($clave, $fila['Contraseña'])){
        unset($fila['Contraseña']);
        $_SESSION["Usuario"] = $fila;

        if($_SESSION['Usuario']['Rol'] == 'administrador'){
            header("Location: admin.php");
            exit();
            }else{
                header("Location: index.php");
                exit();
            }

        }else{
            $errorlogin = "Correo o contraseña incorrectos.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión | Cursolandia</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="Estilos/estilos.css" rel="stylesheet">
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
        <a href="registro.php" class="btn btn-warning">Registrarse</a>
      </div>
    </div>
  </div>
</header>

<main class="bg-soft flex-grow-1 py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6 col-xl-5">

                <div class="card card-cl shadow-sm">
                    <div class="card-body p-4 p-md-5">

                        <div class="text-center mb-4">
                            <span class="icon-circle mb-3"><i class="bi bi-box-arrow-in-right"></i></span>
                            <h1 class="h3 mb-1">Iniciar sesión</h1>
                            <p class="text-secondary mb-0">Ingresá con tu correo y contraseña.</p>
                        </div>

                        <?php if($errorlogin != ""){ ?>
                            <div class="text-danger mb-2"><?php echo $errorlogin; ?></div>
                        <?php } ?>

                        <form action="login.php" method="post" novalidate>
                            <div class="row g-3">

                                <div class="col-12">
                                    <label for="mail" class="form-label">Correo electrónico</label>
                                    <input type="email" value="<?php echo htmlspecialchars($mail) ?>" class="form-control" id="mail" name="mail" maxlength="100" required>
                                    <?php
                                        if(!empty($mjecorreo)){ echo "<br><font color='red'>$mjecorreo</font>"; }
                                    ?>
                                </div>

                                <div class="col-12">
                                    <label for="clave" class="form-label">Contraseña</label>
                                    <input type="password" class="form-control" id="clave" name="clave" required>
                                    <?php
                                        if(!empty($mjeclave)){ echo "<br><font color='red'>$mjeclave</font>"; }
                                    ?>
                                </div>

                                <div class="col-12 d-grid mt-4">
                                    <button type="submit" class="btn btn-warning btn-lg" name="Ingresar">Ingresar</button>
                                </div>

                            </div>
                        </form>

                        <p class="text-center mt-4 mb-0">
                            ¿No tenés cuenta? <a href="registro.php" class="text-gold fw-semibold">Registrate</a>
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