<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit;
}

if (($_SESSION['rol'] ?? '') !== 'impulsa_jurado') {
    header('Location: /index.php');
    exit;
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/jurado_dashboardModel.php';

$userId = (int) $_SESSION['user_id'];
$model = new JuradoDashboardModel($pdo);
$jurado = $model->obtenerJurado($userId);
$estadoTablas = $model->obtenerEstadoTablasCalificaciones();
$formulariosActivos = $estadoTablas['formularios_listos'] ? $model->obtenerFormulariosActivosConCriterios() : [];

$flash = $_SESSION['jurado_dashboard_flash'] ?? null;
unset($_SESSION['jurado_dashboard_flash']);

$mensaje = is_array($flash) ? (string) ($flash['mensaje'] ?? '') : '';
$mensajeTipo = is_array($flash) ? (string) ($flash['tipo'] ?? 'success') : 'success';

$formData = [
    'formulario_id' => isset($_GET['formulario_id']) ? (int) $_GET['formulario_id'] : 0,
    'competidor_numero' => '',
    'competidor_nombre' => '',
    'puntajes' => [],
];

function juradoDashboardRedirect(int $formularioId = 0): void
{
    $redirectUrl = strtok((string) ($_SERVER['REQUEST_URI'] ?? '/views/jurado/jurado_dashboard.php'), '?');
    if ($formularioId > 0) {
        $redirectUrl .= '?formulario_id=' . $formularioId;
    }
    header('Location: ' . $redirectUrl);
    exit;
}

$formularioSeleccionado = null;
foreach ($formulariosActivos as $formulario) {
    if ((int) ($formulario['id'] ?? 0) === (int) $formData['formulario_id']) {
        $formularioSeleccionado = $formulario;
        break;
    }
}

if ($formularioSeleccionado === null && $formulariosActivos) {
    $formularioSeleccionado = $formulariosActivos[0];
    $formData['formulario_id'] = (int) ($formularioSeleccionado['id'] ?? 0);
}

if ($formularioSeleccionado) {
    foreach (($formularioSeleccionado['criterios'] ?? []) as $criterio) {
        $clave = (string) ($criterio['criterio_clave'] ?? '');
        $formData['puntajes'][$clave] = '0';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_evaluacion'])) {
    $formData['formulario_id'] = (int) ($_POST['formulario_id'] ?? 0);
    $formData['competidor_numero'] = trim((string) ($_POST['competidor_numero'] ?? ''));
    $formData['competidor_nombre'] = trim((string) ($_POST['competidor_nombre'] ?? ''));

    $formularioSeleccionado = null;
    foreach ($formulariosActivos as $formulario) {
        if ((int) ($formulario['id'] ?? 0) === (int) $formData['formulario_id']) {
            $formularioSeleccionado = $formulario;
            break;
        }
    }

    if ($formularioSeleccionado) {
        foreach (($formularioSeleccionado['criterios'] ?? []) as $criterio) {
            $clave = (string) ($criterio['criterio_clave'] ?? '');
            $formData['puntajes'][$clave] = (string) ($_POST['puntajes'][$clave] ?? '0');
        }
    }

    if (!$estadoTablas['formularios_listos'] || !$estadoTablas['evaluaciones_listas']) {
        $mensaje = 'Faltan tablas del modulo de calificaciones. Ejecuta primero el SQL nuevo.';
        $mensajeTipo = 'danger';
    } elseif ($formularioSeleccionado === null) {
        $mensaje = 'Selecciona un formulario valido.';
        $mensajeTipo = 'danger';
    } elseif ($formData['competidor_numero'] === '' || $formData['competidor_nombre'] === '') {
        $mensaje = 'Completa el numero y el nombre del competidor.';
        $mensajeTipo = 'danger';
    } else {
        $detalles = [];
        $puntajeTotal = 0.0;
        $criterios = $formularioSeleccionado['criterios'] ?? [];

        foreach ($criterios as $criterio) {
            $clave = (string) ($criterio['criterio_clave'] ?? '');
            $valorCrudo = $_POST['puntajes'][$clave] ?? null;
            $valor = filter_var($valorCrudo, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
            $maximo = (int) ($criterio['puntaje_maximo'] ?? 0);

            if ($valor === false || $valor > $maximo) {
                $mensaje = 'Uno o mas puntajes no son validos para el criterio seleccionado.';
                $mensajeTipo = 'danger';
                break;
            }

            $detalles[] = [
                'criterio_clave' => $clave,
                'criterio_nombre' => (string) ($criterio['criterio_nombre'] ?? ''),
                'puntaje_maximo' => (float) $maximo,
                'puntaje_otorgado' => (float) $valor,
            ];
            $puntajeTotal += (float) $valor;
        }

        if ($mensaje === '') {
            try {
                $promedio = count($detalles) > 0 ? round($puntajeTotal / count($detalles), 2) : 0.0;

                $model->guardarEvaluacion([
                    'formulario_id' => (int) $formularioSeleccionado['id'],
                    'jurado_id' => $userId,
                    'competidor_numero' => $formData['competidor_numero'],
                    'competidor_nombre' => $formData['competidor_nombre'],
                    'categoria' => (string) ($formularioSeleccionado['categoria'] ?? ''),
                    'evento_nombre' => (string) ($formularioSeleccionado['evento_nombre'] ?? ''),
                    'puntaje_total' => $puntajeTotal,
                    'promedio' => $promedio,
                    'detalles' => $detalles,
                ]);

                $_SESSION['jurado_dashboard_flash'] = [
                    'mensaje' => 'Calificacion guardada correctamente.',
                    'tipo' => 'success',
                ];
                juradoDashboardRedirect((int) $formularioSeleccionado['id']);
            } catch (Throwable $e) {
                $mensaje = 'No se pudo guardar la evaluacion.';
                $mensajeTipo = 'danger';
            }
        }
    }
}

$viewData = [
    'jurado' => $jurado,
    'usuarioSesion' => (string) ($jurado['usuario'] ?? $_SESSION['usuario'] ?? 'Jurado'),
    'pageTitle' => 'Formulario de calificacion',
    'pageSubtitle' => 'Completa la evaluacion del competidor usando una plantilla activa.',
    'estadoTablas' => $estadoTablas,
    'formulariosActivos' => $formulariosActivos,
    'formularioSeleccionado' => $formularioSeleccionado,
    'mensaje' => $mensaje,
    'mensajeTipo' => $mensajeTipo,
    'formData' => $formData,
];
