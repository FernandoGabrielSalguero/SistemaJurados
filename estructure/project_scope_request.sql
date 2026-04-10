-- Nueva tabla para almacenar respuestas del formulario publico de proyectos de software
-- Base esperada: u104036906_gestionImpulsa

CREATE TABLE IF NOT EXISTS project_scope_request (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    -- Identificacion
    nombre VARCHAR(150) NOT NULL,
    nombre_proyecto VARCHAR(180) NOT NULL,
    correo VARCHAR(190) NOT NULL,
    whatsapp VARCHAR(80) NOT NULL,

    -- 1. Sobre el proyecto
    q1_descripcion TEXT NOT NULL,
    q2_problema TEXT NOT NULL,
    q3_usuarios TEXT NOT NULL,
    q4_resultado_ideal TEXT NOT NULL,

    -- 2. Tipo de aplicacion
    q5_tipo_aplicacion ENUM(
        'pagina_web',
        'sistema_web_interno',
        'app_mobile',
        'plataforma_web_app_mobile',
        'no_se'
    ) NOT NULL,
    q6_login ENUM('si', 'no', 'no_se') NOT NULL,
    q7_acceso ENUM(
        'solo_equipo',
        'clientes',
        'proveedores',
        'roles_diferentes',
        'no_se'
    ) NOT NULL,

    -- 3. Funcionalidades
    q8_funciones_minimas TEXT NOT NULL,
    q9_funcionalidades JSON NULL,
    q10_admin_vs_usuario TEXT NULL,
    q11_integraciones TEXT NULL,

    -- 4. Operacion y contenido
    q12_contenido ENUM('claro', 'medio', 'no') NOT NULL,
    q13_referencias TEXT NULL,
    q14_diseno ENUM('completa', 'parcial', 'no') NOT NULL,

    -- 5. Prioridad y decision
    q15_urgencia ENUM('cuanto_antes', '1_2_meses', 'mas_adelante', 'explorando') NOT NULL,
    q16_presupuesto ENUM('sin_definir', 'menos_1000000', 'entre_1000000_2000000', 'mas_2000000') NOT NULL,
    q17_modalidad ENUM('por_etapas', 'todo_junto', 'necesito_recomendacion') NOT NULL,
    q18_adicional TEXT NULL,

    -- Metadata
    form_source VARCHAR(100) NOT NULL DEFAULT 'public-new-project',
    estado ENUM('nuevo', 'revisado', 'descartado') NOT NULL DEFAULT 'nuevo',
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    KEY idx_project_scope_correo (correo),
    KEY idx_project_scope_estado (estado),
    KEY idx_project_scope_created_at (created_at),
    KEY idx_project_scope_urgencia (q15_urgencia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
