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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['guardar_formulario'])) {
        $formData['subcategoria'] = trim((string) ($_POST['subcategoria'] ?? ''));
        $formData['categoria'] = trim((string) ($_POST['categoria'] ?? ''));
        $formData['evento_nombre'] = trim((string) ($_POST['evento_nombre'] ?? ''));
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
                $mensaje = 'Completa subcategoria, categoria y nombre del evento.';
                $mensajeTipo = 'danger';
            } elseif ($totalPuntos !== 100) {
                $mensaje = 'La suma total de los criterios debe ser exactamente 100 puntos.';
                $mensajeTipo = 'danger';
            } else {
                try {
                    $model->crearFormulario([
                        'subcategoria' => $formData['subcategoria'],
                        'categoria' => $formData['categoria'],
                        'evento_nombre' => $formData['evento_nombre'],
                        'activo' => $formData['activo'],
                        'creado_por' => $userId,
                        'criterios' => $puntajes,
                    ]);

                    $_SESSION['admin_calificaciones_flash'] = [
                        'mensaje' => 'Subcategoria creada correctamente.',
                        'tipo' => 'success',
                    ];
                    adminCalificacionesRedirect();
                } catch (Throwable $e) {
                    $mensaje = 'No se pudo guardar la subcategoria de calificacion.';
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
}

$formularios = $estadoTablas['formularios_listos'] ? $model->obtenerFormulariosConCriterios() : [];
$metricas = $model->obtenerMetricasResumen($formularios);
$faltantes = $estadoTablas['faltantes'];

$viewData = [
    'administrador' => $administrador,
    'usuarioSesion' => (string) ($administrador['usuario'] ?? $_SESSION['usuario'] ?? 'Administrador'),
    'pageTitle' => 'Calificaciones',
    'pageSubtitle' => 'Crea las subcategorias base que despues completaran los jurados.',
    'criteriosBase' => $criteriosBase,
    'estadoTablas' => $estadoTablas,
    'faltantesTablas' => $faltantes,
    'formularios' => $formularios,
    'metricas' => $metricas,
    'mensaje' => $mensaje,
    'mensajeTipo' => $mensajeTipo,
    'formData' => $formData,
];
