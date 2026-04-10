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

    <style>
        @font-face {
            font-family: 'Montserrat';
            src: url('../../assets/institucionales/fonts/Montserrat/Montserrat-VariableFont_wght.ttf') format('truetype');
            font-weight: 100 900;
            font-style: normal;
            font-display: swap;
        }

        :root {
            --admin-surface: #ffffff;
            --admin-bg: #f3f6fb;
            --admin-border: #e5ebf4;
            --admin-text: #1f2937;
            --admin-muted: #67768a;
            --admin-primary: #2f6df6;
            --admin-primary-soft: #eaf1ff;
            --admin-danger: #ef4444;
            --admin-shadow: 0 10px 28px rgba(15, 23, 42, 0.08);
            --sidebar-width: 208px;
            --sidebar-collapsed-width: 72px;
        }

        html {
            font-size: 14px;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(180deg, #f8fafc 0%, #eff4fb 100%);
            color: var(--admin-text);
        }

        .layout {
            min-height: 100vh;
        }

        .sidebar {
            width: var(--sidebar-width);
            background: rgba(255, 255, 255, 0.96);
            border-right: 1px solid var(--admin-border);
            box-shadow: 8px 0 24px rgba(15, 23, 42, 0.04);
            transition: width 0.22s ease, transform 0.22s ease;
            z-index: 40;
        }

        .sidebar-header {
            padding: 18px 16px;
            border-bottom: 1px solid var(--admin-border);
            gap: 10px;
            min-height: 76px;
        }

        .logo-badge {
            width: 20px;
            height: 20px;
            border-radius: 6px;
            background: linear-gradient(135deg, #2f6df6, #4c8bff);
            position: relative;
            flex-shrink: 0;
        }

        .logo-badge::before,
        .logo-badge::after {
            content: "";
            position: absolute;
            background: rgba(255, 255, 255, 0.96);
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
            font-size: 1rem;
            font-weight: 800;
            color: #202633;
            white-space: nowrap;
        }

        .sidebar-menu {
            padding: 14px 10px;
        }

        .sidebar-menu ul {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .sidebar-menu li {
            border-radius: 13px;
            padding: 11px 14px;
            color: #415066;
            font-weight: 600;
            font-size: 0.95rem;
            transition: background 0.2s ease, transform 0.2s ease, color 0.2s ease;
        }

        .sidebar-menu li .material-icons {
            color: var(--admin-primary);
            font-size: 20px;
        }

        .sidebar-menu li.active {
            background: var(--admin-primary-soft);
            color: #2151c8;
        }

        .sidebar-menu li:not(.active):hover {
            background: #f8fafc;
            transform: translateX(2px);
        }

        .sidebar-footer {
            padding: 12px 10px 14px;
            border-top: 1px solid var(--admin-border);
        }

        .main {
            background:
                radial-gradient(circle at top left, rgba(47, 109, 246, 0.06), transparent 18%),
                linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        }

        .navbar {
            background: rgba(255, 255, 255, 0.86);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.92);
            justify-content: space-between;
            padding: 14px 20px;
            position: sticky;
            top: 0;
            z-index: 20;
        }

        .navbar-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .navbar-title {
            font-size: 1rem;
            font-weight: 800;
            color: #1f2937;
            line-height: 1.2;
        }

        .navbar-subtitle {
            color: var(--admin-muted);
            font-size: 0.85rem;
            line-height: 1.25;
        }

        .btn-icon {
            background: transparent;
            border: 0;
            box-shadow: none;
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-icon .material-icons {
            color: var(--admin-primary);
            font-size: 1.35rem;
        }

        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logout-link {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            border-radius: 999px;
            background: #fff;
            color: var(--admin-danger);
            border: 1px solid #fecaca;
            padding: 9px 14px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.92rem;
        }

        .content {
            padding: 20px;
        }

        .page-shell {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .panel-card {
            background: var(--admin-surface);
            border: 1px solid var(--admin-border);
            border-radius: 20px;
            box-shadow: var(--admin-shadow);
            padding: 20px 22px;
        }

        .hero-card h1,
        .section-title {
            margin: 0 0 6px;
            font-size: 1.25rem;
            font-weight: 800;
            color: #202633;
            line-height: 1.2;
        }

        .hero-card p,
        .section-caption,
        .metric-copy,
        .table-note {
            margin: 0;
            color: var(--admin-muted);
            line-height: 1.5;
            font-size: 0.92rem;
        }

        .hero-actions {
            margin-top: 16px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .primary-chip,
        .secondary-chip {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            border-radius: 13px;
            padding: 10px 14px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.92rem;
        }

        .primary-chip {
            background: linear-gradient(135deg, #2f6df6, #4391ff);
            color: #fff;
            box-shadow: 0 10px 20px rgba(47, 109, 246, 0.18);
        }

        .secondary-chip {
            background: #fff;
            color: #23408f;
            border: 1px solid #d8e4ff;
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin-top: 18px;
        }

        .metric-card {
            background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
            border: 1px solid var(--admin-border);
            border-radius: 18px;
            padding: 18px;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.04);
        }

        .metric-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        .metric-icon {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--admin-primary-soft);
            color: var(--admin-primary);
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .metric-label {
            color: var(--admin-muted);
            font-size: 0.88rem;
            margin-bottom: 4px;
        }

        .metric-value {
            font-size: 1.8rem;
            line-height: 1;
            font-weight: 800;
            color: #202633;
            margin-bottom: 8px;
        }

        .split-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.45fr) minmax(280px, 0.55fr);
            gap: 16px;
        }

        .mini-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 16px;
        }

        .mini-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px;
            border-radius: 16px;
            border: 1px solid var(--admin-border);
            background: #fff;
        }

        .mini-badge {
            width: 34px;
            height: 34px;
            border-radius: 11px;
            background: var(--admin-primary-soft);
            color: var(--admin-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            flex-shrink: 0;
            font-size: 0.95rem;
        }

        .mini-item strong,
        .table-title {
            display: block;
            margin-bottom: 4px;
            color: #202633;
            font-size: 0.94rem;
        }

        .table-responsive {
            margin-top: 18px;
            border: 1px solid var(--admin-border);
            border-radius: 16px;
            overflow: auto;
        }

        .table {
            margin-bottom: 0;
            min-width: 560px;
        }

        .table thead th {
            background: #f8fafc;
            color: #5b6472;
            border-bottom-color: var(--admin-border);
            font-size: 0.74rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 12px 14px;
        }

        .table tbody td {
            padding: 12px 14px;
            font-size: 0.9rem;
            vertical-align: top;
        }

        .table tbody tr:last-child td {
            border-bottom: 0;
        }

        .role-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 6px 11px;
            font-size: 0.75rem;
            font-weight: 700;
            white-space: nowrap;
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
            padding: 28px 16px;
        }

        body.sidebar-collapsed .sidebar {
            width: var(--sidebar-collapsed-width);
        }

        body.sidebar-collapsed .logo-text,
        body.sidebar-collapsed .link-text {
            display: none;
        }

        body.sidebar-collapsed .sidebar-menu li {
            justify-content: center;
            padding-inline: 10px;
        }

        body.sidebar-collapsed .sidebar-header,
        body.sidebar-collapsed .sidebar-footer {
            justify-content: center;
        }

        body.sidebar-collapsed .sidebar-menu li .material-icons {
            margin-right: 0;
        }

        @media (max-width: 1180px) {
            .metrics-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .split-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 860px) {
            .sidebar {
                transform: translateX(-100%);
                width: min(82vw, 260px);
                position: fixed;
                inset: 0 auto 0 0;
            }

            body.sidebar-open .sidebar {
                transform: translateX(0);
            }

            .main {
                width: 100%;
            }

            .navbar {
                padding: 12px 16px;
            }

            .content {
                padding: 16px;
            }

            .panel-card {
                padding: 18px 16px;
                border-radius: 18px;
            }

            .metrics-grid {
                grid-template-columns: 1fr;
            }

            .navbar-actions .navbar-subtitle {
                display: none;
            }

            .logout-link {
                padding: 8px 10px;
            }
        }

        @media (max-width: 560px) {
            html {
                font-size: 13px;
            }

            .hero-actions {
                flex-direction: column;
            }

            .primary-chip,
            .secondary-chip {
                width: 100%;
                justify-content: center;
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
                <button class="btn-icon" type="button" id="collapseSidebarBtn" aria-label="Contraer menú">
                    <span class="material-icons" id="collapseIcon">chevron_left</span>
                </button>
            </div>
        </aside>

        <div class="main">
            <header class="navbar">
                <div class="navbar-left">
                    <button class="btn-icon" type="button" id="toggleSidebarBtn" aria-label="Abrir menú">
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
                        <p>Estás viendo el panel interno de administración de Impulsa. Esta vista quedó organizada con menú lateral, métricas y una tabla central para revisar los accesos guardados en la tabla <code>auth</code>.</p>
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

                        <div class="metrics-grid">
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
                            <p class="section-caption">El menú lateral quedó fijo en un único estilo visual y adaptado a pantallas chicas.</p>

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
                                        <span class="table-note">Espacio disponible para sumar nuevos módulos.</span>
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

    <script>
        const body = document.body;
        const sidebar = document.getElementById('sidebar');
        const collapseButton = document.getElementById('collapseSidebarBtn');
        const collapseIcon = document.getElementById('collapseIcon');
        const toggleSidebarButton = document.getElementById('toggleSidebarBtn');
        const mobileBreakpoint = window.matchMedia('(max-width: 860px)');

        function syncSidebarState() {
            if (mobileBreakpoint.matches) {
                body.classList.remove('sidebar-collapsed');
                collapseIcon.textContent = 'chevron_left';
                return;
            }

            collapseIcon.textContent = body.classList.contains('sidebar-collapsed')
                ? 'chevron_right'
                : 'chevron_left';
        }

        collapseButton.addEventListener('click', () => {
            if (mobileBreakpoint.matches) {
                body.classList.remove('sidebar-open');
                return;
            }

            body.classList.toggle('sidebar-collapsed');
            syncSidebarState();
        });

        toggleSidebarButton.addEventListener('click', () => {
            if (mobileBreakpoint.matches) {
                body.classList.toggle('sidebar-open');
                return;
            }

            body.classList.toggle('sidebar-collapsed');
            syncSidebarState();
        });

        document.addEventListener('click', (event) => {
            if (!mobileBreakpoint.matches || !body.classList.contains('sidebar-open')) {
                return;
            }

            if (sidebar.contains(event.target) || toggleSidebarButton.contains(event.target)) {
                return;
            }

            body.classList.remove('sidebar-open');
        });

        mobileBreakpoint.addEventListener('change', () => {
            body.classList.remove('sidebar-open');
            syncSidebarState();
        });

        syncSidebarState();
    </script>
</body>

</html>
