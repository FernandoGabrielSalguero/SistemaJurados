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

$categoriaSeleccionada = trim((string) ($_GET['categoria'] ?? ''));
$formData = [
    'categoria' => $categoriaSeleccionada,
    'formulario_id' => isset($_GET['formulario_id']) ? (int) $_GET['formulario_id'] : 0,
    'competidor_numero' => '',
    'puntajes' => [],
];

function juradoDashboardParseDecimal(mixed $valor): ?float
{
    if ($valor === null) {
        return null;
    }

    $normalizado = str_replace(',', '.', trim((string) $valor));
    if ($normalizado === '' || !is_numeric($normalizado)) {
        return null;
    }

    return round((float) $normalizado, 1);
}

function juradoDashboardRedirect(string $categoria = '', int $formularioId = 0): void
{
    $redirectUrl = strtok((string) ($_SERVER['REQUEST_URI'] ?? '/views/jurado/jurado_dashboard.php'), '?');
    $query = [];
    if ($categoria !== '') {
        $query['categoria'] = $categoria;
    }
    if ($formularioId > 0) {
        $query['formulario_id'] = $formularioId;
    }
    if ($query) {
        $redirectUrl .= '?' . http_build_query($query);
    }
    header('Location: ' . $redirectUrl);
    exit;
}

$categoriasDisponibles = [];
foreach ($formulariosActivos as $formulario) {
    $categoria = trim((string) ($formulario['categoria'] ?? ''));
    if ($categoria !== '') {
        $categoriasDisponibles[$categoria] = $categoria;
    }
}
$categoriasDisponibles = array_values($categoriasDisponibles);

if ($categoriaSeleccionada === '' && $categoriasDisponibles) {
    $categoriaSeleccionada = $categoriasDisponibles[0];
    $formData['categoria'] = $categoriaSeleccionada;
}

$subcategoriasDisponibles = array_values(array_filter(
    $formulariosActivos,
    static fn(array $formulario): bool => trim((string) ($formulario['categoria'] ?? '')) === $categoriaSeleccionada
));

$formularioSeleccionado = null;
foreach ($subcategoriasDisponibles as $formulario) {
    if ((int) ($formulario['id'] ?? 0) === (int) $formData['formulario_id']) {
        $formularioSeleccionado = $formulario;
        break;
    }
}

if ($formularioSeleccionado === null && $subcategoriasDisponibles) {
    $formularioSeleccionado = $subcategoriasDisponibles[0];
    $formData['formulario_id'] = (int) ($formularioSeleccionado['id'] ?? 0);
}

if ($formularioSeleccionado) {
    foreach (($formularioSeleccionado['criterios'] ?? []) as $criterio) {
        $clave = (string) ($criterio['criterio_clave'] ?? '');
        $formData['puntajes'][$clave] = '0';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_evaluacion'])) {
    $formData['categoria'] = trim((string) ($_POST['categoria'] ?? $categoriaSeleccionada));
    $formData['formulario_id'] = (int) ($_POST['formulario_id'] ?? 0);
    $formData['competidor_numero'] = trim((string) ($_POST['competidor_numero'] ?? ''));

    $categoriaSeleccionada = $formData['categoria'];
    $subcategoriasDisponibles = array_values(array_filter(
        $formulariosActivos,
        static fn(array $formulario): bool => trim((string) ($formulario['categoria'] ?? '')) === $categoriaSeleccionada
    ));

    $formularioSeleccionado = null;
    foreach ($subcategoriasDisponibles as $formulario) {
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
        $mensaje = 'Selecciona una subcategoria valida.';
        $mensajeTipo = 'danger';
    } elseif ($formData['competidor_numero'] === '') {
        $mensaje = 'Selecciona el numero del competidor.';
        $mensajeTipo = 'danger';
    } elseif ($model->existeEvaluacionDuplicada((int) $formularioSeleccionado['id'], $userId, $formData['competidor_numero'])) {
        $mensaje = 'Este jurado ya califico ese numero de competidor en la categoria y subcategoria seleccionadas.';
        $mensajeTipo = 'danger';
    } else {
        $detalles = [];
        $puntajeTotal = 0.0;
        $criterios = $formularioSeleccionado['criterios'] ?? [];

        foreach ($criterios as $criterio) {
            $clave = (string) ($criterio['criterio_clave'] ?? '');
            $valorCrudo = $_POST['puntajes'][$clave] ?? null;
            $valor = juradoDashboardParseDecimal($valorCrudo);
            $maximo = round((float) ($criterio['puntaje_maximo'] ?? 0), 1);

            if ($valor === null || $valor < 0 || $valor > $maximo) {
                $mensaje = 'Uno o mas puntajes no son validos para el criterio seleccionado.';
                $mensajeTipo = 'danger';
                break;
            }

            $detalles[] = [
                'criterio_clave' => $clave,
                'criterio_nombre' => (string) ($criterio['criterio_nombre'] ?? ''),
                'puntaje_maximo' => $maximo,
                'puntaje_otorgado' => $valor,
            ];
            $puntajeTotal += $valor;
        }

        if ($mensaje === '') {
            try {
                $promedio = count($detalles) > 0 ? round($puntajeTotal / count($detalles), 2) : 0.0;

                $model->guardarEvaluacion([
                    'formulario_id' => (int) $formularioSeleccionado['id'],
                    'jurado_id' => $userId,
                    'competidor_numero' => $formData['competidor_numero'],
                    'competidor_nombre' => 'sin nombre',
                    'categoria' => (string) ($formularioSeleccionado['categoria'] ?? ''),
                    'evento_nombre' => (string) ($formularioSeleccionado['evento_nombre'] ?? ''),
                    'puntaje_total' => round($puntajeTotal, 2),
                    'promedio' => $promedio,
                    'detalles' => $detalles,
                ]);

                $_SESSION['jurado_dashboard_flash'] = [
                    'mensaje' => 'Calificacion guardada correctamente.',
                    'tipo' => 'success',
                ];
                juradoDashboardRedirect((string) ($formularioSeleccionado['categoria'] ?? ''), (int) $formularioSeleccionado['id']);
            } catch (Throwable $e) {
                $mensaje = 'No se pudo guardar la evaluacion.';
                $mensajeTipo = 'danger';
            }
        }
    }
}

$viewData = [
    'jurado' => $jurado,
    'usuarioSesion' => (string) ($jurado['nombre'] ?? $jurado['usuario'] ?? $_SESSION['usuario'] ?? 'Jurado'),
    'pageTitle' => 'Formulario de calificacion',
    'pageSubtitle' => 'Selecciona categoria y subcategoria para completar la evaluacion del competidor.',
    'estadoTablas' => $estadoTablas,
    'categoriasDisponibles' => $categoriasDisponibles,
    'subcategoriasDisponibles' => $subcategoriasDisponibles,
    'formulariosActivos' => $formulariosActivos,
    'formularioSeleccionado' => $formularioSeleccionado,
    'mensaje' => $mensaje,
    'mensajeTipo' => $mensajeTipo,
    'formData' => $formData,
];
