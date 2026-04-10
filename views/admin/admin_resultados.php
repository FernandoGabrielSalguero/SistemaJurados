<?php
require_once __DIR__ . '/../../controllers/admin_resultadosController.php';

$administrador = $viewData['administrador'] ?? [];
$usuarioSesion = $viewData['usuarioSesion'] ?? 'Administrador';
$pageTitle = $viewData['pageTitle'] ?? 'Resultados';
$pageSubtitle = $viewData['pageSubtitle'] ?? '';
$estadoTablas = $viewData['estadoTablas'] ?? ['faltantes' => []];
$filtrosDisponibles = $viewData['filtrosDisponibles'] ?? ['formularios' => [], 'categorias' => []];
$filtroFormularioId = (int) ($viewData['filtroFormularioId'] ?? 0);
$filtroCategoria = (string) ($viewData['filtroCategoria'] ?? '');
$resultadosAgrupados = $viewData['resultadosAgrupados'] ?? [];
$metricas = $viewData['metricas'] ?? ['grupos' => 0, 'evaluaciones' => 0, 'competidores' => 0, 'jurados' => 0];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> | Panel de administracion</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link rel="stylesheet" href="https://framework.impulsagroup.com/assets/css/framework.css">
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script src="https://framework.impulsagroup.com/assets/javascript/framework.js" defer></script>
    <style>
        @font-face { font-family:'Montserrat'; src:url('../../assets/institucionales/fonts/Montserrat/Montserrat-VariableFont_wght.ttf') format('truetype'); font-weight:100 900; font-style:normal; font-display:swap; }
        :root { --surface:#fff; --border:#e5ebf4; --text:#1f2937; --muted:#67768a; --primary:#2f6df6; --primary-soft:#eaf1ff; --success:#15803d; --success-soft:#ecfdf3; --warning:#c2410c; --warning-soft:#fff7ed; --danger:#b91c1c; --danger-soft:#fef2f2; --shadow:0 10px 28px rgba(15,23,42,.08); --sidebar-width:208px; --sidebar-collapsed-width:72px; }
        * { box-sizing:border-box; }
        html { font-size:14px; }
        body { margin:0; font-family:'Montserrat',sans-serif; background:linear-gradient(180deg,#f8fafc 0%,#eff4fb 100%); color:var(--text); overflow-x:hidden; }
        .theme-settings-btn,.theme-drawer,.theme-settings-overlay { display:none !important; visibility:hidden !important; pointer-events:none !important; }
        .layout { min-height:100vh; display:flex; width:100%; }
        .sidebar { width:var(--sidebar-width); background:rgba(255,255,255,.96); border-right:1px solid var(--border); box-shadow:8px 0 24px rgba(15,23,42,.04); transition:width .22s ease, transform .22s ease; z-index:40; }
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
        .main { flex:1 1 auto; min-width:0; width:calc(100% - var(--sidebar-width)); max-width:calc(100% - var(--sidebar-width)); background:radial-gradient(circle at top left, rgba(47,109,246,.06), transparent 18%), linear-gradient(180deg,#f8fafc 0%,#f1f5f9 100%); }
        .navbar { background:rgba(255,255,255,.86); backdrop-filter:blur(14px); border-bottom:1px solid rgba(226,232,240,.92); display:flex; justify-content:space-between; align-items:center; gap:12px; padding:14px 20px; position:sticky; top:0; z-index:20; }
        .navbar-left,.navbar-actions { display:flex; align-items:center; gap:10px; }
        .navbar-title { font-size:1rem; font-weight:800; color:#1f2937; line-height:1.2; }
        .navbar-subtitle { color:var(--muted); font-size:.85rem; line-height:1.25; }
        .btn-icon { background:transparent; border:0; width:34px; height:34px; border-radius:10px; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; }
        .btn-icon .material-icons { color:var(--primary); font-size:1.35rem; }
        .logout-link { display:inline-flex; align-items:center; gap:7px; border-radius:999px; background:#fff; color:var(--danger); border:1px solid #fecaca; padding:9px 14px; text-decoration:none; font-weight:700; font-size:.92rem; }
        .content { padding:20px; width:100%; }
        .page-shell { display:flex; flex-direction:column; gap:20px; width:100%; }
        .panel-card { background:var(--surface); border:1px solid var(--border); border-radius:20px; box-shadow:var(--shadow); padding:24px; width:100%; }
        .hero-card h1,.section-title { margin:0 0 6px; font-size:1.25rem; font-weight:800; color:#202633; line-height:1.2; }
        .hero-card p,.section-caption { margin:0; color:var(--muted); line-height:1.5; font-size:.92rem; }
        .alert-inline { margin-top:14px; padding:14px 16px; border-radius:16px; font-size:.92rem; line-height:1.5; border:1px solid #dbeafe; background:#eff6ff; color:#1d4ed8; }
        .alert-inline.warning { background:var(--warning-soft); color:var(--warning); border-color:#fed7aa; }
        .metrics-grid,.filters-grid,.summary-grid { display:grid; gap:14px; }
        .metrics-grid { grid-template-columns:repeat(4,minmax(0,1fr)); }
        .filters-grid { grid-template-columns:repeat(3,minmax(0,1fr)); margin-top:18px; }
        .metric-card,.summary-card { border:1px solid var(--border); border-radius:18px; padding:16px; background:linear-gradient(180deg,#fff 0%,#f9fbff 100%); }
        .metric-label,.summary-label { color:var(--muted); font-size:.82rem; margin-bottom:6px; }
        .metric-value,.summary-value { font-size:1.7rem; font-weight:800; line-height:1; }
        .metric-help { margin-top:8px; color:var(--muted); font-size:.82rem; }
        .form-field { display:flex; flex-direction:column; gap:7px; }
        .form-field label { font-size:.84rem; font-weight:700; color:#334155; }
        .form-field select { width:100%; min-height:46px; border-radius:14px; border:1px solid #d6dfef; padding:10px 14px; background:#fff; color:#111827; font-size:.95rem; }
        .filter-actions { display:flex; align-items:flex-end; gap:10px; }
        .btn-primary,.btn-secondary { border:0; border-radius:14px; padding:12px 18px; font-weight:800; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; }
        .btn-primary { background:linear-gradient(135deg,#2f6df6,#4c8bff); color:#fff; }
        .btn-secondary { background:#fff; color:#415066; border:1px solid #d6dfef; }
        .group-card { border:1px solid var(--border); border-radius:20px; padding:18px; background:#fff; }
        .group-header { display:flex; justify-content:space-between; gap:16px; align-items:flex-start; margin-bottom:16px; }
        .group-actions { display:flex; align-items:center; gap:10px; }
        .group-title { margin:0; font-size:1.08rem; font-weight:800; color:#202633; }
        .group-meta { color:var(--muted); font-size:.9rem; margin-top:4px; }
        .export-btn { width:40px; height:40px; border-radius:12px; border:1px solid #d6dfef; background:#fff; color:#2457cc; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; transition:background .2s ease, border-color .2s ease, transform .2s ease; }
        .export-btn:hover { background:#eef4ff; border-color:#bfd4ff; transform:translateY(-1px); }
        .export-btn .material-icons { font-size:20px; }
        .summary-grid { grid-template-columns:repeat(4,minmax(0,1fr)); margin-bottom:16px; }
        .competitors-stack { display:flex; flex-direction:column; gap:16px; }
        .competitor-card { border:1px solid var(--border); border-radius:18px; background:#fbfdff; overflow:hidden; }
        .competitor-header { display:flex; justify-content:space-between; gap:14px; align-items:flex-start; padding:16px 18px; border-bottom:1px solid var(--border); background:linear-gradient(180deg,#ffffff 0%,#f9fbff 100%); }
        .competitor-title { margin:0; font-size:1rem; font-weight:800; color:#202633; }
        .competitor-meta { color:var(--muted); font-size:.88rem; margin-top:4px; }
        .competitor-stats { display:flex; flex-wrap:wrap; gap:10px; }
        .stat-chip { display:inline-flex; align-items:center; border-radius:999px; background:#eef4ff; color:#2457cc; padding:7px 11px; font-size:.76rem; font-weight:700; }
        .ranking-chip { background:#dbeafe; color:#1d4ed8; }
        .table-responsive { border:1px solid var(--border); border-radius:16px; overflow-x:auto; }
        .table { width:100%; min-width:1180px; border-collapse:collapse; }
        .table thead th { background:#f8fafc; color:#5b6472; border-bottom:1px solid var(--border); font-size:.74rem; text-transform:uppercase; letter-spacing:.04em; padding:12px 14px; text-align:left; }
        .table tbody td { padding:14px; font-size:.9rem; vertical-align:top; border-bottom:1px solid var(--border); }
        .table tbody tr:last-child td { border-bottom:0; }
        .final-row td { background:#f8fbff; font-weight:700; }
        .empty-state { min-height:240px; display:flex; align-items:center; justify-content:center; text-align:center; border:1px dashed #cdd9ee; border-radius:18px; background:linear-gradient(180deg,#fbfdff 0%,#f6f9ff 100%); padding:32px 20px; }
        .empty-state-box { max-width:480px; }
        .empty-state-icon { width:72px; height:72px; margin:0 auto 18px; border-radius:20px; display:inline-flex; align-items:center; justify-content:center; background:var(--primary-soft); color:var(--primary); }
        .empty-state-icon .material-icons { font-size:34px; }
        .empty-state h2 { margin:0 0 10px; font-size:1.2rem; font-weight:800; color:#202633; }
        .empty-state p { margin:0; color:var(--muted); line-height:1.6; }
        body.sidebar-collapsed .sidebar { width:var(--sidebar-collapsed-width); }
        body.sidebar-collapsed .main { width:calc(100% - var(--sidebar-collapsed-width)); max-width:calc(100% - var(--sidebar-collapsed-width)); }
        body.sidebar-collapsed .logo-text, body.sidebar-collapsed .link-text { display:none; }
        body.sidebar-collapsed .sidebar-header, body.sidebar-collapsed .sidebar-menu li { justify-content:center; padding-inline:10px; }
        @media (max-width:1180px) { .metrics-grid,.filters-grid,.summary-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } .group-header { flex-direction:column; } }
        @media (max-width:860px) { .sidebar { position:fixed; top:0; left:0; bottom:0; transform:translateX(-100%); } body.sidebar-open .sidebar { transform:translateX(0); } .main,body.sidebar-collapsed .main { width:100%; max-width:100%; } .navbar { flex-wrap:wrap; padding:12px 16px; } .navbar-actions { width:100%; justify-content:space-between; } .content { padding:16px; } .panel-card { padding:18px; } .metrics-grid,.filters-grid,.summary-grid { grid-template-columns:1fr; } .filter-actions { align-items:stretch; flex-direction:column; } .btn-primary,.btn-secondary { width:100%; } .table { min-width:940px; } .navbar-actions .navbar-subtitle { display:none; } }
        @media (max-width:560px) { html { font-size:13px; } .content { padding:12px; } .panel-card { padding:16px; border-radius:16px; } .logout-link span:last-child { display:none; } }
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
                    <li onclick="location.href='admin_dashboard.php'"><span class="material-icons">home</span><span class="link-text">Inicio</span></li>
                    <li onclick="location.href='admin_calificaciones.php'"><span class="material-icons">fact_check</span><span class="link-text">Calificaciones</span></li>
                    <li class="active" onclick="location.href='admin_resultados.php'"><span class="material-icons">analytics</span><span class="link-text">Resultados</span></li>
                    <li onclick="location.href='../../logout.php'"><span class="material-icons">logout</span><span class="link-text">Salir</span></li>
                </ul>
            </nav>
        </aside>

        <div class="main">
            <header class="navbar">
                <div class="navbar-left">
                    <button type="button" class="btn-icon" id="toggleSidebarBtn" aria-label="Mostrar menu lateral"><span class="material-icons">menu</span></button>
                    <button type="button" class="btn-icon" id="collapseSidebarBtn" aria-label="Colapsar menu lateral"><span class="material-icons" id="collapseIcon">chevron_left</span></button>
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
                        <?php if ($estadoTablas['faltantes'] ?? []): ?>
                            <div class="alert-inline warning">Todavia faltan tablas del modulo: <strong><?= htmlspecialchars(implode(', ', $estadoTablas['faltantes']), ENT_QUOTES, 'UTF-8') ?></strong>.</div>
                        <?php endif; ?>
                    </section>

                    <section class="metrics-grid">
                        <article class="metric-card"><div class="metric-label">Grupos visibles</div><div class="metric-value"><?= (int) $metricas['grupos'] ?></div><div class="metric-help">Combinaciones de formulario y categoria.</div></article>
                        <article class="metric-card"><div class="metric-label">Evaluaciones</div><div class="metric-value"><?= (int) $metricas['evaluaciones'] ?></div><div class="metric-help">Registros cargados por los jurados.</div></article>
                        <article class="metric-card"><div class="metric-label">Competidores</div><div class="metric-value"><?= (int) $metricas['competidores'] ?></div><div class="metric-help">Competidores distintos en la consulta actual.</div></article>
                        <article class="metric-card"><div class="metric-label">Jurados</div><div class="metric-value"><?= (int) $metricas['jurados'] ?></div><div class="metric-help">Jurados que participaron en estos resultados.</div></article>
                    </section>

                    <section class="panel-card">
                        <h2 class="section-title">Filtros</h2>
                        <p class="section-caption">Podes consultar todos los resultados o acotar la vista por formulario y categoria.</p>
                        <form method="get" class="filters-grid">
                            <div class="form-field">
                                <label for="formulario_id">Formulario</label>
                                <select id="formulario_id" name="formulario_id">
                                    <option value="0">Todos los formularios</option>
                                    <?php foreach (($filtrosDisponibles['formularios'] ?? []) as $formulario): ?>
                                        <option value="<?= (int) ($formulario['id'] ?? 0) ?>" <?= $filtroFormularioId === (int) ($formulario['id'] ?? 0) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars((string) ($formulario['nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?> | <?= htmlspecialchars((string) ($formulario['categoria'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-field">
                                <label for="categoria">Categoria</label>
                                <select id="categoria" name="categoria">
                                    <option value="">Todas las categorias</option>
                                    <?php foreach (($filtrosDisponibles['categorias'] ?? []) as $categoria): ?>
                                        <?php $categoriaValor = (string) ($categoria['categoria'] ?? ''); ?>
                                        <option value="<?= htmlspecialchars($categoriaValor, ENT_QUOTES, 'UTF-8') ?>" <?= $filtroCategoria === $categoriaValor ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($categoriaValor, ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="filter-actions">
                                <button type="submit" class="btn-primary">Aplicar filtros</button>
                                <a href="admin_resultados.php" class="btn-secondary">Limpiar</a>
                            </div>
                        </form>
                    </section>

                    <?php if ($resultadosAgrupados): ?>
                        <?php $grupoExportIndex = 0; ?>
                        <?php foreach ($resultadosAgrupados as $grupo): ?>
                            <section class="group-card">
                                <div class="group-header">
                                    <div>
                                        <h2 class="group-title"><?= htmlspecialchars((string) ($grupo['formulario_nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
                                        <div class="group-meta">Categoria: <strong><?= htmlspecialchars((string) ($grupo['categoria'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong> | Evento: <strong><?= htmlspecialchars((string) ($grupo['evento_nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong></div>
                                    </div>
                                    <div class="group-actions">
                                        <button
                                            type="button"
                                            class="export-btn"
                                            title="Descargar resultados en Excel"
                                            aria-label="Descargar resultados en Excel"
                                            data-export-group-index="<?= (int) $grupoExportIndex ?>">
                                            <span class="material-icons">download</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="summary-grid">
                                    <div class="summary-card"><div class="summary-label">Evaluaciones</div><div class="summary-value"><?= (int) ($grupo['total_evaluaciones'] ?? 0) ?></div></div>
                                    <div class="summary-card"><div class="summary-label">Competidores</div><div class="summary-value"><?= (int) ($grupo['total_competidores'] ?? 0) ?></div></div>
                                    <div class="summary-card"><div class="summary-label">Jurados</div><div class="summary-value"><?= (int) ($grupo['total_jurados'] ?? 0) ?></div></div>
                                    <div class="summary-card"><div class="summary-label">Promedio general</div><div class="summary-value"><?= htmlspecialchars(number_format((float) ($grupo['promedio_general'] ?? 0), 2, '.', ''), ENT_QUOTES, 'UTF-8') ?></div></div>
                                </div>

                                <div class="competitors-stack">
                                    <?php foreach (($grupo['competidores_detalle'] ?? []) as $competidor): ?>
                                        <article class="competitor-card">
                                            <div class="competitor-header">
                                                <div>
                                                    <h3 class="competitor-title">Competidor #<?= htmlspecialchars((string) ($competidor['competidor_numero'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                                                    <div class="competitor-meta">
                                                        Nombre principal: <strong><?= htmlspecialchars((string) ($competidor['nombre_mostrar'] ?? 'Sin nombre'), ENT_QUOTES, 'UTF-8') ?></strong>
                                                        <?php if (!empty($competidor['nombres']) && count($competidor['nombres']) > 1): ?>
                                                            | Variantes cargadas: <?= htmlspecialchars(implode(' | ', $competidor['nombres']), ENT_QUOTES, 'UTF-8') ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="competitor-stats">
                                                    <span class="stat-chip ranking-chip">Puesto: <?= (int) ($competidor['puesto'] ?? 0) ?></span>
                                                    <span class="stat-chip">Evaluaciones: <?= (int) ($competidor['total_evaluaciones'] ?? 0) ?></span>
                                                    <span class="stat-chip">Promedio final: <?= htmlspecialchars(number_format((float) ($competidor['promedio_final'] ?? 0), 2, '.', ''), ENT_QUOTES, 'UTF-8') ?></span>
                                                    <span class="stat-chip">Puntaje final: <?= htmlspecialchars(number_format((float) ($competidor['puntaje_final'] ?? 0), 2, '.', ''), ENT_QUOTES, 'UTF-8') ?></span>
                                                </div>
                                            </div>

                                            <div class="table-responsive">
                                                <table class="table">
                                                    <thead>
                                                        <tr>
                                                            <th>ID</th>
                                                            <th>Jurado</th>
                                                            <th>Nombre cargado</th>
                                                            <?php foreach (($grupo['criterios'] ?? []) as $criterio): ?>
                                                                <th><?= htmlspecialchars((string) ($criterio['criterio_nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></th>
                                                            <?php endforeach; ?>
                                                            <th>Total</th>
                                                            <th>Promedio</th>
                                                            <th>Fecha</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach (($competidor['evaluaciones'] ?? []) as $evaluacion): ?>
                                                            <?php
                                                            $detallesIndexados = [];
                                                            foreach (($evaluacion['detalles'] ?? []) as $detalle) {
                                                                $detallesIndexados[(string) ($detalle['criterio_clave'] ?? '')] = $detalle;
                                                            }
                                                            ?>
                                                            <tr>
                                                                <td><?= (int) ($evaluacion['id'] ?? 0) ?></td>
                                                                <td><?= htmlspecialchars((string) ($evaluacion['jurado_display'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                                                <td><?= htmlspecialchars((string) ($evaluacion['competidor_nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                                                <?php foreach (($grupo['criterios'] ?? []) as $criterio): ?>
                                                                    <?php
                                                                    $criterioClave = (string) ($criterio['criterio_clave'] ?? '');
                                                                    $detalle = $detallesIndexados[$criterioClave] ?? null;
                                                                    ?>
                                                                    <td>
                                                                        <?php if ($detalle): ?>
                                                                            <?= htmlspecialchars(number_format((float) ($detalle['puntaje_otorgado'] ?? 0), 0, '.', ''), ENT_QUOTES, 'UTF-8') ?>/<?= htmlspecialchars(number_format((float) ($detalle['puntaje_maximo'] ?? 0), 0, '.', ''), ENT_QUOTES, 'UTF-8') ?>
                                                                        <?php else: ?>
                                                                            -
                                                                        <?php endif; ?>
                                                                    </td>
                                                                <?php endforeach; ?>
                                                                <td><strong><?= htmlspecialchars(number_format((float) ($evaluacion['puntaje_total'] ?? 0), 2, '.', ''), ENT_QUOTES, 'UTF-8') ?></strong></td>
                                                                <td><?= htmlspecialchars(number_format((float) ($evaluacion['promedio'] ?? 0), 2, '.', ''), ENT_QUOTES, 'UTF-8') ?></td>
                                                                <td><?= htmlspecialchars((string) ($evaluacion['creado_en'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                        <tr class="final-row">
                                                            <td colspan="3">Promedio final del competidor</td>
                                                            <?php foreach (($grupo['criterios'] ?? []) as $criterio): ?>
                                                                <?php
                                                                $criterioClave = (string) ($criterio['criterio_clave'] ?? '');
                                                                $criterioPromedio = $competidor['criterios_promedio'][$criterioClave] ?? null;
                                                                ?>
                                                                <td>
                                                                    <?php if ($criterioPromedio): ?>
                                                                        <?= htmlspecialchars(number_format((float) ($criterioPromedio['promedio_otorgado'] ?? 0), 2, '.', ''), ENT_QUOTES, 'UTF-8') ?>/<?= htmlspecialchars(number_format((float) ($criterioPromedio['puntaje_maximo'] ?? 0), 0, '.', ''), ENT_QUOTES, 'UTF-8') ?>
                                                                    <?php else: ?>
                                                                        -
                                                                    <?php endif; ?>
                                                                </td>
                                                            <?php endforeach; ?>
                                                            <td><strong><?= htmlspecialchars(number_format((float) ($competidor['puntaje_final'] ?? 0), 2, '.', ''), ENT_QUOTES, 'UTF-8') ?></strong></td>
                                                            <td><strong><?= htmlspecialchars(number_format((float) ($competidor['promedio_final'] ?? 0), 2, '.', ''), ENT_QUOTES, 'UTF-8') ?></strong></td>
                                                            <td>Puesto <?= (int) ($competidor['puesto'] ?? 0) ?></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            </section>
                            <?php $grupoExportIndex++; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <section class="panel-card">
                            <div class="empty-state">
                                <div class="empty-state-box">
                                    <div class="empty-state-icon" aria-hidden="true"><span class="material-icons">analytics</span></div>
                                    <h2>No hay resultados para mostrar</h2>
                                    <p>Cuando los jurados empiecen a cargar formularios, vas a ver aqui los resultados agrupados por formulario y categoria con el detalle completo de cada carga.</p>
                                </div>
                            </div>
                        </section>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script>
        const resultadosExportables = <?= json_encode($resultadosAgrupados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        const body = document.body;
        const sidebar = document.getElementById('sidebar');
        const collapseButton = document.getElementById('collapseSidebarBtn');
        const collapseIcon = document.getElementById('collapseIcon');
        const toggleSidebarButton = document.getElementById('toggleSidebarBtn');
        const mobileBreakpoint = window.matchMedia('(max-width: 860px)');
        const exportButtons = document.querySelectorAll('[data-export-group-index]');

        function syncSidebarState() {
            if (mobileBreakpoint.matches) {
                body.classList.remove('sidebar-collapsed');
                if (collapseIcon) collapseIcon.textContent = 'chevron_left';
                return;
            }
            if (collapseIcon) collapseIcon.textContent = body.classList.contains('sidebar-collapsed') ? 'chevron_right' : 'chevron_left';
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
            if (!mobileBreakpoint.matches || !body.classList.contains('sidebar-open')) return;
            if ((sidebar && sidebar.contains(event.target)) || (toggleSidebarButton && toggleSidebarButton.contains(event.target))) return;
            body.classList.remove('sidebar-open');
        });

        mobileBreakpoint.addEventListener('change', () => {
            body.classList.remove('sidebar-open');
            syncSidebarState();
        });

        function slugify(value) {
            return String(value || '')
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-zA-Z0-9]+/g, '_')
                .replace(/^_+|_+$/g, '')
                .toLowerCase();
        }

        function exportarGrupoAExcel(groupIndex) {
            const grupo = resultadosExportables[groupIndex];
            if (!grupo || typeof XLSX === 'undefined') {
                return;
            }

            const rankingRows = (grupo.competidores_detalle || []).map((competidor) => {
                const row = {
                    Puesto: competidor.puesto ?? '',
                    'N° Competidor': competidor.competidor_numero ?? '',
                    'Nombre principal': competidor.nombre_mostrar ?? '',
                    'Variantes de nombre': Array.isArray(competidor.nombres) ? competidor.nombres.join(' | ') : '',
                    Evaluaciones: competidor.total_evaluaciones ?? 0,
                    'Puntaje final': competidor.puntaje_final ?? 0,
                    'Promedio final': competidor.promedio_final ?? 0,
                };

                (grupo.criterios || []).forEach((criterio) => {
                    const clave = criterio.criterio_clave || '';
                    const detalle = competidor.criterios_promedio?.[clave];
                    row[`Promedio ${criterio.criterio_nombre}`] = detalle ? detalle.promedio_otorgado : '';
                });

                return row;
            });

            const criteriosPromedioRows = (grupo.competidores_detalle || []).map((competidor) => {
                const row = {
                    Puesto: competidor.puesto ?? '',
                    'N° Competidor': competidor.competidor_numero ?? '',
                    'Nombre principal': competidor.nombre_mostrar ?? '',
                    'Promedio final': competidor.promedio_final ?? 0,
                    'Puntaje final': competidor.puntaje_final ?? 0,
                };

                (grupo.criterios || []).forEach((criterio) => {
                    const clave = criterio.criterio_clave || '';
                    const detalle = competidor.criterios_promedio?.[clave];
                    row[`Promedio ${criterio.criterio_nombre}`] = detalle ? detalle.promedio_otorgado : '';
                });

                return row;
            });

            const detalleRows = [];
            (grupo.competidores_detalle || []).forEach((competidor) => {
                (competidor.evaluaciones || []).forEach((evaluacion) => {
                    const row = {
                        Grupo: `${grupo.formulario_nombre || ''} | ${grupo.categoria || ''}`,
                        Puesto: competidor.puesto ?? '',
                        'N° Competidor': competidor.competidor_numero ?? '',
                        'Nombre principal': competidor.nombre_mostrar ?? '',
                        'Nombre cargado': evaluacion.competidor_nombre ?? '',
                        Jurado: evaluacion.jurado_display ?? '',
                        Evento: evaluacion.evento_nombre ?? '',
                        Categoria: evaluacion.categoria ?? '',
                        'Puntaje total': evaluacion.puntaje_total ?? 0,
                        Promedio: evaluacion.promedio ?? 0,
                        Fecha: evaluacion.creado_en ?? '',
                    };

                    const detallesIndexados = {};
                    (evaluacion.detalles || []).forEach((detalle) => {
                        detallesIndexados[detalle.criterio_clave || ''] = detalle;
                    });

                    (grupo.criterios || []).forEach((criterio) => {
                        const clave = criterio.criterio_clave || '';
                        const detalle = detallesIndexados[clave];
                        row[criterio.criterio_nombre] = detalle ? detalle.puntaje_otorgado : '';
                    });

                    detalleRows.push(row);
                });
            });

            const resumenRows = [
                { Campo: 'Formulario', Valor: grupo.formulario_nombre || '' },
                { Campo: 'Categoria', Valor: grupo.categoria || '' },
                { Campo: 'Evento', Valor: grupo.evento_nombre || '' },
                { Campo: 'Evaluaciones', Valor: grupo.total_evaluaciones || 0 },
                { Campo: 'Competidores', Valor: grupo.total_competidores || 0 },
                { Campo: 'Jurados', Valor: grupo.total_jurados || 0 },
                { Campo: 'Promedio general', Valor: grupo.promedio_general || 0 },
            ];

            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, XLSX.utils.json_to_sheet(resumenRows), 'Resumen');
            XLSX.utils.book_append_sheet(workbook, XLSX.utils.json_to_sheet(rankingRows), 'Ranking');
            XLSX.utils.book_append_sheet(workbook, XLSX.utils.json_to_sheet(criteriosPromedioRows), 'Promedios Criterio');
            XLSX.utils.book_append_sheet(workbook, XLSX.utils.json_to_sheet(detalleRows), 'Evaluaciones');

            const fileName = [
                'resultados',
                slugify(grupo.formulario_nombre || 'formulario'),
                slugify(grupo.categoria || 'categoria')
            ].filter(Boolean).join('_') + '.xlsx';

            XLSX.writeFile(workbook, fileName);
        }

        exportButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const groupIndex = parseInt(button.dataset.exportGroupIndex || '-1', 10);
                if (!Number.isNaN(groupIndex) && groupIndex >= 0) {
                    exportarGrupoAExcel(groupIndex);
                }
            });
        });

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

        syncSidebarState();
        lockFrameworkTheme();
        window.addEventListener('load', lockFrameworkTheme);
    </script>
</body>
</html>
