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

$viewData = [
    'administrador' => $administrador,
    'usuarioSesion' => (string) ($administrador['usuario'] ?? $_SESSION['usuario'] ?? 'Administrador'),
    'pageTitle' => 'Calificaciones',
    'pageSubtitle' => 'Panel preparado para construir el formulario y sus resultados.',
];
