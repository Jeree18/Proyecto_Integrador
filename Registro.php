<?php
session_start();
include "Persistencia/Conexion.php";
$errorcarga="";$nombre="";
$apellido="";
$mail="";
$clave="";
$clave2="";
$dni="";
$nacimiento="";
$telefono="";
$intereses="";
$antecedentes="";
$mjenombre="";
$mjeapellido="";
$mjecorreo="";
$mjeclave="";
$mjeclave2="";
$mjedni="";
$mjefechanac="";
$mjetelefono="";
$mjefoto="";
$error=false;
if(isset($_POST['Registrar'])){
    extract($_POST);
    $nombre       = trim($nombre);
    $apellido     = trim($apellido);
    $mail         = trim($mail);
    $dni          = trim($dni);
    $telefono     = trim($telefono);
    $intereses    = trim($intereses);
    $antecedentes = trim($antecedentes);

    if( strlen($nombre) < 2 || strlen($nombre) > 50){
        $mjenombre = "El nombre debe tener entre 2 y 50 caracteres.";
        $error = true;
    }

    if( strlen($apellido) < 2 || strlen($apellido) > 50){
        $mjeapellido = "El apellido debe tener entre 2 y 50 caracteres.";
        $error = true;
    }

    if(!filter_var($mail, FILTER_VALIDATE_EMAIL) || strlen($mail) > 100){
        $mjecorreo = "Ingresá un correo válido.";
        $error = true;
    }

    if(strlen($clave) < 8 || strlen($clave) > 72){
        $mjeclave = "La contraseña debe tener entre 8 y 72 caracteres.";
        $error = true;
    }

    if($clave2 !== $clave){
        $mjeclave2 = "Las contraseñas no coinciden.";
        $error = true;
    }

    if(!ctype_digit($dni) || strlen($dni) < 7 || strlen($dni) > 8){
        $mjedni = "El DNI debe tener entre 7 y 8 números.";
        $error = true;
    }

    if($nacimiento == "" || $nacimiento > date("Y-m-d")){
        $mjefechanac = "Ingresá una fecha de nacimiento válida.";
        $error = true;
    }

    if($telefono !== "" && (!ctype_digit($telefono) || strlen($telefono) < 8 || strlen($telefono) > 20)){
        $mjetelefono = "El teléfono debe tener entre 8 y 20 números.";
        $error = true;
    }

    $errorFoto = $_FILES['foto']['error'];
    $tipoFoto = $_FILES['foto']['type'];
    if($errorFoto !== UPLOAD_ERR_NO_FILE){
        if($errorFoto !== UPLOAD_ERR_OK){
            $mjefoto = "No se pudo subir la foto. Probá con otra.";
            $error = true;
        } elseif($_FILES['foto']['size'] > 2 * 1024 * 1024){
            $mjefoto = "La foto no puede pesar más de 2 MB.";
            $error = true;
        } elseif($tipoFoto != "image/jpeg" && $tipoFoto != "image/png" && $tipoFoto != "image/webp"){
            $mjefoto = "La foto debe ser JPG, PNG o WEBP.";
            $error = true;
        }
    }
        if($error === false){
        $conexion = conectar();
        $mail = mysqli_real_escape_string($conexion, $mail);
        $dni = mysqli_real_escape_string($conexion, $dni);

        $resultado = mysqli_query($conexion, "SELECT Mail FROM usuarios WHERE Mail = '$mail'");
        if(mysqli_num_rows($resultado) > 0){
            $mjecorreo = "Este correo ya está registrado.";
            $error = true;
        }

        $resultado = mysqli_query($conexion, "SELECT Dni FROM usuarios WHERE Dni = '$dni'");
        if(mysqli_num_rows($resultado) > 0){
            $mjedni = "Este dni ya se encuentra registrado.";
            $error = true;
        }

        if($error === true){
            mysqli_close($conexion);
        }
        if($error === false){
            $claveHash = password_hash($clave, PASSWORD_DEFAULT);

            $nombreFoto = "";
            if($_FILES['foto']['error'] === UPLOAD_ERR_OK){
                $extensiones = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
                $tipo = mime_content_type($_FILES['foto']['tmp_name']);
                $nombreFoto = uniqid() . "." . $extensiones[$tipo];
            }

            $nombre = mysqli_real_escape_string($conexion, $nombre);
            $apellido = mysqli_real_escape_string($conexion, $apellido);
            $telefono = mysqli_real_escape_string($conexion, $telefono);
            $intereses = mysqli_real_escape_string($conexion, $intereses);
            $antecedentes = mysqli_real_escape_string($conexion, $antecedentes);
            $nacimiento = mysqli_real_escape_string($conexion, $nacimiento);

            $sql = "INSERT INTO usuarios(Nombre, Apellido, Mail, `Contraseña`, Dni, Nacimiento, Telefono, Foto, Intereses, Antecedentes, Rol, Plan)
                    VALUES('$nombre', '$apellido', '$mail', '$claveHash', '$dni', '$nacimiento', '$telefono', '$nombreFoto', '$intereses', '$antecedentes', 'usuario', 'gratuito')";

            try{
                mysqli_query($conexion, $sql);
                if($nombreFoto !== ""){
                    move_uploaded_file($_FILES['foto']['tmp_name'], "imagenes/" . $nombreFoto);
                }
                mysqli_close($conexion);
                header("Location: login.php");
                exit();
            }catch(mysqli_sql_exception $e){
                mysqli_close($conexion);
                $errorcarga = "Error al cargar usuario, intentelo de nuevo.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro | Cursolandia</title>

    <!-- Mismos recursos que el index (ajustá rutas/versiones si las tuyas son otras) -->
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
        <a href="login.php" class="btn btn-outline-light me-2">Iniciar sesión</a>
      </div>
    </div>
  </div>
</header>

<main class="bg-soft flex-grow-1 py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8 col-xl-7">

                <div class="card card-cl shadow-sm">
                    <div class="card-body p-4 p-md-5">

                        <div class="text-center mb-4">
                            <span class="icon-circle mb-3"><i class="bi bi-person-plus-fill"></i></span>
                            <h1 class="h3 mb-1">Crear cuenta</h1>
                            <p class="text-secondary mb-0">Los campos con * son obligatorios.</p>
                        </div>
                        <?php if($errorcarga !== ""){ ?>
                                        <div class="text-danger mb-2"><?php echo $errorcarga; ?></div>
                        <?php } ?>

                        <!-- Acá imprimí los errores de tu validación, por ejemplo:
                             <div class="alert alert-danger" role="alert"> ... </div> -->

                        <form action="registro.php" method="post" enctype="multipart/form-data" novalidate>
                            <div class="row g-3">

                                <div class="col-12 col-md-6">
                                    <label for="nombre" class="form-label">Nombre *</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" maxlength="50"  value="<?php echo htmlspecialchars($nombre)?>" required>
                                    <?php 
                                        if(empty(!$mjenombre)){ echo "<br><font color='red'>$mjenombre</font>";}                                             
                                    ?>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="apellido" class="form-label">Apellido *</label>
                                    <input type="text"  value="<?php echo htmlspecialchars($apellido)?>" class="form-control" id="apellido" name="apellido" maxlength="50" required>
                                    <?php 
                                        if(empty(!$mjeapellido)){ echo "<br><font color='red'>$mjeapellido</font>";}                                             
                                    ?>
                                </div>

                                <div class="col-12">
                                    <label for="mail" class="form-label">Correo electrónico *</label>
                                    <input type="email"  value="<?php echo htmlspecialchars($mail)?>" class="form-control" id="mail" name="mail" maxlength="100" required>
                                    <?php 
                                        if(empty(!$mjecorreo)){ echo "<br><font color='red'>$mjecorreo</font>";}                                             
                                    ?>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="clave" class="form-label">Contraseña *</label>
                                    <input type="password"  value="<?php echo htmlspecialchars($clave)?>"class="form-control" id="clave" name="clave" required>
                                    <?php 
                                        if(empty(!$mjeclave)){ echo "<br><font color='red'>$mjeclave</font>";}                                             
                                    ?>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="clave2" class="form-label">Repetir contraseña *</label>
                                    <input type="password"  value="<?php echo htmlspecialchars($clave2)?>" class="form-control" id="clave2" name="clave2" required>
                                    <?php 
                                        if(empty(!$mjeclave2)){ echo "<br><font color='red'>$mjeclave2</font>";}                                             
                                    ?>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="dni" class="form-label">DNI *</label>
                                    <input type="text"  value="<?php echo htmlspecialchars($dni)?>" class="form-control" id="dni" name="dni" maxlength="10" inputmode="numeric" required>
                                    <?php 
                                        if(empty(!$mjedni)){ echo "<br><font color='red'>$mjedni</font>";}                                             
                                    ?>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="nacimiento" class="form-label">Fecha de nacimiento *</label>
                                    <input type="date"  value="<?php echo htmlspecialchars($nacimiento)?>" class="form-control" id="nacimiento" name="nacimiento" required>
                                    <?php 
                                        if(empty(!$mjefechanac)){ echo "<br><font color='red'>$mjefechanac</font>";}                                             
                                    ?>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="telefono" class="form-label">Teléfono</label>
                                    <input type="tel"  value="<?php echo htmlspecialchars($telefono)?>" class="form-control" id="telefono" name="telefono" maxlength="20">
                                    <?php 
                                        if(empty(!$mjetelefono)){ echo "<br><font color='red'>$mjetelefono</font>";}                                             
                                    ?>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="foto" class="form-label">Foto de perfil</label>
                                    <input type="file"  class="form-control" id="foto" name="foto" accept="image/*">
                                    <?php 
                                        if(empty(!$mjefoto)){ echo "<br><font color='red'>$mjefoto</font>";}                                             
                                    ?>
                                </div>

                                <div class="col-12">
                                    <label for="intereses" class="form-label">Intereses</label>
   
                                    <textarea class="form-control" id="intereses" name="intereses" rows="2" style="resize: none;"><?php echo htmlspecialchars($intereses); ?></textarea>
                                </div>

                                <div class="col-12">
                                    <label for="antecedentes" class="form-label">Antecedentes</label>
                                    <textarea class="form-control" id="antecedentes" name="antecedentes" rows="2" style="resize: none;"><?php echo htmlspecialchars($antecedentes); ?></textarea>
                                </div>

                                <div class="col-12 d-grid mt-4">                                                 
                                    <button type="submit" class="btn btn-warning btn-lg" name="Registrar">Crear cuenta</button>
                                </div>
                            
                            </div>
                        </form>

                        <p class="text-center mt-4 mb-0">
                            ¿Ya tenés cuenta? <a href="login.php" class="text-gold fw-semibold">Iniciá sesión</a>
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
          <li class="mb-2"><i class="bi bi-envelope me-2"></i>Jeremias01@gmail.com</li>
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