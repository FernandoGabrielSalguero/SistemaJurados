<?php
$loginError    = $_GET['login_error']    ?? '';
$registerError = $_GET['register_error'] ?? '';
$verifyError   = $_GET['verify_error']   ?? '';
$registerOk    = isset($_GET['register_ok']);
$verifyOk      = isset($_GET['verify_ok']);

$loginMessage    = '';
$registerMessage = '';
$successMessage  = '';
$activeTab       = 'login';

// Errores de login
if ($loginError === 'invalid') {
    $loginMessage = 'Correo o contraseña incorrectos.';
} elseif ($loginError === 'unverified') {
    $loginMessage = 'Tenés que verificar tu correo antes de ingresar. Revisá tu bandeja de entrada.';
}

// Errores de registro
if ($registerError === 'invalid') {
    $registerMessage = 'Ingresá un correo válido. La contraseña debe tener al menos 8 caracteres.';
    $activeTab = 'register';
} elseif ($registerError === 'nomatch') {
    $registerMessage = 'Las contraseñas no coinciden.';
    $activeTab = 'register';
} elseif ($registerError === 'exists') {
    $registerMessage = 'Ese correo ya está registrado.';
    $activeTab = 'register';
}

// Estados de verificación de correo
if ($verifyOk) {
    $successMessage = 'Tu correo fue verificado correctamente. Ya podés ingresar.';
    $activeTab = 'login';
} elseif ($verifyError === 'already_verified') {
    $successMessage = 'Tu correo ya estaba verificado. Podés ingresar normalmente.';
    $activeTab = 'login';
} elseif ($verifyError === 'invalid_token') {
    $loginMessage = 'El enlace de verificación no es válido o ya expiró. Intentá registrarte nuevamente.';
}

