<?php
require_once __DIR__ . '/../../controllers/jurado_dashboardController.php';

$jurado = $viewData['jurado'] ?? [];
$usuarioSesion = $viewData['usuarioSesion'] ?? 'Jurado';
$pageTitle = $viewData['pageTitle'] ?? 'Formulario de calificacion';
$pageSubtitle = $viewData['pageSubtitle'] ?? '';
$estadoTablas = $viewData['estadoTablas'] ?? ['faltantes' => []];
$categoriasDisponibles = $viewData['categoriasDisponibles'] ?? [];
$subcategoriasDisponibles = $viewData['subcategoriasDisponibles'] ?? [];
$formulariosActivos = $viewData['formulariosActivos'] ?? [];
$formularioSeleccionado = $viewData['formularioSeleccionado'] ?? null;
$mensaje = $viewData['mensaje'] ?? '';
$mensajeTipo = $viewData['mensajeTipo'] ?? 'success';
$formData = $viewData['formData'] ?? ['categoria' => '', 'formulario_id' => 0, 'competidor_numero' => '', 'puntajes' => []];

function juradoDashboardBuildMediaUrl(?string $path): string
{
    $path = trim((string) $path);
    if ($path === '') {
        return '';
    }

    if (preg_match('#^(https?:)?//#i', $path) === 1 || str_starts_with($path, 'data:')) {
        return $path;
    }

    if (str_starts_with($path, '../../') || str_starts_with($path, '../')) {
        return $path;
    }

    if (str_starts_with($path, '/')) {
        return '../..' . $path;
    }

    return '../../' . ltrim($path, './');
}

function juradoDashboardInitials(string $label): string
{
    $words = preg_split('/\s+/', trim($label)) ?: [];
    $initials = '';

    foreach ($words as $word) {
        if ($word === '') {
            continue;
        }

        $initials .= function_exists('mb_substr')
            ? mb_strtoupper(mb_substr($word, 0, 1, 'UTF-8'), 'UTF-8')
            : strtoupper(substr($word, 0, 1));

        if (strlen($initials) >= 2) {
            break;
        }
    }

    return $initials !== '' ? $initials : 'JD';
}

