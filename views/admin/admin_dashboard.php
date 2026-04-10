<?php
session_start();

if (!isset($_SESSION['user_id']) || (string) ($_SESSION['rol'] ?? '') !== 'impulsa_administrador') {
    header('Location: /index.php');
    exit;
}

require_once __DIR__ . '/../../config.php';

function authTieneColumna(PDO $pdo, string $columna): bool
{
    try {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM auth LIKE :columna");
        $stmt->execute(['columna' => $columna]);
        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return false;
    }
}

function tablaExiste(PDO $pdo, string $tabla): bool
{
    try {
        $stmt = $pdo->prepare("SHOW TABLES LIKE :tabla");
        $stmt->execute(['tabla' => $tabla]);
        return (bool) $stmt->fetch(PDO::FETCH_NUM);
    } catch (Throwable $e) {
        return false;
    }
}

function normalizarUsuarioBase(string $texto): string
{
    $texto = trim($texto);
    if ($texto === '') {
        return 'jurado';
    }

    $transliterado = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
    if ($transliterado !== false) {
        $texto = $transliterado;
    }

    $texto = strtolower($texto);
    $texto = preg_replace('/[^a-z0-9]+/', '_', $texto) ?? 'jurado';
    $texto = trim($texto, '_');

    return $texto !== '' ? $texto : 'jurado';
}

function generarUsuarioUnico(PDO $pdo, string $nombre): string
{
    $base = normalizarUsuarioBase($nombre);
    $candidato = $base;
    $indice = 1;

    $stmt = $pdo->prepare('SELECT id FROM auth WHERE usuario = :usuario LIMIT 1');

    while (true) {
        $stmt->execute(['usuario' => $candidato]);
        $existe = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$existe) {
            return $candidato;
        }

        $indice++;
        $candidato = $base . '_' . $indice;
    }
}

