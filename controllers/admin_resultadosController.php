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
require_once __DIR__ . '/../models/admin_resultadosModel.php';

$userId = (int) $_SESSION['user_id'];
$model = new AdminResultadosModel($pdo);
$administrador = $model->obtenerAdministrador($userId);
$estadoTablas = $model->obtenerEstadoTablasCalificaciones();

$filtroFormularioId = isset($_GET['formulario_id']) ? (int) $_GET['formulario_id'] : 0;
$filtroCategoria = trim((string) ($_GET['categoria'] ?? ''));

$filtrosDisponibles = ($estadoTablas['formularios_listos'] || $estadoTablas['evaluaciones_listas'])
    ? $model->obtenerFiltrosDisponibles()
    : ['formularios' => [], 'categorias' => []];

$resultadosAgrupados = $estadoTablas['evaluaciones_listas']
    ? $model->obtenerResultadosAgrupados($filtroFormularioId, $filtroCategoria)
    : [];

$metricas = [
    'grupos' => count($resultadosAgrupados),
    'evaluaciones' => 0,
    'competidores' => 0,
    'jurados' => 0,
];

foreach ($resultadosAgrupados as $grupo) {
    $metricas['evaluaciones'] += (int) ($grupo['total_evaluaciones'] ?? 0);
    $metricas['competidores'] += (int) ($grupo['total_competidores'] ?? 0);
    $metricas['jurados'] += (int) ($grupo['total_jurados'] ?? 0);
}

$viewData = [
    'administrador' => $administrador,
    'usuarioSesion' => (string) ($administrador['usuario'] ?? $_SESSION['usuario'] ?? 'Administrador'),
    'pageTitle' => 'Resultados',
    'pageSubtitle' => 'Consulta lo que van cargando los jurados, agrupado por subcategoria y categoria.',
    'estadoTablas' => $estadoTablas,
    'filtrosDisponibles' => $filtrosDisponibles,
    'filtroFormularioId' => $filtroFormularioId,
    'filtroCategoria' => $filtroCategoria,
    'resultadosAgrupados' => $resultadosAgrupados,
    'metricas' => $metricas,
];
