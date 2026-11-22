<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar curso | Cursolandia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="estilos/estilos.css?v=10" rel="stylesheet">
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
        <div class="d-flex align-items-center">
            <i class="bi bi-person-circle text-white fs-2"></i>
            <span class="text-white fw-normal mx-2">María Gómez</span>
            <a href="Mis_cursos.php" class="btn btn-warning btn-sm me-2">Mis cursos</a>
            <a href="cerrar_sesion.php" class="btn btn-outline-light btn-sm">Cerrar sesión</a>
        </div>
      </div>

    </div>
  </div>
</header>

<main class="bg-soft flex-grow-1 py-5">
    <div class="container">
        <div class="row g-4">

            <!-- Columna principal -->
            <div class="col-lg-8">

                <!-- Datos del curso -->
                <div class="card card-cl curso-card shadow-sm mb-4">
                    <div class="position-relative">
                        <img src="imagenes/cursos/default.jpg" class="card-img-top" alt="" style="height: 300px; object-fit: cover;">
                        <div class="position-absolute top-0 start-0 m-3">
                            <span class="badge bg-warning text-dark shadow-sm"><i class="bi bi-star-fill me-1"></i>Destacado</span>
                            <span class="badge bg-success shadow-sm">Activo</span>
                            <span class="badge text-bg-dark shadow-sm">Público</span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <h1 class="h3 mb-3">Desarrollo web desde cero</h1>
                        <p class="text-secondary">Aprendé HTML, CSS y JavaScript desde cero para crear tu primer sitio web completo.</p>

                        <h2 class="h6 mt-4">Etiquetas</h2>
                        <span class="badge text-bg-dark">HTML</span>
                        <span class="badge text-bg-dark">CSS</span>
                        <span class="badge text-bg-dark">JavaScript</span>
                    </div>
                </div>

                <!-- Materiales -->
                <div class="card card-cl curso-card shadow-sm mb-4" id="materiales">
                    <div class="card-body p-4">

                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom border-warning border-3 pb-2 mb-3">
                            <h2 class="h5 mb-0"><i class="bi bi-paperclip text-warning me-2"></i>Materiales</h2>
                            <span class="small text-secondary">2 de 5 archivos &middot; 3,0 MB de 10,0 MB</span>
                        </div>

                        <!-- Subir archivo -->
                        <form method="post" enctype="multipart/form-data" class="mb-4">
                            <label for="archivo" class="form-label small fw-semibold">Subir archivo</label>
                            <div class="input-group has-validation">
                                <input type="file" class="form-control" id="archivo" name="archivo">
                                <button type="submit" class="btn btn-warning" name="subir_material">
                                    <i class="bi bi-upload me-1"></i>Subir
                                </button>
                                <div class="invalid-feedback">Mensaje de error del archivo.</div>
                            </div>
                            <div class="form-text">Máximo 5,0 MB por archivo.</div>
                        </form>

                        <!-- Lista -->
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center gap-2">
                                <a href="#" class="text-decoration-none text-dark">
                                    <i class="bi bi-file-earmark text-warning me-2"></i>Guía de introducción a PHP
                                    <span class="badge text-bg-dark ms-1">PDF</span>
                                </a>
                                <div class="d-flex align-items-center gap-3">
                                    <span class="text-secondary small">2,0 MB</span>
                                    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalBorrarMaterial">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center gap-2">
                                <a href="#" class="text-decoration-none text-dark">
                                    <i class="bi bi-file-earmark text-warning me-2"></i>Ejercicios prácticos
                                    <span class="badge text-bg-dark ms-1">DOCX</span>
                                </a>
                                <div class="d-flex align-items-center gap-3">
                                    <span class="text-secondary small">1,0 MB</span>
                                    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalBorrarMaterial">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </li>
                        </ul>

                        <!-- Mensaje cuando no hay materiales (se muestra solo con PHP) -->
                        <p class="text-secondary mb-0 d-none">Todavía no subiste materiales a este curso.</p>

                    </div>
                </div>

                <!-- Foro -->
                <div class="card card-cl shadow-sm" id="foro">
                    <div class="card-body p-4">

                        <h2 class="h5 border-bottom border-warning border-3 pb-2 mb-3">
                            <i class="bi bi-chat-dots text-warning me-2"></i>Foro del curso
                            <span class="badge text-bg-dark ms-2">3</span>
                        </h2>

                        <div class="accordion" id="accordionForo">

                            <!-- Pregunta 1: respondida, con respuestas (una oculta) -->
                            <div class="accordion-item border rounded overflow-hidden mb-3">
                                <h3 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#pregunta-1" aria-expanded="true">
                                        <div class="w-100 pe-3">
                                            <span class="badge text-bg-dark">Respondida</span>
                                            <div class="fw-semibold mt-1">¿Qué necesito instalar antes de la primera clase?</div>
                                            <div class="small text-secondary">Ana López &middot; 3 respuestas</div>
                                        </div>
                                    </button>
                                </h3>
                                <div id="pregunta-1" class="accordion-collapse collapse show" data-bs-parent="#accordionForo">
                                    <div class="accordion-body">

                                        <p class="text-secondary">Quería saber si tengo que instalar algún programa o si alcanza con el navegador. Gracias.</p>
                                        <hr>

                                        <!-- Respuesta del docente -->
                                        <div class="rounded p-3 mb-3 bg-light">
                                            <p class="mb-1">Alcanza con el navegador y un editor de texto. Para la segunda semana necesitamos XAMPP, pero lo vemos en clase.</p>
                                            <p class="small text-secondary mb-2"><i class="bi bi-person-badge me-1"></i>María Gómez <span class="badge text-bg-dark ms-1">Docente</span></p>
                                            <button type="submit" class="btn btn-outline-dark btn-sm"><i class="bi bi-eye-slash me-1"></i>Ocultar</button>
                                        </div>

                                        <!-- Respuesta de un estudiante -->
                                        <div class="rounded p-3 mb-3 bg-light">
                                            <p class="mb-1">Gracias, entonces voy instalando XAMPP por las dudas.</p>
                                            <p class="small text-secondary mb-2"><i class="bi bi-person me-1"></i>Ana López</p>
                                            <button type="submit" class="btn btn-outline-dark btn-sm"><i class="bi bi-eye-slash me-1"></i>Ocultar</button>
                                        </div>

                                        <!-- Respuesta oculta -->
                                        <div class="rounded p-3 mb-3 bg-light opacity-50">
                                            <span class="badge bg-secondary mb-2"><i class="bi bi-eye-slash me-1"></i>Oculta para los estudiantes</span>
                                            <p class="mb-1">Texto de una respuesta que el docente decidió ocultar.</p>
                                            <p class="small text-secondary mb-2"><i class="bi bi-person me-1"></i>Diego Fernández</p>
                                            <button type="submit" class="btn btn-outline-dark btn-sm"><i class="bi bi-eye me-1"></i>Mostrar</button>
                                        </div>

                                        <!-- Mensaje de error al responder (se muestra solo con PHP) -->
                                        <div class="alert alert-danger small d-none">Mensaje de error de la respuesta.</div>

                                        <!-- Responder -->
                                        <form method="post" class="mb-3">
                                            <div class="input-group">
                                                <textarea class="form-control" name="texto" rows="2" placeholder="Escribí tu respuesta como docente"></textarea>
                                                <button type="submit" class="btn btn-dark" name="responder">Responder</button>
                                            </div>
                                        </form>

                                        <!-- Acciones sobre la pregunta -->
                                        <div class="d-flex flex-wrap gap-2 border-top pt-3">
                                            <button type="submit" class="btn btn-outline-dark btn-sm"><i class="bi bi-lock me-1"></i>Cerrar</button>
                                            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalBorrarPregunta">
                                                <i class="bi bi-trash me-1"></i>Borrar
                                            </button>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <!-- Pregunta 2: abierta, sin respuestas -->
                            <div class="accordion-item border rounded overflow-hidden mb-3">
                                <h3 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pregunta-2" aria-expanded="false">
                                        <div class="w-100 pe-3">
                                            <span class="badge bg-warning text-dark">Abierta</span>
                                            <div class="fw-semibold mt-1">No me abre el material de la unidad 2</div>
                                            <div class="small text-secondary">Diego Fernández &middot; Sin respuestas</div>
                                        </div>
                                    </button>
                                </h3>
                                <div id="pregunta-2" class="accordion-collapse collapse" data-bs-parent="#accordionForo">
                                    <div class="accordion-body">

                                        <p class="text-secondary">Intento descargar el PDF y me dice que el archivo está dañado. ¿Les pasa a otros?</p>
                                        <hr>
                                        <p class="text-secondary small">Todavía no hay respuestas.</p>

                                        <form method="post" class="mb-3">
                                            <div class="input-group">
                                                <textarea class="form-control" name="texto" rows="2" placeholder="Escribí tu respuesta como docente"></textarea>
                                                <button type="submit" class="btn btn-dark" name="responder">Responder</button>
                                            </div>
                                        </form>

                                        <div class="d-flex flex-wrap gap-2 border-top pt-3">
                                            <button type="submit" class="btn btn-outline-dark btn-sm"><i class="bi bi-lock me-1"></i>Cerrar</button>
                                            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalBorrarPregunta">
                                                <i class="bi bi-trash me-1"></i>Borrar
                                            </button>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <!-- Pregunta 3: cerrada (no se puede responder, solo reabrir o borrar) -->
                            <div class="accordion-item border rounded overflow-hidden">
                                <h3 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pregunta-3" aria-expanded="false">
                                        <div class="w-100 pe-3">
                                            <span class="badge bg-secondary">Cerrada</span>
                                            <div class="fw-semibold mt-1">¿Hay que entregar el trabajo final en grupo?</div>
                                            <div class="small text-secondary">Carlos Rodríguez &middot; 1 respuesta</div>
                                        </div>
                                    </button>
                                </h3>
                                <div id="pregunta-3" class="accordion-collapse collapse" data-bs-parent="#accordionForo">
                                    <div class="accordion-body">

                                        <p class="text-secondary">Quiero saber si el trabajo final es individual o grupal.</p>
                                        <hr>

                                        <div class="rounded p-3 mb-3 bg-light">
                                            <p class="mb-1">Puede ser individual o en grupos de hasta tres personas.</p>
                                            <p class="small text-secondary mb-2"><i class="bi bi-person-badge me-1"></i>María Gómez <span class="badge text-bg-dark ms-1">Docente</span></p>
                                            <button type="submit" class="btn btn-outline-dark btn-sm"><i class="bi bi-eye-slash me-1"></i>Ocultar</button>
                                        </div>

                                        <div class="d-flex flex-wrap gap-2 border-top pt-3">
                                            <button type="submit" class="btn btn-outline-dark btn-sm"><i class="bi bi-unlock me-1"></i>Reabrir</button>
                                            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalBorrarPregunta">
                                                <i class="bi bi-trash me-1"></i>Borrar
                                            </button>
                                        </div>

                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Mensaje cuando no hay preguntas (se muestra solo con PHP) -->
                        <p class="text-secondary mb-0 d-none">Todavía no hay preguntas en este curso.</p>

                    </div>
                </div>

            </div><!-- fin col-lg-8 -->

            <!-- Columna lateral -->
            <div class="col-lg-4">
                <div class="card card-cl shadow-sm sticky-lg-top" style="top: 90px;">
                    <div class="card-body p-4">

                        <p class="display-6 fw-bold mb-3">$15.000,00</p>

                        <ul class="list-unstyled mb-4">
                            <li class="mb-2"><i class="bi bi-calendar-event text-gold me-2"></i>01/11/2026 al 27/12/2026</li>
                            <li class="mb-2"><i class="bi bi-people text-gold me-2"></i>3 inscriptos de 30</li>
                        </ul>

                        <h2 class="h6 border-bottom border-warning border-3 pb-2 mb-3">Inscriptos</h2>
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex justify-content-between align-items-center mb-2">
                                <span>
                                    <i class="bi bi-person text-gold me-1"></i>Daniel Barroso
                                    <span class="d-block small text-secondary">17/11/2026</span>
                                </span>
                                <span class="badge bg-success">Activa</span>
                            </li>
                            <li class="d-flex justify-content-between align-items-center mb-2">
                                <span>
                                    <i class="bi bi-person text-gold me-1"></i>Ana López
                                    <span class="d-block small text-secondary">19/11/2026</span>
                                </span>
                                <span class="badge bg-warning text-dark">Pendiente</span>
                            </li>
                        </ul>

                        <!-- Mensaje cuando no hay inscriptos (se muestra solo con PHP) -->
                        <p class="text-secondary small mb-0 d-none">Todavía no hay inscriptos.</p>

                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<!-- Modal: borrar material (en PHP va uno por cada material, con su id oculto) -->
