<?php

ini_set('session.gc_maxlifetime', 31536000);
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/AuthModel.php';

use SVE\Mail\Mailer;
require_once __DIR__ . '/../mail/Mail.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /index.php');
    exit;
}

$action = $_POST['action'] ?? 'login';
$auth   = new AuthModel($pdo);

// =========================================================================
// REGISTRO
// =========================================================================
if ($action === 'register') {
    $correo          = trim((string)($_POST['correo']            ?? ''));
    $password        = (string)($_POST['contrasena']             ?? '');
    $passwordConfirm = (string)($_POST['contrasena_confirm']     ?? '');

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
        registrarAuditoria($pdo, [
            'evento' => 'register_error',
            'estado' => 'invalid',
            'datos'  => ['correo' => $correo],
        ]);
        header('Location: /index.php?register_error=invalid');
        exit;
    }

    if ($password !== $passwordConfirm) {
        registrarAuditoria($pdo, [
            'evento' => 'register_error',
            'estado' => 'nomatch',
            'datos'  => ['correo' => $correo],
        ]);
        header('Location: /index.php?register_error=nomatch');
        exit;
    }

    $result = $auth->registrar($correo, $password);

    if (!$result['ok']) {
        registrarAuditoria($pdo, [
            'evento' => 'register_error',
            'estado' => $result['error'],
            'datos'  => ['correo' => $correo],
        ]);
        $errorKey = $result['error'] === 'exists' ? 'exists' : 'invalid';
        header('Location: /index.php?register_error=' . $errorKey);
        exit;
    }

    // Enviar correo de verificación
    $appUrl    = rtrim((string)(getenv('APP_URL') ?: ''), '/');
    $verifyUrl = $appUrl . '/auth/verificar.php?token=' . urlencode($result['token']);

    Mailer::enviarVerificacionCorreo([
        'correo'       => $result['correo'],
        'link'         => $verifyUrl,
        'user_auth_id' => $result['id'],
    ]);

    registrarAuditoria($pdo, [
        'evento'        => 'register_ok',
        'estado'        => 'ok',
        'usuario_id'    => $result['id'],
        'usuario_login' => $result['correo'],
        'rol'           => 'impulsa_emprendedor',
    ]);

    header('Location: /index.php?register_ok=1');
    exit;
}

// =========================================================================
// LOGIN
// =========================================================================
$correo   = trim((string)($_POST['correo']    ?? ''));
$password = (string)($_POST['contrasena']     ?? '');

if ($correo === '' || $password === '') {
    registrarAuditoria($pdo, [
        'evento' => 'login_error',
        'estado' => 'invalid',
        'datos'  => ['correo' => $correo],
    ]);
    header('Location: /index.php?login_error=invalid');
    exit;
}

$result = $auth->login($correo, $password);

if (!$result['ok']) {
    registrarAuditoria($pdo, [
        'evento' => 'login_error',
        'estado' => $result['error'],
        'datos'  => ['correo' => $correo],
    ]);
    header('Location: /index.php?login_error=invalid');
    exit;
}

// Correo sin verificar — no puede ingresar
if (!$result['verificado']) {
    registrarAuditoria($pdo, [
        'evento'        => 'login_error',
        'estado'        => 'unverified',
        'usuario_id'    => $result['id'],
        'usuario_login' => $result['correo'],
    ]);
    header('Location: /index.php?login_error=unverified');
    exit;
}

// Sesión base
$_SESSION['user_id'] = $result['id'];
$_SESSION['correo']  = $result['correo'];
$_SESSION['rol']     = $result['rol'];

// Sesión extendida — datos de perfil
$perfil = $auth->obtenerInfoPerfil($result['id']);
$_SESSION['nombre']           = $perfil['nombre'];
$_SESSION['apellido']         = $perfil['apellido'];
$_SESSION['apodo']            = $perfil['apodo'];
$_SESSION['avatar_path']      = $perfil['avatar_path'];
$_SESSION['fecha_nacimiento'] = $perfil['fecha_nacimiento'];

registrarAuditoria($pdo, [
    'evento'        => 'login_ok',
    'estado'        => 'ok',
    'usuario_id'    => $result['id'],
    'usuario_login' => $result['correo'],
    'rol'           => $result['rol'],
]);

// Routing por rol
switch ($result['rol']) {
    case 'impulsa_administrador':
        header('Location: /views/admin/admin_dashboard.php');
        break;
    case 'impulsa_emprendedor':
        header('Location: /views/emprendedor/emprendedor_dashboard.php');
        break;
    default:
        header('Location: /index.php?login_error=invalid');
}
exit;
