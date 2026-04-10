<?php
require_once __DIR__ . '/../../controllers/admin_calificacionesController.php';

$administrador = $viewData['administrador'] ?? [];
$usuarioSesion = $viewData['usuarioSesion'] ?? 'Administrador';
$pageTitle = $viewData['pageTitle'] ?? 'Calificaciones';
$pageSubtitle = $viewData['pageSubtitle'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> | Panel de administración</title>

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
            --admin-surface: #ffffff;
            --admin-border: #e5ebf4;
            --admin-text: #1f2937;
            --admin-muted: #67768a;
            --admin-primary: #2f6df6;
            --admin-primary-soft: #eaf1ff;
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
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
        }

        .sidebar-menu li .material-icons {
            color: var(--admin-primary);
            font-size: 20px;
        }

        .sidebar-menu li.active {
            background: var(--admin-primary-soft);
            color: #2151c8;
        }

        .main {
            flex: 1 1 auto;
            min-width: 0;
            width: calc(100% - var(--sidebar-width));
            max-width: calc(100% - var(--sidebar-width));
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
            cursor: pointer;
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
            color: #ef4444;
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
            background: var(--admin-surface);
            border: 1px solid var(--admin-border);
            border-radius: 20px;
            box-shadow: var(--admin-shadow);
            padding: 24px;
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
        .section-caption {
            margin: 0;
            color: var(--admin-muted);
            line-height: 1.5;
            font-size: 0.92rem;
        }

        .empty-state {
            min-height: 320px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            border: 1px dashed #cdd9ee;
            border-radius: 18px;
            background: linear-gradient(180deg, #fbfdff 0%, #f6f9ff 100%);
            padding: 32px 20px;
        }

        .empty-state-box {
            max-width: 460px;
        }

        .empty-state-icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 18px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--admin-primary-soft);
            color: var(--admin-primary);
        }

        .empty-state-icon .material-icons {
            font-size: 34px;
        }

        .empty-state h2 {
            margin: 0 0 10px;
            font-size: 1.2rem;
            font-weight: 800;
            color: #202633;
        }

        .empty-state p {
            margin: 0;
            color: var(--admin-muted);
            line-height: 1.6;
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

        body.sidebar-collapsed .sidebar-header {
            justify-content: center;
            padding-inline: 10px;
        }

        body.sidebar-collapsed .sidebar-menu li {
            justify-content: center;
            padding-inline: 10px;
        }

        @media (max-width: 860px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                transform: translateX(-100%);
            }

            body.sidebar-open .sidebar {
                transform: translateX(0);
            }

            .main,
            body.sidebar-collapsed .main {
                width: 100%;
                max-width: 100%;
            }

            .navbar {
                padding-inline: 16px;
            }

            .content {
                padding: 16px;
            }

            .navbar-actions .navbar-subtitle {
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
                    <li onclick="location.href='admin_dashboard.php'">
                        <span class="material-icons">home</span>
                        <span class="link-text">Inicio</span>
                    </li>
                    <li class="active" onclick="location.href='admin_calificaciones.php'">
                        <span class="material-icons">table_chart</span>
                        <span class="link-text">Calificaciones</span>
                    </li>
                    <li onclick="location.href='../../logout.php'">
                        <span class="material-icons">logout</span>
                        <span class="link-text">Salir</span>
                    </li>
                </ul>
            </nav>
        </aside>

        <div class="main">
            <header class="navbar">
                <div class="navbar-left">
                    <button type="button" class="btn-icon" id="toggleSidebarBtn" aria-label="Mostrar menú lateral">
                        <span class="material-icons">menu</span>
                    </button>
                    <button type="button" class="btn-icon" id="collapseSidebarBtn" aria-label="Colapsar menú lateral">
                        <span class="material-icons" id="collapseIcon">chevron_left</span>
                    </button>
                    <div>
                        <div class="navbar-title"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="navbar-subtitle"><?= htmlspecialchars($pageSubtitle, ENT_QUOTES, 'UTF-8') ?></div>
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

            <main class="content">
                <div class="page-shell">
                    <section class="panel-card hero-card">
                        <h1><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h1>
                        <p><?= htmlspecialchars($pageSubtitle, ENT_QUOTES, 'UTF-8') ?></p>
                    </section>

                    <section class="panel-card">
                        <div class="empty-state">
                            <div class="empty-state-box">
                                <div class="empty-state-icon" aria-hidden="true">
                                    <span class="material-icons">fact_check</span>
                                </div>
                                <h2>Módulo en preparación</h2>
                                <p>La estructura base ya quedó separada en modelo, vista y controlador. En el próximo paso podemos montar el formulario de calificaciones y el bloque para visualizar resultados.</p>
                            </div>
                        </div>
                    </section>
                </div>
            </main>
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
                if (collapseIcon) {
                    collapseIcon.textContent = 'chevron_left';
                }
                return;
            }

            if (collapseIcon) {
                collapseIcon.textContent = body.classList.contains('sidebar-collapsed')
                    ? 'chevron_right'
                    : 'chevron_left';
            }
        }

        collapseButton?.addEventListener('click', () => {
            if (mobileBreakpoint.matches) {
                body.classList.remove('sidebar-open');
                return;
            }

            body.classList.toggle('sidebar-collapsed');
            syncSidebarState();
        });

        toggleSidebarButton?.addEventListener('click', () => {
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

            if ((sidebar && sidebar.contains(event.target)) || (toggleSidebarButton && toggleSidebarButton.contains(event.target))) {
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
            root.dataset.themeAccent = 'indigo';
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
