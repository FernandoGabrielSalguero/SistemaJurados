## Base de datos actual

Base: `u104036906_sistemaJurado`

### Tabla `auth`

| Columna | Tipo | Nulo | Clave | Default | Extra |
|---|---|---|---|---|---|
| id | int(10) unsigned | NO | PRI |  | auto_increment |
| usuario | varchar(120) | NO | UNI |  |  |
| contrasena | varchar(255) | NO |  |  |  |
| codigo_acceso | varchar(255) | NO |  |  |  |
| codigo_acceso_visible | varchar(120) | YES |  |  |  |
| rol | enum('impulsa_administrador','impulsa_jurado') | NO |  | impulsa_jurado |  |
| acceso_habilitado | tinyint(1) | NO |  | 1 |  |
| creado_en | timestamp | YES |  | current_timestamp() |  |

### Tabla `informacion_usuarios`

| Columna | Tipo | Nulo | Clave | Default | Extra |
|---|---|---|---|---|---|
| id | int(10) unsigned | NO | PRI |  | auto_increment |
| user_auth_id | int(10) unsigned | NO | UNI |  |  |
| nombre | varchar(150) | NO |  |  |  |
| creado_en | timestamp | YES |  | current_timestamp() |  |
| actualizado_en | timestamp | YES |  | current_timestamp() | on update current_timestamp() |

Relacion:
- `informacion_usuarios.user_auth_id` referencia a `auth.id`

---

## SQL nuevo para modulo de calificaciones

Este bloque deja preparada la persistencia para:
- formularios creados por administracion
- criterios fijos con puntajes configurables
- evaluaciones hechas por jurados
- detalle por criterio
- ranking y consultas historicas futuras

```sql
CREATE TABLE IF NOT EXISTS calificacion_formularios (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(180) NOT NULL,
    categoria VARCHAR(180) NOT NULL,
    evento_nombre VARCHAR(180) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_por INT UNSIGNED NOT NULL,
    creado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_calificacion_formularios_categoria (categoria),
    KEY idx_calificacion_formularios_evento (evento_nombre),
    KEY idx_calificacion_formularios_activo (activo),
    CONSTRAINT fk_calificacion_formularios_creado_por
        FOREIGN KEY (creado_por) REFERENCES auth(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS calificacion_formulario_criterios (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    formulario_id INT UNSIGNED NOT NULL,
    criterio_clave VARCHAR(80) NOT NULL,
    criterio_nombre VARCHAR(120) NOT NULL,
    puntaje_maximo INT UNSIGNED NOT NULL,
    orden_visual TINYINT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_formulario_criterio (formulario_id, criterio_clave),
    KEY idx_formulario_criterios_formulario (formulario_id),
    CONSTRAINT fk_formulario_criterios_formulario
        FOREIGN KEY (formulario_id) REFERENCES calificacion_formularios(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS calificacion_evaluaciones (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    formulario_id INT UNSIGNED NOT NULL,
    jurado_id INT UNSIGNED NOT NULL,
    competidor_numero VARCHAR(30) NOT NULL,
    competidor_nombre VARCHAR(180) NOT NULL,
    categoria VARCHAR(180) NOT NULL,
    evento_nombre VARCHAR(180) NOT NULL,
    puntaje_total DECIMAL(6,2) NOT NULL,
    promedio DECIMAL(6,2) NOT NULL,
    creado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_calificacion_evaluaciones_formulario (formulario_id),
    KEY idx_calificacion_evaluaciones_jurado (jurado_id),
    KEY idx_calificacion_evaluaciones_categoria (categoria),
    KEY idx_calificacion_evaluaciones_evento (evento_nombre),
    KEY idx_calificacion_evaluaciones_competidor (competidor_numero, competidor_nombre),
    CONSTRAINT fk_calificacion_evaluaciones_formulario
        FOREIGN KEY (formulario_id) REFERENCES calificacion_formularios(id),
    CONSTRAINT fk_calificacion_evaluaciones_jurado
        FOREIGN KEY (jurado_id) REFERENCES auth(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS calificacion_evaluacion_detalles (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    evaluacion_id INT UNSIGNED NOT NULL,
    criterio_clave VARCHAR(80) NOT NULL,
    criterio_nombre VARCHAR(120) NOT NULL,
    puntaje_maximo DECIMAL(6,2) NOT NULL,
    puntaje_otorgado DECIMAL(6,2) NOT NULL,
    PRIMARY KEY (id),
    KEY idx_calificacion_detalles_evaluacion (evaluacion_id),
    CONSTRAINT fk_calificacion_detalles_evaluacion
        FOREIGN KEY (evaluacion_id) REFERENCES calificacion_evaluaciones(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Criterios fijos del formulario

Siempre deben existir estos 7 criterios:
- Tiempo
- Musicalidad
- Tecnica
- Dificultad
- Sincronizacion
- Coreografia
- Talento en el show

Regla:
- la suma de `puntaje_maximo` de los 7 criterios debe ser exactamente `100`

---

## Flujo previsto

### 1. Administrador

Guarda un formulario con:
- nombre del formulario
- categoria
- nombre del evento
- activo o inactivo
- puntaje maximo por criterio

### 2. Jurado

Completa una evaluacion con:
- formulario seleccionado
- numero del competidor
- nombre del competidor
- puntaje otorgado por cada criterio

### 3. Resultado guardado

Cada voto debe conservar:
- que juez voto
- que formulario uso
- evento
- categoria
- competidor
- detalle por criterio
- puntaje total
- promedio

---

## Consultas futuras habilitadas por este esquema

Con estas tablas se puede construir despues:
- ranking por categoria
- promedio general por competidor
- historial completo por evento
- detalle por jurado
- visualizacion en tiempo real desde otro perfil
