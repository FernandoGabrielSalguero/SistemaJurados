<?php

session_start();

// Protección: debe estar logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit;
}

// Protección: solo administradores
if (($_SESSION['rol'] ?? '') !== 'impulsa_administrador') {
    header('Location: /index.php');
    exit;
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/admin_dashboardModel.php';

$userId = (int) $_SESSION['user_id'];
$model  = new AdminDashboardModel($pdo);

$perfil      = $model->obtenerPerfil($userId);
$stats       = $model->obtenerEstadisticasUsuarios();
$recientes   = $model->obtenerRegistrosRecientes(10);
