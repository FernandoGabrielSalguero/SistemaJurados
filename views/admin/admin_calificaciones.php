<?php
require_once __DIR__ . '/../../controllers/admin_calificacionesController.php';

$administrador = $viewData['administrador'] ?? [];
$usuarioSesion = $viewData['usuarioSesion'] ?? 'Administrador';
$pageTitle = $viewData['pageTitle'] ?? 'Calificaciones';
$pageSubtitle = $viewData['pageSubtitle'] ?? '';
$criteriosBase = $viewData['criteriosBase'] ?? [];
$estadoTablas = $viewData['estadoTablas'] ?? ['formularios_listos' => false, 'evaluaciones_listas' => false, 'faltantes' => []];
$faltantesTablas = $viewData['faltantesTablas'] ?? [];
$formularios = $viewData['formularios'] ?? [];
$metricas = $viewData['metricas'] ?? [];
$mensaje = $viewData['mensaje'] ?? '';
$mensajeTipo = $viewData['mensajeTipo'] ?? 'success';
$formData = $viewData['formData'] ?? ['subcategoria' => '', 'categoria' => '', 'evento_nombre' => '', 'activo' => 1, 'puntajes' => []];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> | Panel de administracion</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link rel="stylesheet" href="https://framework.impulsagroup.com/assets/css/framework.css">
    <script src="https://framework.impulsagroup.com/assets/javascript/framework.js" defer></script>
    <style>
        @font-face { font-family:'Montserrat'; src:url('../../assets/institucionales/fonts/Montserrat/Montserrat-VariableFont_wght.ttf') format('truetype'); font-weight:100 900; font-style:normal; font-display:swap; }
        :root { --bg:#f5f8fd; --card:#fff; --border:#e5ebf4; --text:#1f2937; --muted:#67768a; --primary:#2f6df6; --primary-soft:#eaf1ff; --success:#15803d; --success-bg:#ecfdf3; --danger:#b91c1c; --danger-bg:#fef2f2; --warning:#c2410c; --warning-bg:#fff7ed; --shadow:0 10px 28px rgba(15,23,42,.08); --sidebar-width:208px; --sidebar-collapsed-width:72px; }
        * { box-sizing:border-box; }
        html { font-size:14px; }
        body { margin:0; font-family:'Montserrat',sans-serif; background:linear-gradient(180deg,#f8fafc 0%,#eff4fb 100%); color:var(--text); overflow-x:hidden; }
        .theme-settings-btn,.theme-drawer,.theme-settings-overlay { display:none !important; }
        .layout { min-height:100vh; display:flex; width:100%; max-width:100%; overflow-x:hidden; align-items:stretch; }
        .sidebar { flex:0 0 var(--sidebar-width); width:var(--sidebar-width); background:rgba(255,255,255,.96); border-right:1px solid var(--border); box-shadow:8px 0 24px rgba(15,23,42,.04); transition:width .22s ease, transform .22s ease; z-index:40; }
        .sidebar-header { padding:18px 16px; border-bottom:1px solid var(--border); min-height:76px; display:flex; align-items:center; gap:10px; }
        .logo-badge { width:20px; height:20px; border-radius:6px; background:linear-gradient(135deg,#2f6df6,#4c8bff); position:relative; flex-shrink:0; }
        .logo-badge::before,.logo-badge::after { content:""; position:absolute; background:rgba(255,255,255,.96); }
        .logo-badge::before { width:100%; height:2px; top:50%; left:0; transform:translateY(-50%); }
        .logo-badge::after { width:2px; height:100%; left:50%; top:0; transform:translateX(-50%); }
        .logo-text { font-size:1rem; font-weight:800; color:#202633; white-space:nowrap; }
        .sidebar-menu { padding:14px 10px; }
        .sidebar-menu ul { display:flex; flex-direction:column; gap:6px; margin:0; padding:0; list-style:none; }
        .sidebar-menu li { border-radius:13px; padding:11px 14px; color:#415066; font-weight:600; font-size:.95rem; display:flex; align-items:center; gap:12px; cursor:pointer; }
        .sidebar-menu li .material-icons { color:var(--primary); font-size:20px; }
        .sidebar-menu li.active { background:var(--primary-soft); color:#2151c8; }
        .main { flex:1 1 auto; min-width:0; width:auto; max-width:none; background:radial-gradient(circle at top left, rgba(47,109,246,.06), transparent 18%), linear-gradient(180deg,#f8fafc 0%,#f1f5f9 100%); overflow-x:hidden; }
        .navbar { background:rgba(255,255,255,.86); backdrop-filter:blur(14px); border-bottom:1px solid rgba(226,232,240,.92); display:flex; justify-content:space-between; padding:14px 20px; position:sticky; top:0; z-index:20; }
        .navbar-left,.navbar-actions { display:flex; align-items:center; gap:10px; }
        .navbar-title { font-size:1rem; font-weight:800; color:#1f2937; }
        .navbar-subtitle { color:var(--muted); font-size:.85rem; line-height:1.25; }
        .btn-icon { background:transparent; border:0; width:34px; height:34px; border-radius:10px; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; }
        .btn-icon .material-icons { color:var(--primary); font-size:1.35rem; }
        .logout-link { display:inline-flex; align-items:center; gap:7px; border-radius:999px; background:#fff; color:#ef4444; border:1px solid #fecaca; padding:9px 14px; text-decoration:none; font-weight:700; font-size:.92rem; }
        .content { padding:20px; width:100%; max-width:100%; min-width:0; overflow-x:hidden; }
        .page-shell { display:flex; flex-direction:column; gap:20px; width:100%; max-width:100%; min-width:0; }
        .panel-card { background:var(--card); border:1px solid var(--border); border-radius:20px; box-shadow:var(--shadow); padding:24px; width:100%; max-width:100%; min-width:0; }
        .hero-card h1,.section-title { margin:0 0 6px; font-size:1.25rem; font-weight:800; color:#202633; }
        .hero-card p,.section-caption { margin:0; color:var(--muted); line-height:1.5; font-size:.92rem; }
        .metrics-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; }
        .metric-card,.criterio-item,.guide-list li { border:1px solid var(--border); border-radius:18px; padding:16px; background:linear-gradient(180deg,#fff 0%,#f9fbff 100%); }
        .metric-label { color:var(--muted); font-size:.82rem; margin-bottom:6px; }
        .metric-value { font-size:1.8rem; font-weight:800; line-height:1; }
        .metric-help { margin-top:8px; color:var(--muted); font-size:.82rem; }
        .form-section { width:100%; }
        .alert-inline { margin-top:14px; padding:14px 16px; border-radius:16px; font-size:.92rem; line-height:1.5; border:1px solid #dbeafe; background:#eff6ff; color:#1d4ed8; }
        .alert-inline.success { background:var(--success-bg); color:var(--success); border-color:#bbf7d0; }
        .alert-inline.danger { background:var(--danger-bg); color:var(--danger); border-color:#fecaca; }
        .alert-inline.warning { background:var(--warning-bg); color:var(--warning); border-color:#fed7aa; }
        .form-grid,.criterios-grid { display:grid; gap:14px; margin-top:18px; }
        .form-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .criterios-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .form-field { display:flex; flex-direction:column; gap:7px; }
        .form-field.full { grid-column:1 / -1; }
        .form-field label { font-size:.84rem; font-weight:700; color:#334155; }
        .form-field input[type="text"], .form-field input[type="number"] { width:100%; min-height:44px; border-radius:14px; border:1px solid #d6dfef; padding:10px 14px; background:#fff; color:#111827; font-size:.95rem; }
        .criterio-item-title { font-weight:800; margin-bottom:4px; }
        .criterio-item-hint { color:var(--muted); font-size:.8rem; margin-bottom:10px; }
        .checkbox-field { display:inline-flex; align-items:center; gap:10px; margin-top:14px; font-weight:600; color:#334155; }
        .score-summary { margin-top:18px; display:flex; align-items:center; justify-content:space-between; gap:12px; border-radius:16px; padding:16px 18px; background:#0f172a; color:#fff; }
        .score-summary-label { font-size:.88rem; color:rgba(255,255,255,.72); }
        .score-summary-value { font-size:2rem; font-weight:800; line-height:1; }
        .score-summary-value.invalid { color:#fca5a5; }
        .form-actions { margin-top:18px; display:flex; justify-content:flex-end; }
        .btn-primary { border:0; border-radius:14px; background:linear-gradient(135deg,#2f6df6,#4c8bff); color:#fff; padding:12px 18px; font-weight:800; cursor:pointer; }
        .table-responsive { margin-top:18px; border:1px solid var(--border); border-radius:16px; width:100%; max-width:100%; overflow:auto; -webkit-overflow-scrolling:touch; }
        .table { width:100%; min-width:940px; border-collapse:collapse; }
        .table thead th { background:#f8fafc; color:#5b6472; border-bottom:1px solid var(--border); font-size:.74rem; text-transform:uppercase; letter-spacing:.04em; padding:12px 14px; text-align:left; }
        .table tbody td { padding:14px; font-size:.9rem; vertical-align:top; border-bottom:1px solid var(--border); }
        .table tbody tr:last-child td { border-bottom:0; }
        .status-pill,.criterio-chip { display:inline-flex; align-items:center; border-radius:999px; padding:6px 11px; font-size:.75rem; font-weight:700; white-space:nowrap; }
        .status-pill.active { background:#dcfce7; color:#166534; }
        .status-pill.inactive { background:#e5e7eb; color:#374151; }
        .criterios-listado { display:flex; flex-wrap:wrap; gap:8px; }
        .criterio-chip { background:#eef4ff; color:#2457cc; }
        .switch-field { display:inline-flex; align-items:center; gap:10px; }
        .switch-input { display:none; }
        .switch-label { position:relative; width:46px; height:26px; background:#d1d5db; border-radius:999px; cursor:pointer; }
        .switch-label::after { content:""; position:absolute; top:3px; left:3px; width:20px; height:20px; border-radius:50%; background:#fff; transition:transform .2s ease; }
        .switch-input:checked + .switch-label { background:#60a5fa; }
        .switch-input:checked + .switch-label::after { transform:translateX(20px); }
        .switch-state { font-size:.82rem; font-weight:700; }
        .switch-state.enabled { color:#166534; }
        .switch-state.disabled { color:#6b7280; }
        .empty-state { min-height:240px; display:flex; align-items:center; justify-content:center; text-align:center; border:1px dashed #cdd9ee; border-radius:18px; background:linear-gradient(180deg,#fbfdff 0%,#f6f9ff 100%); padding:32px 20px; }
        .empty-state-box { max-width:480px; }
        .empty-state-icon { width:72px; height:72px; margin:0 auto 18px; border-radius:20px; display:inline-flex; align-items:center; justify-content:center; background:var(--primary-soft); color:var(--primary); }
        .empty-state-icon .material-icons { font-size:34px; }
        .empty-state h2 { margin:0 0 10px; font-size:1.2rem; font-weight:800; }
        .empty-state p { margin:0; color:var(--muted); line-height:1.6; }
        @media (max-width:1180px) { .metrics-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } .criterios-grid,.form-grid { grid-template-columns:1fr; } .table { min-width:820px; } }
        @media (max-width:860px) { .sidebar { position:fixed; top:0; left:0; bottom:0; transform:translateX(-100%); flex-basis:min(82vw,260px); width:min(82vw,260px); } body.sidebar-open .sidebar { transform:translateX(0); } .main { width:100%; max-width:100%; } .navbar { padding-inline:16px; flex-wrap:wrap; gap:12px; } .navbar-left,.navbar-actions { width:100%; } .navbar-actions { justify-content:space-between; } .content { padding:16px; } .panel-card { padding:18px; } .metrics-grid { grid-template-columns:1fr; } .score-summary { flex-direction:column; align-items:flex-start; } .form-actions { justify-content:stretch; } .btn-primary { width:100%; } .table { min-width:720px; } .navbar-actions .navbar-subtitle { display:none; } }
        @media (max-width:560px) { .content { padding:12px; } .panel-card { padding:16px; border-radius:16px; } .navbar { padding:12px; } .logout-link { padding:8px 12px; } .score-summary-value { font-size:1.6rem; } .table { min-width:640px; } }
    </style>
</head>
<body>
    <div class="layout">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header"><span class="logo-badge" aria-hidden="true"></span><span class="logo-text">Impulsa</span></div>
            <nav class="sidebar-menu">
                <ul>
                    <li onclick="location.href='admin_dashboard.php'"><span class="material-icons">home</span><span class="link-text">Inicio</span></li>
                    <li class="active" onclick="location.href='admin_calificaciones.php'"><span class="material-icons">fact_check</span><span class="link-text">Calificaciones</span></li>
                    <li onclick="location.href='admin_resultados.php'"><span class="material-icons">analytics</span><span class="link-text">Resultados</span></li>
                </ul>
            </nav>
        </aside>
        <div class="main">
            <header class="navbar">
                <div class="navbar-left">
                    <button type="button" class="btn-icon" id="toggleSidebarBtn" aria-label="Mostrar menu lateral"><span class="material-icons">menu</span></button>
                    <div>
                        <div class="navbar-title"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="navbar-subtitle"><?= htmlspecialchars($pageSubtitle, ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                </div>
                <div class="navbar-actions">
                    <div class="navbar-subtitle"><?= htmlspecialchars($usuarioSesion, ENT_QUOTES, 'UTF-8') ?></div>
                    <a href="../../logout.php" class="logout-link"><span class="material-icons">logout</span><span>Salir</span></a>
                </div>
            </header>
            <main class="content">
                <div class="page-shell">
                    <section class="panel-card hero-card">
                        <h1><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h1>
                        <p><?= htmlspecialchars($pageSubtitle, ENT_QUOTES, 'UTF-8') ?></p>
                        <?php if ($mensaje !== ''): ?><div class="alert-inline <?= htmlspecialchars($mensajeTipo, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                        <?php if ($faltantesTablas): ?><div class="alert-inline warning">Todavia faltan tablas del modulo: <strong><?= htmlspecialchars(implode(', ', $faltantesTablas), ENT_QUOTES, 'UTF-8') ?></strong>. El SQL sugerido ya quedo documentado en <code>assets/estructura_base_datos.md</code>.</div><?php endif; ?>
                    </section>

                    <section class="metrics-grid">
                        <article class="metric-card"><div class="metric-label">Formularios creados</div><div class="metric-value"><?= (int) ($metricas['formularios_total'] ?? 0) ?></div><div class="metric-help">Formularios configurados por administracion.</div></article>
                        <article class="metric-card"><div class="metric-label">Formularios activos</div><div class="metric-value"><?= (int) ($metricas['formularios_activos'] ?? 0) ?></div><div class="metric-help">Disponibles para jurados.</div></article>
                        <article class="metric-card"><div class="metric-label">Categorias cubiertas</div><div class="metric-value"><?= (int) ($metricas['categorias_total'] ?? 0) ?></div><div class="metric-help">Categorias con configuracion propia.</div></article>
                        <article class="metric-card"><div class="metric-label">Evaluaciones guardadas</div><div class="metric-value"><?= (int) ($metricas['evaluaciones_total'] ?? 0) ?></div><div class="metric-help">Se completara en la vista del jurado.</div></article>
                    </section>

                    <section class="panel-card form-section">
                            <h2 class="section-title">Crear formulario de calificacion</h2>
                            <p class="section-caption">El administrador define el estilo, la categoria, el evento y el puntaje maximo de cada criterio del formulario. La suma total siempre debe ser 100.</p>
                            <form method="post" id="formularioCalificacion">
                                <input type="hidden" name="guardar_formulario" value="1">
                                <div class="form-grid">
                                    <div class="form-field full">
                                        <label for="evento_nombre">Nombre del evento</label>
                                        <input type="text" id="evento_nombre" name="evento_nombre" value="<?= htmlspecialchars((string) ($formData['evento_nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Ej. Campeonato Nacional 2026" required>
                                    </div>
                                    <div class="form-field">
                                        <label for="subcategoria">Estilo</label>
                                        <input type="text" id="subcategoria" name="subcategoria" value="<?= htmlspecialchars((string) ($formData['subcategoria'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Ej. Malambo escenico" required>
                                    </div>
                                    <div class="form-field">
                                        <label for="categoria">Categoria</label>
                                        <input type="text" id="categoria" name="categoria" value="<?= htmlspecialchars((string) ($formData['categoria'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Ej. Adultos parejas" required>
                                    </div>
                                </div>
                                <div class="criterios-grid">
                                    <?php foreach ($criteriosBase as $criterio): ?>
                                        <?php $clave = (string) ($criterio['clave'] ?? ''); ?>
                                        <div class="criterio-item">
                                            <div class="criterio-item-title"><?= htmlspecialchars((string) ($criterio['nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                            <div class="criterio-item-hint">Rango disponible para el desplegable del jurado.</div>
                                            <div class="form-field">
                                                <label for="puntaje_<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>">Puntaje maximo</label>
                                                <input class="criterio-puntaje" type="number" min="0" step="1" id="puntaje_<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>" name="puntajes[<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>]" value="<?= htmlspecialchars((string) ($formData['puntajes'][$clave] ?? '0'), ENT_QUOTES, 'UTF-8') ?>" required>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <label class="checkbox-field"><input type="checkbox" name="activo" value="1" <?= (int) ($formData['activo'] ?? 0) === 1 ? 'checked' : '' ?>><span>Dejar este formulario activo al guardarlo</span></label>
                                <div class="score-summary">
                                    <div><div class="score-summary-label">Suma total de puntos</div><div class="score-summary-label">Debe cerrar exactamente en 100.</div></div>
                                    <div class="score-summary-value" id="scoreSummaryValue">0</div>
                                </div>
                                    <div class="form-actions"><button type="submit" class="btn-primary">Guardar formulario</button></div>
                            </form>
                    </section>

                    <section class="panel-card">
                        <h2 class="section-title">Formularios de calificacion creados</h2>
                        <p class="section-caption">Listado de formularios ya disponibles para usar en el modulo de jurados.</p>
                        <?php if ($formularios): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr><th>ID</th><th>Estilo</th><th>Evento</th><th>Categoria</th><th>Criterios</th><th>Total</th><th>Evaluaciones</th><th>Estado</th><th>Accion</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($formularios as $formulario): ?>
                                            <tr>
                                                <td><?= (int) ($formulario['id'] ?? 0) ?></td>
                                                <td><strong><?= htmlspecialchars((string) ($formulario['subcategoria'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong><br><span class="section-caption">Creado por <?= htmlspecialchars((string) ($formulario['creador_usuario'] ?? 'Administrador'), ENT_QUOTES, 'UTF-8') ?></span></td>
                                                <td><?= htmlspecialchars((string) ($formulario['evento_nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars((string) ($formulario['categoria'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><div class="criterios-listado"><?php foreach (($formulario['criterios'] ?? []) as $criterio): ?><span class="criterio-chip"><?= htmlspecialchars((string) ($criterio['criterio_nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?> <?= (int) ($criterio['puntaje_maximo'] ?? 0) ?></span><?php endforeach; ?></div></td>
                                                <td><strong><?= (int) ($formulario['puntaje_total'] ?? 0) ?></strong> pts</td>
                                                <td><?= (int) ($formulario['total_evaluaciones'] ?? 0) ?></td>
                                                <td><span class="status-pill <?= (int) ($formulario['activo'] ?? 0) === 1 ? 'active' : 'inactive' ?>"><?= (int) ($formulario['activo'] ?? 0) === 1 ? 'Activo' : 'Inactivo' ?></span></td>
                                                <td>
                                                    <form method="post">
                                                        <input type="hidden" name="toggle_formulario_id" value="<?= (int) ($formulario['id'] ?? 0) ?>">
                                                        <div class="switch-field">
                                                            <input class="switch-input" type="checkbox" id="formulario_activo_<?= (int) ($formulario['id'] ?? 0) ?>" name="formulario_activo" <?= (int) ($formulario['activo'] ?? 0) === 1 ? 'checked' : '' ?> onchange="this.form.submit()">
                                                            <label class="switch-label" for="formulario_activo_<?= (int) ($formulario['id'] ?? 0) ?>"></label>
                                                            <span class="switch-state <?= (int) ($formulario['activo'] ?? 0) === 1 ? 'enabled' : 'disabled' ?>"><?= (int) ($formulario['activo'] ?? 0) === 1 ? 'Disponible' : 'Oculto' ?></span>
                                                        </div>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-state-box">
                                    <div class="empty-state-icon" aria-hidden="true"><span class="material-icons">playlist_add_check</span></div>
                                    <h2>No hay formularios creados todavia</h2>
                                    <p>Una vez que ejecutes las tablas nuevas y guardes el primer formulario, lo vas a ver listado aca con sus criterios, total y estado.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </section>
                </div>
            </main>
        </div>
    </div>

    <script>
        const body = document.body;
        const sidebar = document.getElementById('sidebar');
        const toggleSidebarButton = document.getElementById('toggleSidebarBtn');
        const mobileBreakpoint = window.matchMedia('(max-width: 860px)');
        const scoreInputs = document.querySelectorAll('.criterio-puntaje');
        const scoreSummaryValue = document.getElementById('scoreSummaryValue');

        function updateScoreSummary() {
            let total = 0;
            scoreInputs.forEach((input) => {
                const value = parseInt(input.value || '0', 10);
                total += Number.isNaN(value) ? 0 : value;
            });
            if (scoreSummaryValue) {
                scoreSummaryValue.textContent = String(total);
                scoreSummaryValue.classList.toggle('invalid', total !== 100);
            }
        }

        toggleSidebarButton?.addEventListener('click', () => {
            if (mobileBreakpoint.matches) { body.classList.toggle('sidebar-open'); return; }
        });

        document.addEventListener('click', (event) => {
            if (!mobileBreakpoint.matches || !body.classList.contains('sidebar-open')) return;
            if ((sidebar && sidebar.contains(event.target)) || (toggleSidebarButton && toggleSidebarButton.contains(event.target))) return;
            body.classList.remove('sidebar-open');
        });

        mobileBreakpoint.addEventListener('change', () => { body.classList.remove('sidebar-open'); });
        scoreInputs.forEach((input) => input.addEventListener('input', updateScoreSummary));

        function lockFrameworkTheme() {
            const root = document.documentElement;
            root.dataset.theme = 'light';
            root.dataset.themeMode = 'light';
            root.dataset.themeAccent = 'indigo';
            root.dataset.themeSurface = 'solid';
            root.classList.add('theme-ready');
            ['impulsa_theme_mode', 'impulsa_theme_accent', 'impulsa_theme_surface', 'impulsa_theme_motion'].forEach((key) => localStorage.removeItem(key));
            document.getElementById('themeSettingsToggle')?.remove();
            document.getElementById('themeSettingsDrawer')?.remove();
            document.getElementById('themeSettingsOverlay')?.remove();
        }

        updateScoreSummary();
        lockFrameworkTheme();
        window.addEventListener('load', lockFrameworkTheme);
    </script>
</body>
</html>
