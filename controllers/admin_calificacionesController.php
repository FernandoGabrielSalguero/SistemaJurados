<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit;
}

if (($_SESSION['rol'] ?? '') !== 'impulsa_administrador') {
    header('Location: /index.php');
    exit;
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/admin_calificacionesModel.php';

$userId = (int) $_SESSION['user_id'];
$model = new AdminCalificacionesModel($pdo);
$administrador = $model->obtenerAdministrador($userId);
$criteriosBase = $model->obtenerCriteriosBase();
$estadoTablas = $model->obtenerEstadoTablasCalificaciones();

$flash = $_SESSION['admin_calificaciones_flash'] ?? null;
unset($_SESSION['admin_calificaciones_flash']);

$mensaje = is_array($flash) ? (string) ($flash['mensaje'] ?? '') : '';
$mensajeTipo = is_array($flash) ? (string) ($flash['tipo'] ?? 'success') : 'success';

$formData = [
    'subcategoria' => '',
    'categoria' => '',
    'evento_nombre' => '',
    'imagen_url' => '',
    'activo' => 1,
    'puntajes' => [],
];

foreach ($criteriosBase as $criterio) {
    $formData['puntajes'][$criterio['clave']] = (string) ($criterio['sugerido'] ?? 0);
}

function adminCalificacionesRedirect(): void
{
    $redirectUrl = strtok((string) ($_SERVER['REQUEST_URI'] ?? '/views/admin/admin_calificaciones.php'), '?');
    header('Location: ' . $redirectUrl);
    exit;
}

function adminCalificacionesNormalizarNombreArchivo(string $nombre): string
{
    $nombre = pathinfo($nombre, PATHINFO_FILENAME);
    $nombre = strtolower(trim($nombre));
    $nombre = preg_replace('/[^a-z0-9]+/', '-', $nombre) ?? '';
    $nombre = trim($nombre, '-');

    return $nombre !== '' ? $nombre : 'evento';
}

function adminCalificacionesRutaAbsolutaDesdeRelativa(?string $rutaRelativa): string
{
    $rutaRelativa = trim((string) $rutaRelativa);
    if ($rutaRelativa === '') {
        return '';
    }

    return dirname(__DIR__) . '/' . ltrim(str_replace('\\', '/', $rutaRelativa), '/');
}

$modoEdicion = false;
$formularioEditandoId = 0;

if (isset($_GET['editar'])) {
    $formularioEditandoId = (int) ($_GET['editar'] ?? 0);
    if ($formularioEditandoId > 0 && ($estadoTablas['formularios_listos'] ?? false)) {
        $formularioEditando = $model->obtenerFormularioPorId($formularioEditandoId);
        if ($formularioEditando) {
            $modoEdicion = true;
            $formData['subcategoria'] = (string) ($formularioEditando['subcategoria'] ?? '');
            $formData['categoria'] = (string) ($formularioEditando['categoria'] ?? '');
            $formData['evento_nombre'] = (string) ($formularioEditando['evento_nombre'] ?? '');
            $formData['imagen_url'] = (string) ($formularioEditando['imagen_url'] ?? '');
            $formData['activo'] = (int) ($formularioEditando['activo'] ?? 0);
            foreach (($formularioEditando['criterios'] ?? []) as $criterio) {
                $clave = (string) ($criterio['criterio_clave'] ?? '');
                if ($clave !== '') {
                    $formData['puntajes'][$clave] = (string) ((int) ($criterio['puntaje_maximo'] ?? 0));
                }
            }
        } else {
            $_SESSION['admin_calificaciones_flash'] = [
                'mensaje' => 'No se encontro el formulario seleccionado para editar.',
                'tipo' => 'danger',
            ];
            adminCalificacionesRedirect();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['guardar_formulario'])) {
        $formularioEditandoId = (int) ($_POST['formulario_id'] ?? 0);
        $modoEdicion = $formularioEditandoId > 0;
        $formData['subcategoria'] = trim((string) ($_POST['subcategoria'] ?? ''));
        $formData['categoria'] = trim((string) ($_POST['categoria'] ?? ''));
        $formData['evento_nombre'] = trim((string) ($_POST['evento_nombre'] ?? ''));
        $formData['imagen_url'] = trim((string) ($_POST['imagen_url_actual'] ?? ''));
        $formData['activo'] = isset($_POST['activo']) ? 1 : 0;

        $puntajes = [];
        $totalPuntos = 0;

        foreach ($criteriosBase as $criterio) {
            $clave = $criterio['clave'];
            $valorCrudo = $_POST['puntajes'][$clave] ?? '';
            $valorNormalizado = filter_var($valorCrudo, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

            if ($valorNormalizado === false) {
                $mensaje = 'Cada criterio debe tener un puntaje entero mayor o igual a cero.';
                $mensajeTipo = 'danger';
                break;
            }

            $puntajes[] = [
                'nombre' => $criterio['nombre'],
                'clave' => $clave,
                'puntaje_maximo' => (int) $valorNormalizado,
                'orden' => (int) $criterio['orden'],
            ];
            $formData['puntajes'][$clave] = (string) $valorNormalizado;
            $totalPuntos += (int) $valorNormalizado;
        }

        if ($mensaje === '') {
            if (!$estadoTablas['formularios_listos']) {
                $mensaje = 'Faltan tablas del modulo de calificaciones. Ejecuta primero el SQL nuevo en assets/estructura_base_datos.md.';
                $mensajeTipo = 'danger';
            } elseif ($formData['subcategoria'] === '' || $formData['categoria'] === '' || $formData['evento_nombre'] === '') {
                $mensaje = 'Completa estilo, categoria y nombre del evento.';
                $mensajeTipo = 'danger';
            } elseif ($totalPuntos !== 100) {
                $mensaje = 'La suma total de los criterios debe ser exactamente 100 puntos.';
                $mensajeTipo = 'danger';
            } else {
                $imagenTmpPath = '';
                $imagenDestinoAbsoluto = '';
                $imagenAnteriorRelativa = $formData['imagen_url'];
                $eliminarImagenActual = isset($_POST['eliminar_imagen_actual']);

                if ($modoEdicion && $formularioEditandoId <= 0) {
                    $mensaje = 'No se encontro el formulario a editar.';
                    $mensajeTipo = 'danger';
                } elseif ($modoEdicion) {
                    $formularioActual = $model->obtenerFormularioPorId($formularioEditandoId);
                    if (!$formularioActual) {
                        $mensaje = 'No se encontro el formulario a editar.';
                        $mensajeTipo = 'danger';
                    } else {
                        $imagenAnteriorRelativa = (string) ($formularioActual['imagen_url'] ?? '');
                        $formData['imagen_url'] = $imagenAnteriorRelativa;
                    }
                }

                if (isset($_FILES['imagen_evento']) && (int) ($_FILES['imagen_evento']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                    if (!(bool) ($estadoTablas['imagen_columna_lista'] ?? false)) {
                        $mensaje = 'Para guardar una imagen primero debes agregar la columna imagen_url en calificacion_formularios.';
                        $mensajeTipo = 'danger';
                    } else {
                        $archivo = $_FILES['imagen_evento'];
                        $error = (int) ($archivo['error'] ?? UPLOAD_ERR_NO_FILE);

                        if ($error !== UPLOAD_ERR_OK) {
                            $mensaje = 'No se pudo subir la imagen seleccionada.';
                            $mensajeTipo = 'danger';
                        } else {
                            $extension = strtolower((string) pathinfo((string) ($archivo['name'] ?? ''), PATHINFO_EXTENSION));
                            $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                            $mime = (string) mime_content_type((string) ($archivo['tmp_name'] ?? ''));
                            $mimesPermitidos = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

                            if (!in_array($extension, $extensionesPermitidas, true) || !in_array($mime, $mimesPermitidos, true)) {
                                $mensaje = 'La imagen debe estar en formato JPG, PNG, WEBP o GIF.';
                                $mensajeTipo = 'danger';
                            } else {
                                $directorioUploads = dirname(__DIR__) . '/uploads/events';
                                if (!is_dir($directorioUploads) && !mkdir($directorioUploads, 0775, true) && !is_dir($directorioUploads)) {
                                    $mensaje = 'No se pudo preparar la carpeta de imagenes del evento.';
                                    $mensajeTipo = 'danger';
                                } else {
                                    $baseNombre = adminCalificacionesNormalizarNombreArchivo($formData['evento_nombre'] . '-' . $formData['subcategoria']);
                                    $nombreFinal = $baseNombre . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
                                    $imagenDestinoAbsoluto = $directorioUploads . '/' . $nombreFinal;
                                    $imagenTmpPath = (string) ($archivo['tmp_name'] ?? '');
                                    $formData['imagen_url'] = 'uploads/events/' . $nombreFinal;
                                }
                            }
                        }
                    }
                } elseif ($eliminarImagenActual && (bool) ($estadoTablas['imagen_columna_lista'] ?? false)) {
                    $formData['imagen_url'] = '';
                }

                try {
                    if ($mensaje !== '') {
                        throw new RuntimeException($mensaje);
                    }

                    if ($imagenTmpPath !== '' && !move_uploaded_file($imagenTmpPath, $imagenDestinoAbsoluto)) {
                        throw new RuntimeException('No se pudo mover la imagen subida.');
                    }

                    if ($modoEdicion) {
                        $model->actualizarFormulario($formularioEditandoId, [
                            'subcategoria' => $formData['subcategoria'],
                            'categoria' => $formData['categoria'],
                            'evento_nombre' => $formData['evento_nombre'],
                            'imagen_url' => $formData['imagen_url'] !== '' ? $formData['imagen_url'] : null,
                            'activo' => $formData['activo'],
                            'criterios' => $puntajes,
                        ]);
                    } else {
                        $model->crearFormulario([
                            'subcategoria' => $formData['subcategoria'],
                            'categoria' => $formData['categoria'],
                            'evento_nombre' => $formData['evento_nombre'],
                            'imagen_url' => $formData['imagen_url'] !== '' ? $formData['imagen_url'] : null,
                            'activo' => $formData['activo'],
                            'creado_por' => $userId,
                            'criterios' => $puntajes,
                        ]);
                    }

                    if ($imagenAnteriorRelativa !== '' && $imagenAnteriorRelativa !== $formData['imagen_url']) {
                        $imagenAnteriorAbsoluta = adminCalificacionesRutaAbsolutaDesdeRelativa($imagenAnteriorRelativa);
                        if ($imagenAnteriorAbsoluta !== '' && is_file($imagenAnteriorAbsoluta)) {
                            @unlink($imagenAnteriorAbsoluta);
                        }
                    }

                    $_SESSION['admin_calificaciones_flash'] = [
                        'mensaje' => $modoEdicion ? 'Formulario actualizado correctamente.' : 'Formulario creado correctamente.',
                        'tipo' => 'success',
                    ];
                    adminCalificacionesRedirect();
                } catch (Throwable $e) {
                    if ($imagenDestinoAbsoluto !== '' && is_file($imagenDestinoAbsoluto)) {
                        @unlink($imagenDestinoAbsoluto);
                    }
                    if ($mensaje === '') {
                        $mensaje = 'No se pudo guardar el formulario de calificacion.';
                    }
                    $mensajeTipo = 'danger';
                }
            }
        }
    }

    if (isset($_POST['toggle_formulario_id'])) {
        $formularioId = (int) ($_POST['toggle_formulario_id'] ?? 0);
        $nuevoEstado = isset($_POST['formulario_activo']) ? 1 : 0;

        if (!$estadoTablas['formularios_listos']) {
            $_SESSION['admin_calificaciones_flash'] = [
                'mensaje' => 'Faltan tablas del modulo de calificaciones. Ejecuta primero el SQL nuevo en assets/estructura_base_datos.md.',
                'tipo' => 'danger',
            ];
            adminCalificacionesRedirect();
        }

        try {
            $actualizado = $model->actualizarEstadoFormulario($formularioId, $nuevoEstado === 1);
            $_SESSION['admin_calificaciones_flash'] = [
                'mensaje' => $actualizado
                    ? ($nuevoEstado === 1 ? 'Formulario activado correctamente.' : 'Formulario desactivado correctamente.')
                    : 'No se encontro el formulario seleccionado.',
                'tipo' => $actualizado ? 'success' : 'danger',
            ];
        } catch (Throwable $e) {
            $_SESSION['admin_calificaciones_flash'] = [
                'mensaje' => 'No se pudo actualizar el estado del formulario.',
                'tipo' => 'danger',
            ];
        }

        adminCalificacionesRedirect();
    }

    if (isset($_POST['eliminar_formulario_id'])) {
        $formularioId = (int) ($_POST['eliminar_formulario_id'] ?? 0);

        if (!$estadoTablas['formularios_listos']) {
            $_SESSION['admin_calificaciones_flash'] = [
                'mensaje' => 'Faltan tablas del modulo de calificaciones. Ejecuta primero el SQL nuevo en assets/estructura_base_datos.md.',
                'tipo' => 'danger',
            ];
            adminCalificacionesRedirect();
        }

        try {
            $resultado = $model->eliminarFormularioEnCascada($formularioId);
            if ((bool) ($resultado['eliminado'] ?? false)) {
                $imagenAbsoluta = adminCalificacionesRutaAbsolutaDesdeRelativa((string) ($resultado['imagen_url'] ?? ''));
                if ($imagenAbsoluta !== '' && is_file($imagenAbsoluta)) {
                    @unlink($imagenAbsoluta);
                }
            }

            $_SESSION['admin_calificaciones_flash'] = [
                'mensaje' => (bool) ($resultado['eliminado'] ?? false)
                    ? 'Formulario, criterios y evaluaciones eliminados correctamente.'
                    : 'No se encontro el formulario seleccionado.',
                'tipo' => (bool) ($resultado['eliminado'] ?? false) ? 'success' : 'danger',
            ];
        } catch (Throwable $e) {
            $_SESSION['admin_calificaciones_flash'] = [
                'mensaje' => 'No se pudo eliminar el formulario seleccionado.',
                'tipo' => 'danger',
            ];
        }

        adminCalificacionesRedirect();
    }
}

$formularios = $estadoTablas['formularios_listos'] ? $model->obtenerFormulariosConCriterios() : [];
$metricas = $model->obtenerMetricasResumen($formularios);
$faltantes = $estadoTablas['faltantes'];

$viewData = [
    'administrador' => $administrador,
    'usuarioSesion' => (string) ($administrador['usuario'] ?? $_SESSION['usuario'] ?? 'Administrador'),
    'pageTitle' => 'Formulario de calificaciones',
    'pageSubtitle' => 'Crea los formularios que los jurados van a visualizar desde su tablet',
    'criteriosBase' => $criteriosBase,
    'estadoTablas' => $estadoTablas,
    'faltantesTablas' => $faltantes,
    'formularios' => $formularios,
    'metricas' => $metricas,
    'mensaje' => $mensaje,
    'mensajeTipo' => $mensajeTipo,
    'formData' => $formData,
    'modoEdicion' => $modoEdicion,
    'formularioEditandoId' => $formularioEditandoId,
];
