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

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="https://framework.impulsagroup.com/assets/css/framework.css">
    <script src="https://framework.impulsagroup.com/assets/javascript/framework.js" defer></script>

    <style>
        @font-face {
            font-family: 'Montserrat';
            src: url('../../assets/institucionales/fonts/Montserrat/Montserrat-VariableFont_wght.ttf') format('truetype');
            font-weight: 100 900;
            font-style: normal;
            font-display: swap;
        }

        :root {
            --panel-surface: #ffffff;
            --panel-bg: #f6f7fb;
            --panel-border: #e8ecf4;
            --panel-text: #1f2937;
            --panel-muted: #6a7688;
            --panel-primary: #e4a800;
            --panel-primary-soft: #fff6d9;
            --panel-danger: #ef4444;
            --panel-shadow: 0 10px 28px rgba(15, 23, 42, 0.08);
            --sidebar-width: 208px;
            --sidebar-collapsed-width: 72px;
        }

        html {
            font-size: 14px;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(180deg, #fffdf7 0%, #f7f8fc 100%);
            color: var(--panel-text);
            overflow-x: hidden;
        }

        .theme-settings-btn,
        .theme-drawer,
        .theme-settings-overlay {
            display: none !important;
            visibility: hidden !important;
            pointer-events: none !important;
        }

        .layout {
            min-height: 100vh;
            display: flex !important;
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }

        .sidebar {
            width: var(--sidebar-width);
            background: rgba(255, 255, 255, 0.96);
            border-right: 1px solid var(--panel-border);
            box-shadow: 8px 0 24px rgba(15, 23, 42, 0.04);
            transition: width 0.22s ease, transform 0.22s ease;
            z-index: 40;
        }

        .sidebar-header {
            padding: 18px 16px;
            border-bottom: 1px solid var(--panel-border);
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
            color: var(--panel-primary);
            font-size: 20px;
        }

        .sidebar-menu li.active {
            background: var(--panel-primary-soft);
            color: #b77900;
        }

        .sidebar-menu li:not(.active):hover {
            background: #fffaf0;
            transform: translateX(2px);
        }

        .sidebar-footer {
            padding: 12px 10px 14px;
            border-top: 1px solid var(--panel-border);
        }

        .main {
            flex: 1 1 auto;
            min-width: 0;
            width: calc(100% - var(--sidebar-width));
            max-width: calc(100% - var(--sidebar-width));
            background:
                radial-gradient(circle at top left, rgba(228, 168, 0, 0.07), transparent 18%),
                linear-gradient(180deg, #fffdf7 0%, #f8fafc 100%);
        }

        .navbar {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(232, 236, 244, 0.96);
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
            color: var(--panel-muted);
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
            color: var(--panel-primary);
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
            color: var(--panel-danger);
            border: 1px solid #fecaca;
            padding: 9px 14px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.92rem;
        }

        .content {
            padding: 20px;
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }

        .page-shell {
            display: flex;
            flex-direction: column;
            gap: 20px;
            width: 100%;
            max-width: 100%;
        }

        .panel-card {
            background: var(--panel-surface);
            border: 1px solid var(--panel-border);
            border-radius: 20px;
            box-shadow: var(--panel-shadow);
            padding: 20px 22px;
            width: 100%;
            max-width: 100%;
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
            color: var(--panel-muted);
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
            background: linear-gradient(135deg, #e4a800, #f3c23d);
            color: #3a2b00;
            box-shadow: 0 10px 20px rgba(228, 168, 0, 0.18);
        }

        .secondary-chip {
            background: #fff;
            color: #9a6c00;
            border: 1px solid #f5dd96;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin-top: 18px;
        }

        .stat-card {
            background: linear-gradient(180deg, #ffffff 0%, #fffef8 100%);
            border: 1px solid var(--panel-border);
            border-radius: 18px;
            padding: 18px;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.04);
        }

        .stat-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        .stat-icon {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--panel-primary-soft);
            color: #b77900;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .stat-label {
            color: var(--panel-muted);
            font-size: 0.88rem;
            margin-bottom: 4px;
        }

        .stat-value {
            font-size: 1.2rem;
            line-height: 1.25;
            font-weight: 800;
            color: #202633;
            word-break: break-word;
        }

        .split-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(280px, 0.85fr);
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
            border: 1px solid var(--panel-border);
            background: #fff;
        }

        .mini-badge {
            width: 34px;
            height: 34px;
            border-radius: 11px;
            background: var(--panel-primary-soft);
            color: #b77900;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            flex-shrink: 0;
            font-size: 0.95rem;
        }

        .mini-item strong {
            display: block;
            margin-bottom: 4px;
            color: #202633;
            font-size: 0.94rem;
        }

        .info-strip {
            margin-top: 18px;
            border-radius: 16px;
            border: 1px solid #f6e4a6;
            background: #fffdf4;
            padding: 15px 16px;
            color: #7c5a00;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        body.sidebar-collapsed .sidebar {
            width: var(--sidebar-collapsed-width);
        }

        body.sidebar-collapsed .main {
            width: calc(100% - var(--sidebar-collapsed-width));
            max-width: calc(100% - var(--sidebar-collapsed-width));
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
            .stats-grid,
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
                width: 100% !important;
                max-width: 100% !important;
                min-width: 0;
                margin-left: 0;
            }

            .navbar {
                padding: 12px 16px;
                width: 100%;
                max-width: 100%;
            }

            .content {
                padding: 16px;
            }

            .panel-card {
                padding: 18px 16px;
                border-radius: 18px;
            }

            .stats-grid {
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
                    <li class="active" onclick="location.href='jurado_dashboard.php'">
                        <span class="material-icons">home</span>
                        <span class="link-text">Inicio</span>
                    </li>
                    <li>
                        <span class="material-icons">gavel</span>
                        <span class="link-text">Evaluación</span>
                    </li>
                    <li>
                        <span class="material-icons">fact_check</span>
                        <span class="link-text">Criterios</span>
                    </li>
                    <li>
                        <span class="material-icons">inventory_2</span>
                        <span class="link-text">Postulaciones</span>
                    </li>
                    <li>
                        <span class="material-icons">assignment_turned_in</span>
                        <span class="link-text">Resultados</span>
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
                        <div class="navbar-subtitle">Panel de jurado</div>
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
                        <h1>Bienvenido al panel de jurado</h1>
                        <p>Ingresaste con un código de acceso válido. Esta pantalla mantiene la misma estructura visual del panel admin para que toda la experiencia del sistema sea consistente.</p>
                        <div class="hero-actions">
                            <a href="#datos" class="primary-chip">
                                <span class="material-icons">visibility</span>
                                <span>Ver datos</span>
                            </a>
                            <a href="#acceso" class="secondary-chip">
                                <span class="material-icons">vpn_key</span>
                                <span>Ver acceso</span>
                            </a>
                        </div>
                    </div>

                    <div class="panel-card" id="datos">
                        <h2 class="section-title">Datos de la sesión</h2>
                        <p class="section-caption">Información básica del usuario autenticado con rol <code>impulsa_jurado</code>.</p>

                        <div class="stats-grid">
                            <article class="stat-card">
                                <div class="stat-head">
                                    <div>
                                        <div class="stat-label">ID</div>
                                        <div class="stat-value"><?= (int) ($jurado['id'] ?? 0) ?></div>
                                    </div>
                                    <span class="stat-icon material-icons">badge</span>
                                </div>
                                <p class="metric-copy">Identificador interno del registro guardado en la tabla <code>auth</code>.</p>
                            </article>

                            <article class="stat-card">
                                <div class="stat-head">
                                    <div>
                                        <div class="stat-label">Usuario</div>
                                        <div class="stat-value"><?= htmlspecialchars($usuarioSesion, ENT_QUOTES, 'UTF-8') ?></div>
                                    </div>
                                    <span class="stat-icon material-icons">person</span>
                                </div>
                                <p class="metric-copy">Nombre de usuario asociado al código de acceso que validó el ingreso.</p>
                            </article>

                            <article class="stat-card">
                                <div class="stat-head">
                                    <div>
                                        <div class="stat-label">Rol</div>
                                        <div class="stat-value"><?= htmlspecialchars((string) ($_SESSION['rol'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                    </div>
                                    <span class="stat-icon material-icons">verified_user</span>
                                </div>
                                <p class="metric-copy">Perfil habilitado para revisar evaluaciones y operar como jurado.</p>
                            </article>
                        </div>
                    </div>

                    <div class="split-grid">
                        <div class="panel-card" id="acceso">
                            <h2 class="section-title">Acceso validado</h2>
                            <p class="section-caption">Este ingreso no depende de usuario y contraseña, sino de la coincidencia con un <code>codigo_acceso</code> válido almacenado en la base.</p>

                            <div class="info-strip">
                                El sistema compara el código ingresado contra los hashes guardados en la tabla <code>auth</code>. Si encuentra una coincidencia para el rol <code>impulsa_jurado</code>, se genera la sesión y se habilita el acceso a este panel.
                            </div>

                            <div class="info-strip">
                                Fecha de creación del registro:
                                <strong><?= htmlspecialchars((string) ($jurado['creado_en'] ?? 'Sin fecha disponible'), ENT_QUOTES, 'UTF-8') ?></strong>
                            </div>
                        </div>

                        <aside class="panel-card">
                            <h2 class="section-title">Cómo navegar</h2>
                            <p class="section-caption">La estructura, la tipografía y la escala están alineadas con el dashboard de administración.</p>

                            <div class="mini-list">
                                <div class="mini-item">
                                    <span class="mini-badge">1</span>
                                    <div>
                                        <strong>Inicio</strong>
                                        <span class="table-note">Resumen rápido del estado actual de tu sesión.</span>
                                    </div>
                                </div>

                                <div class="mini-item">
                                    <span class="mini-badge">2</span>
                                    <div>
                                        <strong>Evaluación</strong>
                                        <span class="table-note">Espacio previsto para sumar formularios o rúbricas de análisis.</span>
                                    </div>
                                </div>

                                <div class="mini-item">
                                    <span class="mini-badge">3</span>
                                    <div>
                                        <strong>Resultados</strong>
                                        <span class="table-note">Zona disponible para mostrar dictámenes o estados finales.</span>
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

        function lockFrameworkTheme() {
            const root = document.documentElement;
            root.dataset.theme = 'light';
            root.dataset.themeMode = 'light';
            root.dataset.themeAccent = 'amber';
            root.dataset.themeSurface = 'solid';
            root.classList.add('theme-ready');

            [
                'impulsa_theme_mode',
                'impulsa_theme_accent',
                'impulsa_theme_surface',
                'impulsa_theme_motion'
            ].forEach((key) => localStorage.removeItem(key));

            document.getElementById('themeSettingsToggle')?.remove();
            document.getElementById('themeSettingsDrawer')?.remove();
            document.getElementById('themeSettingsOverlay')?.remove();
        }

        syncSidebarState();
        lockFrameworkTheme();
        window.addEventListener('load', lockFrameworkTheme);
    </script>
</body>

</html>
