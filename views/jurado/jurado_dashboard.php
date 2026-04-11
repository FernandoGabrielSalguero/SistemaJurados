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
        :root { --surface:#fff; --border:#e8ecf4; --text:#1f2937; --muted:#6a7688; --primary:#e4a800; --success:#15803d; --success-soft:#ecfdf3; --danger:#b91c1c; --danger-soft:#fef2f2; --warning:#c2410c; --warning-soft:#fff7ed; --shadow:0 10px 28px rgba(15,23,42,.08); }
        * { box-sizing:border-box; }
        html { font-size:14px; }
        body { margin:0; font-family:'Montserrat',sans-serif; background:linear-gradient(180deg,#fffdf7 0%,#f7f8fc 100%); color:var(--text); }
        .theme-settings-btn,.theme-drawer,.theme-settings-overlay { display:none !important; visibility:hidden !important; pointer-events:none !important; }
        .page { min-height:100vh; display:flex; flex-direction:column; }
        .navbar { background:rgba(255,255,255,.9); backdrop-filter:blur(14px); border-bottom:1px solid rgba(232,236,244,.96); display:flex; justify-content:space-between; align-items:center; gap:16px; padding:14px 20px; position:sticky; top:0; z-index:20; }
        .navbar-title { font-size:1rem; font-weight:800; color:#1f2937; line-height:1.2; }
        .navbar-subtitle { color:var(--muted); font-size:.85rem; line-height:1.25; }
        .navbar-user { font-size:1.22rem; font-weight:800; color:#172033; line-height:1.1; text-align:right; }
        .navbar-actions { display:flex; align-items:center; gap:12px; }
        .logout-link { display:inline-flex; align-items:center; gap:7px; border-radius:999px; background:#fff; color:var(--danger); border:1px solid #fecaca; padding:9px 14px; text-decoration:none; font-weight:700; font-size:.92rem; }
        .content { width:100%; max-width:none; margin:0; padding:24px 20px 32px; }
        .page-shell { display:flex; flex-direction:column; gap:20px; }
        .panel-card { background:var(--surface); border:1px solid var(--border); border-radius:20px; box-shadow:var(--shadow); padding:24px; width:100%; }
        .hero-card h1,.section-title { margin:0 0 6px; font-size:1.25rem; font-weight:800; color:#202633; line-height:1.2; }
        .hero-card p,.section-caption,.field-help { margin:0; color:var(--muted); line-height:1.5; font-size:.92rem; }
        .alert-inline { margin-top:14px; padding:14px 16px; border-radius:16px; font-size:.92rem; line-height:1.5; border:1px solid #dbeafe; background:#eff6ff; color:#1d4ed8; }
        .alert-inline.success { background:var(--success-soft); color:var(--success); border-color:#bbf7d0; }
        .alert-inline.danger { background:var(--danger-soft); color:var(--danger); border-color:#fecaca; }
        .alert-inline.warning { background:var(--warning-soft); color:var(--warning); border-color:#fed7aa; }
        .form-stack { display:flex; flex-direction:column; gap:18px; }
        .form-layout { display:grid; grid-template-columns:minmax(0,1.65fr) minmax(300px,.75fr); gap:20px; align-items:start; }
        .form-main { display:flex; flex-direction:column; gap:18px; min-width:0; }
        .top-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; }
        .form-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:14px; }
        .criteria-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:14px; }
        .form-field { display:flex; flex-direction:column; gap:7px; }
        .form-field.full { grid-column:1 / -1; }
        .form-field label { font-size:.84rem; font-weight:700; color:#334155; }
        .form-field input[type="text"], .form-field select { width:100%; min-height:46px; border-radius:14px; border:1px solid #d6dfef; padding:10px 14px; background:#fff; color:#111827; font-size:.95rem; }
        .form-shell { display:flex; flex-direction:column; gap:18px; }
        .readonly-field { display:flex; align-items:center; min-height:46px; border-radius:14px; border:1px solid #d6dfef; padding:10px 14px; background:#f8fafc; color:#111827; font-size:.95rem; font-weight:700; }
        .summary-card,.criteria-card { border:1px solid var(--border); border-radius:18px; padding:16px; background:linear-gradient(180deg,#fff 0%,#fffef8 100%); }
        .summary-label,.criteria-meta { color:var(--muted); font-size:.82rem; margin-bottom:6px; }
        .summary-value { font-size:1.15rem; font-weight:800; line-height:1.2; }
        .criteria-title { font-weight:800; margin:0 0 4px; color:#202633; }
        .questions-card .criteria-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .slider-row { display:flex; align-items:center; gap:12px; }
        .score-slider { flex:1; accent-color:#e4a800; }
        .slider-value { min-width:54px; text-align:right; font-weight:800; color:#202633; }
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
        .score-summary { display:flex; align-items:center; justify-content:space-between; gap:14px; border-radius:18px; padding:18px; background:#0f172a; color:#fff; }
        .score-summary-label { color:rgba(255,255,255,.72); font-size:.88rem; }
        .score-blocks { display:flex; gap:24px; }
        .score-summary-value { font-size:2rem; font-weight:800; line-height:1; }
        .summary-aside { position:sticky; top:92px; }
        .resume-list { display:flex; flex-direction:column; gap:14px; }
        .resume-item { padding-bottom:12px; border-bottom:1px solid var(--border); }
        .resume-item:last-child { padding-bottom:0; border-bottom:0; }
        .resume-item-label { color:var(--muted); font-size:.8rem; margin-bottom:4px; }
        .resume-item-value { color:#202633; font-size:.98rem; font-weight:800; word-break:break-word; }
        .summary-aside .score-summary { margin-top:18px; flex-direction:column; align-items:flex-start; }
        .summary-aside .score-blocks { width:100%; justify-content:space-between; }
        .form-actions { display:flex; justify-content:stretch; margin-top:18px; }
        .btn-primary { border:0; border-radius:14px; background:linear-gradient(135deg,#e4a800,#f3c23d); color:#3a2b00; padding:12px 18px; font-weight:800; cursor:pointer; }
        .empty-state { min-height:260px; display:flex; align-items:center; justify-content:center; text-align:center; border:1px dashed #e8ecf4; border-radius:18px; background:linear-gradient(180deg,#fffef8 0%,#fffdf4 100%); padding:32px 20px; }
        .empty-state-box { max-width:480px; }
        .empty-state-icon { width:72px; height:72px; margin:0 auto 18px; border-radius:20px; display:inline-flex; align-items:center; justify-content:center; background:#fff6d9; color:#b77900; }
        .empty-state-icon .material-icons { font-size:34px; }
        .empty-state h2 { margin:0 0 10px; font-size:1.2rem; font-weight:800; color:#202633; }
        .empty-state p { margin:0; color:var(--muted); line-height:1.6; }
        @media (max-width:1180px) { .form-layout { grid-template-columns:1fr; } .summary-aside { position:static; } .top-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } .questions-card .criteria-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
        @media (max-width:860px) { .navbar { flex-wrap:wrap; padding:12px 16px; } .navbar-actions { width:100%; justify-content:space-between; } .content { padding:16px; } .panel-card { padding:18px; border-radius:18px; } .top-grid,.form-grid,.criteria-grid,.questions-card .criteria-grid,.confirm-grid { grid-template-columns:1fr; } .score-summary { flex-direction:column; align-items:flex-start; } .score-blocks { width:100%; justify-content:space-between; } .btn-primary { width:100%; } .navbar-subtitle { display:none; } .navbar-user { font-size:1.08rem; } }
        @media (max-width:560px) { html { font-size:13px; } .content { padding:12px; } .panel-card { padding:16px; border-radius:16px; } .logout-link span:last-child { display:none; } .score-summary-value { font-size:1.6rem; } .score-blocks { gap:16px; } }
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
                <?php if ($mensaje !== ''): ?>
                    <section class="panel-card">
                        <div class="alert-inline <?= htmlspecialchars($mensajeTipo, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div>
                        <?php if ($estadoTablas['faltantes'] ?? []): ?>
                            <div class="alert-inline warning">Todavia faltan tablas del modulo: <strong><?= htmlspecialchars(implode(', ', $estadoTablas['faltantes']), ENT_QUOTES, 'UTF-8') ?></strong>.</div>
                        <?php endif; ?>
                    </section>
                <?php elseif ($estadoTablas['faltantes'] ?? []): ?>
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
                                                <label for="formulario_id">Subcategoria</label>
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

                                    <div class="score-summary">
                                        <div>
                                            <div class="score-summary-label">Resultado de la calificacion</div>
                                            <div class="score-summary-label">Se actualiza en tiempo real.</div>
                                        </div>
                                        <div class="score-blocks">
                                            <div>
                                                <div class="score-summary-label">Total</div>
                                                <div class="score-summary-value" id="puntajeTotal">0</div>
                                            </div>
                                            <div>
                                                <div class="score-summary-label">Promedio</div>
                                                <div class="score-summary-value" id="puntajePromedio">0.00</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-actions">
                                        <button type="submit" class="btn-primary" id="guardarCalificacionBtn">Guardar calificacion</button>
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

    <div id="confirmacionModal" class="modal hidden">
        <div class="modal-content">
            <h3>Confirmar calificacion</h3>
            <div class="confirm-summary">
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
        const puntajePromedio = document.getElementById('puntajePromedio');
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
        let envioConfirmado = false;

        function formatScore(value, decimals = 1) {
            return Number(value || 0).toFixed(decimals).replace('.', ',');
        }

        function openModal() {
            confirmacionModal?.classList.remove('hidden');
        }

        function closeModal() {
            confirmacionModal?.classList.add('hidden');
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
            const promedio = scoreSelects.length > 0 ? (total / scoreSelects.length) : 0;
            if (puntajeTotal) puntajeTotal.textContent = formatScore(total, 1);
            if (puntajePromedio) puntajePromedio.textContent = formatScore(promedio, 2);
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
                modalResultado.textContent = `Total ${puntajeTotal?.textContent || '0,0'} | Promedio ${puntajePromedio?.textContent || '0,00'}`;
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
        cancelarGuardadoBtn?.addEventListener('click', closeModal);
        confirmacionModal?.addEventListener('click', (event) => {
            if (event.target === confirmacionModal) {
                closeModal();
            }
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && confirmacionModal && !confirmacionModal.classList.contains('hidden')) {
                closeModal();
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
            openModal();
        });
        confirmarGuardadoBtn?.addEventListener('click', () => {
            envioConfirmado = true;
            closeModal();
            evaluacionForm?.submit();
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
        lockFrameworkTheme();
        window.addEventListener('load', lockFrameworkTheme);
    </script>
</body>
</html>
