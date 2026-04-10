<?php
session_start();

if (!isset($_SESSION['user_id']) || (string) ($_SESSION['rol'] ?? '') !== 'impulsa_administrador') {
    header('Location: /index.php');
    exit;
}

require_once __DIR__ . '/../../config.php';

$stmt = $pdo->query(
    "SELECT
        COUNT(*) AS total_usuarios,
        SUM(rol = 'impulsa_administrador') AS total_administradores,
        SUM(rol = 'impulsa_jurado') AS total_jurados
     FROM auth"
);
$stats = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : [];

$stmtRecientes = $pdo->prepare(
    "SELECT id, usuario, rol, creado_en
     FROM auth
     ORDER BY creado_en DESC, id DESC
     LIMIT 10"
);
$stmtRecientes->execute();
$recientes = $stmtRecientes->fetchAll(PDO::FETCH_ASSOC);

$usuarioSesion = (string) ($_SESSION['usuario'] ?? $_SESSION['correo'] ?? 'Administrador');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #eff6ff 0%, #f8fafc 50%, #e2e8f0 100%);
        }

        .dashboard-shell {
            max-width: 1100px;
        }

        .hero-card,
        .panel-card {
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 14px 34px rgba(15, 23, 42, 0.08);
        }

        .kpi-card {
            border-radius: 1rem;
            border: 1px solid #e2e8f0;
            background: #fff;
        }
    </style>
</head>

<body>
    <div class="container py-4 py-lg-5 dashboard-shell">
        <div class="card hero-card mb-4">
            <div class="card-body p-4 p-lg-5">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                    <div>
                        <p class="text-uppercase text-primary fw-semibold mb-2">Administrador</p>
                        <h1 class="h3 mb-2">Hola, <?= htmlspecialchars($usuarioSesion, ENT_QUOTES, 'UTF-8') ?></h1>
                        <p class="text-secondary mb-0">Ingresaste con usuario y contraseña. Desde acá podés controlar los accesos registrados en `auth`.</p>
                    </div>
                    <a href="../../logout.php" class="btn btn-outline-danger">Cerrar sesión</a>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <div class="text-secondary small mb-2">Usuarios en auth</div>
                        <div class="display-6 fw-semibold"><?= (int) ($stats['total_usuarios'] ?? 0) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <div class="text-secondary small mb-2">Administradores</div>
                        <div class="display-6 fw-semibold"><?= (int) ($stats['total_administradores'] ?? 0) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card kpi-card h-100">
                    <div class="card-body">
                        <div class="text-secondary small mb-2">Jurados</div>
                        <div class="display-6 fw-semibold"><?= (int) ($stats['total_jurados'] ?? 0) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card panel-card">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="h5 mb-1">Últimos accesos cargados</h2>
                        <p class="text-secondary mb-0">Listado de registros disponibles en la tabla `auth`.</p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>Creado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recientes): ?>
                                <?php foreach ($recientes as $registro): ?>
                                    <tr>
                                        <td><?= (int) $registro['id'] ?></td>
                                        <td><?= htmlspecialchars((string) $registro['usuario'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars((string) $registro['rol'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars((string) ($registro['creado_en'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-secondary py-4">No hay registros disponibles en `auth`.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
