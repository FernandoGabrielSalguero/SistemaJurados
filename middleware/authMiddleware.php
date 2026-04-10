<?php
// middleware/authMiddleware.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verifica que el usuario esté logueado y tenga el rol adecuado.
 * @param string $requiredRole El rol requerido para acceder (ej: 'sve', 'cooperativa', 'productor')
 */
function checkAccess($requiredRole) {
if (!isset($_SESSION['nombre'])) {
    // Usuario no logueado
    header('Location: /index.php');
    exit;
}

    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== $requiredRole) {
        // Usuario logueado pero con rol incorrecto
        echo "🚫 Acceso restringido: esta sección es solo para el rol <strong>$requiredRole</strong>.";
        exit;
    }
}
