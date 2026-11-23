
<?php
session_start();
include "Persistencia/Conexion.php";

if(!isset($_SESSION['Usuario'])){
    header("Location: login.php");
    exit();
}

if($_SESSION['Usuario']['Plan'] == 'pro'){
    header("Location: contratar_pro.php");
    exit();
}

$precio = "5000";
$alias = "CURSOLANDIA.PRO";
$titular = "NOMBRE DEL TITULAR";

$mensaje = "";
$tipoMensaje = "";

if(isset($_POST['enviar_comprobante'])){

    $nombreTitular = trim($_POST['titular_transferencia']);
    $fechaTransferencia = $_POST['fecha_transferencia'];

    if($nombreTitular == "" || $fechaTransferencia == "" || !isset($_FILES['comprobante'])){
        $mensaje = "Completá todos los campos.";
        $tipoMensaje = "danger";
    }elseif($_FILES['comprobante']['error'] != 0){
        $mensaje = "Hubo un problema al subir el comprobante.";
        $tipoMensaje = "danger";
    }else{

        $extension = strtolower(pathinfo($_FILES['comprobante']['name'], PATHINFO_EXTENSION));

        if(!in_array($extension, ['jpg', 'jpeg', 'png', 'pdf'])){
            $mensaje = "El comprobante debe ser JPG, PNG o PDF.";
            $tipoMensaje = "danger";
        }elseif($_FILES['comprobante']['size'] > 5 * 1024 * 1024){
            $mensaje = "El comprobante no puede superar los 5 MB.";
            $tipoMensaje = "danger";
        }else{

            $carpeta = "comprobantes/";

            if(!is_dir($carpeta)){
                mkdir($carpeta, 0775, true);
            }

            $nombreArchivo = "comprobante_" . $_SESSION['Usuario']['ID'] . "_" . time() . "." . $extension;
            $destino = $carpeta . $nombreArchivo;

            if(move_uploaded_file($_FILES['comprobante']['tmp_name'], $destino)){

                $conexion = conectar();

                $idUsuario = (int)$_SESSION['Usuario']['ID'];
                $nombreSeguro = mysqli_real_escape_string($conexion, $nombreTitular);
                $fechaSegura = mysqli_real_escape_string($conexion, $fechaTransferencia);
                $archivoSeguro = mysqli_real_escape_string($conexion, $nombreArchivo);

                // Esta consulta supone que ya existe la tabla solicitudes_pro
                $sql = "INSERT INTO solicitudpro (ID_usuario, comprobante, Fecha_emision, Estado) VALUES ($idUsuario, '$archivoSeguro', CURDATE(), 'Pendiente')";

                if(mysqli_query($conexion, $sql)){
                    $mensaje = "¡Tu comprobante fue enviado correctamente! Tu solicitud quedó pendiente de revisión.";
                    $tipoMensaje = "success";
                }else{
                    unlink($destino);
                    $mensaje = "No se pudo registrar tu solicitud. Intentá nuevamente.";
                    $tipoMensaje = "danger";
                }

                mysqli_close($conexion);

            }else{
                $mensaje = "No se pudo guardar el comprobante.";
                $tipoMensaje = "danger";
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Contratar Plan Pro - Cursolandia</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="Estilos/estilos.css?v=2" rel="stylesheet">
</head>

<body class="d-flex flex-column min-vh-100">

<!-- HEADER -->
<header class="py-2 text-bg-dark sticky-top">
    <div class="container-fluid px-3">

        <div class="d-flex flex-wrap align-items-center justify-content-between">

            <a href="index.php" class="d-flex align-items-center text-white text-decoration-none">
                <i class="bi bi-rocket-takeoff-fill text-warning fs-3 me-2"></i>
                <span class="fs-5 fw-semibold">Cursolandia</span>
            </a>

            <div class="d-flex align-items-center">

                <?php if($_SESSION['Usuario']['Foto'] != ""){ ?>

                    <img src="imagenes/<?php echo htmlspecialchars($_SESSION['Usuario']['Foto']); ?>"
                         class="rounded-circle"
                         width="40"
                         height="40"
                         style="object-fit: cover;">

                <?php }else{ ?>

                    <i class="bi bi-person-circle text-white fs-2"></i>

                <?php } ?>

                <span class="text-white mx-2">
                    <?php
                    echo htmlspecialchars($_SESSION['Usuario']['Nombre'])." ".
                         htmlspecialchars($_SESSION['Usuario']['Apellido']);
                    ?>
                </span>

                <a href="Mis_cursos.php" class="btn btn-warning btn-sm me-2">
                    Mis cursos
                </a>

                <a href="cerrar_sesion.php" class="btn btn-outline-light btn-sm">
                    Cerrar sesión
                </a>

            </div>

        </div>

    </div>
</header>


<!-- CONTENIDO -->
<main class="flex-grow-1 bg-soft py-5">

    <div class="container">

        <div class="text-center mb-5">

            <span class="badge bg-warning text-dark px-3 py-2 mb-3">
                <i class="bi bi-star-fill me-1"></i>
                Cursolandia Pro
            </span>

            <h1 class="fw-bold display-5">
                Completá tu contratación
            </h1>

            <p class="text-secondary fs-5">
                Realizá la transferencia y enviá tu comprobante para activar tu plan.
            </p>

        </div>


        <div class="row justify-content-center g-4">

            <!-- DATOS DE PAGO -->
            <div class="col-lg-5">

                <div class="card card-cl shadow-sm h-100">

                    <div class="card-body p-4">

                        <h3 class="fw-bold mb-4">
                            <i class="bi bi-bank text-warning me-2"></i>
                            Datos de transferencia
                        </h3>

                        <div class="text-center bg-dark text-white rounded-3 p-4 mb-4">

                            <i class="bi bi-star-fill text-warning display-4"></i>

                            <h4 class="fw-bold mt-3">
                                Plan Pro
                            </h4>

                            <p class="text-white-50 mb-1">
                                Suscripción al plan
                            </p>

                            <h2 class="fw-bold text-warning">
                                $<?php echo number_format((float)$precio, 0, ',', '.'); ?>
                            </h2>

                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary small">
                                ALIAS
                            </label>

                            <div class="input-group">
                                <input type="text" class="form-control fw-bold"
                                       value="<?php echo htmlspecialchars($alias); ?>" readonly>

                                <button class="btn btn-warning" type="button"
                                        onclick="copiarAlias()">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary small">
                                TITULAR
                            </label>
                            <?php echo "<strong>".htmlspecialchars($titular)."</strong>" ?>
                             
                        </div>

                        <div class="alert alert-info small mb-0">
                            <i class="bi bi-info-circle-fill me-1"></i>
                            Realizá la transferencia por el importe indicado y guardá el comprobante.
                        </div>

                    </div>

                </div>

            </div>


            <!-- FORMULARIO -->
            <div class="col-lg-5">

                <div class="card card-cl shadow-sm h-100">

                    <div class="card-body p-4">

                        <h3 class="fw-bold mb-3">
                            <i class="bi bi-receipt text-warning me-2"></i>
                            Enviar comprobante
                        </h3>

                        <p class="text-secondary">
                            Una vez realizada la transferencia, completá los siguientes datos.
                        </p>
                        <?php if($mensaje != ""){ ?>
                        <div class="alert alert-<?php echo $tipoMensaje; ?>">
                            <?php echo htmlspecialchars($mensaje); ?>
                        </div>
                        <?php } ?>
                        <form  method="POST" enctype="multipart/form-data">

                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    Nombre del titular de la transferencia
                                </label>

                                <input type="text" name="titular_transferencia"
                                       class="form-control" required
                                       placeholder="Nombre y apellido">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    Fecha de transferencia
                                </label>

                                <input type="date" name="fecha_transferencia"
                                       class="form-control" required
                                       max="<?php echo date('Y-m-d'); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    Comprobante de pago
                                </label>

                                <input type="file" name="comprobante"
                                       class="form-control" required
                                       accept=".jpg,.jpeg,.png,.pdf">

                                <small class="text-secondary">
                                    Formatos permitidos: JPG, PNG o PDF.
                                </small>
                            </div>

                            <div class="alert alert-warning small">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                Tu solicitud quedará pendiente hasta que se verifique el comprobante.
                            </div>

                            <button type="submit" name="enviar_comprobante" class="btn btn-warning btn-lg w-100 fw-bold">
                                <i class="bi bi-send-fill me-2"></i>
                                Enviar comprobante
                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </div>


        <div class="text-center mt-4">

            <a href="contratar_pro.php" class="btn btn-outline-dark">
                <i class="bi bi-arrow-left me-1"></i>
                Volver al Plan Pro
            </a>

        </div>

    </div>

</main>


<!-- FOOTER -->
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


<script>

function copiarAlias(){

    let alias = "<?php echo htmlspecialchars($alias, ENT_QUOTES); ?>";

    navigator.clipboard.writeText(alias);

    alert("Alias copiado correctamente.");
}

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
