<?php

require_once __DIR__ . '/../config.php';

class AdminDashboardModel
{
    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    /**
     * Devuelve el perfil completo del administrador (auth + info + contacto).
     *
     * @return array<string, mixed>
     */
    public function obtenerPerfil(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                ua.id,
                ua.correo,
                ua.rol,
                ua.email_verified_at,
                ua.created_at,
                ui.nombre,
                ui.apellido,
                ui.apodo,
                ui.avatar_path,
                ui.fecha_nacimiento,
                uc.check_correo,
                uc.permison_correo,
                uc.whatsapp,
                uc.check_whatsapp,
                uc.permison_whatsapp
             FROM user_auth ua
             LEFT JOIN user_info     ui ON ui.user_auth_id = ua.id
             LEFT JOIN user_contacto uc ON uc.user_auth_id = ua.id
             WHERE ua.id = :id
             LIMIT 1"
        );
        $stmt->execute(['id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Estadísticas generales de usuarios registrados en la plataforma.
     *
     * @return array{total: int, verificados: int, sin_verificar: int, administradores: int, emprendedores: int}
     */
    public function obtenerEstadisticasUsuarios(): array
    {
        $stmt = $this->db->query(
            "SELECT
                COUNT(*)                                              AS total,
                SUM(email_verified_at IS NOT NULL)                   AS verificados,
                SUM(email_verified_at IS NULL)                       AS sin_verificar,
                SUM(rol = 'impulsa_administrador')                   AS administradores,
                SUM(rol = 'impulsa_emprendedor')                     AS emprendedores
             FROM user_auth"
        );
        $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : [];

        return [
            'total'           => (int) ($row['total']           ?? 0),
            'verificados'     => (int) ($row['verificados']     ?? 0),
            'sin_verificar'   => (int) ($row['sin_verificar']   ?? 0),
            'administradores' => (int) ($row['administradores'] ?? 0),
            'emprendedores'   => (int) ($row['emprendedores']   ?? 0),
        ];
    }

    /**
     * Últimos N usuarios registrados.
     *
     * @return array<int, array<string, mixed>>
     */
    public function obtenerRegistrosRecientes(int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                ua.id,
                ua.correo,
                ua.rol,
                ua.email_verified_at,
                ua.created_at,
                ui.nombre,
                ui.apellido
                , ui.avatar_path
             FROM user_auth ua
             LEFT JOIN user_info ui ON ui.user_auth_id = ua.id
             ORDER BY ua.created_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
