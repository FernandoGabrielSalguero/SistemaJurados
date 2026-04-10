<?php

class AdminCalificacionesModel
{
    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    /**
     * Devuelve los datos base del administrador autenticado.
     *
     * @return array<string, mixed>
     */
    public function obtenerAdministrador(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, usuario, rol, creado_en
             FROM auth
             WHERE id = :id
               AND rol = 'impulsa_administrador'
             LIMIT 1"
        );
        $stmt->execute(['id' => $userId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }
}
