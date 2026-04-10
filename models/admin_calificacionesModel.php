<?php

class AdminCalificacionesModel
{
    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    /**
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

    /**
     * @return array<int, array<string, mixed>>
     */
    public function obtenerCriteriosBase(): array
    {
        return [
            ['clave' => 'tiempo', 'nombre' => 'Tiempo', 'orden' => 1, 'sugerido' => 20],
            ['clave' => 'musicalidad', 'nombre' => 'Musicalidad', 'orden' => 2, 'sugerido' => 10],
            ['clave' => 'tecnica', 'nombre' => 'Tecnica', 'orden' => 3, 'sugerido' => 20],
            ['clave' => 'dificultad', 'nombre' => 'Dificultad', 'orden' => 4, 'sugerido' => 10],
            ['clave' => 'sincronizacion', 'nombre' => 'Sincronizacion', 'orden' => 5, 'sugerido' => 20],
            ['clave' => 'coreografia', 'nombre' => 'Coreografia', 'orden' => 6, 'sugerido' => 10],
            ['clave' => 'talento_show', 'nombre' => 'Talento en el show', 'orden' => 7, 'sugerido' => 10],
        ];
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
     * @param array<string, mixed> $data
     */
    public function crearFormulario(array $data): int
    {
        $this->db->beginTransaction();

        try {
            $stmtFormulario = $this->db->prepare(
                "INSERT INTO calificacion_formularios
                    (nombre, categoria, evento_nombre, activo, creado_por)
                 VALUES
                    (:nombre, :categoria, :evento_nombre, :activo, :creado_por)"
            );

            $stmtFormulario->execute([
                'nombre' => $data['nombre'],
                'categoria' => $data['categoria'],
                'evento_nombre' => $data['evento_nombre'],
                'activo' => (int) ($data['activo'] ?? 0),
                'creado_por' => (int) $data['creado_por'],
            ]);

            $formularioId = (int) $this->db->lastInsertId();

            $stmtCriterio = $this->db->prepare(
                "INSERT INTO calificacion_formulario_criterios
                    (formulario_id, criterio_clave, criterio_nombre, puntaje_maximo, orden_visual)
                 VALUES
                    (:formulario_id, :criterio_clave, :criterio_nombre, :puntaje_maximo, :orden_visual)"
            );

            foreach ($data['criterios'] as $criterio) {
                $stmtCriterio->execute([
                    'formulario_id' => $formularioId,
                    'criterio_clave' => $criterio['clave'],
                    'criterio_nombre' => $criterio['nombre'],
                    'puntaje_maximo' => (int) $criterio['puntaje_maximo'],
                    'orden_visual' => (int) $criterio['orden'],
                ]);
            }

            $this->db->commit();
            return $formularioId;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function actualizarEstadoFormulario(int $formularioId, bool $activo): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE calificacion_formularios
             SET activo = :activo
             WHERE id = :id"
        );
        $stmt->execute([
            'activo' => $activo ? 1 : 0,
            'id' => $formularioId,
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function obtenerFormulariosConCriterios(): array
    {
        $tieneEvaluaciones = $this->tablaExiste('calificacion_evaluaciones');

        $sql = "SELECT f.id,
                       f.nombre,
                       f.categoria,
                       f.evento_nombre,
                       f.activo,
                       f.creado_por,
                       f.creado_en,
                       a.usuario AS creador_usuario";

        if ($tieneEvaluaciones) {
            $sql .= ",
                       COUNT(e.id) AS total_evaluaciones";
        } else {
            $sql .= ",
                       0 AS total_evaluaciones";
        }

        $sql .= "
                FROM calificacion_formularios f
                LEFT JOIN auth a ON a.id = f.creado_por";

        if ($tieneEvaluaciones) {
            $sql .= "
                LEFT JOIN calificacion_evaluaciones e ON e.formulario_id = f.id";
        }

        $sql .= "
                GROUP BY f.id, f.nombre, f.categoria, f.evento_nombre, f.activo, f.creado_por, f.creado_en, a.usuario
                ORDER BY f.activo DESC, f.creado_en DESC, f.id DESC";

        $stmt = $this->db->query($sql);
        $formularios = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

        if (!$formularios) {
            return [];
        }

        $ids = array_map(
            static fn(array $formulario): int => (int) $formulario['id'],
            $formularios
        );
        $criteriosPorFormulario = $this->obtenerCriteriosPorFormularioIds($ids);

        foreach ($formularios as &$formulario) {
            $formularioId = (int) $formulario['id'];
            $criterios = $criteriosPorFormulario[$formularioId] ?? [];
            $formulario['criterios'] = $criterios;
            $formulario['puntaje_total'] = array_sum(
                array_map(
                    static fn(array $criterio): int => (int) $criterio['puntaje_maximo'],
                    $criterios
                )
            );
        }
        unset($formulario);

        return $formularios;
    }

    /**
     * @param array<int, array<string, mixed>> $formularios
     * @return array<string, int>
     */
    public function obtenerMetricasResumen(array $formularios): array
    {
        $metricas = [
            'formularios_total' => count($formularios),
            'formularios_activos' => 0,
            'categorias_total' => 0,
            'evaluaciones_total' => 0,
        ];

        $categorias = [];

        foreach ($formularios as $formulario) {
            if ((int) ($formulario['activo'] ?? 0) === 1) {
                $metricas['formularios_activos']++;
            }

            $categoria = trim((string) ($formulario['categoria'] ?? ''));
            if ($categoria !== '') {
                $categorias[$categoria] = true;
            }

            $metricas['evaluaciones_total'] += (int) ($formulario['total_evaluaciones'] ?? 0);
        }

        $metricas['categorias_total'] = count($categorias);

        return $metricas;
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
            "SELECT formulario_id,
                    criterio_clave,
                    criterio_nombre,
                    puntaje_maximo,
                    orden_visual
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
