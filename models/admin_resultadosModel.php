<?php

class AdminResultadosModel
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
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function obtenerFiltrosDisponibles(): array
    {
        $formularios = [];
        $categorias = [];

        try {
            $stmtFormularios = $this->db->query(
                "SELECT DISTINCT id, nombre, categoria, evento_nombre
                 FROM calificacion_formularios
                 ORDER BY nombre ASC, categoria ASC"
            );
            $formularios = $stmtFormularios ? $stmtFormularios->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Throwable $e) {
            $formularios = [];
        }

        try {
            $stmtCategorias = $this->db->query(
                "SELECT DISTINCT categoria
                 FROM calificacion_formularios
                 WHERE categoria <> ''
                 ORDER BY categoria ASC"
            );
            $categorias = $stmtCategorias ? $stmtCategorias->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Throwable $e) {
            $categorias = [];
        }

        return [
            'formularios' => $formularios,
            'categorias' => $categorias,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function obtenerResultadosAgrupados(int $formularioId = 0, string $categoria = ''): array
    {
        $sql = "SELECT e.id,
                       e.formulario_id,
                       e.jurado_id,
                       e.competidor_numero,
                       e.competidor_nombre,
                       e.categoria,
                       e.evento_nombre,
                       e.puntaje_total,
                       e.promedio,
                       e.creado_en,
                       f.nombre AS formulario_nombre,
                       a.usuario AS jurado_usuario";

        if ($this->tablaExiste('informacion_usuarios')) {
            $sql .= ", iu.nombre AS jurado_nombre";
        } else {
            $sql .= ", NULL AS jurado_nombre";
        }

        $sql .= "
                FROM calificacion_evaluaciones e
                INNER JOIN calificacion_formularios f ON f.id = e.formulario_id
                INNER JOIN auth a ON a.id = e.jurado_id";

        if ($this->tablaExiste('informacion_usuarios')) {
            $sql .= "
                LEFT JOIN informacion_usuarios iu ON iu.user_auth_id = a.id";
        }

        $sql .= "
                WHERE 1 = 1";

        $params = [];
        if ($formularioId > 0) {
            $sql .= " AND e.formulario_id = :formulario_id";
            $params['formulario_id'] = $formularioId;
        }
        if ($categoria !== '') {
            $sql .= " AND e.categoria = :categoria";
            $params['categoria'] = $categoria;
        }

        $sql .= " ORDER BY f.nombre ASC, e.categoria ASC, e.puntaje_total DESC, e.creado_en DESC, e.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $evaluaciones = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        if (!$evaluaciones) {
            return [];
        }

        $detallesPorEvaluacion = $this->obtenerDetallesPorEvaluacionIds(
            array_map(static fn(array $row): int => (int) $row['id'], $evaluaciones)
        );

        $agrupados = [];
        foreach ($evaluaciones as $evaluacion) {
            $evaluacionId = (int) $evaluacion['id'];
            $groupKey = (int) $evaluacion['formulario_id'] . '|' . (string) $evaluacion['categoria'];

            if (!isset($agrupados[$groupKey])) {
                $agrupados[$groupKey] = [
                    'formulario_id' => (int) $evaluacion['formulario_id'],
                    'formulario_nombre' => (string) $evaluacion['formulario_nombre'],
                    'categoria' => (string) $evaluacion['categoria'],
                    'evento_nombre' => (string) $evaluacion['evento_nombre'],
                    'evaluaciones' => [],
                    'competidores_detalle' => [],
                    'criterios' => [],
                    'total_evaluaciones' => 0,
                    'competidores' => [],
                    'jurados' => [],
                    'puntaje_acumulado' => 0.0,
                ];
            }

            $nombreJurado = trim((string) ($evaluacion['jurado_nombre'] ?? ''));
            $evaluacion['jurado_display'] = $nombreJurado !== ''
                ? $nombreJurado
                : (string) ($evaluacion['jurado_usuario'] ?? 'Jurado');
            $evaluacion['detalles'] = $detallesPorEvaluacion[$evaluacionId] ?? [];

            $agrupados[$groupKey]['evaluaciones'][] = $evaluacion;
            $agrupados[$groupKey]['total_evaluaciones']++;
            $agrupados[$groupKey]['puntaje_acumulado'] += (float) ($evaluacion['puntaje_total'] ?? 0);
            $agrupados[$groupKey]['competidores'][(string) $evaluacion['competidor_numero'] . '|' . (string) $evaluacion['competidor_nombre']] = true;
            $agrupados[$groupKey]['jurados'][(string) $evaluacion['jurado_id']] = true;

            $competidorNumero = trim((string) ($evaluacion['competidor_numero'] ?? ''));
            if ($competidorNumero === '') {
                $competidorNumero = 'Sin numero';
            }

            if (!isset($agrupados[$groupKey]['competidores_detalle'][$competidorNumero])) {
                $agrupados[$groupKey]['competidores_detalle'][$competidorNumero] = [
                    'competidor_numero' => $competidorNumero,
                    'nombres' => [],
                    'evaluaciones' => [],
                    'puntaje_acumulado' => 0.0,
                    'total_evaluaciones' => 0,
                ];
            }

            $nombreCompetidor = trim((string) ($evaluacion['competidor_nombre'] ?? ''));
            if ($nombreCompetidor !== '') {
                $agrupados[$groupKey]['competidores_detalle'][$competidorNumero]['nombres'][$nombreCompetidor] = true;
            }

            $agrupados[$groupKey]['competidores_detalle'][$competidorNumero]['evaluaciones'][] = $evaluacion;
            $agrupados[$groupKey]['competidores_detalle'][$competidorNumero]['puntaje_acumulado'] += (float) ($evaluacion['puntaje_total'] ?? 0);
            $agrupados[$groupKey]['competidores_detalle'][$competidorNumero]['total_evaluaciones']++;

            foreach ($evaluacion['detalles'] as $detalle) {
                $criterioClave = (string) ($detalle['criterio_clave'] ?? '');
                if ($criterioClave === '') {
                    continue;
                }

                if (!isset($agrupados[$groupKey]['criterios'][$criterioClave])) {
                    $agrupados[$groupKey]['criterios'][$criterioClave] = [
                        'criterio_clave' => $criterioClave,
                        'criterio_nombre' => (string) ($detalle['criterio_nombre'] ?? ''),
                    ];
                }
            }
        }

        $resultado = [];
        foreach ($agrupados as $grupo) {
            $grupo['total_competidores'] = count($grupo['competidores']);
            $grupo['total_jurados'] = count($grupo['jurados']);
            $grupo['promedio_general'] = $grupo['total_evaluaciones'] > 0
                ? round($grupo['puntaje_acumulado'] / $grupo['total_evaluaciones'], 2)
                : 0.0;

            $competidoresOrdenados = array_values($grupo['competidores_detalle']);
            usort(
                $competidoresOrdenados,
                static function (array $a, array $b): int {
                    return strnatcasecmp((string) $a['competidor_numero'], (string) $b['competidor_numero']);
                }
            );

            foreach ($competidoresOrdenados as &$competidor) {
                $competidor['nombres'] = array_keys($competidor['nombres']);
                $competidor['nombre_mostrar'] = $competidor['nombres'][0] ?? 'Sin nombre';
                $competidor['promedio_general'] = $competidor['total_evaluaciones'] > 0
                    ? round($competidor['puntaje_acumulado'] / $competidor['total_evaluaciones'], 2)
                    : 0.0;

                usort(
                    $competidor['evaluaciones'],
                    static function (array $a, array $b): int {
                        return strcmp((string) ($a['creado_en'] ?? ''), (string) ($b['creado_en'] ?? ''));
                    }
                );
            }
            unset($competidor);

            $grupo['competidores_detalle'] = $competidoresOrdenados;
            $grupo['criterios'] = array_values($grupo['criterios']);

            unset(
                $grupo['competidores'],
                $grupo['jurados'],
                $grupo['puntaje_acumulado'],
                $grupo['evaluaciones']
            );
            $resultado[] = $grupo;
        }

        return $resultado;
    }

    /**
     * @param array<int, int> $ids
     * @return array<int, array<int, array<string, mixed>>>
     */
    private function obtenerDetallesPorEvaluacionIds(array $ids): array
    {
        if (!$ids) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare(
            "SELECT evaluacion_id, criterio_clave, criterio_nombre, puntaje_maximo, puntaje_otorgado
             FROM calificacion_evaluacion_detalles
             WHERE evaluacion_id IN ($placeholders)
             ORDER BY evaluacion_id ASC, id ASC"
        );
        $stmt->execute($ids);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $agrupados = [];
        foreach ($rows as $row) {
            $evaluacionId = (int) $row['evaluacion_id'];
            if (!isset($agrupados[$evaluacionId])) {
                $agrupados[$evaluacionId] = [];
            }
            $agrupados[$evaluacionId][] = $row;
        }

        return $agrupados;
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
}