$tieneAccesoHabilitado = authTieneColumna($pdo, 'acceso_habilitado');
$tieneCodigoVisible = authTieneColumna($pdo, 'codigo_acceso_visible');
$tieneTablaInformacionUsuarios = tablaExiste($pdo, 'informacion_usuarios');
$mensaje = '';
$mensajeTipo = 'success';
$mostrarModalJurado = false;
$nombreNuevoJurado = '';
$codigoNuevoJurado = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_jurado'])) {
    $mostrarModalJurado = true;
    $nombreNuevoJurado = trim((string) ($_POST['nombre_jurado'] ?? ''));
    $codigoNuevoJurado = trim((string) ($_POST['codigo_acceso_jurado'] ?? ''));

    if (!$tieneTablaInformacionUsuarios) {
        $mensaje = 'Falta la tabla informacion_usuarios. Primero ejecutá la migración SQL.';
        $mensajeTipo = 'danger';
    } elseif ($nombreNuevoJurado === '' || $codigoNuevoJurado === '') {
        $mensaje = 'Completá nombre y código de acceso para crear el jurado.';
        $mensajeTipo = 'danger';
    } else {
        try {
            $pdo->beginTransaction();

            $usuarioGenerado = generarUsuarioUnico($pdo, $nombreNuevoJurado);
            $passwordDummy = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
            $codigoHash = password_hash($codigoNuevoJurado, PASSWORD_DEFAULT);

            $columnasAuth = ['usuario', 'contrasena', 'codigo_acceso', 'rol'];
            $placeholdersAuth = [':usuario', ':contrasena', ':codigo_acceso', ':rol'];
            $paramsAuth = [
                'usuario' => $usuarioGenerado,
                'contrasena' => $passwordDummy,
                'codigo_acceso' => $codigoHash,
                'rol' => 'impulsa_jurado',
            ];

            if ($tieneCodigoVisible) {
                $columnasAuth[] = 'codigo_acceso_visible';
                $placeholdersAuth[] = ':codigo_acceso_visible';
                $paramsAuth['codigo_acceso_visible'] = $codigoNuevoJurado;
            }

            if ($tieneAccesoHabilitado) {
                $columnasAuth[] = 'acceso_habilitado';
                $placeholdersAuth[] = ':acceso_habilitado';
                $paramsAuth['acceso_habilitado'] = 1;
            }

            $sqlInsertAuth = sprintf(
                'INSERT INTO auth (%s) VALUES (%s)',
                implode(', ', $columnasAuth),
                implode(', ', $placeholdersAuth)
            );

            $stmtInsertAuth = $pdo->prepare($sqlInsertAuth);
            $stmtInsertAuth->execute($paramsAuth);

            $nuevoUserId = (int) $pdo->lastInsertId();

            $stmtInsertInfo = $pdo->prepare(
                'INSERT INTO informacion_usuarios (user_auth_id, nombre)
                 VALUES (:user_auth_id, :nombre)'
            );
            $stmtInsertInfo->execute([
                'user_auth_id' => $nuevoUserId,
                'nombre' => $nombreNuevoJurado,
            ]);

            $pdo->commit();
            $mensaje = 'Jurado creado correctamente.';
            $mensajeTipo = 'success';
            $mostrarModalJurado = false;
            $nombreNuevoJurado = '';
            $codigoNuevoJurado = '';
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $mensaje = 'No se pudo crear el jurado.';
            $mensajeTipo = 'danger';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_acceso_usuario_id']) && $tieneAccesoHabilitado) {
    $usuarioId = (int) $_POST['toggle_acceso_usuario_id'];
    $nuevoEstado = isset($_POST['acceso_habilitado']) ? 1 : 0;

    $stmtToggle = $pdo->prepare(
        "UPDATE auth
         SET acceso_habilitado = :estado
         WHERE id = :id"
    );

    if ($stmtToggle->execute([
        'estado' => $nuevoEstado,
        'id' => $usuarioId,
    ])) {
        $mensaje = $nuevoEstado === 1
            ? 'El acceso del usuario fue habilitado.'
            : 'El acceso del usuario fue deshabilitado.';
    } else {
        $mensaje = 'No se pudo actualizar el acceso del usuario.';
        $mensajeTipo = 'danger';
    }
}

$selectUsuarios = "SELECT id, usuario, rol, creado_en";
if ($tieneAccesoHabilitado) {
    $selectUsuarios .= ", acceso_habilitado";
}
$selectUsuarios .= " FROM auth ORDER BY creado_en DESC, id DESC";

$stmtUsuarios = $pdo->query($selectUsuarios);
$usuarios = $stmtUsuarios ? $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC) : [];

$selectJurados = "SELECT a.id, a.usuario, a.rol";
if ($tieneCodigoVisible) {
    $selectJurados .= ", a.codigo_acceso_visible";
}
if ($tieneTablaInformacionUsuarios) {
    $selectJurados .= ", iu.nombre";
}
$selectJurados .= " FROM auth a";
if ($tieneTablaInformacionUsuarios) {
    $selectJurados .= " LEFT JOIN informacion_usuarios iu ON iu.user_auth_id = a.id";
}
$selectJurados .= " WHERE a.rol = 'impulsa_jurado' ORDER BY a.creado_en DESC, a.id DESC";

$stmtJurados = $pdo->query($selectJurados);
$jurados = $stmtJurados ? $stmtJurados->fetchAll(PDO::FETCH_ASSOC) : [];

