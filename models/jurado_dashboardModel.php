<?php

require_once __DIR__ . '/../config.php';

class EmprendedorDashboardModel
{
    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    /**
     * Devuelve el perfil completo del emprendedor (auth + info + contacto).
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

    public function obtenerProgresoMision(int $userId): bool
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT completado
                 FROM emprendedor_mision
                 WHERE user_auth_id = :id
                 LIMIT 1"
            );
            $stmt->execute(['id' => $userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return !empty($row['completado']);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function obtenerProgresoVision(int $userId): bool
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT completado
                 FROM emprendedor_vision
                 WHERE user_auth_id = :id
                 LIMIT 1"
            );
            $stmt->execute(['id' => $userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return !empty($row['completado']);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function obtenerProgresoBuyerPersona(int $userId): bool
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT completado
                 FROM emprendedor_buyer_persona
                 WHERE user_auth_id = :id
                 LIMIT 1"
            );
            $stmt->execute(['id' => $userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return !empty($row['completado']);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function obtenerResumenMision(int $userId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT mision_estructura, completado
                 FROM emprendedor_mision
                 WHERE user_auth_id = :id
                 LIMIT 1"
            );
            $stmt->execute(['id' => $userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function obtenerResumenVision(int $userId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT vision_estructura, completado
                 FROM emprendedor_vision
                 WHERE user_auth_id = :id
                 LIMIT 1"
            );
            $stmt->execute(['id' => $userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function obtenerResumenBuyerPersona(int $userId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT buyer_persona_estructura, completado
                 FROM emprendedor_buyer_persona
                 WHERE user_auth_id = :id
                 LIMIT 1"
            );
            $stmt->execute(['id' => $userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }

    public function obtenerProgresoLandingPage(int $userId): bool
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT completado
                 FROM landing_page_request
                 WHERE user_auth_id = :id
                 LIMIT 1"
            );
            $stmt->execute(['id' => $userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return !empty($row['completado']);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function puedeAccederLanding(int $userId): bool
    {
        return $this->obtenerProgresoMision($userId)
            && $this->obtenerProgresoVision($userId)
            && $this->obtenerProgresoBuyerPersona($userId);
    }
}
