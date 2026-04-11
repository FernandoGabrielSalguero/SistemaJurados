<?php

class JuradoDashboardModel
{
    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    /**
     * @return array<string, mixed>
     */
    public function obtenerJurado(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT a.id,
                    a.usuario,
                    a.rol,
                    a.creado_en,
                    iu.nombre
             FROM auth
             AS a
             LEFT JOIN informacion_usuarios AS iu
                ON iu.user_auth_id = a.id
             WHERE a.id = :id
               AND a.rol = 'impulsa_jurado'
             LIMIT 1"
        );
        $stmt->execute(['id' => $userId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @return array<string, mixed>
     */
    public function obtenerEstadoTablasCalificaciones(): array
    {
        $requeridas = [
            'calificacion_formularios',
            'calificacion_formulario_criterios',
            'calificacion_evaluaciones',
            'calificacion_evaluacion_detalles',
        ];

        $faltantes = [];
        foreach ($requeridas as $tabla) {
            if (!$this->tablaExiste($tabla)) {
                $faltantes[] = $tabla;
            }
        }

        return [
            'formularios_listos' => !in_array('calificacion_formularios', $faltantes, true)
                && !in_array('calificacion_formulario_criterios', $faltantes, true),
            'evaluaciones_listas' => !in_array('calificacion_evaluaciones', $faltantes, true)
                && !in_array('calificacion_evaluacion_detalles', $faltantes, true),
            'faltantes' => $faltantes,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function obtenerFormulariosActivosConCriterios(): array
    {
        $stmt = $this->db->query(
            "SELECT id, subcategoria, categoria, evento_nombre, activo
             FROM calificacion_formularios
             WHERE activo = 1
             ORDER BY categoria ASC, subcategoria ASC, creado_en DESC, id DESC"
        );
        $formularios = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

        if (!$formularios) {
            return [];
        }

        $ids = array_map(static fn(array $formulario): int => (int) $formulario['id'], $formularios);
        $criterios = $this->obtenerCriteriosPorFormularioIds($ids);

        foreach ($formularios as &$formulario) {
            $formularioId = (int) $formulario['id'];
            $formulario['criterios'] = $criterios[$formularioId] ?? [];
        }
        unset($formulario);

        return $formularios;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function guardarEvaluacion(array $data): int
    {
        $this->db->beginTransaction();

        try {
            $stmtEvaluacion = $this->db->prepare(
                "INSERT INTO calificacion_evaluaciones
                    (formulario_id, jurado_id, competidor_numero, competidor_nombre, categoria, evento_nombre, puntaje_total, promedio)
                 VALUES
                    (:formulario_id, :jurado_id, :competidor_numero, :competidor_nombre, :categoria, :evento_nombre, :puntaje_total, :promedio)"
            );

            $stmtEvaluacion->execute([
                'formulario_id' => (int) $data['formulario_id'],
                'jurado_id' => (int) $data['jurado_id'],
                'competidor_numero' => $data['competidor_numero'],
                'competidor_nombre' => $data['competidor_nombre'],
                'categoria' => $data['categoria'],
                'evento_nombre' => $data['evento_nombre'],
                'puntaje_total' => $data['puntaje_total'],
                'promedio' => $data['promedio'],
            ]);

            $evaluacionId = (int) $this->db->lastInsertId();

            $stmtDetalle = $this->db->prepare(
                "INSERT INTO calificacion_evaluacion_detalles
                    (evaluacion_id, criterio_clave, criterio_nombre, puntaje_maximo, puntaje_otorgado)
                 VALUES
                    (:evaluacion_id, :criterio_clave, :criterio_nombre, :puntaje_maximo, :puntaje_otorgado)"
            );

            foreach ($data['detalles'] as $detalle) {
                $stmtDetalle->execute([
                    'evaluacion_id' => $evaluacionId,
                    'criterio_clave' => $detalle['criterio_clave'],
                    'criterio_nombre' => $detalle['criterio_nombre'],
                    'puntaje_maximo' => $detalle['puntaje_maximo'],
                    'puntaje_otorgado' => $detalle['puntaje_otorgado'],
                ]);
            }

            $this->db->commit();
            return $evaluacionId;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function existeEvaluacionDuplicada(int $formularioId, int $juradoId, string $competidorNumero): bool
    {
        $stmt = $this->db->prepare(
            "SELECT id
             FROM calificacion_evaluaciones
             WHERE formulario_id = :formulario_id
               AND jurado_id = :jurado_id
               AND competidor_numero = :competidor_numero
             LIMIT 1"
        );
        $stmt->execute([
            'formulario_id' => $formularioId,
            'jurado_id' => $juradoId,
            'competidor_numero' => $competidorNumero,
        ]);

        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function tablaExiste(string $tabla): bool
    {
        try {
            $stmt = $this->db->prepare("SHOW TABLES LIKE :tabla");
            $stmt->execute(['tabla' => $tabla]);
            return (bool) $stmt->fetch(PDO::FETCH_NUM);
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * @param array<int, int> $ids
     * @return array<int, array<int, array<string, mixed>>>
     */
    private function obtenerCriteriosPorFormularioIds(array $ids): array
    {
        if (!$ids) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare(
            "SELECT formulario_id, criterio_clave, criterio_nombre, puntaje_maximo, orden_visual
             FROM calificacion_formulario_criterios
             WHERE formulario_id IN ($placeholders)
             ORDER BY formulario_id ASC, orden_visual ASC, id ASC"
        );
        $stmt->execute($ids);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $agrupados = [];
        foreach ($rows as $row) {
            $formularioId = (int) $row['formulario_id'];
            if (!isset($agrupados[$formularioId])) {
                $agrupados[$formularioId] = [];
            }
            $agrupados[$formularioId][] = $row;
        }

        return $agrupados;
    }
}