<div class="modal fade" id="modalBorrarMaterial" tabindex="-1" aria-labelledby="tituloBorrarMaterial" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 border-top border-warning border-4">
            <div class="modal-header">
                <h2 class="modal-title h5" id="tituloBorrarMaterial"><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Borrar material</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">¿Seguro que querés borrar <strong>Guía de introducción a PHP</strong>? Los estudiantes van a perder el acceso al archivo.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Cancelar</button>
                <form method="post" class="d-inline">
                    <input type="hidden" name="id_material" value="1">
                    <button type="submit" name="borrar_material" class="btn btn-dark">Sí, borrar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: borrar pregunta (en PHP va uno por cada pregunta, con su id oculto) -->
<div class="modal fade" id="modalBorrarPregunta" tabindex="-1" aria-labelledby="tituloBorrarPregunta" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 border-top border-warning border-4">
            <div class="modal-header">
                <h2 class="modal-title h5" id="tituloBorrarPregunta"><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Borrar pregunta</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">¿Seguro que querés borrar esta pregunta? Se van a borrar también todas sus respuestas.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Cancelar</button>
                <form method="post" class="d-inline">
                    <input type="hidden" name="id_pregunta" value="1">
                    <button type="submit" name="borrar_pregunta" class="btn btn-dark">Sí, borrar</button>
                </form>
            </div>
        </div>
    </div>
</div>

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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>