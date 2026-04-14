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
            'imagen_columna_lista' => $this->columnaExiste('calificacion_formularios', 'imagen_url'),
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
            if ($this->columnaExiste('calificacion_formularios', 'imagen_url')) {
                $stmtFormulario = $this->db->prepare(
                    "INSERT INTO calificacion_formularios
                        (subcategoria, categoria, evento_nombre, imagen_url, activo, creado_por)
                     VALUES
                        (:subcategoria, :categoria, :evento_nombre, :imagen_url, :activo, :creado_por)"
                );

                $stmtFormulario->execute([
                    'subcategoria' => $data['subcategoria'],
                    'categoria' => $data['categoria'],
                    'evento_nombre' => $data['evento_nombre'],
                    'imagen_url' => $data['imagen_url'] ?? null,
                    'activo' => (int) ($data['activo'] ?? 0),
                    'creado_por' => (int) $data['creado_por'],
                ]);
            } else {
                $stmtFormulario = $this->db->prepare(
                    "INSERT INTO calificacion_formularios
                        (subcategoria, categoria, evento_nombre, activo, creado_por)
                     VALUES
                        (:subcategoria, :categoria, :evento_nombre, :activo, :creado_por)"
                );

                $stmtFormulario->execute([
                    'subcategoria' => $data['subcategoria'],
                    'categoria' => $data['categoria'],
                    'evento_nombre' => $data['evento_nombre'],
                    'activo' => (int) ($data['activo'] ?? 0),
                    'creado_por' => (int) $data['creado_por'],
                ]);
            }

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
     * @return array<string, mixed>
     */
    public function obtenerFormularioPorId(int $formularioId): array
    {
        $tieneImagenUrl = $this->columnaExiste('calificacion_formularios', 'imagen_url');

        $sql = "SELECT f.id,
                       f.subcategoria,
                       f.categoria,
                       f.evento_nombre,
                       " . ($tieneImagenUrl ? "f.imagen_url" : "NULL AS imagen_url") . ",
                       f.activo,
                       f.creado_por,
                       f.creado_en
                FROM calificacion_formularios f
                WHERE f.id = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $formularioId]);
        $formulario = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        if (!$formulario) {
            return [];
        }

        $criterios = $this->obtenerCriteriosPorFormularioIds([$formularioId]);
        $formulario['criterios'] = $criterios[$formularioId] ?? [];

        return $formulario;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function actualizarFormulario(int $formularioId, array $data): bool
    {
        $this->db->beginTransaction();

        try {
            if ($this->columnaExiste('calificacion_formularios', 'imagen_url')) {
                $stmtFormulario = $this->db->prepare(
                    "UPDATE calificacion_formularios
                     SET subcategoria = :subcategoria,
                         categoria = :categoria,
                         evento_nombre = :evento_nombre,
                         imagen_url = :imagen_url,
                         activo = :activo
                     WHERE id = :id"
                );

                $stmtFormulario->execute([
                    'id' => $formularioId,
                    'subcategoria' => $data['subcategoria'],
                    'categoria' => $data['categoria'],
                    'evento_nombre' => $data['evento_nombre'],
                    'imagen_url' => $data['imagen_url'] ?? null,
                    'activo' => (int) ($data['activo'] ?? 0),
                ]);
            } else {
                $stmtFormulario = $this->db->prepare(
                    "UPDATE calificacion_formularios
                     SET subcategoria = :subcategoria,
                         categoria = :categoria,
                         evento_nombre = :evento_nombre,
                         activo = :activo
                     WHERE id = :id"
                );

                $stmtFormulario->execute([
                    'id' => $formularioId,
                    'subcategoria' => $data['subcategoria'],
                    'categoria' => $data['categoria'],
                    'evento_nombre' => $data['evento_nombre'],
                    'activo' => (int) ($data['activo'] ?? 0),
                ]);
            }

            $stmtEliminarCriterios = $this->db->prepare(
                "DELETE FROM calificacion_formulario_criterios
                 WHERE formulario_id = :formulario_id"
            );
            $stmtEliminarCriterios->execute(['formulario_id' => $formularioId]);

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
            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function eliminarFormularioEnCascada(int $formularioId): array
    {
        $this->db->beginTransaction();

        try {
            $formulario = $this->obtenerFormularioPorId($formularioId);
            if (!$formulario) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                return ['eliminado' => false, 'imagen_url' => null];
            }

            if ($this->tablaExiste('calificacion_evaluaciones') && $this->tablaExiste('calificacion_evaluacion_detalles')) {
                $stmtEvaluaciones = $this->db->prepare(
                    "SELECT id
                     FROM calificacion_evaluaciones
                     WHERE formulario_id = :formulario_id"
                );
                $stmtEvaluaciones->execute(['formulario_id' => $formularioId]);
                $evaluacionIds = array_map(
                    static fn($id): int => (int) $id,
                    $stmtEvaluaciones->fetchAll(PDO::FETCH_COLUMN) ?: []
                );

                if ($evaluacionIds) {
                    $placeholders = implode(', ', array_fill(0, count($evaluacionIds), '?'));

                    $stmtDetalles = $this->db->prepare(
                        "DELETE FROM calificacion_evaluacion_detalles
                         WHERE evaluacion_id IN ($placeholders)"
                    );
                    $stmtDetalles->execute($evaluacionIds);

                    $stmtEliminarEvaluaciones = $this->db->prepare(
                        "DELETE FROM calificacion_evaluaciones
                         WHERE id IN ($placeholders)"
                    );
                    $stmtEliminarEvaluaciones->execute($evaluacionIds);
                }
            }

            $stmtEliminarCriterios = $this->db->prepare(
                "DELETE FROM calificacion_formulario_criterios
                 WHERE formulario_id = :formulario_id"
            );
            $stmtEliminarCriterios->execute(['formulario_id' => $formularioId]);

            $stmtEliminarFormulario = $this->db->prepare(
                "DELETE FROM calificacion_formularios
                 WHERE id = :id"
            );
            $stmtEliminarFormulario->execute(['id' => $formularioId]);

            $this->db->commit();

            return [
                'eliminado' => $stmtEliminarFormulario->rowCount() > 0,
                'imagen_url' => $formulario['imagen_url'] ?? null,
            ];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function obtenerFormulariosConCriterios(): array
    {
        $tieneEvaluaciones = $this->tablaExiste('calificacion_evaluaciones');
        $tieneImagenUrl = $this->columnaExiste('calificacion_formularios', 'imagen_url');

        $sql = "SELECT f.id,
                       f.subcategoria,
                       f.categoria,
                       f.evento_nombre,
                       " . ($tieneImagenUrl ? "f.imagen_url" : "NULL AS imagen_url") . ",
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
                GROUP BY f.id, f.subcategoria, f.categoria, f.evento_nombre, " . ($tieneImagenUrl ? "f.imagen_url, " : "") . "f.activo, f.creado_por, f.creado_en, a.usuario
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

    private function columnaExiste(string $tabla, string $columna): bool
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*)
                 FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = :tabla
                   AND COLUMN_NAME = :columna"
            );
            $stmt->execute([
                'tabla' => $tabla,
                'columna' => $columna,
            ]);

            return (int) $stmt->fetchColumn() > 0;
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
