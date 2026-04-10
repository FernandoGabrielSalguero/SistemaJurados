<?php
ini_set('session.gc_maxlifetime', 31536000);
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

require_once __DIR__ . '/config.php';

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

function redirigirPorRol(string $rol): void
{
    if ($rol === 'impulsa_administrador') {
        header('Location: /views/admin/admin_dashboard.php');
        exit;
    }

    if ($rol === 'impulsa_jurado') {
        header('Location: /views/jurado/jurado_dashboard.php');
        exit;
    }

    header('Location: /index.php');
    exit;
}

function iniciarSesionUsuario(array $auth): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $auth['id'];
    $_SESSION['usuario'] = (string) $auth['usuario'];
    $_SESSION['correo'] = (string) $auth['usuario'];
    $_SESSION['rol'] = (string) $auth['rol'];
}

if (isset($_SESSION['user_id'], $_SESSION['rol'])) {
    redirigirPorRol((string) $_SESSION['rol']);
}

$error = '';
$mostrarAccesoAdmin = false;
$tieneAccesoHabilitado = authTieneColumna($pdo, 'acceso_habilitado');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim((string) ($_POST['usuario'] ?? ''));
    $contrasena = (string) ($_POST['contrasena'] ?? '');
    $codigoAcceso = trim((string) ($_POST['codigo_acceso'] ?? ''));

    $quiereIngresarComoAdmin = $usuario !== '' || $contrasena !== '';

    if ($quiereIngresarComoAdmin) {
        $mostrarAccesoAdmin = true;

        if ($usuario === '' || $contrasena === '') {
            $error = 'Para ingresar como administrador completá usuario y contraseña.';
        } else {
            $sql = 'SELECT id, usuario, contrasena, rol';
            if ($tieneAccesoHabilitado) {
                $sql .= ', acceso_habilitado';
            }
            $sql .= ' FROM auth WHERE usuario = :usuario AND rol = :rol LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'usuario' => $usuario,
                'rol' => 'impulsa_administrador',
            ]);
            $auth = $stmt->fetch(PDO::FETCH_ASSOC);

            $credencialesValidas =
                $auth &&
                password_verify($contrasena, (string) $auth['contrasena']);

            if (!$credencialesValidas) {
                $error = 'Usuario o contraseña inválidos.';
            } elseif ($tieneAccesoHabilitado && (int) ($auth['acceso_habilitado'] ?? 1) !== 1) {
                $error = 'Tu acceso al sistema está deshabilitado.';
            } else {
                iniciarSesionUsuario($auth);
                redirigirPorRol($_SESSION['rol']);
            }
        }
    } else {
        if ($codigoAcceso === '') {
            $error = 'Ingresá un código de acceso válido.';
        } else {
            $sqlJurados = 'SELECT id, usuario, codigo_acceso, rol';
            if ($tieneAccesoHabilitado) {
                $sqlJurados .= ', acceso_habilitado';
            }
            $sqlJurados .= ' FROM auth WHERE rol = :rol';
            $stmt = $pdo->prepare($sqlJurados);
            $stmt->execute(['rol' => 'impulsa_jurado']);
            $jurados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $juradoValido = null;
            foreach ($jurados as $jurado) {
                if (
                    password_verify($codigoAcceso, (string) $jurado['codigo_acceso']) &&
                    (!$tieneAccesoHabilitado || (int) ($jurado['acceso_habilitado'] ?? 1) === 1)
                ) {
                    $juradoValido = $jurado;
                    break;
                }
            }

            if ($juradoValido === null) {
                $error = 'Código de acceso inválido o usuario deshabilitado.';
            } else {
                iniciarSesionUsuario($juradoValido);
                redirigirPorRol($_SESSION['rol']);
            }
        }
    }
}
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Sistema Jurados</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #e2e8f0 0%, #f8fafc 55%, #dbeafe 100%);
        }

        .login-card {
            max-width: 460px;
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 14px 34px rgba(15, 23, 42, 0.12);
        }

        .access-toggle {
            width: 52px;
            height: 52px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .admin-access-box {
            background: #f8fafc;
            border: 1px solid #dbeafe;
            border-radius: 0.9rem;
            padding: 1rem;
        }

        .divider-label {
            font-size: 0.78rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #64748b;
        }
    </style>
</head>

<body class="d-flex align-items-center justify-content-center p-3">
    <main class="card login-card w-100">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <i class="bi bi-shield-lock fs-1 text-primary"></i>
                <h1 class="h4 mt-2 mb-1">Acceso al sistema</h1>
            </div>

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="post" autocomplete="off" novalidate>
                <div class="mb-4">
                    <label for="codigo_acceso" class="form-label">Código de acceso</label>
                    <input
                        type="text"
                        class="form-control"
                        id="codigo_acceso"
                        name="codigo_acceso"
                        autocomplete="off"
                        autocapitalize="off"
                        autocorrect="off"
                        spellcheck="false"
                        placeholder="Ingresá tu código"
                        value="">
                </div>

                <div class="text-center mb-3">
                    <button
                        type="button"
                        class="btn btn-outline-primary access-toggle"
                        id="toggleAdminAccess"
                        aria-expanded="<?= $mostrarAccesoAdmin ? 'true' : 'false' ?>"
                        aria-controls="adminAccessFields"
                        title="Mostrar acceso administrador">
                        <i class="bi bi-person-lock"></i>
                    </button>
                </div>

                <div class="<?= $mostrarAccesoAdmin ? '' : 'd-none' ?>" id="adminAccessFields">
                    <div class="admin-access-box mb-4">
                        <div class="mb-3">
                            <label for="usuario" class="form-label">Usuario</label>
                            <input
                                type="text"
                                class="form-control"
                                id="usuario"
                                name="usuario"
                                autocomplete="off"
                                autocapitalize="off"
                                autocorrect="off"
                                spellcheck="false"
                                value="">
                        </div>

                        <div class="mb-0">
                            <label for="contrasena" class="form-label">Contraseña</label>
                            <input
                                type="password"
                                class="form-control"
                                id="contrasena"
                                name="contrasena"
                                autocomplete="new-password">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    Ingresar
                </button>
            </form>
        </div>
    </main>

    <script>
        const toggleButton = document.getElementById('toggleAdminAccess');
        const adminFields = document.getElementById('adminAccessFields');

        toggleButton.addEventListener('click', () => {
            adminFields.classList.toggle('d-none');
            const expanded = !adminFields.classList.contains('d-none');
            toggleButton.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        });
    </script>
</body>

</html>