$usuarioSesion = (string) ($_SESSION['usuario'] ?? $_SESSION['correo'] ?? 'Administrador');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de administración</title>

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
            --admin-danger: #ef4444;
            --admin-success: #15803d;
            --admin-warning: #b45309;
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
        }

        .sidebar-menu li .material-icons {
            color: var(--admin-primary);
            font-size: 20px;
        }

        .sidebar-menu li.active {
            background: var(--admin-primary-soft);
            color: #2151c8;
        }

        .sidebar-footer {
            padding: 12px 10px 14px;
            border-top: 1px solid var(--admin-border);
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
        .table-note,
        .jurado-code-note {
            margin: 0;
            color: var(--admin-muted);
            line-height: 1.5;
            font-size: 0.92rem;
        }

        .alert-inline {
            margin-top: 12px;
            padding: 12px 14px;
            border-radius: 14px;
            font-size: 0.9rem;
            border: 1px solid #dbeafe;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .alert-inline.danger {
            border-color: #fecaca;
            background: #fef2f2;
            color: #b91c1c;
        }

        .split-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.45fr) minmax(320px, 0.75fr);
            gap: 16px;
        }

        .table-responsive {
            margin-top: 18px;
            border: 1px solid var(--admin-border);
            border-radius: 16px;
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
        }

        .table {
            margin-bottom: 0;
            min-width: 720px;
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
            vertical-align: middle;
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

        .switch-form {
            margin: 0;
        }

        .switch-field {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .switch-input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .switch-label {
            position: relative;
            display: inline-flex;
            align-items: center;
            width: 44px;
            height: 24px;
            border-radius: 999px;
            background: #dbe4f2;
            transition: background 0.2s ease;
            cursor: pointer;
        }

        .switch-label::after {
            content: "";
            position: absolute;
            top: 3px;
            left: 3px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.18);
            transition: transform 0.2s ease;
        }

        .switch-input:checked + .switch-label {
            background: #3b82f6;
        }

        .switch-input:checked + .switch-label::after {
            transform: translateX(20px);
        }

        .switch-state {
            font-size: 0.82rem;
            font-weight: 700;
        }

        .switch-state.enabled {
            color: var(--admin-success);
        }

        .switch-state.disabled {
            color: var(--admin-warning);
        }

        .jurado-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 16px;
        }

        .jurado-item {
            border: 1px solid var(--admin-border);
            border-radius: 16px;
            padding: 14px;
            background: #fff;
        }

        .jurado-item-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 8px;
        }

        .jurado-name {
            font-size: 0.95rem;
            font-weight: 800;
            color: #202633;
        }

        .jurado-code {
            display: inline-flex;
            align-items: center;
            min-height: 36px;
            width: 100%;
            padding: 9px 11px;
            border-radius: 12px;
            border: 1px dashed #cbd5e1;
            background: #f8fafc;
            color: #0f172a;
            font-size: 0.88rem;
            font-weight: 700;
            word-break: break-all;
        }

        .section-header-inline {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 6px;
        }

        .section-header-inline .section-title {
            margin-bottom: 0;
        }

        .add-jurado-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid #dbeafe;
            background: #eff6ff;
            color: #1d4ed8;
            border-radius: 12px;
            padding: 8px 12px;
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            white-space: nowrap;
        }

        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.48);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 18px;
            z-index: 120;
        }

        .modal-backdrop[hidden] {
            display: none;
        }

        .modal-card {
            width: min(100%, 460px);
            background: #fff;
            border-radius: 20px;
            border: 1px solid var(--admin-border);
            box-shadow: 0 18px 48px rgba(15, 23, 42, 0.22);
            padding: 20px;
        }

        .modal-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 16px;
        }

        .modal-title {
            margin: 0 0 4px;
            font-size: 1.05rem;
            font-weight: 800;
            color: #202633;
        }

        .modal-copy {
            margin: 0;
            font-size: 0.9rem;
            color: var(--admin-muted);
            line-height: 1.45;
        }

        .modal-close {
            border: 0;
            background: #f8fafc;
            color: #475569;
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            flex-shrink: 0;
        }

        .form-stack {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .form-field label {
            display: block;
            margin-bottom: 6px;
            font-size: 0.86rem;
            font-weight: 700;
            color: #334155;
        }

        .form-field input {
            width: 100%;
            min-height: 42px;
            border: 1px solid #dbe3f0;
            border-radius: 12px;
            padding: 10px 12px;
            font-size: 0.92rem;
            color: #0f172a;
            background: #fff;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 18px;
        }

        .btn-secondary-modal,
        .btn-primary-modal {
            border-radius: 12px;
            padding: 10px 14px;
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
        }

        .btn-secondary-modal {
            border: 1px solid #dbe3f0;
            background: #fff;
            color: #334155;
        }

        .btn-primary-modal {
            border: 1px solid #2563eb;
            background: #2563eb;
            color: #fff;
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

            .section-header-inline {
                align-items: stretch;
                flex-direction: column;
            }

            .add-jurado-btn {
                width: 100%;
                justify-content: center;
            }

            .logout-link span:last-child {
                display: none;
            }

            .jurado-item-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .modal-actions {
                flex-direction: column-reverse;
            }

            .btn-secondary-modal,
            .btn-primary-modal {
                width: 100%;
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
                        <span class="material-icons">group</span>
                        <span class="link-text">Usuarios</span>
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
                        <p>Desde acá podés administrar los usuarios del sistema, habilitar o bloquear accesos y revisar los jurados registrados.</p>
                        <?php if ($mensaje !== ''): ?>
                            <div class="alert-inline <?= $mensajeTipo === 'danger' ? 'danger' : '' ?>">
                                <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!$tieneAccesoHabilitado): ?>
                            <div class="alert-inline danger">
                                Falta la columna <code>acceso_habilitado</code> en la tabla <code>auth</code>. Hasta que la agregues, los switches no van a tener efecto.
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="split-grid">
                        <div class="panel-card" id="registros">
                            <h2 class="section-title">Usuarios registrados en el sistema</h2>
                            <p class="table-note">Listado completo de usuarios creados en <code>auth</code> con control de acceso individual.</p>

                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Usuario</th>
                                            <th>Rol</th>
                                            <th>Creado</th>
                                            <th>Acceso</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($usuarios): ?>
                                            <?php foreach ($usuarios as $usuario): ?>
                                                <?php
                                                $esAdmin = (string) $usuario['rol'] === 'impulsa_administrador';
                                                $accesoHabilitado = $tieneAccesoHabilitado ? (int) ($usuario['acceso_habilitado'] ?? 1) === 1 : true;
                                                ?>
                                                <tr>
                                                    <td><?= (int) $usuario['id'] ?></td>
                                                    <td><?= htmlspecialchars((string) $usuario['usuario'], ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td>
                                                        <span class="role-pill <?= $esAdmin ? 'role-admin' : 'role-jurado' ?>">
                                                            <?= htmlspecialchars((string) $usuario['rol'], ENT_QUOTES, 'UTF-8') ?>
                                                        </span>
                                                    </td>
                                                    <td><?= htmlspecialchars((string) ($usuario['creado_en'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td>
                                                        <form method="post" class="switch-form">
                                                            <input type="hidden" name="toggle_acceso_usuario_id" value="<?= (int) $usuario['id'] ?>">
                                                            <div class="switch-field">
                                                                <input
                                                                    class="switch-input"
                                                                    type="checkbox"
                                                                    id="acceso_usuario_<?= (int) $usuario['id'] ?>"
                                                                    name="acceso_habilitado"
                                                                    <?= $accesoHabilitado ? 'checked' : '' ?>
                                                                    <?= $tieneAccesoHabilitado ? '' : 'disabled' ?>
                                                                    onchange="this.form.submit()">
                                                                <label class="switch-label" for="acceso_usuario_<?= (int) $usuario['id'] ?>"></label>
                                                                <span class="switch-state <?= $accesoHabilitado ? 'enabled' : 'disabled' ?>">
                                                                    <?= $accesoHabilitado ? 'Habilitado' : 'Bloqueado' ?>
                                                                </span>
                                                            </div>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-secondary">No hay usuarios registrados todavía.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <aside class="panel-card">
                            <div class="section-header-inline">
                                <h2 class="section-title">Jurados registrados</h2>
                                <button type="button" class="add-jurado-btn" id="openJuradoModalBtn">
                                    <span class="material-icons">person_add</span>
                                    <span>Añadir jurado</span>
                                </button>
                            </div>
                            <p class="section-caption">Listado de usuarios con rol <code>impulsa_jurado</code>.</p>

                            <?php if (!$tieneCodigoVisible): ?>
                                <div class="alert-inline danger" style="margin-top:16px;">
                                    El código original no puede leerse desde <code>codigo_acceso</code> porque está hasheado. Si querés verlo acá, necesitás una columna adicional como <code>codigo_acceso_visible</code>.
                                </div>
                            <?php endif; ?>

                            <div class="jurado-list">
                                <?php if ($jurados): ?>
                                    <?php foreach ($jurados as $jurado): ?>
                                        <div class="jurado-item">
                                            <div class="jurado-item-header">
                                                <div class="jurado-name">
                                                    <?= htmlspecialchars((string) ($jurado['nombre'] ?? $jurado['usuario']), ENT_QUOTES, 'UTF-8') ?>
                                                </div>
                                                <span class="role-pill role-jurado">impulsa_jurado</span>
                                            </div>
                                            <div class="jurado-code-note" style="margin-bottom:8px;">
                                                Usuario: <?= htmlspecialchars((string) $jurado['usuario'], ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                            <div class="jurado-code">
                                                <?= $tieneCodigoVisible
                                                    ? htmlspecialchars((string) ($jurado['codigo_acceso_visible'] ?? 'Sin código visible'), ENT_QUOTES, 'UTF-8')
                                                    : 'No disponible con el esquema actual'
                                                ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="jurado-code-note">No hay jurados registrados.</div>
                                <?php endif; ?>
                            </div>
                        </aside>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <div class="modal-backdrop" id="juradoModal" <?= $mostrarModalJurado ? '' : 'hidden' ?>>
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="juradoModalTitle">
            <div class="modal-header">
                <div>
                    <h3 class="modal-title" id="juradoModalTitle">Añadir jurado</h3>
                    <p class="modal-copy">Completá el nombre y el código de acceso. El usuario se genera automáticamente.</p>
                </div>
                <button type="button" class="modal-close" id="closeJuradoModalBtn" aria-label="Cerrar modal">
                    <span class="material-icons">close</span>
                </button>
            </div>

            <form method="post">
                <input type="hidden" name="crear_jurado" value="1">
                <div class="form-stack">
                    <div class="form-field">
                        <label for="nombre_jurado">Nombre</label>
                        <input
                            type="text"
                            id="nombre_jurado"
                            name="nombre_jurado"
                            value="<?= htmlspecialchars($nombreNuevoJurado, ENT_QUOTES, 'UTF-8') ?>"
                            autocomplete="off"
                            required>
                    </div>

                    <div class="form-field">
                        <label for="codigo_acceso_jurado">Código de acceso</label>
                        <input
                            type="text"
                            id="codigo_acceso_jurado"
                            name="codigo_acceso_jurado"
                            value="<?= htmlspecialchars($codigoNuevoJurado, ENT_QUOTES, 'UTF-8') ?>"
                            autocomplete="off"
                            required>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-secondary-modal" id="cancelJuradoModalBtn">Cancelar</button>
                    <button type="submit" class="btn-primary-modal">Guardar jurado</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const body = document.body;
        const sidebar = document.getElementById('sidebar');
        const collapseButton = document.getElementById('collapseSidebarBtn');
        const collapseIcon = document.getElementById('collapseIcon');
        const toggleSidebarButton = document.getElementById('toggleSidebarBtn');
        const mobileBreakpoint = window.matchMedia('(max-width: 860px)');
        const juradoModal = document.getElementById('juradoModal');
        const openJuradoModalBtn = document.getElementById('openJuradoModalBtn');
        const closeJuradoModalBtn = document.getElementById('closeJuradoModalBtn');
        const cancelJuradoModalBtn = document.getElementById('cancelJuradoModalBtn');

        function openJuradoModal() {
            juradoModal?.removeAttribute('hidden');
        }

        function closeJuradoModal() {
            juradoModal?.setAttribute('hidden', 'hidden');
        }

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

        openJuradoModalBtn?.addEventListener('click', openJuradoModal);
        closeJuradoModalBtn?.addEventListener('click', closeJuradoModal);
        cancelJuradoModalBtn?.addEventListener('click', closeJuradoModal);

        juradoModal?.addEventListener('click', (event) => {
            if (event.target === juradoModal) {
                closeJuradoModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeJuradoModal();
            }
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
