<?php
require_once __DIR__ . '/../../controllers/emprendedor_dashboardController.php';

$displayName = $perfil['apodo'] ?? $perfil['nombre'] ?? $_SESSION['correo'] ?? 'Emprendedor';
$displayName = htmlspecialchars((string) $displayName, ENT_QUOTES, 'UTF-8');
$avatarUrl = obtenerAvatarUrl($perfil['avatar_path'] ?? ($_SESSION['avatar_path'] ?? null));
$avatarInitial = obtenerInicialAvatar($displayName);

$correoVerificado = !empty($perfil['check_correo']);
$misionTexto = htmlspecialchars((string) ($misionResumen['mision_estructura'] ?? ''), ENT_QUOTES, 'UTF-8');
$visionTexto = htmlspecialchars((string) ($visionResumen['vision_estructura'] ?? ''), ENT_QUOTES, 'UTF-8');
$buyerPersonaTexto = htmlspecialchars((string) ($buyerPersonaResumen['buyer_persona_estructura'] ?? ''), ENT_QUOTES, 'UTF-8');
$ocultarIntro = $misionCompletada && $visionCompletada && $buyerPersonaCompletado;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Impulsa - Mi espacio</title>

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="../../assets/framework/framework.css">
    <script src="../../assets/framework/framework.js" defer></script>

    <style>
        .profile-card {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .profile-avatar {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 22px;
            font-weight: 700;
            flex-shrink: 0;
            text-transform: uppercase;
        }
        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            display: block;
        }

        .profile-info h2 {
            margin: 0 0 4px;
            font-size: 20px;
        }

        .profile-info p {
            margin: 0;
            font-size: 14px;
            color: #6b7280;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-success {
            background: #dcfce7;
            color: #15803d;
        }

        .badge-warning {
            background: #fef3c7;
            color: #b45309;
        }

        .navbar {
            justify-content: space-between;
        }

        .navbar-left {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sidebar-brand-icon {
            width: 32px;
            height: 32px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .intro-card h3 {
            margin: 0 0 16px;
            font-size: 24px;
        }

        .intro-card p {
            margin: 0 0 14px;
            line-height: 1.65;
            color: #4b5563;
        }

        .intro-card p:last-child {
            margin-bottom: 0;
        }

        .roadmap-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px;
            margin-top: 20px;
        }

        .roadmap-card {
            min-height: 230px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 18px;
        }

        .roadmap-step {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 74px;
            height: 34px;
            padding: 0 12px;
            border-radius: 999px;
            background: #eef2ff;
            color: #4338ca;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 14px;
        }

        .roadmap-card h4 {
            margin: 0 0 10px;
            font-size: 20px;
        }

        .roadmap-card p {
            margin: 0;
            color: #6b7280;
            line-height: 1.6;
        }

        .roadmap-summary {
            color: #374151 !important;
            white-space: pre-line;
        }

        .roadmap-summary.is-long {
            display: -webkit-box;
            -webkit-line-clamp: 5;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .roadmap-card .btn {
            width: 100%;
        }

        .roadmap-card .btn:disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }

        .sidebar-menu li.is-disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        .roadmap-checklist {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .roadmap-checklist li {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #4b5563;
            font-size: 15px;
        }

        .roadmap-check {
            width: 20px;
            height: 20px;
            border-radius: 6px;
            border: 2px solid #d1d5db;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }

        .roadmap-check .material-icons {
            font-size: 14px;
            opacity: 0;
            transform: scale(0.8);
            transition: all 0.2s ease;
        }

        .roadmap-checklist li.is-done .roadmap-check {
            border-color: #22c55e;
            background: #dcfce7;
            color: #15803d;
        }

        .roadmap-checklist li.is-done .roadmap-check .material-icons {
            opacity: 1;
            transform: scale(1);
        }
    </style>
</head>

<body>
    <div class="layout">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <img src="../../assets/institucionales/icons/Isotipo grande.png" alt="Impulsa Emprende" class="sidebar-brand-icon">
                <span class="logo-text">impulsa emprende</span>
            </div>
            <nav class="sidebar-menu">
                <ul>
                    <li class="active" onclick="location.href='emprendedor_dashboard.php'">
                        <span class="material-icons" style="color:#6366f1">home</span>
                        <span class="link-text">Inicio</span>
                    </li>
                    <li onclick="location.href='emprendedor_mision.php'">
                        <span class="material-icons" style="color:#6366f1">track_changes</span>
                        <span class="link-text">Misión</span>
                    </li>
                    <li onclick="location.href='emprendedor_vision.php'">
                        <span class="material-icons" style="color:#6366f1">lightbulb</span>
                        <span class="link-text">Visión</span>
                    </li>
                    <li onclick="location.href='emprendedor_buyerPersona.php'">
                        <span class="material-icons" style="color:#6366f1">groups</span>
                        <span class="link-text">Buyer Persona</span>
                    </li>
                    <li class="<?= $landingDisponible ? '' : 'is-disabled' ?>" <?= $landingDisponible ? "onclick=\"location.href='landing_page_request.php'\"" : '' ?>>
                        <span class="material-icons" style="color:#6366f1">rocket_launch</span>
                        <span class="link-text">Landing Page</span>
                    </li>
                    <li onclick="location.href='../../logout.php'">
                        <span class="material-icons" style="color:red">logout</span>
                        <span class="link-text">Salir</span>
                    </li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <button class="btn-icon" onclick="toggleSidebar()">
                    <span class="material-icons" id="collapseIcon">chevron_left</span>
                </button>
            </div>
        </aside>

        <div class="main">
            <header class="navbar">
                <div class="navbar-left">
                    <button class="btn-icon" onclick="toggleSidebar()">
                        <span class="material-icons">menu</span>
                    </button>
                    <div class="navbar-title">Mi espacio</div>
                </div>
                <?= renderBotonPerfil($perfil['avatar_path'] ?? ($_SESSION['avatar_path'] ?? null)) ?>
            </header>

            <section class="content">
                <div class="card">
                    <div class="profile-card">
                        <div class="profile-avatar"><?php if ($avatarUrl): ?><img src="<?= htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Avatar del usuario"><?php else: ?><?= htmlspecialchars($avatarInitial, ENT_QUOTES, 'UTF-8') ?><?php endif; ?></div>
                        <div class="profile-info">
                            <h2>Hola, <?= $displayName ?></h2>
                            <p><?= htmlspecialchars($perfil['correo'] ?? $_SESSION['correo'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                            <?php if ($correoVerificado): ?>
                                <span class="badge badge-success" style="margin-top:6px">
                                    <span class="material-icons" style="font-size:14px">verified</span>
                                    Correo verificado
                                </span>
                            <?php else: ?>
                                <span class="badge badge-warning" style="margin-top:6px">
                                    <span class="material-icons" style="font-size:14px">warning</span>
                                    Correo sin verificar
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if (!$ocultarIntro): ?>
                    <div class="card intro-card">
                        <h3>¡Qué lindo tenerte acá!</h3>
                        <p>Estás empezando un camino muy importante: darle forma a tu emprendimiento con una base firme.</p>
                        <p>En este recorrido vamos a acompañarte para ordenar ideas clave como tu misión, tu visión y tu buyer persona. Tener esto claro te va a ayudar a entender mejor tu emprendimiento, comunicarlo con más seguridad y prepararte para crecer.</p>
                        <p>Y si hoy no sabés bien de qué se trata cada concepto, no pasa nada. Vamos a construirlo juntos, paso a paso.</p>
                        <p>Mirá las tarjetas de abajo y empecemos a trabajar en tu misión, tu visión y tu buyer persona.</p>
                        <p>Al terminar, vas a poder solicitar tu página web gratuita.</p>
                        <p>¡Comencemos!</p>
                    </div>
                <?php endif; ?>

                <div class="roadmap-grid">
                    <div class="card roadmap-card">
                        <div>
                            <span class="roadmap-step">Paso 1</span>
                            <h4>Tu misión</h4>
                            <?php if ($misionCompletada && $misionTexto !== ''): ?>
                                <p class="roadmap-summary"><?= $misionTexto ?></p>
                            <?php else: ?>
                                <p>La misión define qué hace tu emprendimiento y qué valor ofrece.</p>
                            <?php endif; ?>
                        </div>
                        <?php if (!$misionCompletada): ?>
                            <button class="btn btn-aceptar" type="button" onclick="location.href='emprendedor_mision.php'">Creemos tu misión</button>
                        <?php endif; ?>
                    </div>

                    <div class="card roadmap-card">
                        <div>
                            <span class="roadmap-step">Paso 2</span>
                            <h4>Tu visión</h4>
                            <?php if ($visionCompletada && $visionTexto !== ''): ?>
                                <p class="roadmap-summary"><?= $visionTexto ?></p>
                            <?php else: ?>
                                <p>La visión marca hacia dónde querés llegar con tu proyecto.</p>
                            <?php endif; ?>
                        </div>
                        <?php if (!$visionCompletada): ?>
                            <button class="btn btn-aceptar" type="button" onclick="location.href='emprendedor_vision.php'">Creemos tu visión</button>
                        <?php endif; ?>
                    </div>

                    <div class="card roadmap-card">
                        <div>
                            <span class="roadmap-step">Paso 3</span>
                            <h4>Tu buyer persona</h4>
                            <?php if ($buyerPersonaCompletado && $buyerPersonaTexto !== ''): ?>
                                <p class="roadmap-summary is-long"><?= $buyerPersonaTexto ?></p>
                            <?php else: ?>
                                <p>El buyer persona te ayuda a identificar a tu cliente ideal y entender mejor sus necesidades.</p>
                            <?php endif; ?>
                        </div>
                        <?php if (!$buyerPersonaCompletado): ?>
                            <button class="btn btn-aceptar" type="button" onclick="location.href='emprendedor_buyerPersona.php'">Creemos tu buyer persona</button>
                        <?php endif; ?>
                    </div>

                    <div class="card roadmap-card">
                        <div>
                            <span class="roadmap-step">Paso 4</span>
                            <h4>Tu página web</h4>
                            <ul class="roadmap-checklist">
                                <li data-progress-item="mision">
                                    <span class="roadmap-check"><span class="material-icons">check</span></span>
                                    <span>Misión</span>
                                </li>
                                <li data-progress-item="vision">
                                    <span class="roadmap-check"><span class="material-icons">check</span></span>
                                    <span>Visión</span>
                                </li>
                                <li data-progress-item="buyer_persona">
                                    <span class="roadmap-check"><span class="material-icons">check</span></span>
                                    <span>Buyer Persona</span>
                                </li>
                                <li data-progress-item="landing_page">
                                    <span class="roadmap-check"><span class="material-icons">check</span></span>
                                    <span>Página Web</span>
                                </li>
                            </ul>
                        </div>
                        <?php if (!$landingPageCompletada): ?>
                            <button class="btn btn-aceptar" type="button" onclick="location.href='landing_page_request.php'" <?= $landingDisponible ? '' : 'disabled' ?>>
                                Creemos tu landing page
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <?php require_once __DIR__ . '/../../partials/modal_perfil/modal_perfil.php'; ?>

    <script>
        const sesion = {
            user_id: <?= json_encode($_SESSION['user_id'] ?? null) ?>,
            correo: <?= json_encode($_SESSION['correo'] ?? null) ?>,
            rol: <?= json_encode($_SESSION['rol'] ?? null) ?>,
            nombre: <?= json_encode($_SESSION['nombre'] ?? null) ?>,
            apellido: <?= json_encode($_SESSION['apellido'] ?? null) ?>,
            apodo: <?= json_encode($_SESSION['apodo'] ?? null) ?>,
            fecha_nacimiento: <?= json_encode($_SESSION['fecha_nacimiento'] ?? null) ?>,
        };
        console.group('[Impulsa] Sesión activa');
        console.table(sesion);
        console.groupEnd();

        const progressItems = document.querySelectorAll('[data-progress-item]');
        const progressState = {
            mision: <?= json_encode($misionCompletada) ?>,
            vision: <?= json_encode($visionCompletada) ?>,
            buyer_persona: <?= json_encode($buyerPersonaCompletado) ?>,
            landing_page: <?= json_encode($landingPageCompletada) ?>,
        };

        progressItems.forEach((item) => {
            const key = item.getAttribute('data-progress-item');
            item.classList.toggle('is-done', Boolean(progressState[key]));
        });
    </script>
</body>

</html>
