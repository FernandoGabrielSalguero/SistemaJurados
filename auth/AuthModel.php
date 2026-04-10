<?php

class AuthModel
{
    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    // -------------------------------------------------------------------------
    // Registro
    // -------------------------------------------------------------------------

    /**
     * Registra un nuevo usuario en user_auth, user_info y user_contacto.
     *
     * @return array{ok: true,  id: int, correo: string, token: string}
     *       | array{ok: false, error: 'exists'|'db_error'}
     */
    public function registrar(string $correo, string $password): array
    {
        $correo = strtolower(trim($correo));

        $stmt = $this->db->prepare(
            "SELECT id FROM user_auth WHERE correo = :correo LIMIT 1"
        );
        $stmt->execute(['correo' => $correo]);
        if ($stmt->fetch()) {
            return ['ok' => false, 'error' => 'exists'];
        }

        $hash  = password_hash($password, PASSWORD_BCRYPT);
        $token = bin2hex(random_bytes(32)); // 64 chars hex, criptográficamente seguro

        try {
            $this->db->beginTransaction();

            // 1. Credenciales
            $stmt = $this->db->prepare(
                "INSERT INTO user_auth (correo, password, rol, verification_token)
                 VALUES (:correo, :password, 'impulsa_emprendedor', :token)"
            );
            $stmt->execute([
                'correo'   => $correo,
                'password' => $hash,
                'token'    => $token,
            ]);
            $userId = (int) $this->db->lastInsertId();

            // 2. Perfil vacío — se completa en el onboarding
            $stmt = $this->db->prepare(
                "INSERT INTO user_info (user_auth_id) VALUES (:id)"
            );
            $stmt->execute(['id' => $userId]);

            // 3. Contacto — correo espejado, verificación pendiente
            $stmt = $this->db->prepare(
                "INSERT INTO user_contacto (user_auth_id, correo, check_correo)
                 VALUES (:id, :correo, 0)"
            );
            $stmt->execute(['id' => $userId, 'correo' => $correo]);

            $this->db->commit();

            return ['ok' => true, 'id' => $userId, 'correo' => $correo, 'token' => $token];

        } catch (\Throwable) {
            $this->db->rollBack();
            return ['ok' => false, 'error' => 'db_error'];
        }
    }

    // -------------------------------------------------------------------------
    // Login
    // -------------------------------------------------------------------------

    /**
     * Autentica al usuario por correo y contraseña.
     *
     * @return array{ok: true,  id: int, correo: string, rol: string, verificado: bool}
     *       | array{ok: false, error: 'invalid'}
     */
    public function login(string $correo, string $password): array
    {
        $correo = strtolower(trim($correo));

        $stmt = $this->db->prepare(
            "SELECT id, correo, password, rol, email_verified_at
             FROM user_auth
             WHERE correo = :correo
             LIMIT 1"
        );
        $stmt->execute(['correo' => $correo]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, (string) $user['password'])) {
            return ['ok' => false, 'error' => 'invalid'];
        }

        return [
            'ok'         => true,
            'id'         => (int) $user['id'],
            'correo'     => (string) $user['correo'],
            'rol'        => (string) $user['rol'],
            'verificado' => $user['email_verified_at'] !== null,
        ];
    }

    // -------------------------------------------------------------------------
    // Perfil de sesión
    // -------------------------------------------------------------------------

    /**
     * Devuelve nombre, apellido, apodo y fecha_nacimiento para poblar la sesión.
     *
     * @return array{nombre: string|null, apellido: string|null, apodo: string|null, avatar_path: string|null, fecha_nacimiento: string|null}
     */
    public function obtenerInfoPerfil(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT ui.nombre, ui.apellido, ui.apodo, ui.avatar_path, ui.fecha_nacimiento
             FROM user_info ui
             WHERE ui.user_auth_id = :id
             LIMIT 1"
        );
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'nombre'           => $row['nombre']           ?? null,
            'apellido'         => $row['apellido']         ?? null,
            'apodo'            => $row['apodo']            ?? null,
            'avatar_path'      => $row['avatar_path']      ?? null,
            'fecha_nacimiento' => $row['fecha_nacimiento'] ?? null,
        ];
    }

    // -------------------------------------------------------------------------
    // Verificación de correo
    // -------------------------------------------------------------------------

    /**
     * Valida el token y activa el correo del usuario.
     *
     * @return array{ok: true,  id: int}
     *       | array{ok: false, error: 'invalid_token'|'already_verified'|'db_error'}
     */
    public function verificarToken(string $token): array
    {
        if ($token === '') {
            return ['ok' => false, 'error' => 'invalid_token'];
        }

        $stmt = $this->db->prepare(
            "SELECT id, email_verified_at
             FROM user_auth
             WHERE verification_token = :token
             LIMIT 1"
        );
        $stmt->execute(['token' => $token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return ['ok' => false, 'error' => 'invalid_token'];
        }

        if ($user['email_verified_at'] !== null) {
            return ['ok' => false, 'error' => 'already_verified'];
        }

        $userId = (int) $user['id'];

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare(
                "UPDATE user_auth
                 SET email_verified_at = NOW(), verification_token = NULL
                 WHERE id = :id"
            );
            $stmt->execute(['id' => $userId]);

            $stmt = $this->db->prepare(
                "UPDATE user_contacto SET check_correo = 1 WHERE user_auth_id = :id"
            );
            $stmt->execute(['id' => $userId]);

            $this->db->commit();

            return ['ok' => true, 'id' => $userId];

        } catch (\Throwable) {
            $this->db->rollBack();
            return ['ok' => false, 'error' => 'db_error'];
        }
    }
}