$juradoNombre = (string) ($jurado['nombre'] ?? $jurado['usuario'] ?? $usuarioSesion);
$eventoNombre = (string) ($formularioSeleccionado['evento_nombre'] ?? '');
$juradoAvatarUrl = juradoDashboardBuildMediaUrl((string) ($jurado['avatar_path'] ?? ''));
$eventoImagenUrl = juradoDashboardBuildMediaUrl((string) ($formularioSeleccionado['imagen_url'] ?? ''));
$juradoInitials = juradoDashboardInitials($juradoNombre);
$eventoInitials = juradoDashboardInitials($eventoNombre !== '' ? $eventoNombre : 'Evento');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> | Panel de jurado</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link rel="stylesheet" href="https://framework.impulsagroup.com/assets/css/framework.css">
    <script src="https://framework.impulsagroup.com/assets/javascript/framework.js" defer></script>
    <style>
        @font-face { font-family:'Montserrat'; src:url('../../assets/institucionales/fonts/Montserrat/Montserrat-VariableFont_wght.ttf') format('truetype'); font-weight:100 900; font-style:normal; font-display:swap; }
        :root {
            --font-family:'Montserrat',sans-serif;
            --base-font-size:14px;
            --page-bg-start:#fffdf7;
            --page-bg-end:#f7f8fc;
            --surface:#ffffff;
            --border:#e8ecf4;
            --text:#1f2937;
            --muted:#6a7688;
            --primary:#e4a800;
            --primary-strong:#f3c23d;
            --primary-text:#3a2b00;
            --success:#15803d;
            --success-soft:#ecfdf3;
            --danger:#b91c1c;
            --danger-soft:#fef2f2;
            --warning:#c2410c;
            --warning-soft:#fff7ed;
            --navbar-bg:#ffffff;
            --navbar-border:#e8ecf4;
            --card-radius:20px;
            --control-radius:14px;
            --panel-padding:24px;
            --content-max-width:1600px;
            --shadow-color:15,23,42;
            --shadow-y:10px;
            --shadow-blur:28px;
            --shadow-alpha:.08;
            --summary-bg:#0f172a;
            --summary-text:#ffffff;
            --summary-muted:#cbd5e1;
            --field-bg:#ffffff;
            --field-readonly-bg:#f8fafc;
            --shadow:0 var(--shadow-y) var(--shadow-blur) rgba(var(--shadow-color),var(--shadow-alpha));
        }
        * { box-sizing:border-box; }
        html { font-size:var(--base-font-size); }
        body { margin:0; font-family:var(--font-family); background:linear-gradient(180deg,var(--page-bg-start) 0%,var(--page-bg-end) 100%); color:var(--text); transition:background .25s ease,color .25s ease,font-size .25s ease; }
        .theme-settings-btn,.theme-drawer,.theme-settings-overlay { display:none !important; visibility:hidden !important; pointer-events:none !important; }
        .page { min-height:100vh; display:flex; flex-direction:column; }
        .navbar { background:var(--navbar-bg); backdrop-filter:blur(14px); border-bottom:1px solid var(--navbar-border); display:flex; justify-content:space-between; align-items:center; gap:16px; padding:14px 20px; position:sticky; top:0; z-index:20; transition:background .25s ease,border-color .25s ease; }
        .navbar-title { font-size:1rem; font-weight:800; color:var(--text); line-height:1.2; }
        .navbar-subtitle { color:var(--muted); font-size:.85rem; line-height:1.25; }
        .navbar-user { font-size:1.22rem; font-weight:800; color:var(--text); line-height:1.1; text-align:right; }
        .navbar-actions { display:flex; align-items:center; gap:12px; }
        .logout-link { display:inline-flex; align-items:center; gap:7px; border-radius:999px; background:var(--surface); color:var(--danger); border:1px solid #fecaca; padding:9px 14px; text-decoration:none; font-weight:700; font-size:.92rem; }
        .content { width:100%; max-width:var(--content-max-width); margin:0 auto; padding:24px 20px 32px; transition:max-width .25s ease,padding .25s ease; }
        .page-shell { display:flex; flex-direction:column; gap:20px; }
        .panel-card { background:var(--surface); border:1px solid var(--border); border-radius:var(--card-radius); box-shadow:var(--shadow); padding:var(--panel-padding); width:100%; transition:background .25s ease,border-color .25s ease,border-radius .25s ease,box-shadow .25s ease,padding .25s ease; }
        .hero-card h1,.section-title { margin:0 0 6px; font-size:1.25rem; font-weight:800; color:var(--text); line-height:1.2; }
        .hero-card p,.section-caption,.field-help { margin:0; color:var(--muted); line-height:1.5; font-size:.92rem; }
        .alert-inline { margin-top:14px; padding:14px 16px; border-radius:16px; font-size:.92rem; line-height:1.5; border:1px solid #dbeafe; background:#eff6ff; color:#1d4ed8; }
        .alert-inline.success { background:var(--success-soft); color:var(--success); border-color:#bbf7d0; }
        .alert-inline.danger { background:var(--danger-soft); color:var(--danger); border-color:#fecaca; }
        .alert-inline.warning { background:var(--warning-soft); color:var(--warning); border-color:#fed7aa; }
        .form-stack { display:flex; flex-direction:column; gap:18px; }
        .form-layout { display:grid; grid-template-columns:minmax(0,1.65fr) minmax(300px,.75fr); gap:20px; align-items:stretch; }
        .form-main { display:flex; flex-direction:column; gap:18px; min-width:0; }
        .top-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; }
        .form-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:14px; }
        .criteria-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:14px; }
        .form-field { display:flex; flex-direction:column; gap:7px; }
        .form-field.full { grid-column:1 / -1; }
        .form-field label { font-size:.84rem; font-weight:700; color:var(--text); }
        .form-field input[type="text"], .form-field select { width:100%; min-height:46px; border-radius:var(--control-radius); border:1px solid #d6dfef; padding:10px 14px; background:var(--field-bg); color:var(--text); font-size:.95rem; transition:background .25s ease,color .25s ease,border-radius .25s ease; }
        .form-shell { display:flex; flex-direction:column; gap:18px; }
        .readonly-field { display:flex; align-items:center; min-height:46px; border-radius:var(--control-radius); border:1px solid #d6dfef; padding:10px 14px; background:var(--field-readonly-bg); color:var(--text); font-size:.95rem; font-weight:700; transition:background .25s ease,color .25s ease,border-radius .25s ease; }
        .summary-card,.criteria-card { border:1px solid var(--border); border-radius:18px; padding:16px; background:linear-gradient(180deg,var(--surface) 0%,color-mix(in srgb, var(--surface) 88%, #fff4c7 12%) 100%); transition:background .25s ease,border-color .25s ease; }
        .summary-label,.criteria-meta { color:var(--muted); font-size:.82rem; margin-bottom:6px; }
        .summary-value { font-size:1.15rem; font-weight:800; line-height:1.2; }
        .criteria-title { font-weight:800; margin:0 0 4px; color:var(--text); }
        .questions-card .criteria-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .slider-row { display:flex; align-items:center; gap:12px; }
        .score-slider { flex:1; accent-color:var(--primary); }
        .slider-value { min-width:54px; text-align:right; font-weight:800; color:var(--text); }
        .confirm-summary { display:flex; flex-direction:column; gap:14px; text-align:left; }
        .confirm-section { padding-bottom:12px; border-bottom:1px solid var(--border); }
        .confirm-section:last-child { padding-bottom:0; border-bottom:0; }
        .confirm-title { margin:0 0 8px; font-size:.95rem; font-weight:800; color:#202633; }
        .confirm-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:10px 14px; }
        .confirm-item-label { color:var(--muted); font-size:.78rem; margin-bottom:2px; }
        .confirm-item-value { color:#202633; font-size:.94rem; font-weight:700; }
        .confirm-criteria-list { margin:0; padding:0; list-style:none; display:flex; flex-direction:column; gap:8px; }
        .confirm-criteria-item { display:flex; align-items:center; justify-content:space-between; gap:16px; font-size:.92rem; }
        .confirm-criteria-item span:last-child { font-weight:800; color:#202633; }
        .confirm-status { margin-top:6px; border-radius:16px; padding:14px 16px; font-size:.92rem; line-height:1.5; border:1px solid transparent; }
        .confirm-status.error { background:var(--danger-soft); color:var(--danger); border-color:#fecaca; }
        .save-toast { position:fixed; top:20px; right:20px; z-index:80; min-width:300px; max-width:min(420px,calc(100vw - 32px)); display:flex; align-items:center; gap:14px; padding:16px 18px; border-radius:20px; border:1px solid rgba(134,239,172,.9); background:linear-gradient(135deg,rgba(236,253,243,.98),rgba(220,252,231,.96)); box-shadow:0 24px 60px rgba(21,128,61,.18); color:#166534; opacity:0; transform:translate3d(0,-16px,0) scale(.96); pointer-events:none; }
        .save-toast.show { animation:saveToastIn .35s ease-out forwards, saveToastOut .45s ease-in 4.2s forwards; }
        .save-toast-icon { width:42px; height:42px; border-radius:14px; display:inline-flex; align-items:center; justify-content:center; background:linear-gradient(135deg,#22c55e,#16a34a); color:#fff; box-shadow:0 12px 24px rgba(34,197,94,.28); }
        .save-toast-title { margin:0 0 2px; font-size:.98rem; font-weight:800; color:#14532d; }
        .save-toast-text { margin:0; font-size:.88rem; color:#166534; }
        @keyframes saveToastIn { from { opacity:0; transform:translate3d(0,-16px,0) scale(.96); } to { opacity:1; transform:translate3d(0,0,0) scale(1); } }
        @keyframes saveToastOut { from { opacity:1; transform:translate3d(0,0,0) scale(1); } to { opacity:0; transform:translate3d(0,-12px,0) scale(.98); } }
        .score-summary { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:18px; border-radius:18px; padding:18px; background:var(--summary-bg); color:var(--summary-text); transition:background .25s ease,color .25s ease; }
        .score-summary-label { color:var(--summary-muted); font-size:.88rem; }
        .score-blocks { display:flex; gap:24px; }
        .score-summary-column { display:flex; flex-direction:column; gap:10px; min-width:0; }
        .score-summary-value { font-size:2rem; font-weight:800; line-height:1; }
        .tentative-rank { display:inline-flex; align-items:center; justify-content:center; min-height:54px; padding:12px 16px; border-radius:16px; font-size:1.2rem; font-weight:800; line-height:1.2; }
        .tentative-rank.rank-empty { background:rgba(148,163,184,.16); color:var(--summary-text); }
        .tentative-rank.rank-third { background:#ffedd5; color:#c2410c; }
        .tentative-rank.rank-second { background:#fef3c7; color:#b45309; }
        .tentative-rank.rank-first { background:#dcfce7; color:#15803d; }
        .summary-aside { position:sticky; top:92px; display:flex; flex-direction:column; align-self:stretch; height:100%; min-height:100%; }
        .summary-top { flex:1 1 auto; display:flex; flex-direction:column; min-height:0; }
        .summary-avatars { flex:0 0 auto; min-height:0; display:flex; align-items:center; justify-content:center; gap:clamp(18px,3vw,34px); margin:0 0 18px; padding:clamp(8px,1.8vw,18px) 0 clamp(10px,1.8vw,18px); }
        .summary-avatar { width:clamp(86px,10vw,156px); aspect-ratio:1 / 1; border-radius:50%; border:3px solid rgba(228,168,0,.24); background:linear-gradient(135deg,#fff6cf,#ffe083); color:#6f4e00; display:inline-flex; align-items:center; justify-content:center; overflow:hidden; box-shadow:0 14px 34px rgba(15,23,42,.18); font-size:clamp(1.15rem,2.1vw,1.8rem); font-weight:800; letter-spacing:.04em; flex:0 0 auto; }
        .summary-avatar img { width:100%; height:100%; object-fit:cover; display:block; }
        .summary-avatar.event-avatar { border-color:rgba(15,23,42,.12); background:linear-gradient(135deg,#eef2ff,#dbeafe); color:#1e3a8a; }
        .resume-list { display:flex; flex-direction:column; gap:14px; }
        .resume-item { padding-bottom:12px; border-bottom:1px solid var(--border); }
        .resume-item:last-child { padding-bottom:0; border-bottom:0; }
        .resume-item-label { color:var(--muted); font-size:.8rem; margin-bottom:4px; }
        .resume-item-value { color:var(--text); font-size:.98rem; font-weight:800; word-break:break-word; }
        .summary-aside .score-summary { margin-top:18px; }
        .summary-aside .score-blocks { width:100%; justify-content:flex-start; }
        .summary-footer { margin-top:auto; display:flex; flex-direction:column; gap:18px; }
        .form-actions { display:flex; justify-content:stretch; margin-top:0; padding-top:0; }
        .btn-primary { border:0; border-radius:var(--control-radius); background:linear-gradient(135deg,var(--primary),var(--primary-strong)); color:var(--primary-text); padding:12px 18px; font-weight:800; cursor:pointer; transition:border-radius .25s ease,background .25s ease,color .25s ease; }
        .empty-state { min-height:260px; display:flex; align-items:center; justify-content:center; text-align:center; border:1px dashed var(--border); border-radius:18px; background:linear-gradient(180deg,color-mix(in srgb, var(--surface) 88%, #fff7d6 12%) 0%,color-mix(in srgb, var(--surface) 92%, #fffbef 8%) 100%); padding:32px 20px; }
        .empty-state-box { max-width:480px; }
        .empty-state-icon { width:72px; height:72px; margin:0 auto 18px; border-radius:20px; display:inline-flex; align-items:center; justify-content:center; background:color-mix(in srgb, var(--primary) 18%, #ffffff 82%); color:#b77900; }
        .empty-state-icon .material-icons { font-size:34px; }
        .empty-state h2 { margin:0 0 10px; font-size:1.2rem; font-weight:800; color:var(--text); }
        .empty-state p { margin:0; color:var(--muted); line-height:1.6; }
        .dashboard-style-btn { position:fixed; right:22px; bottom:22px; z-index:65; width:58px; height:58px; border:0; border-radius:18px; display:inline-flex; align-items:center; justify-content:center; background:linear-gradient(135deg,var(--primary),var(--primary-strong)); color:var(--primary-text); box-shadow:0 18px 40px rgba(var(--shadow-color),.22); cursor:pointer; transition:transform .2s ease, box-shadow .2s ease; }
        .dashboard-style-btn:hover { transform:translateY(-2px) rotate(8deg); box-shadow:0 22px 46px rgba(var(--shadow-color),.28); }
        .dashboard-style-btn .material-icons { font-size:28px; }
        .dashboard-style-overlay { position:fixed; inset:0; background:rgba(15,23,42,.42); backdrop-filter:blur(4px); z-index:69; opacity:0; pointer-events:none; transition:opacity .25s ease; }
        .dashboard-style-overlay.open { opacity:1; pointer-events:auto; }
        .dashboard-style-drawer { position:fixed; top:0; right:0; width:min(430px,100vw); height:100vh; z-index:70; background:var(--surface); border-left:1px solid var(--border); box-shadow:-20px 0 48px rgba(var(--shadow-color),.18); display:flex; flex-direction:column; transform:translateX(100%); transition:transform .3s ease,background .25s ease,border-color .25s ease; }
        .dashboard-style-drawer.open { transform:translateX(0); }
        .style-drawer-header { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; padding:22px 22px 18px; border-bottom:1px solid var(--border); }
        .style-drawer-title { margin:0; font-size:1.2rem; font-weight:800; color:var(--text); }
        .style-drawer-caption { margin:6px 0 0; color:var(--muted); font-size:.9rem; line-height:1.5; }
        .style-drawer-close { width:42px; height:42px; border:1px solid var(--border); border-radius:14px; background:var(--surface); color:var(--text); display:inline-flex; align-items:center; justify-content:center; cursor:pointer; }
        .style-drawer-body { flex:1; overflow:auto; padding:18px 22px 24px; display:flex; flex-direction:column; gap:18px; }
        .style-group { border:1px solid var(--border); border-radius:20px; padding:16px; background:linear-gradient(180deg,var(--surface) 0%,color-mix(in srgb, var(--surface) 90%, #eef2ff 10%) 100%); }
        .style-group h4 { margin:0 0 12px; font-size:.96rem; font-weight:800; color:var(--text); }
        .style-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; }
        .style-field { display:flex; flex-direction:column; gap:6px; }
        .style-field.full { grid-column:1 / -1; }
        .style-field label { font-size:.8rem; font-weight:700; color:var(--muted); }
        .style-field input[type="color"] { width:100%; height:42px; border:1px solid var(--border); border-radius:12px; background:var(--surface); padding:4px; cursor:pointer; }
        .style-field input[type="range"] { width:100%; }
        .style-range-value { font-size:.82rem; font-weight:800; color:var(--text); }
        .style-actions { display:flex; gap:10px; padding-top:4px; }
        .style-btn-secondary { border:1px solid var(--border); border-radius:14px; background:var(--surface); color:var(--text); padding:11px 14px; font-weight:700; cursor:pointer; flex:1; }
        .style-presets { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:10px; }
        .style-preset-btn { border:1px solid var(--border); border-radius:16px; padding:10px; background:var(--surface); cursor:pointer; color:var(--text); text-align:left; }
        .style-preset-swatch { height:10px; border-radius:999px; margin-bottom:8px; }
        @media (max-width:1180px) { .form-layout { grid-template-columns:1fr; } .summary-aside { position:static; min-height:auto; } .summary-top { flex:0 0 auto; } .top-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } .questions-card .criteria-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
        @media (max-width:860px) { .navbar { flex-wrap:wrap; padding:12px 16px; } .navbar-actions { width:100%; justify-content:space-between; } .content { padding:16px; } .panel-card { padding:18px; border-radius:18px; } .top-grid,.form-grid,.criteria-grid,.questions-card .criteria-grid,.confirm-grid,.style-grid,.style-presets,.score-summary { grid-template-columns:1fr; } .summary-avatars { gap:18px; } .summary-avatar { width:clamp(84px,22vw,128px); } .score-blocks { width:100%; justify-content:space-between; } .btn-primary { width:100%; } .navbar-subtitle { display:none; } .navbar-user { font-size:1.08rem; } .dashboard-style-drawer { width:min(100vw,430px); } }
        @media (max-width:560px) { html { font-size:13px; } .content { padding:12px; } .panel-card { padding:16px; border-radius:16px; } .summary-avatars { justify-content:flex-start; } .summary-avatar { width:clamp(78px,24vw,104px); } .logout-link span:last-child { display:none; } .score-summary-value { font-size:1.6rem; } .score-blocks { gap:16px; } .dashboard-style-btn { right:14px; bottom:14px; width:54px; height:54px; border-radius:16px; } .style-drawer-header,.style-drawer-body { padding-left:16px; padding-right:16px; } }
    </style>
</head>
<body>
    <div class="page">
        <header class="navbar">
            <div>
                <div class="navbar-title"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></div>
                <div class="navbar-subtitle"><?= htmlspecialchars($pageSubtitle, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="navbar-actions">
                <div class="navbar-user"><?= htmlspecialchars($usuarioSesion, ENT_QUOTES, 'UTF-8') ?></div>
                <a href="../../logout.php" class="logout-link">
                    <span class="material-icons">logout</span>
                    <span>Salir</span>
                </a>
            </div>
        </header>

        <main class="content">
            <div class="page-shell">
                <?php if (($estadoTablas['faltantes'] ?? []) && $mensajeTipo !== 'danger'): ?>
                    <section class="panel-card">
                        <div class="alert-inline warning">Todavia faltan tablas del modulo: <strong><?= htmlspecialchars(implode(', ', $estadoTablas['faltantes']), ENT_QUOTES, 'UTF-8') ?></strong>.</div>
                    </section>
                <?php endif; ?>

                <?php if ($formularioSeleccionado): ?>
                    <section>

                        <form method="post" class="form-stack" id="evaluacionForm">
                            <input type="hidden" name="guardar_evaluacion" value="1">
                            <div class="form-layout">
                                <div class="form-main form-shell">
                                    <div class="panel-card">
                                        <h3 class="section-title">Datos de la evaluacion</h3>
                                        <div class="top-grid">
                                            <div class="form-field">
                                                <label for="evento_nombre">Evento</label>
                                                <div class="readonly-field" id="evento_nombre"><?= htmlspecialchars((string) ($formularioSeleccionado['evento_nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                            </div>
                                            <div class="form-field">
                                                <label for="categoria">Categoria</label>
                                                <select id="categoria" name="categoria" onchange="window.location.href='?categoria=' + encodeURIComponent(this.value);">
                                                    <?php foreach ($categoriasDisponibles as $categoria): ?>
                                                        <option value="<?= htmlspecialchars((string) $categoria, ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($formData['categoria'] ?? '') === (string) $categoria ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars((string) $categoria, ENT_QUOTES, 'UTF-8') ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-field">
                                                <label for="formulario_id">Estilo</label>
                                                <select id="formulario_id" name="formulario_id" onchange="window.location.href='?categoria=' + encodeURIComponent(document.getElementById('categoria').value) + '&formulario_id=' + this.value;">
                                                    <?php foreach ($subcategoriasDisponibles as $formulario): ?>
                                                        <option value="<?= (int) ($formulario['id'] ?? 0) ?>" <?= (int) ($formData['formulario_id'] ?? 0) === (int) ($formulario['id'] ?? 0) ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars((string) ($formulario['subcategoria'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-field">
                                                <label for="competidor_numero">N° del competidor</label>
                                                <select id="competidor_numero" name="competidor_numero" required>
                                                    <option value="">Seleccionar</option>
                                                    <?php for ($i = 1; $i <= 2000; $i++): ?>
                                                        <option value="<?= $i ?>" <?= (string) ($formData['competidor_numero'] ?? '') === (string) $i ? 'selected' : '' ?>><?= $i ?></option>
                                                    <?php endfor; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="panel-card questions-card">
                                        <h3 class="section-title">Criterios de evaluacion</h3>
                                        <div class="criteria-grid">
                                            <?php foreach (($formularioSeleccionado['criterios'] ?? []) as $criterio): ?>
                                                <?php
                                                $clave = (string) ($criterio['criterio_clave'] ?? '');
                                                $maximo = number_format((float) ($criterio['puntaje_maximo'] ?? 0), 1, '.', '');
                                                $actual = number_format((float) ($formData['puntajes'][$clave] ?? '0'), 1, '.', '');
                                                ?>
                                                <div class="criteria-card">
                                                    <div class="criteria-title"><?= htmlspecialchars((string) ($criterio['criterio_nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                                    <div class="criteria-meta">Puntaje maximo: <?= htmlspecialchars(str_replace('.', ',', $maximo), ENT_QUOTES, 'UTF-8') ?></div>
                                                    <div class="form-field">
                                                        <label for="puntaje_<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>">Puntaje otorgado</label>
                                                        <div class="slider-row">
                                                            <input type="range" id="puntaje_<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>" name="puntajes[<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>]" class="score-slider" min="0" max="<?= htmlspecialchars($maximo, ENT_QUOTES, 'UTF-8') ?>" step="0.1" value="<?= htmlspecialchars($actual, ENT_QUOTES, 'UTF-8') ?>" required>
                                                            <span class="slider-value" data-score-output><?= htmlspecialchars(str_replace('.', ',', $actual), ENT_QUOTES, 'UTF-8') ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>

                                <aside class="panel-card summary-aside">
                                    <div class="summary-top">
                                        <div class="summary-avatars" aria-hidden="true">
                                            <div class="summary-avatar" title="Jurado">
                                                <?php if ($juradoAvatarUrl !== ''): ?>
                                                    <img src="<?= htmlspecialchars($juradoAvatarUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Foto del jurado <?= htmlspecialchars($juradoNombre, ENT_QUOTES, 'UTF-8') ?>">
                                                <?php else: ?>
                                                    <span><?= htmlspecialchars($juradoInitials, ENT_QUOTES, 'UTF-8') ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="summary-avatar event-avatar" title="Evento">
                                                <?php if ($eventoImagenUrl !== ''): ?>
                                                    <img src="<?= htmlspecialchars($eventoImagenUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Imagen del evento <?= htmlspecialchars($eventoNombre, ENT_QUOTES, 'UTF-8') ?>">
                                                <?php else: ?>
                                                    <span><?= htmlspecialchars($eventoInitials, ENT_QUOTES, 'UTF-8') ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <h3 class="section-title">Resumen</h3>
                                        <p class="section-caption">Vista previa de la calificacion que estas cargando.</p>

                                        <div class="resume-list">
                                            <div class="resume-item">
                                                <div class="resume-item-label">Subcategoria</div>
                                                <div class="resume-item-value" id="resumenFormulario"><?= htmlspecialchars((string) ($formularioSeleccionado['subcategoria'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                            </div>
                                            <div class="resume-item">
                                                <div class="resume-item-label">Jurado</div>
                                                <div class="resume-item-value"><?= htmlspecialchars($usuarioSesion, ENT_QUOTES, 'UTF-8') ?></div>
                                            </div>
                                            <div class="resume-item">
                                                <div class="resume-item-label">Competencia</div>
                                                <div class="resume-item-value"><?= htmlspecialchars((string) ($formularioSeleccionado['evento_nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                            </div>
                                            <div class="resume-item">
                                                <div class="resume-item-label">Categoria</div>
                                                <div class="resume-item-value" id="resumenCategoria"><?= htmlspecialchars((string) ($formularioSeleccionado['categoria'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                            </div>
                                            <div class="resume-item">
                                                <div class="resume-item-label">N° del competidor</div>
                                                <div class="resume-item-value" id="resumenCompetidorNumero"><?= htmlspecialchars((string) ($formData['competidor_numero'] ?? ''), ENT_QUOTES, 'UTF-8') ?: 'Sin completar' ?></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="summary-footer">
                                        <div class="score-summary">
                                            <div class="score-summary-column">
                                                <div class="score-summary-label">Resultado de la calificacion</div>
                                                <div class="score-blocks">
                                                    <div>
                                                        <div class="score-summary-value" id="puntajeTotal">0</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="score-summary-column">
                                                <div class="score-summary-label">Puesto tentativo</div>
                                                <div class="tentative-rank rank-empty" id="puestoTentativo">Sin definir</div>
                                            </div>
                                        </div>

                                        <div class="form-actions">
                                            <button type="submit" class="btn-primary" id="guardarCalificacionBtn">Guardar calificacion</button>
                                        </div>
                                    </div>
                                </aside>
                            </div>
                        </form>
                    </section>
                <?php else: ?>
                    <section class="panel-card">
                        <div class="empty-state">
                            <div class="empty-state-box">
                                <div class="empty-state-icon" aria-hidden="true"><span class="material-icons">fact_check</span></div>
                                <h2>No hay subcategorias activas</h2>
                                <p>El administrador todavia no publico ninguna subcategoria de calificacion para que puedas evaluar competidores.</p>
                            </div>
                        </div>
                    </section>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <button type="button" class="dashboard-style-btn" id="dashboardStyleBtn" aria-label="Personalizar dashboard">
        <span class="material-icons">settings</span>
    </button>

    <div class="dashboard-style-overlay" id="dashboardStyleOverlay"></div>

    <aside class="dashboard-style-drawer" id="dashboardStyleDrawer" aria-hidden="true">
        <div class="style-drawer-header">
            <div>
                <h3 class="style-drawer-title">Personalizar dashboard</h3>
                <p class="style-drawer-caption">Elegí un preset visual y ajustá solo el tamaño de la letra.</p>
            </div>
            <button type="button" class="style-drawer-close" id="dashboardStyleClose" aria-label="Cerrar panel">
                <span class="material-icons">close</span>
            </button>
        </div>
        <div class="style-drawer-body">
            <section class="style-group">
                <h4>Presets</h4>
                <div class="style-presets">
                    <button type="button" class="style-preset-btn" data-preset="default">
                        <div class="style-preset-swatch" style="background:linear-gradient(90deg,#fffdf7,#e4a800,#1f2937)"></div>
                        Clasico
                    </button>
                    <button type="button" class="style-preset-btn" data-preset="midnight">
                        <div class="style-preset-swatch" style="background:linear-gradient(90deg,#0f172a,#38bdf8,#e2e8f0)"></div>
                        Midnight
                    </button>
                    <button type="button" class="style-preset-btn" data-preset="forest">
                        <div class="style-preset-swatch" style="background:linear-gradient(90deg,#f3fff6,#22c55e,#14532d)"></div>
                        Forest
                    </button>
                    <button type="button" class="style-preset-btn" data-preset="sunset">
                        <div class="style-preset-swatch" style="background:linear-gradient(90deg,#fff7ed,#f97316,#7c2d12)"></div>
                        Sunset
                    </button>
                    <button type="button" class="style-preset-btn" data-preset="ocean">
                        <div class="style-preset-swatch" style="background:linear-gradient(90deg,#ecfeff,#06b6d4,#164e63)"></div>
                        Ocean
                    </button>
                    <button type="button" class="style-preset-btn" data-preset="contrast">
                        <div class="style-preset-swatch" style="background:linear-gradient(90deg,#000000,#fde047,#ffffff)"></div>
                        Alto contraste
                    </button>
                </div>
            </section>

            <section class="style-group">
                <h4>Lectura</h4>
                <div class="style-grid">
                    <div class="style-field full">
                        <label for="styleBaseFontSize">Tamaño base</label>
                        <input type="range" id="styleBaseFontSize" data-theme-control="baseFontSize" min="12" max="18" step="1">
                        <span class="style-range-value" data-theme-display="baseFontSize"></span>
                    </div>
                </div>
            </section>

            <div class="style-actions">
                <button type="button" class="style-btn-secondary" id="styleResetBtn">Restablecer</button>
                <button type="button" class="style-btn-secondary" id="styleCloseBtn">Cerrar</button>
            </div>
        </div>
    </aside>

    <?php if ($mensaje !== '' && $mensajeTipo === 'success'): ?>
        <div id="saveToast" class="save-toast show" role="status" aria-live="polite">
            <div class="save-toast-icon" aria-hidden="true">
                <span class="material-icons">check</span>
            </div>
            <div>
                <p class="save-toast-title">Calificacion registrada</p>
                <p class="save-toast-text"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </div>
    <?php endif; ?>

    <div id="confirmacionModal" class="modal hidden">
        <div class="modal-content">
            <h3 id="confirmModalTitle">Confirmar calificacion</h3>
            <div class="confirm-summary">
                <div id="confirmModalStatus" class="confirm-status error hidden"></div>
                <div class="confirm-section">
                    <div class="confirm-title">Resumen de la evaluacion</div>
                    <div class="confirm-grid">
                        <div>
                            <div class="confirm-item-label">Evento</div>
                            <div class="confirm-item-value" id="modalEvento"></div>
                        </div>
                        <div>
                            <div class="confirm-item-label">Jurado</div>
                            <div class="confirm-item-value" id="modalJurado"></div>
                        </div>
                        <div>
                            <div class="confirm-item-label">Categoria</div>
                            <div class="confirm-item-value" id="modalCategoria"></div>
                        </div>
                        <div>
                            <div class="confirm-item-label">Subcategoria</div>
                            <div class="confirm-item-value" id="modalSubcategoria"></div>
                        </div>
                        <div>
                            <div class="confirm-item-label">N° del competidor</div>
                            <div class="confirm-item-value" id="modalCompetidor"></div>
                        </div>
                        <div>
                            <div class="confirm-item-label">Resultado</div>
                            <div class="confirm-item-value" id="modalResultado"></div>
                        </div>
                    </div>
                </div>
                <div class="confirm-section">
                    <div class="confirm-title">Criterios seleccionados</div>
                    <ul class="confirm-criteria-list" id="modalCriterios"></ul>
                </div>
            </div>
            <div class="form-buttons">
                <button class="btn btn-aceptar" type="button" id="confirmarGuardadoBtn">Confirmar</button>
                <button class="btn btn-cancelar" type="button" id="cancelarGuardadoBtn">Cancelar</button>
            </div>
        </div>
    </div>

    <script>
        const scoreSelects = document.querySelectorAll('.score-slider');
        const puntajeTotal = document.getElementById('puntajeTotal');
        const puestoTentativo = document.getElementById('puestoTentativo');
        const competidorNumeroInput = document.getElementById('competidor_numero');
        const resumenCompetidorNumero = document.getElementById('resumenCompetidorNumero');
        const formularioSelect = document.getElementById('formulario_id');
        const resumenFormulario = document.getElementById('resumenFormulario');
        const resumenCategoria = document.getElementById('resumenCategoria');
        const categoriaSelect = document.getElementById('categoria');
        const evaluacionForm = document.getElementById('evaluacionForm');
        const confirmacionModal = document.getElementById('confirmacionModal');
        const confirmarGuardadoBtn = document.getElementById('confirmarGuardadoBtn');
        const cancelarGuardadoBtn = document.getElementById('cancelarGuardadoBtn');
        const modalEvento = document.getElementById('modalEvento');
        const modalJurado = document.getElementById('modalJurado');
        const modalCategoria = document.getElementById('modalCategoria');
        const modalSubcategoria = document.getElementById('modalSubcategoria');
        const modalCompetidor = document.getElementById('modalCompetidor');
        const modalResultado = document.getElementById('modalResultado');
        const modalCriterios = document.getElementById('modalCriterios');
        const confirmModalTitle = document.getElementById('confirmModalTitle');
        const confirmModalStatus = document.getElementById('confirmModalStatus');
        const saveToast = document.getElementById('saveToast');
        const dashboardStyleBtn = document.getElementById('dashboardStyleBtn');
        const dashboardStyleDrawer = document.getElementById('dashboardStyleDrawer');
        const dashboardStyleOverlay = document.getElementById('dashboardStyleOverlay');
        const dashboardStyleClose = document.getElementById('dashboardStyleClose');
        const styleResetBtn = document.getElementById('styleResetBtn');
        const styleCloseBtn = document.getElementById('styleCloseBtn');
        const themeControls = document.querySelectorAll('[data-theme-control]');
        const themeDisplays = document.querySelectorAll('[data-theme-display]');
        const themePresetButtons = document.querySelectorAll('[data-preset]');
        let envioConfirmado = false;
        let confirmModalMode = 'confirm';
        const serverMessage = <?= json_encode((string) $mensaje, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
        const serverMessageType = <?= json_encode((string) $mensajeTipo, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
        const themeStorageKey = 'jurado_dashboard_custom_theme_v1';
        const dashboardThemeDefaults = {
            baseFontSize: 14,
            pageBgStart: '#fffdf7',
            pageBgEnd: '#f7f8fc',
            surface: '#ffffff',
            border: '#e8ecf4',
            text: '#1f2937',
            muted: '#6a7688',
            primary: '#e4a800',
            primaryStrong: '#f3c23d',
            primaryText: '#3a2b00',
            navbarBg: '#ffffff',
            navbarBorder: '#e8ecf4',
            cardRadius: 20,
            controlRadius: 14,
            panelPadding: 24,
            contentMaxWidth: 1600,
            shadowY: 10,
            shadowBlur: 28,
            shadowAlpha: 0.08,
            summaryBg: '#0f172a',
            summaryText: '#ffffff',
            summaryMuted: '#cbd5e1',
            fieldBg: '#ffffff',
            fieldReadonlyBg: '#f8fafc'
        };
        const dashboardThemePresets = {
            default: { ...dashboardThemeDefaults },
            midnight: {
                ...dashboardThemeDefaults,
                pageBgStart: '#0b1120',
                pageBgEnd: '#172554',
                surface: '#0f172a',
                border: '#334155',
                text: '#e2e8f0',
                muted: '#94a3b8',
                primary: '#38bdf8',
                primaryStrong: '#0ea5e9',
                primaryText: '#082f49',
                navbarBg: '#0f172a',
                navbarBorder: '#334155',
                summaryBg: '#020617',
                summaryText: '#e2e8f0',
                summaryMuted: '#94a3b8',
                fieldBg: '#111827',
                fieldReadonlyBg: '#1e293b',
                shadowY: 16,
                shadowBlur: 40,
                shadowAlpha: 0.24
            },
            forest: {
                ...dashboardThemeDefaults,
                pageBgStart: '#f3fff6',
                pageBgEnd: '#dcfce7',
                surface: '#ffffff',
                border: '#bbf7d0',
                text: '#14532d',
                muted: '#3f7a54',
                primary: '#22c55e',
                primaryStrong: '#16a34a',
                primaryText: '#f0fdf4',
                navbarBg: '#f0fdf4',
                navbarBorder: '#bbf7d0',
                summaryBg: '#14532d',
                summaryText: '#f0fdf4',
                summaryMuted: '#bbf7d0',
                fieldBg: '#ffffff',
                fieldReadonlyBg: '#f0fdf4',
                shadowY: 12,
                shadowBlur: 34,
                shadowAlpha: 0.16
            },
            sunset: {
                ...dashboardThemeDefaults,
                pageBgStart: '#fff7ed',
                pageBgEnd: '#ffedd5',
                surface: '#fffaf5',
                border: '#fdba74',
                text: '#7c2d12',
                muted: '#9a3412',
                primary: '#f97316',
                primaryStrong: '#fb923c',
                primaryText: '#fff7ed',
                navbarBg: '#fff7ed',
                navbarBorder: '#fdba74',
                summaryBg: '#7c2d12',
                summaryText: '#fff7ed',
                summaryMuted: '#fdba74',
                fieldBg: '#ffffff',
                fieldReadonlyBg: '#fff7ed',
                shadowY: 12,
                shadowBlur: 30,
                shadowAlpha: 0.14
            },
            ocean: {
                ...dashboardThemeDefaults,
                pageBgStart: '#ecfeff',
                pageBgEnd: '#cffafe',
                surface: '#f8feff',
                border: '#67e8f9',
                text: '#164e63',
                muted: '#0f766e',
                primary: '#06b6d4',
                primaryStrong: '#0891b2',
                primaryText: '#ecfeff',
                navbarBg: '#ecfeff',
                navbarBorder: '#67e8f9',
                summaryBg: '#164e63',
                summaryText: '#ecfeff',
                summaryMuted: '#67e8f9',
                fieldBg: '#ffffff',
                fieldReadonlyBg: '#ecfeff',
                shadowY: 12,
                shadowBlur: 30,
                shadowAlpha: 0.14
            },
            contrast: {
                ...dashboardThemeDefaults,
                pageBgStart: '#000000',
                pageBgEnd: '#111111',
                surface: '#000000',
                border: '#fde047',
                text: '#ffffff',
                muted: '#fef08a',
                primary: '#fde047',
                primaryStrong: '#facc15',
                primaryText: '#000000',
                navbarBg: '#000000',
                navbarBorder: '#fde047',
                summaryBg: '#ffffff',
                summaryText: '#000000',
                summaryMuted: '#374151',
                fieldBg: '#111111',
                fieldReadonlyBg: '#1a1a1a',
                shadowY: 0,
                shadowBlur: 0,
                shadowAlpha: 0
            }
        };
        let dashboardThemeState = { ...dashboardThemeDefaults };

        function formatScore(value, decimals = 1) {
            return Number(value || 0).toFixed(decimals).replace('.', ',');
        }

        function getTentativeRank(total) {
            if (total >= 95) {
                return { label: 'Primer Lugar', className: 'rank-first' };
            }
            if (total >= 85) {
                return { label: 'Segundo Lugar', className: 'rank-second' };
            }
            if (total >= 74) {
                return { label: 'Tercer Lugar', className: 'rank-third' };
            }
            return { label: 'Sin definir', className: 'rank-empty' };
        }

        function getThemeDisplayValue(key, value) {
            if (['baseFontSize', 'panelPadding', 'cardRadius', 'controlRadius', 'shadowBlur', 'shadowY'].includes(key)) {
                return `${value}px`;
            }
            if (key === 'contentMaxWidth') {
                return `${value}px`;
            }
            if (key === 'shadowAlpha') {
                return Number(value).toFixed(2);
            }
            return String(value);
        }

        function applyDashboardTheme(theme, persist = true) {
            dashboardThemeState = { ...dashboardThemeDefaults, ...theme };
            const root = document.documentElement;
            root.style.setProperty('--base-font-size', `${dashboardThemeState.baseFontSize}px`);
            root.style.setProperty('--page-bg-start', dashboardThemeState.pageBgStart);
            root.style.setProperty('--page-bg-end', dashboardThemeState.pageBgEnd);
            root.style.setProperty('--surface', dashboardThemeState.surface);
            root.style.setProperty('--border', dashboardThemeState.border);
            root.style.setProperty('--text', dashboardThemeState.text);
            root.style.setProperty('--muted', dashboardThemeState.muted);
            root.style.setProperty('--primary', dashboardThemeState.primary);
            root.style.setProperty('--primary-strong', dashboardThemeState.primaryStrong);
            root.style.setProperty('--primary-text', dashboardThemeState.primaryText);
            root.style.setProperty('--navbar-bg', dashboardThemeState.navbarBg);
            root.style.setProperty('--navbar-border', dashboardThemeState.navbarBorder);
            root.style.setProperty('--card-radius', `${dashboardThemeState.cardRadius}px`);
            root.style.setProperty('--control-radius', `${dashboardThemeState.controlRadius}px`);
            root.style.setProperty('--panel-padding', `${dashboardThemeState.panelPadding}px`);
            root.style.setProperty('--content-max-width', `${dashboardThemeState.contentMaxWidth}px`);
            root.style.setProperty('--shadow-y', `${dashboardThemeState.shadowY}px`);
            root.style.setProperty('--shadow-blur', `${dashboardThemeState.shadowBlur}px`);
            root.style.setProperty('--shadow-alpha', String(dashboardThemeState.shadowAlpha));
            root.style.setProperty('--summary-bg', dashboardThemeState.summaryBg);
            root.style.setProperty('--summary-text', dashboardThemeState.summaryText);
            root.style.setProperty('--summary-muted', dashboardThemeState.summaryMuted);
            root.style.setProperty('--field-bg', dashboardThemeState.fieldBg);
            root.style.setProperty('--field-readonly-bg', dashboardThemeState.fieldReadonlyBg);

            themeControls.forEach((control) => {
                const key = control.dataset.themeControl;
                if (!key || !(key in dashboardThemeState)) {
                    return;
                }
                control.value = String(dashboardThemeState[key]);
            });

            themeDisplays.forEach((display) => {
                const key = display.dataset.themeDisplay;
                if (!key || !(key in dashboardThemeState)) {
                    return;
                }
                display.textContent = getThemeDisplayValue(key, dashboardThemeState[key]);
            });

            if (persist) {
                localStorage.setItem(themeStorageKey, JSON.stringify(dashboardThemeState));
            }
        }

        function loadStoredDashboardTheme() {
            try {
                const raw = localStorage.getItem(themeStorageKey);
                if (!raw) {
                    return { ...dashboardThemeDefaults };
                }

                return { ...dashboardThemeDefaults, ...JSON.parse(raw) };
            } catch (error) {
                return { ...dashboardThemeDefaults };
            }
        }

        function normalizeThemeValue(key, value) {
            if (key === 'baseFontSize') {
                return parseInt(String(value), 10);
            }
            return value;
        }

        function openDashboardStyleDrawer() {
            dashboardStyleDrawer?.classList.add('open');
            dashboardStyleOverlay?.classList.add('open');
            dashboardStyleDrawer?.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeDashboardStyleDrawer() {
            dashboardStyleDrawer?.classList.remove('open');
            dashboardStyleOverlay?.classList.remove('open');
            dashboardStyleDrawer?.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        function openConfirmModal() {
            confirmacionModal?.classList.remove('hidden');
        }

        function closeConfirmModal() {
            confirmacionModal?.classList.add('hidden');
        }

        function setConfirmModalMode(mode, message = '') {
            if (!confirmModalTitle || !confirmModalStatus || !confirmarGuardadoBtn) {
                return;
            }

            confirmModalMode = mode;

            if (mode === 'error') {
                confirmModalTitle.textContent = 'No se pudo guardar la calificacion';
                confirmModalStatus.textContent = message;
                confirmModalStatus.classList.remove('hidden');
                confirmarGuardadoBtn.textContent = 'Entendido';
            } else {
                confirmModalTitle.textContent = 'Confirmar calificacion';
                confirmModalStatus.textContent = '';
                confirmModalStatus.classList.add('hidden');
                confirmarGuardadoBtn.textContent = 'Confirmar';
            }
        }

        function actualizarResumen() {
            let total = 0;
            scoreSelects.forEach((select) => {
                const value = parseFloat((select.value || '0').replace(',', '.'));
                total += Number.isNaN(value) ? 0 : value;
                const output = select.closest('.slider-row')?.querySelector('[data-score-output]');
                if (output) {
                    output.textContent = formatScore(value, 1);
                }
            });
            if (puntajeTotal) puntajeTotal.textContent = formatScore(total, 1);
            if (puestoTentativo) {
                const rank = getTentativeRank(total);
                puestoTentativo.textContent = rank.label;
                puestoTentativo.className = `tentative-rank ${rank.className}`;
            }
            if (resumenCompetidorNumero && competidorNumeroInput) {
                const numero = competidorNumeroInput.value.trim();
                resumenCompetidorNumero.textContent = numero !== '' ? numero : 'Sin completar';
            }
            if (resumenFormulario && formularioSelect) {
                resumenFormulario.textContent = formularioSelect.options[formularioSelect.selectedIndex]?.textContent?.trim() || '';
            }
            if (resumenCategoria && categoriaSelect) {
                resumenCategoria.textContent = categoriaSelect.options[categoriaSelect.selectedIndex]?.textContent?.trim() || '';
            }
        }

        function construirResumenModal() {
            if (modalEvento) {
                modalEvento.textContent = document.getElementById('evento_nombre')?.textContent?.trim() || '-';
            }
            if (modalJurado) {
                modalJurado.textContent = <?= json_encode((string) $usuarioSesion, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
            }
            if (modalCategoria && categoriaSelect) {
                modalCategoria.textContent = categoriaSelect.options[categoriaSelect.selectedIndex]?.textContent?.trim() || '-';
            }
            if (modalSubcategoria && formularioSelect) {
                modalSubcategoria.textContent = formularioSelect.options[formularioSelect.selectedIndex]?.textContent?.trim() || '-';
            }
            if (modalCompetidor && competidorNumeroInput) {
                modalCompetidor.textContent = competidorNumeroInput.value.trim() || '-';
            }
            if (modalResultado) {
                modalResultado.textContent = `Total ${puntajeTotal?.textContent || '0,0'} | ${puestoTentativo?.textContent || 'Sin definir'}`;
            }
            if (modalCriterios) {
                modalCriterios.innerHTML = '';
                scoreSelects.forEach((select) => {
                    const card = select.closest('.criteria-card');
                    const criterio = card?.querySelector('.criteria-title')?.textContent?.trim() || 'Criterio';
                    const item = document.createElement('li');
                    item.className = 'confirm-criteria-item';
                    item.innerHTML = `<span>${criterio}</span><span>${formatScore(select.value, 1)}</span>`;
                    modalCriterios.appendChild(item);
                });
            }
        }

        scoreSelects.forEach((select) => {
            select.addEventListener('input', actualizarResumen);
            select.addEventListener('change', actualizarResumen);
        });
        competidorNumeroInput?.addEventListener('change', actualizarResumen);
        cancelarGuardadoBtn?.addEventListener('click', closeConfirmModal);
        confirmacionModal?.addEventListener('click', (event) => {
            if (event.target === confirmacionModal) {
                closeConfirmModal();
            }
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && confirmacionModal && !confirmacionModal.classList.contains('hidden')) {
                closeConfirmModal();
            }
        });
        evaluacionForm?.addEventListener('submit', (event) => {
            if (envioConfirmado) {
                return;
            }

            event.preventDefault();
            if (!evaluacionForm.checkValidity()) {
                evaluacionForm.reportValidity();
                return;
            }

            actualizarResumen();
            construirResumenModal();
            setConfirmModalMode('confirm');
            openConfirmModal();
        });
        confirmarGuardadoBtn?.addEventListener('click', () => {
            if (confirmModalMode === 'error') {
                closeConfirmModal();
                return;
            }
            envioConfirmado = true;
            closeConfirmModal();
            evaluacionForm?.submit();
        });

        if (serverMessageType === 'danger' && serverMessage !== '') {
            actualizarResumen();
            construirResumenModal();
            setConfirmModalMode('error', serverMessage);
            openConfirmModal();
        }
        if (saveToast) {
            saveToast.addEventListener('animationend', (event) => {
                if (event.animationName === 'saveToastOut') {
                    saveToast.remove();
                }
            });
        }
        dashboardStyleBtn?.addEventListener('click', openDashboardStyleDrawer);
        dashboardStyleOverlay?.addEventListener('click', closeDashboardStyleDrawer);
        dashboardStyleClose?.addEventListener('click', closeDashboardStyleDrawer);
        styleCloseBtn?.addEventListener('click', closeDashboardStyleDrawer);
        styleResetBtn?.addEventListener('click', () => applyDashboardTheme(dashboardThemeDefaults));
        themePresetButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const preset = button.dataset.preset;
                if (!preset || !dashboardThemePresets[preset]) {
                    return;
                }
                applyDashboardTheme(dashboardThemePresets[preset]);
            });
        });
        themeControls.forEach((control) => {
            const eventName = control.tagName === 'SELECT' ? 'change' : 'input';
            control.addEventListener(eventName, () => {
                const key = control.dataset.themeControl;
                if (!key) {
                    return;
                }
                applyDashboardTheme({
                    ...dashboardThemeState,
                    [key]: normalizeThemeValue(key, control.value)
                });
            });
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && dashboardStyleDrawer?.classList.contains('open')) {
                closeDashboardStyleDrawer();
            }
        });

        function lockFrameworkTheme() {
            const root = document.documentElement;
            root.dataset.theme = 'light';
            root.dataset.themeMode = 'light';
            root.dataset.themeAccent = 'amber';
            root.dataset.themeSurface = 'solid';
            root.classList.add('theme-ready');
            ['impulsa_theme_mode', 'impulsa_theme_accent', 'impulsa_theme_surface', 'impulsa_theme_motion'].forEach((key) => localStorage.removeItem(key));
            document.getElementById('themeSettingsToggle')?.remove();
            document.getElementById('themeSettingsDrawer')?.remove();
            document.getElementById('themeSettingsOverlay')?.remove();
        }

        actualizarResumen();
        applyDashboardTheme(loadStoredDashboardTheme(), false);
        lockFrameworkTheme();
        window.addEventListener('load', lockFrameworkTheme);
    </script>
</body>
</html>
