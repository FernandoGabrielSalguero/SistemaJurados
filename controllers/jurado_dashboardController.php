<?php

session_start();

// Protección: debe estar logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit;
}

// Protección: solo emprendedores
if (($_SESSION['rol'] ?? '') !== 'impulsa_emprendedor') {
    header('Location: /index.php');
    exit;
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/emprendedor_dashboardModel.php';

$userId = (int) $_SESSION['user_id'];
$model  = new EmprendedorDashboardModel($pdo);
$perfil = $model->obtenerPerfil($userId);
$misionCompletada = $model->obtenerProgresoMision($userId);
$visionCompletada = $model->obtenerProgresoVision($userId);
$buyerPersonaCompletado = $model->obtenerProgresoBuyerPersona($userId);
$landingPageCompletada = $model->obtenerProgresoLandingPage($userId);
$landingDisponible = $model->puedeAccederLanding($userId);

$misionResumen = $model->obtenerResumenMision($userId);
$visionResumen = $model->obtenerResumenVision($userId);
$buyerPersonaResumen = $model->obtenerResumenBuyerPersona($userId);
