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

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/framework/framework.css">
    <script src="../../assets/framework/framework.js" defer></script>

    <style>
        :root {
            --admin-bg: #f4f6fb;
            --admin-surface: #ffffff;
            --admin-border: #e7ebf3;
            --admin-text: #1f2937;
            --admin-muted: #5f6b7a;
            --admin-primary: #2f6df6;
            --admin-primary-soft: #eaf1ff;
            --admin-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
        }

        body {
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            color: var(--admin-text);
        }

        .layout {
            min-height: 100vh;
        }

        .sidebar {
            background: rgba(255, 255, 255, 0.94);
            border-right: 1px solid var(--admin-border);
            box-shadow: 8px 0 26px rgba(15, 23, 42, 0.04);
        }

        .sidebar-header {
            padding: 22px 20px;
            border-bottom: 1px solid var(--admin-border);
            gap: 12px;
        }

        .logo-badge {
            width: 22px;
            height: 22px;
            border-radius: 7px;
            background: linear-gradient(135deg, #2f6df6, #4c8bff);
            position: relative;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.28);
        }

        .logo-badge::before,
        .logo-badge::after {
            content: "";
            position: absolute;
            background: rgba(255, 255, 255, 0.95);
        }

        .logo-badge::before {
            width: 100%;
            height: 2px;
            top: 50%;
            left: 0;
            transform: translateY(-50%);
        }

        .logo-badge::after {
            width: 2px;
            height: 100%;
            left: 50%;
            top: 0;
            transform: translateX(-50%);
        }

        .logo-text {
            font-size: 1.65rem;
            font-weight: 800;
            color: #202633;
        }

        .sidebar-menu {
            padding: 18px 14px;
        }

        .sidebar-menu ul {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .sidebar-menu li {
            border-radius: 14px;
            padding: 12px 14px;
            color: #3d4a5c;
            font-weight: 600;
            transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
        }

        .sidebar-menu li .material-icons {
            color: var(--admin-primary);
            font-size: 22px;
        }

        .sidebar-menu li.active {
            background: var(--admin-primary-soft);
            color: #1d4ed8;
        }

        .sidebar-menu li:not(.active):hover {
            background: #f8fafc;
            transform: translateX(2px);
        }

        .sidebar-footer {
            padding: 14px;
            border-top: 1px solid var(--admin-border);
        }

        .main {
            background:
                radial-gradient(circle at top left, rgba(47, 109, 246, 0.08), transparent 22%),
                linear-gradient(180deg, #f8fafc 0%, #f2f5fa 100%);
        }

        .navbar {
            background: rgba(255, 255, 255, 0.82);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.92);
            justify-content: space-between;
            padding: 18px 24px;
            position: sticky;
            top: 0;
            z-index: 20;
        }

        .navbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .navbar-title {
            font-size: 1.45rem;
            font-weight: 800;
            color: #1f2937;
        }

        .navbar-subtitle {
            color: var(--admin-muted);
            font-size: 0.95rem;
        }

        .btn-icon {
            background: transparent;
            border: 0;
            box-shadow: none;
        }

        .btn-icon .material-icons {
            color: var(--admin-primary);
        }

        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logout-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            background: #fff;
            color: #dc2626;
            border: 1px solid #fecaca;
            padding: 10px 16px;
            text-decoration: none;
            font-weight: 700;
        }

        .content {
            padding: 28px;
        }

        .page-shell {
            display: flex;
            flex-direction: column;
            gap: 28px;
        }

        .panel-card {
            background: var(--admin-surface);
            border: 1px solid var(--admin-border);
            border-radius: 22px;
            box-shadow: var(--admin-shadow);
            padding: 26px 24px;
        }

        .hero-card h1,
        .section-title {
            margin: 0 0 8px;
            font-size: 2rem;
            font-weight: 800;
            color: #202633;
        }

        .hero-card p,
        .section-caption,
        .metric-copy,
        .table-note {
            margin: 0;
            color: var(--admin-muted);
            line-height: 1.55;
        }

        .hero-actions {
            margin-top: 20px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .primary-chip,
        .secondary-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 14px;
            padding: 12px 18px;
            text-decoration: none;
            font-weight: 700;
        }

        .primary-chip {
            background: linear-gradient(135deg, #2f6df6, #4391ff);
            color: #fff;
            box-shadow: 0 12px 24px rgba(47, 109, 246, 0.24);
        }

        .secondary-chip {
            background: #fff;
            color: #23408f;
            border: 1px solid #d8e4ff;
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
        }

        .metric-card {
            background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
            border: 1px solid var(--admin-border);
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
        }

        .metric-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }

        .metric-icon {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--admin-primary-soft);
            color: var(--admin-primary);
        }

        .metric-label {
            color: var(--admin-muted);
            font-size: 0.95rem;
            margin-bottom: 6px;
        }

        .metric-value {
            font-size: 2rem;
            line-height: 1;
            font-weight: 800;
            color: #202633;
            margin-bottom: 10px;
        }

        .split-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.3fr) minmax(320px, 0.7fr);
            gap: 20px;
        }

        .mini-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
            margin-top: 20px;
        }

        .mini-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px 18px;
            border-radius: 18px;
            border: 1px solid var(--admin-border);
            background: #fff;
        }

        .mini-badge {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: var(--admin-primary-soft);
            color: var(--admin-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            flex-shrink: 0;
        }

        .mini-item strong,
        .table-title {
            display: block;
            margin-bottom: 4px;
            color: #202633;
        }

        .table-responsive {
            margin-top: 22px;
            border: 1px solid var(--admin-border);
            border-radius: 18px;
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: #f8fafc;
            color: #5b6472;
            border-bottom-color: var(--admin-border);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .table tbody tr:last-child td {
            border-bottom: 0;
        }

        .role-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 7px 12px;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .role-pill.role-admin {
            background: #eaf1ff;
            color: #2457cc;
        }

        .role-pill.role-jurado {
            background: #fff4d8;
            color: #a16207;
        }

        .empty-state {
            text-align: center;
            color: var(--admin-muted);
            padding: 34px 18px;
        }

        @media (max-width: 1080px) {
            .metrics-grid,
            .split-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .content {
                padding: 18px;
            }

            .navbar {
                padding: 14px 18px;
            }

            .panel-card {
                padding: 22px 18px;
                border-radius: 18px;
            }

            .hero-card h1,
            .section-title {
                font-size: 1.6rem;
            }

            .navbar-actions {
                gap: 8px;
            }

            .logout-link span:last-child {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="layout">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <span class="logo-badge" aria-hidden="true"></span>
                <span class="logo-text">Impulsa</span>
            </div>

            <nav class="sidebar-menu">
                <ul>
                    <li class="active" onclick="location.href='admin_dashboard.php'">
                        <span class="material-icons">home</span>
                        <span class="link-text">Inicio</span>
                    </li>
                    <li>
                        <span class="material-icons">dashboard</span>
                        <span class="link-text">Panel</span>
                    </li>
                    <li>
                        <span class="material-icons">badge</span>
                        <span class="link-text">Administradores</span>
                    </li>
                    <li>
                        <span class="material-icons">groups</span>
                        <span class="link-text">Jurados</span>
                    </li>
                    <li>
                        <span class="material-icons">table_chart</span>
                        <span class="link-text">Registros</span>
                    </li>
                    <li onclick="location.href='../../logout.php'">
                        <span class="material-icons">logout</span>
                        <span class="link-text">Salir</span>
                    </li>
                </ul>
            </nav>

            <div class="sidebar-footer">
                <button class="btn-icon" type="button" onclick="toggleSidebar()">
                    <span class="material-icons" id="collapseIcon">chevron_left</span>
                </button>
            </div>
        </aside>

        <div class="main">
            <header class="navbar">
                <div class="navbar-left">
                    <button class="btn-icon" type="button" onclick="toggleSidebar()">
                        <span class="material-icons">menu</span>
                    </button>
                    <div>
                        <div class="navbar-title">Inicio</div>
                        <div class="navbar-subtitle">Panel de administración</div>
                    </div>
                </div>

                <div class="navbar-actions">
                    <div class="navbar-subtitle"><?= htmlspecialchars($usuarioSesion, ENT_QUOTES, 'UTF-8') ?></div>
                    <a href="../../logout.php" class="logout-link">
                        <span class="material-icons">logout</span>
                        <span>Salir</span>
                    </a>
                </div>
            </header>

            <section class="content">
                <div class="page-shell">
                    <div class="panel-card hero-card">
                        <h1>¡Qué gusto verte!</h1>
                        <p>Estás viendo el panel interno de administración de Impulsa. Esta vista quedó organizada con menú lateral, tarjetas de métricas y una tabla central para revisar los accesos guardados en la tabla <code>auth</code>.</p>
                        <div class="hero-actions">
                            <a href="#resumen" class="primary-chip">
                                <span class="material-icons">play_arrow</span>
                                <span>Ver resumen</span>
                            </a>
                            <a href="#registros" class="secondary-chip">
                                <span class="material-icons">table_rows</span>
                                <span>Ir a registros</span>
                            </a>
                        </div>
                    </div>

                    <div class="panel-card" id="resumen">
                        <h2 class="section-title">Resumen general</h2>
                        <p class="section-caption">Indicadores rápidos del sistema de acceso para administradores y jurados.</p>

                        <div class="metrics-grid" style="margin-top:22px;">
                            <article class="metric-card">
                                <div class="metric-head">
                                    <div>
                                        <div class="metric-label">Usuarios en auth</div>
                                        <div class="metric-value"><?= (int) ($stats['total_usuarios'] ?? 0) ?></div>
                                    </div>
                                    <span class="metric-icon material-icons">group</span>
                                </div>
                                <p class="metric-copy">Total de credenciales cargadas en la tabla principal de acceso.</p>
                            </article>

                            <article class="metric-card">
                                <div class="metric-head">
                                    <div>
                                        <div class="metric-label">Administradores</div>
                                        <div class="metric-value"><?= (int) ($stats['total_administradores'] ?? 0) ?></div>
                                    </div>
                                    <span class="metric-icon material-icons">admin_panel_settings</span>
                                </div>
                                <p class="metric-copy">Usuarios que ingresan con combinación de usuario y contraseña.</p>
                            </article>

                            <article class="metric-card">
                                <div class="metric-head">
                                    <div>
                                        <div class="metric-label">Jurados</div>
                                        <div class="metric-value"><?= (int) ($stats['total_jurados'] ?? 0) ?></div>
                                    </div>
                                    <span class="metric-icon material-icons">verified_user</span>
                                </div>
                                <p class="metric-copy">Usuarios habilitados para entrar con código de acceso válido.</p>
                            </article>
                        </div>
                    </div>

                    <div class="split-grid">
                        <div class="panel-card" id="registros">
                            <h2 class="section-title">Últimos accesos cargados</h2>
                            <p class="table-note">Listado de registros disponibles en <code>auth</code>, ordenados por fecha de creación.</p>

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
                                                <?php $esAdmin = (string) $registro['rol'] === 'impulsa_administrador'; ?>
                                                <tr>
                                                    <td><?= (int) $registro['id'] ?></td>
                                                    <td>
                                                        <strong class="table-title"><?= htmlspecialchars((string) $registro['usuario'], ENT_QUOTES, 'UTF-8') ?></strong>
                                                        <span class="table-note">Credencial activa para ingreso</span>
                                                    </td>
                                                    <td>
                                                        <span class="role-pill <?= $esAdmin ? 'role-admin' : 'role-jurado' ?>">
                                                            <?= htmlspecialchars((string) $registro['rol'], ENT_QUOTES, 'UTF-8') ?>
                                                        </span>
                                                    </td>
                                                    <td><?= htmlspecialchars((string) ($registro['creado_en'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="empty-state">No hay registros disponibles en <code>auth</code>.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <aside class="panel-card">
                            <h2 class="section-title">Cómo navegar</h2>
                            <p class="section-caption">El menú lateral replica la estructura del framework para que el panel se sienta consistente con tu referencia.</p>

                            <div class="mini-list">
                                <div class="mini-item">
                                    <span class="mini-badge">1</span>
                                    <div>
                                        <strong>Inicio</strong>
                                        <span class="table-note">Acceso rápido al resumen del dashboard.</span>
                                    </div>
                                </div>

                                <div class="mini-item">
                                    <span class="mini-badge">2</span>
                                    <div>
                                        <strong>Panel</strong>
                                        <span class="table-note">Espacio pensado para sumar nuevas tarjetas o módulos.</span>
                                    </div>
                                </div>

                                <div class="mini-item">
                                    <span class="mini-badge">3</span>
                                    <div>
                                        <strong>Registros</strong>
                                        <span class="table-note">Zona para controlar administradores y jurados creados.</span>
                                    </div>
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>
            </section>
        </div>
    </div>
</body>

</html>
