<?php
require_once __DIR__ . '/../../controllers/admin_dashboardController.php';

$displayName = $perfil['apodo'] ?? $perfil['nombre'] ?? $_SESSION['correo'] ?? 'Admin';
$displayName = htmlspecialchars((string) $displayName, ENT_QUOTES, 'UTF-8');
$avatarUrl = obtenerAvatarUrl($perfil['avatar_path'] ?? ($_SESSION['avatar_path'] ?? null));
$avatarInitial = obtenerInicialAvatar($displayName);

$correoVerificado = !empty($perfil['check_correo']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Impulsa — Admin</title>

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
            background: linear-gradient(135deg, #111827, #374151);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 22px;
            font-weight: 700;
            flex-shrink: 0;
            text-transform: uppercase;
        }
        .profile-avatar img,
        .user-initials img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            display: block;
        }

        .profile-info h2 { margin: 0 0 4px; font-size: 20px; }
        .profile-info p  { margin: 0; font-size: 14px; color: #6b7280; }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-success { background: #dcfce7; color: #15803d; }
        .badge-warning { background: #fef3c7; color: #b45309; }
        .badge-admin   { background: #e0e7ff; color: #3730a3; }

        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
        }

        .kpi-card {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px 18px;
            border-radius: 16px;
            border: 1px solid #e5e7eb;
            background: #fff;
        }

        .kpi-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .kpi-icon.indigo  { color: #4f46e5; background: #eef2ff; }
        .kpi-icon.success { color: #15803d; background: #dcfce7; }
        .kpi-icon.warning { color: #b45309; background: #fef3c7; }
        .kpi-icon.neutral { color: #334155; background: #e2e8f0; }
        .kpi-icon.purple  { color: #7c3aed; background: #ede9fe; }

        .kpi-label { font-size: 13px; color: #6b7280; margin-bottom: 4px; }
        .kpi-value { font-size: 22px; font-weight: 700; color: #111827; }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
            margin: 0 0 14px;
        }

        .users-table { width: 100%; border-collapse: collapse; }
        .users-table thead th {
            text-align: left;
            font-size: 12px;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        .users-table tbody td {
            padding: 12px 10px;
            font-size: 14px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .users-table tbody tr:last-child td { border-bottom: none; }

        .user-pill { display: inline-flex; align-items: center; gap: 8px; }
        .user-initials {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e0e7ff;
            color: #3730a3;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            flex-shrink: 0;
            text-transform: uppercase;
        }

        .table-wrap { overflow-x: auto; }

        /* Botón de perfil en navbar */
        .navbar { justify-content: space-between; }
        .navbar-left { display: flex; align-items: center; gap: 8px; }
        .sidebar-brand-icon {
            width: 32px;
            height: 32px;
            object-fit: contain;
            flex-shrink: 0;
        }
    </style>
</head>

<body>
    <div class="layout">

        <!-- SIDEBAR -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <img src="../../assets/institucionales/icons/Isotipo grande.png" alt="Impulsa Emprende" class="sidebar-brand-icon">
                <span class="logo-text">impulsa emprende</span>
            </div>
            <nav class="sidebar-menu">
                <ul>
                    <li class="active" onclick="location.href='admin_dashboard.php'">
                        <span class="material-icons" style="color:#6366f1">home</span>
                        <span class="link-text">Inicio</span>
                    </li>
                    <li onclick="location.href='admin_users.php'">
                        <span class="material-icons" style="color:#2563eb">group</span>
                        <span class="link-text">Usuarios</span>
                    </li>
                    <li onclick="location.href='admin_proceso_emprende.php'">
                        <span class="material-icons" style="color:#0f766e">assignment</span>
                        <span class="link-text">Proceso emprende</span>
                    </li>
                    <li onclick="location.href='admin_newproject.php'">
                        <span class="material-icons" style="color:#f59e0b">rocket_launch</span>
                        <span class="link-text">Solicitudes externas</span>
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

        <!-- MAIN -->
        <div class="main">

            <!-- NAVBAR -->
            <header class="navbar">
                <div class="navbar-left">
                    <button class="btn-icon" onclick="toggleSidebar()">
                        <span class="material-icons">menu</span>
                    </button>
                    <div class="navbar-title">Panel de administración</div>
                </div>
                <?= renderBotonPerfil($perfil['avatar_path'] ?? ($_SESSION['avatar_path'] ?? null)) ?>
            </header>

            <!-- CONTENIDO -->
            <section class="content">

                <!-- Bienvenida -->
                <div class="card">
                    <div class="profile-card">
                        <div class="profile-avatar"><?php if ($avatarUrl): ?><img src="<?= htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Avatar del usuario"><?php else: ?><?= htmlspecialchars($avatarInitial, ENT_QUOTES, 'UTF-8') ?><?php endif; ?></div>
                        <div class="profile-info">
                            <h2>Hola, <?= $displayName ?></h2>
                            <p><?= htmlspecialchars($perfil['correo'] ?? $_SESSION['correo'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                            <div style="display:flex;gap:6px;margin-top:6px;flex-wrap:wrap">
                                <span class="badge badge-admin">
                                    <span class="material-icons" style="font-size:14px">admin_panel_settings</span>
                                    Administrador
                                </span>
                                <?php if ($correoVerificado): ?>
                                    <span class="badge badge-success">
                                        <span class="material-icons" style="font-size:14px">verified</span>
                                        Correo verificado
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-warning">
                                        <span class="material-icons" style="font-size:14px">warning</span>
                                        Correo sin verificar
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KPIs -->
                <div class="card">
                    <p class="section-title">Usuarios de la plataforma</p>
                    <div class="kpi-grid">
                        <div class="kpi-card">
                            <div class="kpi-icon indigo"><span class="material-icons">group</span></div>
                            <div>
                                <div class="kpi-label">Total registrados</div>
                                <div class="kpi-value"><?= number_format($stats['total'], 0, ',', '.') ?></div>
                            </div>
                        </div>
                        <div class="kpi-card">
                            <div class="kpi-icon success"><span class="material-icons">mark_email_read</span></div>
                            <div>
                                <div class="kpi-label">Correos verificados</div>
                                <div class="kpi-value"><?= number_format($stats['verificados'], 0, ',', '.') ?></div>
                            </div>
                        </div>
                        <div class="kpi-card">
                            <div class="kpi-icon warning"><span class="material-icons">schedule_send</span></div>
                            <div>
                                <div class="kpi-label">Sin verificar</div>
                                <div class="kpi-value"><?= number_format($stats['sin_verificar'], 0, ',', '.') ?></div>
                            </div>
                        </div>
                        <div class="kpi-card">
                            <div class="kpi-icon purple"><span class="material-icons">rocket_launch</span></div>
                            <div>
                                <div class="kpi-label">Emprendedores</div>
                                <div class="kpi-value"><?= number_format($stats['emprendedores'], 0, ',', '.') ?></div>
                            </div>
                        </div>
                        <div class="kpi-card">
                            <div class="kpi-icon neutral"><span class="material-icons">admin_panel_settings</span></div>
                            <div>
                                <div class="kpi-label">Administradores</div>
                                <div class="kpi-value"><?= number_format($stats['administradores'], 0, ',', '.') ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Últimos registros -->
                <div class="card">
                    <p class="section-title">Últimos registros</p>
                    <div class="table-wrap">
                        <table class="users-table">
                            <thead>
                                <tr>
                                    <th>Usuario</th>
                                    <th>Correo</th>
                                    <th>Rol</th>
                                    <th>Verificado</th>
                                    <th>Registrado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recientes)): ?>
                                    <?php foreach ($recientes as $u): ?>
                                        <?php
                                        $nombre  = trim(($u['nombre'] ?? '') . ' ' . ($u['apellido'] ?? ''));
                                        $inicial = mb_strtoupper(mb_substr($nombre ?: ($u['correo'] ?? '?'), 0, 1));
                                        $label   = $nombre ?: $u['correo'];
                                        $esAdmin = $u['rol'] === 'impulsa_administrador';
                                        $rowAvatarUrl = obtenerAvatarUrl($u['avatar_path'] ?? null);
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="user-pill">
                                                    <div class="user-initials"><?php if ($rowAvatarUrl): ?><img src="<?= htmlspecialchars($rowAvatarUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Avatar del usuario"><?php else: ?><?= htmlspecialchars($inicial, ENT_QUOTES, 'UTF-8') ?><?php endif; ?></div>
                                                    <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($u['correo'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td>
                                                <span class="badge <?= $esAdmin ? 'badge-admin' : 'badge-success' ?>">
                                                    <?= $esAdmin ? 'Admin' : 'Emprendedor' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($u['email_verified_at']): ?>
                                                    <span class="material-icons" style="color:#15803d;font-size:20px">check_circle</span>
                                                <?php else: ?>
                                                    <span class="material-icons" style="color:#d1d5db;font-size:20px">radio_button_unchecked</span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="color:#6b7280;font-size:13px">
                                                <?= htmlspecialchars(date('d/m/Y H:i', strtotime($u['created_at'])), ENT_QUOTES, 'UTF-8') ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" style="color:#9ca3af;text-align:center;padding:24px">
                                            Sin usuarios registrados todavía.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </section>
        </div>
    </div>

    <?php require_once __DIR__ . '/../../partials/modal_perfil/modal_perfil.php'; ?>

    <script>
        // Sesión en consola
        const sesion = {
            user_id:          <?= json_encode($_SESSION['user_id']          ?? null) ?>,
            correo:           <?= json_encode($_SESSION['correo']           ?? null) ?>,
            rol:              <?= json_encode($_SESSION['rol']              ?? null) ?>,
            nombre:           <?= json_encode($_SESSION['nombre']           ?? null) ?>,
            apellido:         <?= json_encode($_SESSION['apellido']         ?? null) ?>,
            apodo:            <?= json_encode($_SESSION['apodo']            ?? null) ?>,
            fecha_nacimiento: <?= json_encode($_SESSION['fecha_nacimiento'] ?? null) ?>,
        };
        console.group('[Impulsa] Sesión activa — Admin');
        console.table(sesion);
        console.groupEnd();

    </script>
</body>

</html>
