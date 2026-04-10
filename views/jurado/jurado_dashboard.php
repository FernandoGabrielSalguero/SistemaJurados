<?php
session_start();

if (!isset($_SESSION['user_id']) || (string) ($_SESSION['rol'] ?? '') !== 'impulsa_jurado') {
    header('Location: /index.php');
    exit;
}

require_once __DIR__ . '/../../config.php';

$stmt = $pdo->prepare(
    "SELECT id, usuario, rol, creado_en
     FROM auth
     WHERE id = :id
     LIMIT 1"
);
$stmt->execute(['id' => (int) $_SESSION['user_id']]);
$jurado = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

$usuarioSesion = (string) ($jurado['usuario'] ?? $_SESSION['usuario'] ?? 'Jurado');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de jurado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #fefce8 0%, #fff7ed 45%, #f8fafc 100%);
        }

        .dashboard-shell {
            max-width: 900px;
        }

        .hero-card,
        .panel-card {
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 14px 34px rgba(15, 23, 42, 0.08);
        }
    </style>
</head>

<body>
    <div class="container py-4 py-lg-5 dashboard-shell">
        <div class="card hero-card mb-4">
            <div class="card-body p-4 p-lg-5">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                    <div>
                        <p class="text-uppercase text-warning-emphasis fw-semibold mb-2">Jurado</p>
                        <h1 class="h3 mb-2">Bienvenido, <?= htmlspecialchars($usuarioSesion, ENT_QUOTES, 'UTF-8') ?></h1>
                        <p class="text-secondary mb-0">Ingresaste con un código de acceso válido. Este panel quedó preparado para el rol `impulsa_jurado`.</p>
                    </div>
                    <a href="../../logout.php" class="btn btn-outline-danger">Cerrar sesión</a>
                </div>
            </div>
        </div>

        <div class="card panel-card">
            <div class="card-body p-4">
                <h2 class="h5 mb-3">Datos de la sesión</h2>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-secondary small mb-2">ID</div>
                            <div class="fs-4 fw-semibold"><?= (int) ($jurado['id'] ?? 0) ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-secondary small mb-2">Usuario</div>
                            <div class="fs-4 fw-semibold"><?= htmlspecialchars($usuarioSesion, ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-secondary small mb-2">Rol</div>
                            <div class="fs-4 fw-semibold"><?= htmlspecialchars((string) ($_SESSION['rol'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-light border mt-4 mb-0" role="alert">
                    El acceso del jurado depende únicamente de que el código ingresado coincida con un `codigo_acceso` válido guardado en la tabla `auth`.
                </div>
            </div>
        </div>
    </div>
</body>

</html>