// Registro exitoso
if ($registerOk) {
    $successMessage = 'Cuenta creada. Revisá tu correo para verificar tu cuenta antes de ingresar.';
    $activeTab = 'login';
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impulsa Emprende</title>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-0EQQR8G45D"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-0EQQR8G45D');
    </script>
    <style>
        @font-face {
            font-family: 'Montserrat';
            src: url('assets/institucionales/fonts/Montserrat/Montserrat-VariableFont_wght.ttf') format('truetype');
            font-weight: 100 900;
            font-style: normal;
            font-display: swap;
        }

        @font-face {
            font-family: 'Montserrat';
            src: url('assets/institucionales/fonts/Montserrat/Montserrat-Italic-VariableFont_wght.ttf') format('truetype');
            font-weight: 100 900;
            font-style: italic;
            font-display: swap;
        }

        :root {
            color-scheme: light;
            --bg: #0a0b12;
            --bg-soft: #111321;
            --surface: #15182a;
            --surface-strong: #1a1f36;
            --ink: #f5f7ff;
            --muted: #a7b0c5;
            --accent: #59f2e8;
            --accent-2: #ff6b6b;
            --accent-3: #f5c542;
            --stroke: rgba(255, 255, 255, 0.08);
            --shadow: 0 18px 60px rgba(10, 12, 25, 0.55);
            --radius: 18px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Montserrat', 'Segoe UI', sans-serif;
            background: radial-gradient(1200px 700px at 70% -20%, #242955 0%, transparent 70%),
                radial-gradient(900px 600px at 20% 10%, #1f2449 0%, transparent 68%),
                var(--bg);
            color: var(--ink);
            min-height: 100vh;
            overflow-x: hidden;
        }

        body::before,
        body::after {
            content: '';
            position: fixed;
            width: 420px;
            height: 420px;
            border-radius: 50%;
            filter: blur(0);
            opacity: 0.5;
            z-index: 0;
            pointer-events: none;
        }

        body::before {
            left: -160px;
            top: 10%;
            background: radial-gradient(circle at 30% 30%, rgba(89, 242, 232, 0.35), transparent 70%);
        }

        body::after {
            right: -200px;
            bottom: -80px;
            background: radial-gradient(circle at 30% 30%, rgba(255, 107, 107, 0.35), transparent 70%);
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .page {
            position: relative;
            z-index: 1;
        }

        .nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 24px 6vw;
            gap: 24px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-family: 'Montserrat', 'Segoe UI', sans-serif;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .brand-mark {
            /* Ajustá acá el tamaño de la marca del header */
            display: block;
            width: auto;
            height: 74px;
            max-width: min(300px, 60vw);
            object-fit: contain;
        }

        .nav-links {
            display: flex;
            gap: 20px;
            color: var(--muted);
            font-size: 14px;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .nav-toggle {
            display: none;
        }

        .nav-toggle-btn {
            display: none;
            align-items: center;
            justify-content: center;
            width: 46px;
            height: 46px;
            border-radius: 14px;
            border: 1px solid var(--stroke);
            background: rgba(255, 255, 255, 0.04);
            cursor: pointer;
        }

        .nav-toggle-btn span {
            position: relative;
            width: 20px;
            height: 2px;
            background: var(--ink);
            border-radius: 999px;
            transition: background 0.2s ease, transform 0.2s ease;
        }

        .nav-toggle-btn span::before,
        .nav-toggle-btn span::after {
            content: '';
            position: absolute;
            left: 0;
            width: 20px;
            height: 2px;
            background: var(--ink);
            border-radius: 999px;
            transition: transform 0.2s ease, top 0.2s ease, bottom 0.2s ease;
        }

        .nav-toggle-btn span::before {
            top: -6px;
        }

        .nav-toggle-btn span::after {
            bottom: -6px;
        }

        .nav-cta {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 18px;
            border-radius: 999px;
            border: 1px solid var(--stroke);
            background: rgba(255, 255, 255, 0.02);
            color: var(--ink);
            font-weight: 500;
            transition: transform 0.2s ease, background 0.2s ease;
        }

        .nav-cta:hover {
            transform: translateY(-1px);
            background: rgba(255, 255, 255, 0.08);
        }

        .hero {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 40px;
            padding: 40px 6vw 80px;
            align-items: center;
        }

        .hero h1 {
            font-family: 'Montserrat', 'Segoe UI', sans-serif;
            font-size: clamp(2.4rem, 4vw, 4.2rem);
            line-height: 1.02;
            margin: 0 0 18px;
        }

        .hero h1 span {
            color: var(--accent);
        }

        .hero p {
            color: var(--muted);
            font-size: 1.05rem;
            margin: 0 0 24px;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
        }

        .btn {
            display: inline-block;
            padding: 12px 22px;
            border-radius: 999px;
            font-weight: 600;
            font-size: 0.95rem;
            border: 1px solid transparent;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent), #54d6ff);
            color: #05070f;
            box-shadow: 0 12px 30px rgba(89, 242, 232, 0.35);
        }

        .btn-secondary {
            background: transparent;
            color: var(--ink);
            border-color: var(--stroke);
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-block {
            display: block;
            width: 100%;
            text-align: center;
        }

        .hero-card {
            background: linear-gradient(160deg, rgba(255, 255, 255, 0.06), rgba(255, 255, 255, 0));
            border-radius: var(--radius);
            border: 1px solid var(--stroke);
            padding: 24px;
            box-shadow: var(--shadow);
        }

        .hero-card h3 {
            font-family: 'Montserrat', 'Segoe UI', sans-serif;
            font-size: 1.2rem;
            margin: 0 0 14px;
        }

        .process-diagram {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0;
            overflow: hidden;
            border-radius: 18px;
            border: 1px solid var(--stroke);
        }

        .process-step {
            position: relative;
            padding: 18px 22px 18px 24px;
            min-height: 176px;
            color: #f9fbff;
            clip-path: polygon(0 0, calc(100% - 22px) 0, 100% 50%, calc(100% - 22px) 100%, 0 100%, 22px 50%);
        }

        .process-step:first-child {
            clip-path: polygon(0 0, calc(100% - 22px) 0, 100% 50%, calc(100% - 22px) 100%, 0 100%);
            padding-left: 22px;
        }

        .process-step:last-child {
            clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%, 22px 50%);
        }

        .process-step:nth-child(1) {
            background: linear-gradient(135deg, #7558d8, #9d7df4);
        }

        .process-step:nth-child(2) {
            background: linear-gradient(135deg, #ffb34f, #ff8a3d);
        }

        .process-step:nth-child(3) {
            background: linear-gradient(135deg, #33c7ff, #1a95ff);
        }

        .process-step:nth-child(4) {
            background: linear-gradient(135deg, #ff7e42, #ff5f67);
        }

        .process-badge {
            width: 54px;
            height: 54px;
            margin: 0 0 16px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.92);
            color: #101322;
            font-weight: 800;
            font-size: 1.2rem;
            box-shadow: 0 10px 24px rgba(8, 10, 20, 0.22);
        }

        .process-step h4 {
            margin: 0 0 10px;
            font-family: 'Montserrat', 'Segoe UI', sans-serif;
            font-size: 1rem;
            line-height: 1.25;
        }

        .process-step p {
            margin: 0;
            color: rgba(255, 255, 255, 0.92);
            font-size: 0.92rem;
            line-height: 1.45;
        }

        .sections {
            padding: 0 6vw 80px;
            display: grid;
            gap: 32px;
        }

        .section {
            background: var(--surface-strong);
            border-radius: var(--radius);
            border: 1px solid var(--stroke);
            padding: 28px;
        }

        .section h2 {
            font-family: 'Montserrat', 'Segoe UI', sans-serif;
            margin: 0 0 14px;
            font-size: 1.6rem;
        }

        .section-cta {
            margin-top: 24px;
        }

        .tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            color: var(--muted);
            font-size: 0.9rem;
        }

        .tag {
            padding: 6px 12px;
            border-radius: 999px;
            border: 1px solid var(--stroke);
            background: rgba(255, 255, 255, 0.03);
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
        }

        .card {
            background: var(--surface);
            border-radius: 16px;
            border: 1px solid var(--stroke);
            padding: 18px;
            min-height: 150px;
        }

        .card h3 {
            margin: 0 0 10px;
            font-family: 'Montserrat', 'Segoe UI', sans-serif;
        }

        .card p {
            margin: 0;
            color: var(--muted);
            font-size: 0.95rem;
        }

        .feature-list {
            margin: 18px 0 22px;
            padding-left: 22px;
            color: var(--muted);
        }

        .feature-list li+li {
            margin-top: 12px;
        }

        .feature-list strong {
            color: var(--ink);
        }

        .steps {
            display: grid;
            gap: 18px;
            margin-top: 22px;
        }

        .step {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 16px;
            align-items: start;
            padding: 20px;
            border-radius: 18px;
            border: 1px solid var(--stroke);
            background: linear-gradient(160deg, rgba(255, 255, 255, 0.06), rgba(255, 255, 255, 0.02));
        }

        .step-number {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(89, 242, 232, 0.12);
            border: 1px solid rgba(89, 242, 232, 0.24);
            color: var(--accent);
            font-weight: 700;
        }

        .step h3 {
            margin: 0 0 8px;
            font-size: 1.05rem;
        }

        .step p {
            margin: 0;
            color: var(--muted);
            line-height: 1.6;
        }

        .steps-note {
            margin: 22px 0 0;
            color: var(--muted);
            font-size: 0.95rem;
            line-height: 1.7;
        }

        .footer {
            padding: 24px 6vw 40px;
            color: var(--muted);
            border-top: 1px solid var(--stroke);
            text-align: center;
        }

        .modal {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(4, 5, 12, 0.6);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
            z-index: 10;
            padding: 24px;
        }

        .modal.is-open {
            opacity: 1;
            pointer-events: auto;
        }

        .modal-dialog {
            width: min(460px, 100%);
            background: var(--surface-strong);
            border-radius: 20px;
            border: 1px solid var(--stroke);
            padding: 24px;
            box-shadow: var(--shadow);
            position: relative;
        }

        .modal-dialog.modal-dialog-wide {
            width: min(760px, 100%);
            max-height: calc(100vh - 48px);
            overflow: auto;
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .modal-header h3 {
            margin: 0;
            font-family: 'Montserrat', 'Segoe UI', sans-serif;
        }

        .modal-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 16px;
        }

        .tab-btn {
            flex: 1;
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid var(--stroke);
            background: rgba(255, 255, 255, 0.03);
            color: var(--muted);
            cursor: pointer;
            font-weight: 600;
        }

        .tab-btn.is-active {
            color: var(--ink);
            border-color: rgba(89, 242, 232, 0.6);
            box-shadow: 0 0 0 2px rgba(89, 242, 232, 0.12);
        }

        .close-btn {
            border: none;
            background: transparent;
            color: var(--muted);
            font-size: 1.2rem;
            cursor: pointer;
        }

        .field {
            display: grid;
            gap: 8px;
            margin-bottom: 14px;
        }

        .field label {
            font-size: 0.9rem;
            color: var(--muted);
        }

        .field input {
            padding: 12px 14px;
            border-radius: 12px;
            border: 1px solid var(--stroke);
            background: rgba(255, 255, 255, 0.04);
            color: var(--ink);
            outline: none;
        }

        .field input:focus {
            border-color: rgba(89, 242, 232, 0.6);
            box-shadow: 0 0 0 3px rgba(89, 242, 232, 0.15);
        }

        .error {
            padding: 10px 12px;
            border-radius: 12px;
            background: rgba(255, 107, 107, 0.15);
            border: 1px solid rgba(255, 107, 107, 0.3);
            color: #ffdede;
            margin-bottom: 12px;
            font-size: 0.9rem;
        }

        .success {
            padding: 10px 12px;
            border-radius: 12px;
            background: rgba(89, 242, 232, 0.12);
            border: 1px solid rgba(89, 242, 232, 0.3);
            color: #b6fff8;
            margin-bottom: 12px;
            font-size: 0.9rem;
        }

        .tab-panel {
            display: none;
        }

        .tab-panel.is-active {
            display: block;
        }

        .modal-actions {
            display: grid;
            gap: 12px;
        }

        .helper {
            color: var(--muted);
            font-size: 0.85rem;
        }

        .password-wrap {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: var(--accent);
            cursor: pointer;
            font-size: 0.85rem;
        }

        @media (max-width: 720px) {
            .nav {
                position: relative;
                align-items: center;
            }

            .brand-mark {
                height: 62px;
                max-width: min(240px, 62vw);
            }

            .nav-toggle-btn {
                display: inline-flex;
            }

            .nav-menu {
                position: absolute;
                top: calc(100% + 10px);
                right: 6vw;
                left: 6vw;
                display: none;
                flex-direction: column;
                align-items: stretch;
                gap: 12px;
                padding: 16px;
                border-radius: 18px;
                border: 1px solid var(--stroke);
                background: rgba(17, 19, 33, 0.98);
                box-shadow: var(--shadow);
            }

            .nav-links {
                flex-direction: column;
                gap: 12px;
            }

            .nav-links a,
            .nav-cta {
                width: 100%;
                justify-content: center;
            }

            .nav-toggle:checked+.nav-toggle-btn+.nav-menu {
                display: flex;
            }

            .nav-toggle:checked+.nav-toggle-btn span {
                background: transparent;
            }

            .nav-toggle:checked+.nav-toggle-btn span::before {
                top: 0;
                transform: rotate(45deg);
            }

            .nav-toggle:checked+.nav-toggle-btn span::after {
                bottom: 0;
                transform: rotate(-45deg);
            }

            .process-diagram {
                grid-template-columns: 1fr;
                gap: 12px;
                border: 0;
                overflow: visible;
            }

            .process-step,
            .process-step:first-child,
            .process-step:last-child {
                clip-path: none;
                border-radius: 18px;
                min-height: auto;
                padding: 18px;
            }

        }
    </style>
</head>

<body>
    <div class="page">
        <nav class="nav">
            <div class="brand">
                <img class="brand-mark" src="assets/institucionales/Impulsa Emprende white.png"
                    alt="Impulsa Emprende">
            </div>
            <input class="nav-toggle" id="nav-toggle" type="checkbox" aria-label="Abrir menú">
            <label class="nav-toggle-btn" for="nav-toggle" aria-hidden="true">
                <span></span>
            </label>
            <div class="nav-menu">
                <div class="nav-links">
                    <a href="#plataforma">Programa</a>
                    <a href="#modulos">Qué incluye</a>
                    <a href="#pasos">Pasos</a>
                </div>
                <button class="nav-cta" type="button" data-open-modal>
                    Ingresar
                </button>
            </div>
        </nav>

        <section class="hero">

            <div>
                <h1>Impulsá tu emprendimiento con una base <span>clara y profesional</span>.</h1>
                <p>En Impulsa Emprende te acompañamos de manera gratuita para ayudarte a organizar tu negocio desde el comienzo. Primero, te registrás en el sistema.</p>
                <p>Luego, trabajás en definir aspectos clave de tu emprendimiento, como tu misión, tu visión y el perfil de tu cliente ideal.</p>
                <p>Una vez que tu proyecto tiene una base sólida, te vinculamos con un profesional que desarrollará tu página web sin costo.</p>
                <div class="hero-actions">
                    <button class="btn btn-primary" type="button" data-open-modal>¡Registrate ahora!</button>
                    <a class="btn btn-secondary" href="#pasos">Ver cómo funciona</a>
                </div>
            </div>

            <div class="hero-card">
                <h3>Un proceso simple para emprender con claridad</h3>
                <div class="process-diagram">
                    <div class="process-step">
                        <div class="process-badge">01</div>
                        <h4>Registrate</h4>
                        <p>Creá tu cuenta y empezá a ordenar tu emprendimiento desde el sistema.</p>
                    </div>
                    <div class="process-step">
                        <div class="process-badge">02</div>
                        <h4>Definimos tu base</h4>
                        <p>Trabajamos tu misión, tu visión y tu buyer persona para darle claridad a tu negocio.</p>
                    </div>
                    <div class="process-step">
                        <div class="process-badge">03</div>
                        <h4>Creamos tu landing</h4>
                        <p>Con esa base lista, un profesional desarrolla tu landing page con una imagen clara y profesional.</p>
                    </div>
                    <div class="process-step">
                        <div class="process-badge">04</div>
                        <h4>Mostrás mejor lo que hacés</h4>
                        <p>Tu negocio se entiende mejor, genera más confianza y te ayuda a atraer más clientes.</p>
                    </div>
                </div>
            </div>

        </section>

        <section class="sections">
            <div class="section" id="modulos">
                <h2>Qué incluye este acompañamiento para emprendedores</h2>
                <h3>Primero trabajamos la base de tu negocio</h3>
                <p>Una página web no es lo primero. Lo primero es tener claridad sobre tu emprendimiento. Porque, aunque una web se vea linda, no sirve de mucho si no está claro qué hacés, para quién lo hacés y qué querés lograr con tu negocio.</p>
                <p>Por eso, en Impulsa Emprende empezamos por tres pilares fundamentales:</p>
                <ul class="feature-list">
                    <li><strong>Misión:</strong> define qué hace tu emprendimiento y qué valor ofrece.</li>
                    <li><strong>Visión:</strong> marca hacia dónde querés crecer y qué futuro buscás construir.</li>
                    <li><strong>Buyer persona:</strong> te ayuda a entender con claridad a tu cliente ideal, qué necesita y cómo comunicarte con esa persona.</li>
                </ul>
                <p>Estos elementos son esenciales en el mundo emprendedor porque te permiten ordenar tus ideas, tomar mejores decisiones, comunicar tu propuesta con más claridad y generar más confianza. Cuando no están definidos, muchas veces el negocio se vuelve confuso: cuesta explicar lo que ofrecés, llegar al público correcto y diferenciarte.</p>
                <p>Por eso lo hacemos antes que la página web. Porque una web no reemplaza la estrategia: la comunica.</p>
                <div class="section-cta">
                    <button class="btn btn-primary btn-block" type="button" data-open-modal>Registrate</button>
                </div>
            </div>

            <div class="section" id="pasos">
                <h2>Cómo funciona el proceso</h2>
                <p>El objetivo es muy simple: primero ordenamos tu negocio y después creamos tu página web. Así tu emprendimiento puede presentarse con claridad desde el principio.</p>
                <div class="steps">
                    <div class="step">
                        <div class="step-number">1</div>
                        <div>
                            <h3>Te registrás</h3>
                            <p>Creás tu cuenta y empezás tu proceso dentro del sistema de forma simple y rápida.</p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <div>
                            <h3>Definís tu misión</h3>
                            <p>Trabajás qué hace tu emprendimiento, qué problema resuelve y cuál es su propósito.</p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-number">3</div>
                        <div>
                            <h3>Definís tu visión</h3>
                            <p>Dejás claro hacia dónde querés llevar tu negocio para crecer con dirección y foco.</p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-number">4</div>
                        <div>
                            <h3>Definís tu buyer persona</h3>
                            <p>Identificás a tu cliente ideal para saber a quién le hablás y cómo conectar mejor con esa persona.</p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-number">5</div>
                        <div>
                            <h3>Recibís tu página web gratis</h3>
                            <p>Cuando tu negocio ya tiene una base sólida y clara, te contactamos con un profesional que construye tu página web gratis.</p>
                        </div>
                    </div>
                </div>
                <p class="steps-note">Este proceso hace que tu página web no sea solo una página linda. Hace que sea una herramienta útil para presentar tu negocio, explicar tu propuesta y dar una imagen profesional desde el inicio.</p>
            </div>

            <div class="section" id="comunidad">
                <h2>Emprender no debería ser confuso ni solitario</h2>
                <p>Si tenés una idea o ya diste tus primeros pasos, te acompañamos para que entiendas mejor tu negocio y lo comuniques con claridad. En esta primera etapa, el proceso es gratuito y está pensado para ayudarte a avanzar con más seguridad.</p>
                <div class="hero-actions">
                    <button class="btn btn-primary" type="button" data-open-modal>¡Registrate ahora!</button>
                </div>
            </div>
        </section>

        <footer class="footer">
            Impulsa Emprende · Acompañamiento gratuito para emprendedores que quieren ordenar su negocio y lanzar una página web profesional con claridad.
        </footer>
    </div>

    <div class="modal" data-modal aria-hidden="true">
        <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="login-title">
            <div class="modal-header">
                <h3 id="login-title">Ingresar a Impulsa Emprende</h3>
                <button class="close-btn" type="button" aria-label="Cerrar" data-close-modal>✕</button>
            </div>
            <div class="modal-tabs" role="tablist">
                <button class="tab-btn" type="button" data-tab="login" role="tab">Ingresar</button>
                <button class="tab-btn" type="button" data-tab="register" role="tab">Crear cuenta</button>
            </div>
            <?php if ($successMessage): ?>
                <div class="success"><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <?php if ($loginMessage): ?>
                <div class="error"><?= htmlspecialchars($loginMessage, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <?php if ($registerMessage): ?>
                <div class="error"><?= htmlspecialchars($registerMessage, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <div class="tab-panel" data-tab-panel="login">
                <form action="/auth/login.php" method="POST">
                    <input type="hidden" name="action" value="login">
                    <div class="field">
                        <label for="correo_login">Correo</label>
                        <input type="email" id="correo_login" name="correo" placeholder="tu@correo.com" required>
                    </div>
                    <div class="field password-wrap">
                        <label for="contrasena_login">Contraseña</label>
                        <input type="password" id="contrasena_login" name="contrasena"
                            placeholder="Ingresá tu contraseña" required>
                        <button class="toggle-password" type="button" aria-label="Mostrar contraseña"
                            data-toggle-password data-target="contrasena_login">Mostrar</button>
                    </div>
                    <div class="modal-actions">
                        <button class="btn btn-primary" type="submit">Entrar</button>
                        <span class="helper">Si ya formás parte del programa, ingresá con tu correo y contraseña.</span>
                    </div>
                </form>
            </div>
            <div class="tab-panel" data-tab-panel="register">
                <form action="/auth/login.php" method="POST">
                    <input type="hidden" name="action" value="register">
                    <div class="field">
                        <label for="correo_reg">Correo</label>
                        <input type="email" id="correo_reg" name="correo" placeholder="tu@correo.com" required>
                    </div>
                    <div class="field password-wrap">
                        <label for="contrasena_reg">Contraseña</label>
                        <input type="password" id="contrasena_reg" name="contrasena" placeholder="Mínimo 8 caracteres"
                            required>
                        <button class="toggle-password" type="button" aria-label="Mostrar contraseña"
                            data-toggle-password data-target="contrasena_reg">Mostrar</button>
                    </div>
                    <div class="field password-wrap">
                        <label for="contrasena_confirm">Verificar contraseña</label>
                        <input type="password" id="contrasena_confirm" name="contrasena_confirm"
                            placeholder="Repetí la contraseña" required>
                        <button class="toggle-password" type="button" aria-label="Mostrar contraseña"
                            data-toggle-password data-target="contrasena_confirm">Mostrar</button>
                    </div>
                    <div class="modal-actions">
                        <button class="btn btn-primary" type="submit">Crear cuenta</button>
                        <span class="helper">Tu correo será el usuario de acceso al programa.</span>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const modal = document.querySelector('[data-modal]');
        const openButtons = document.querySelectorAll('[data-open-modal]');
        const closeButtons = document.querySelectorAll('[data-close-modal]');
        const title = document.getElementById('login-title');
        const tabButtons = document.querySelectorAll('[data-tab]');
        const tabPanels = document.querySelectorAll('[data-tab-panel]');
        const toggleButtons = document.querySelectorAll('[data-toggle-password]');
        const shouldOpen = <?= ($loginMessage || $registerMessage || $successMessage) ? 'true' : 'false' ?>;
        const defaultTab = <?= json_encode($activeTab) ?>;

        const openModal = () => {
            if (!modal) return;
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            const firstInput = modal.querySelector('input');
            if (firstInput) {
                firstInput.focus();
            }
        };

        const closeModal = () => {
            if (!modal) return;
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
        };

        openButtons.forEach(btn => btn.addEventListener('click', openModal));
        closeButtons.forEach(btn => btn.addEventListener('click', closeModal));

        if (modal) {
            modal.addEventListener('click', event => {
                if (event.target === modal) {
                    closeModal();
                }
            });
        }

        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && modal?.classList.contains('is-open')) {
                closeModal();
            }
        });

        const setTab = (tabName) => {
            tabButtons.forEach(button => {
                const isActive = button.dataset.tab === tabName;
                button.classList.toggle('is-active', isActive);
            });
            tabPanels.forEach(panel => {
                const isActive = panel.dataset.tabPanel === tabName;
                panel.classList.toggle('is-active', isActive);
            });
            if (title) {
                title.textContent = tabName === 'register' ? 'Crear cuenta' : 'Ingresar a Impulsa Emprende';
            }
        };

        tabButtons.forEach(button => {
            button.addEventListener('click', () => setTab(button.dataset.tab));
        });

        toggleButtons.forEach(button => {
            button.addEventListener('click', () => {
                const targetId = button.dataset.target;
                const targetInput = targetId ? document.getElementById(targetId) : null;
                if (!targetInput) return;
                const isPassword = targetInput.getAttribute('type') === 'password';
                targetInput.setAttribute('type', isPassword ? 'text' : 'password');
                button.textContent = isPassword ? 'Ocultar' : 'Mostrar';
                button.setAttribute('aria-label', isPassword ? 'Ocultar contraseña' : 'Mostrar contraseña');
            });
        });

        setTab(defaultTab || 'login');

        if (shouldOpen) {
            openModal();
        }
    </script>
</body>

</html>
