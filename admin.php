
<?php
session_start();
include "Persistencia/Conexion.php";

if(!isset($_SESSION['Usuario'])){
    header("Location: login.php");
    exit();
}

if($_SESSION['Usuario']['Rol'] != 'administrador'){
    echo "No tenés permisos para acceder a esta página.";
    exit();
}

$conexion = conectar();

$mensaje = "";
$tipoMensaje = "";

// Procesar aceptación o rechazo
if(isset($_POST['accion']) && isset($_POST['id_solicitud'])){

    $idSolicitud = (int)$_POST['id_solicitud'];
    $idAdmin = (int)$_SESSION['Usuario']['ID'];
    $accion = $_POST['accion'];

    // Buscar la solicitud y comprobar que siga pendiente
    $sqlBuscar = "SELECT ID_usuario
                  FROM solicitudpro
                  WHERE ID_solicitud = $idSolicitud
                  AND Estado = 'Pendiente'";

    $resultadoBuscar = mysqli_query($conexion, $sqlBuscar);

    if(mysqli_num_rows($resultadoBuscar) > 0){

        $solicitud = mysqli_fetch_assoc($resultadoBuscar);
        $idUsuario = (int)$solicitud['ID_usuario'];

        if($accion == "aceptar"){

            mysqli_begin_transaction($conexion);

            $sqlSolicitud = "UPDATE solicitudpro
                             SET Estado = 'Aprobada',
                                 ID_admin = $idAdmin,
                                 Fecha_vencimiento = DATE_ADD(CURDATE(), INTERVAL 1 MONTH)
                             WHERE ID_solicitud = $idSolicitud";

            $sqlUsuario = "UPDATE usuarios
                           SET Plan = 'pro'
                           WHERE ID = $idUsuario";

            if(mysqli_query($conexion, $sqlSolicitud) &&
               mysqli_query($conexion, $sqlUsuario)){

                mysqli_commit($conexion);
                $mensaje = "La solicitud fue aprobada. El usuario ya tiene el Plan Pro.";
                $tipoMensaje = "success";

            }else{

                mysqli_rollback($conexion);
                $mensaje = "No se pudo aprobar la solicitud.";
                $tipoMensaje = "danger";
            }

        }elseif($accion == "rechazar"){

            $sql = "UPDATE solicitudpro
                    SET Estado = 'Rechazada',
                        ID_admin = $idAdmin
                    WHERE ID_solicitud = $idSolicitud";

            if(mysqli_query($conexion, $sql)){
                $mensaje = "La solicitud fue rechazada.";
                $tipoMensaje = "success";
            }else{
                $mensaje = "No se pudo rechazar la solicitud.";
                $tipoMensaje = "danger";
            }
        }

    }else{
        $mensaje = "La solicitud ya fue revisada o no existe.";
        $tipoMensaje = "warning";
    }
}

// Obtener solicitudes pendientes y datos de sus solicitantes
$sql = "SELECT s.ID_solicitud, s.comprobante, s.Fecha_emision,
               s.Estado, u.ID, u.Nombre, u.Apellido, u.Mail,
               u.Telefono, u.Foto, u.DNI
        FROM solicitudpro s
        INNER JOIN usuarios u ON s.ID_usuario = u.ID
        WHERE s.Estado = 'Pendiente'
        ORDER BY s.Fecha_emision ASC";

$solicitudes = mysqli_query($conexion, $sql);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Solicitudes Pro - Cursolandia</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="Estilos/estilos.css?v=2" rel="stylesheet">
</head>

<body class="bg-soft">

<header class="py-3 text-bg-dark">
    <div class="container d-flex justify-content-between align-items-center">

        <a href="index.php" class="text-white text-decoration-none fs-5 fw-semibold">
            <i class="bi bi-rocket-takeoff-fill text-warning me-2"></i>
            Cursolandia
        </a>

        <a href="index.php" class="btn btn-outline-light btn-sm">
            <i class="bi bi-house me-1"></i> Volver al inicio
        </a>

    </div>
</header>

