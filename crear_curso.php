<?php
session_start();
include "Persistencia/Conexion.php";
$errtitulo="";
$errdesc="";
$errinicio="";
$errfin="";
$errcosto="";
if(!isset($_SESSION['Usuario'])){
header("location:login.php");
exit();
}
$error = false;
$errtitulo = $errdesc = $errinicio = $errfin = $errcosto = $errcupo = $errpublico = $errlimite = "";
$error = false;
$titulo = $descripcion = $fecha_inicio = $fecha_fin = $costo = $cupo = $etiquetas ="";
$publico = "si";

if(isset($_POST['crear'])){
    $titulo       = trim($_POST['titulo'] ?? "");
    $descripcion  = trim($_POST['descripcion'] ?? "");
    $fecha_inicio = trim($_POST['fecha_inicio'] ?? "");
    $fecha_fin    = trim($_POST['fecha_fin'] ?? "");
    $costo        = trim($_POST['costo'] ?? "");
    $cupo         = trim($_POST['cupo'] ?? "");
    $etiquetas    = trim($_POST['etiquetas'] ?? "");
    $publico      = $_POST['publico'] ?? "";

 if($titulo == ""){
        $errtitulo = "Ingresá el título del curso.";
        $error = true;
    }elseif(mb_strlen($titulo) < 3 || mb_strlen($titulo) > 100 ){
        $errtitulo = "Título incorrecto.";
        $error = true;
    }
    if($descripcion == ""){
        $errdesc = "Ingresá la descripción del curso.";
        $error = true;
    }elseif(mb_strlen($descripcion) < 8){
        $errdesc = "La descripción debe tener al menos 8 caracteres.";
        $error = true;
    }   
     if($fecha_inicio == ""){
        $errinicio = "Ingresá la fecha de inicio.";
        $error = true;
    }else{
        $partes = explode("-", $fecha_inicio);
        if(count($partes) != 3 || !checkdate((int)$partes[1], (int)$partes[2], (int)$partes[0])){
            $errinicio = "La fecha de inicio no es válida.";
            $error = true;
        }
    }

    if($fecha_fin == ""){
        $errfin = "Ingresá la fecha de finalizacion.";
        $error = true;
    }else{
        $partes = explode("-", $fecha_fin);
        if(count($partes) != 3 || !checkdate((int)$partes[1], (int)$partes[2], (int)$partes[0])){
            $errfin = "La fecha de fin no es válida.";
            $error = true;
        }elseif($errinicio == "" && $fecha_fin < $fecha_inicio){
            $errfin = "La fecha de finalizacion no puede ser anterior a la de inicio.";
            $error = true;
        }
    }

    if($costo == ""){
        $costo = 0;
    }elseif(!is_numeric($costo) || $costo < 0){
        $errcosto = "El costo incorrecto.";
        $error = true;
    }

    if($cupo == ""){
        $cupo = 0;
    }elseif(filter_var($cupo, FILTER_VALIDATE_INT) === false || $cupo < 0){
        $errcupo = "Catidad  de cupos incorrecta.";
        $error = true;
    }
    if($publico != "si" && $publico != "no"){
        $errpublico = "Elegí si el curso es público o privado.";
        $error = true;
    }
    if($error == false){
        $conexion = conectar();
        $idcreador = (int)$_SESSION['Usuario']['ID'];
        if($_SESSION['Usuario']['Plan'] != 'pro'){
            $res = mysqli_query($conexion, "SELECT COUNT(*) AS total FROM cursos WHERE ID_Creador = $idcreador AND Fecha_Fin >= CURDATE()");
            $fila = mysqli_fetch_assoc($res);
        if($fila['total'] >= 3){
            $errlimite = "Con el plan gratuito solo podés tener hasta 3 cursos activos.";
            $error = true;
            }
        }
        if(!$error){
        $publicoSeguro = mysqli_real_escape_string($conexion, $publico);
        $tituloSeguro = mysqli_real_escape_string($conexion, $titulo);
        $descSegura = mysqli_real_escape_string($conexion, $descripcion);
        $inicioSeguro = mysqli_real_escape_string($conexion, $fecha_inicio);
        $finSeguro = mysqli_real_escape_string($conexion, $fecha_fin);
        $costoSeguro = (float)$costo;
        $cupoSeguro = (int)$cupo;

        mysqli_query($conexion, "INSERT INTO cursos (Titulo, Descripcion, Costo, Cupo, Fecha_Inicio, Fecha_Fin, ID_Creador, Publico)
        VALUES ('$tituloSeguro', '$descSegura', $costoSeguro, $cupoSeguro, '$inicioSeguro', '$finSeguro', $idcreador, '$publicoSeguro')");

        $idcurso = mysqli_insert_id($conexion);
        mysqli_close($conexion);

        // aca irian las etiquetas

        header("Location: index.php");
        exit();
        }
    }
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
                            <?php if($errlimite != ""){ ?>
                                <div class="col-12"><div class="alert alert-danger"><?php echo $errlimite; ?></div></div>
                            <?php } ?>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="titulo" class="form-label">Título *</label>
                                    <input type="text" class="form-control <?php if($errtitulo != "") echo 'is-invalid'; ?>" id="titulo" name="titulo" maxlength="100" value="<?php echo htmlspecialchars($titulo); ?>">
                                    <div class="invalid-feedback"><?php echo $errtitulo; ?></div>
                                </div>

                                <div class="col-12">
                                    <label for="descripcion" class="form-label">Descripción *</label>
                                    <textarea class="form-control <?php if($errdesc != "") echo 'is-invalid'; ?>" id="descripcion" name="descripcion" rows="4"><?php echo htmlspecialchars($descripcion); ?></textarea>
                                    <div class="invalid-feedback"><?php echo $errdesc; ?></div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="fecha_inicio" class="form-label">Fecha de inicio *</label>
                                    <input type="date" class="form-control <?php if($errin != "") echo 'is-invalid'; ?>" id="fecha_inicio" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>"required>
                                    <div class="invalid-feedback"><?php echo $errin; ?></div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="fecha_fin" class="form-label">Fecha de finalización *</label>
                                    <input type="date" class="form-control <?php if($errfin != "") echo 'is-invalid'; ?>" id="fecha_fin" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin); ?>" required>
                                    <div class="invalid-feedback"><?php echo $errfin; ?></div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="costo" class="form-label">Costo <span class="text-secondary"></span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control <?php if($errcosto != "") echo 'is-invalid'; ?>" id="costo" name="costo" min="0" step="0.01" value="<?php echo htmlspecialchars($costo); ?>">
                                        <div class="invalid-feedback"><?php echo $errcosto; ?></div>
                                    </div>
                                    
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="cupo" class="form-label">Cupo <span class="text-secondary"></span></label>
                                    <input type="number" class="form-control <?php if($errcupo != "") echo 'is-invalid'; ?>" id="cupo" name="cupo" min="0" value="<?php echo htmlspecialchars($cupo); ?>">
                                    <div class="invalid-feedback"><?php echo $errcupo; ?></div>
                                    
                                </div>

                                <div class="col-12">
                                    <label class="form-label d-block">Acceso *</label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="publico" id="acceso_publico" value="si" <?php if($publico != "no") echo 'checked'; ?>>
                                        <label class="form-check-label" for="acceso_publico">Público</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="publico" id="acceso_privado" value="no" <?php if($publico == "no") echo 'checked'; ?>>
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