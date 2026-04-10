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

function redirigirPorRol(string $rol): void
{
    if ($rol === 'impulsa_administrador') {
        header('Location: /views/admin/admin_dashboard.php');
        exit;
    }

    if ($rol === 'impulsa_emprendedor') {
        header('Location: /views/emprendedor/emprendedor_dashboard.php');
        exit;
    }

    header('Location: /index.php');
    exit;
}

if (isset($_SESSION['user_id'], $_SESSION['rol'])) {
    redirigirPorRol((string) $_SESSION['rol']);
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim((string) ($_POST['usuario'] ?? ''));
    $contrasena = (string) ($_POST['contrasena'] ?? '');
    $codigoAcceso = trim((string) ($_POST['codigo_acceso'] ?? ''));

    if ($usuario === '' || $contrasena === '' || $codigoAcceso === '') {
        $error = 'Completá usuario, contraseña y código de acceso.';
    } else {
        $sql = 'SELECT id, usuario, contrasena, codigo_acceso, rol FROM auth WHERE usuario = :usuario LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['usuario' => $usuario]);
        $auth = $stmt->fetch(PDO::FETCH_ASSOC);

        $credencialesValidas =
            $auth &&
            password_verify($contrasena, (string) $auth['contrasena']) &&
            password_verify($codigoAcceso, (string) $auth['codigo_acceso']);

        if (!$credencialesValidas) {
            $error = 'Credenciales inválidas.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $auth['id'];
            $_SESSION['correo'] = (string) $auth['usuario'];
            $_SESSION['rol'] = (string) ($auth['rol'] ?: 'impulsa_emprendedor');

            redirigirPorRol($_SESSION['rol']);
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
            max-width: 420px;
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 14px 34px rgba(15, 23, 42, 0.12);
        }
    </style>
</head>

<body class="d-flex align-items-center justify-content-center p-3">
    <main class="card login-card w-100">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <i class="bi bi-shield-lock fs-1 text-primary"></i>
                <h1 class="h4 mt-2 mb-1">Iniciar sesión</h1>
                <p class="text-secondary mb-0">Acceso al sistema</p>
            </div>

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="post" autocomplete="off" novalidate>
                <div class="mb-3">
                    <label for="usuario" class="form-label">Usuario</label>
                    <input
                        type="text"
                        class="form-control"
                        id="usuario"
                        name="usuario"
                        required
                        value="<?= isset($usuario) ? htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8') : '' ?>">
                </div>

                <div class="mb-3">
                    <label for="contrasena" class="form-label">Contraseña</label>
                    <input
                        type="password"
                        class="form-control"
                        id="contrasena"
                        name="contrasena"
                        required>
                </div>

                <div class="mb-4">
                    <label for="codigo_acceso" class="form-label">Código de acceso</label>
                    <input
                        type="text"
                        class="form-control"
                        id="codigo_acceso"
                        name="codigo_acceso"
                        required>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    Ingresar
                </button>
            </form>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>