<main class="container py-5">

    <div class="mb-4">
        <h2 class="fw-bold">Solicitudes de Plan Pro</h2>
        <p class="text-secondary">
            Revisá los comprobantes y administrá las solicitudes de los usuarios.
        </p>
    </div>

    <?php if($mensaje != ""){ ?>
        <div class="alert alert-<?php echo $tipoMensaje; ?>">
            <?php echo htmlspecialchars($mensaje); ?>
        </div>
    <?php } ?>

    <?php if(mysqli_num_rows($solicitudes) > 0){ ?>

        <div class="row g-4">

            <?php while($fila = mysqli_fetch_assoc($solicitudes)){ ?>

                <div class="col-12">

                    <div class="card card-cl shadow-sm">

                        <div class="card-body p-4">

                            <div class="row g-4">

                                <!-- Datos del solicitante -->
                                <div class="col-md-7">

                                    <h5 class="fw-bold mb-3">
                                        <i class="bi bi-person-circle text-gold me-2"></i>
                                        Datos del solicitante
                                    </h5>

                                    <div class="d-flex align-items-center mb-3">

                                        <?php if($fila['Foto'] != ""){ ?>

                                            <img src="imagenes/<?php echo htmlspecialchars($fila['Foto']); ?>"
                                                 width="65" height="65"
                                                 class="rounded-circle me-3"
                                                 style="object-fit:cover;">

                                        <?php }else{ ?>

                                            <i class="bi bi-person-circle fs-1 text-secondary me-3"></i>

                                        <?php } ?>

                                        <div>
                                            <h6 class="mb-1 fw-bold">
                                                <?php echo htmlspecialchars($fila['Nombre']." ".$fila['Apellido']); ?>
                                            </h6>

                                            <span class="badge bg-warning text-dark">
                                                Solicitud pendiente
                                            </span>
                                        </div>

                                    </div>

                                    <p class="mb-2">
                                        <strong>Correo:</strong>
                                        <?php echo htmlspecialchars($fila['Mail']); ?>
                                    </p>

                                    <p class="mb-2">
                                        <strong>Teléfono:</strong>
                                        <?php echo htmlspecialchars($fila['Telefono'] ?? "No registrado"); ?>
                                    </p>

                                    <p class="mb-2">
                                        <strong>DNI:</strong>
                                        <?php echo htmlspecialchars($fila['DNI']); ?>
                                    </p>

                                    <p class="mb-2">
                                        <strong>Fecha de solicitud:</strong>
                                        <?php echo date("d/m/Y", strtotime($fila['Fecha_emision'])); ?>
                                    </p>

                                    <p class="mb-0">
                                        <strong>Número de solicitud:</strong>
                                        <?php echo $fila['ID_solicitud']; ?>
                                    </p>

                                </div>

                                <!-- Comprobante -->
                                <div class="col-md-5">

                                    <h5 class="fw-bold mb-3">
                                        <i class="bi bi-file-earmark-check text-gold me-2"></i>
                                        Comprobante de pago
                                    </h5>

                                    <?php
                                    $archivo = $fila['comprobante'];
                                    $extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
                                    $ruta = "comprobantes/" . rawurlencode($archivo);
                                    ?>

                                    <?php if(in_array($extension, ['jpg', 'jpeg', 'png'])){ ?>

                                        <a href="<?php echo htmlspecialchars($ruta); ?>" target="_blank">
                                            <img src="<?php echo htmlspecialchars($ruta); ?>"
                                                 class="img-fluid rounded border"
                                                 style="max-height:250px; width:100%; object-fit:contain;">
                                        </a>

                                    <?php }elseif($extension == "pdf"){ ?>

                                        <div class="border rounded p-4 text-center">
                                            <i class="bi bi-file-earmark-pdf text-danger display-4"></i>
                                            <p class="mb-2">Comprobante en PDF</p>

                                            <a href="<?php echo htmlspecialchars($ruta); ?>"
                                               target="_blank"
                                               class="btn btn-outline-primary btn-sm">
                                                Ver comprobante
                                            </a>
                                        </div>

                                    <?php }else{ ?>

                                        <p class="text-danger">Formato de comprobante no reconocido.</p>

                                    <?php } ?>

                                </div>

                            </div>

                            <hr>

                            <!-- Botones de decisión -->
                            <div class="d-flex justify-content-end gap-2 flex-wrap">

                                <form method="POST">
                                    <input type="hidden" name="id_solicitud"
                                           value="<?php echo $fila['ID_solicitud']; ?>">

                                    <button type="submit" name="accion" value="rechazar"
                                            class="btn btn-outline-danger"
                                            onclick="return confirm('¿Estás seguro de rechazar esta solicitud?');">
                                        <i class="bi bi-x-circle me-1"></i>
                                        Rechazar
                                    </button>
                                </form>

                                <form method="POST">
                                    <input type="hidden" name="id_solicitud"
                                           value="<?php echo $fila['ID_solicitud']; ?>">

                                    <button type="submit" name="accion" value="aceptar"
                                            class="btn btn-warning">
                                        <i class="bi bi-check-circle me-1"></i>
                                        Aceptar solicitud
                                    </button>
                                </form>

                            </div>

                        </div>
                    </div>

                </div>

            <?php } ?>

        </div>

    <?php }else{ ?>

        <div class="card border-0 shadow-sm text-center p-5">

            <i class="bi bi-inbox display-3 text-secondary"></i>

            <h5 class="fw-bold mt-3">No hay solicitudes pendientes</h5>

            <p class="text-secondary mb-0">
                Cuando un usuario solicite el Plan Pro, aparecerá aquí.
            </p>

        </div>

    <?php } ?>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
