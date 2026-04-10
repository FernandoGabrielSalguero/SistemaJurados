📚 Estructura completa de la base de datos: u104036906_sistemaJurado
📄 Tabla: auth
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
usuario	varchar(120)	NO	UNI		
contrasena	varchar(255)	NO			
codigo_acceso	varchar(255)	NO			
codigo_acceso_visible	varchar(120)	YES			
rol	enum('impulsa_administrador','impulsa_jurado')	NO		impulsa_jurado	
acceso_habilitado	tinyint(1)	NO		1	
creado_en	timestamp	YES		current_timestamp()	

📄 Tabla: calificacion_evaluacion_detalles
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
evaluacion_id	int(10) unsigned	NO	MUL		
criterio_clave	varchar(80)	NO			
criterio_nombre	varchar(120)	NO			
puntaje_maximo	decimal(6,2)	NO			
puntaje_otorgado	decimal(6,2)	NO			

🔗 Relaciones:
Columna evaluacion_id referencia a calificacion_evaluaciones.id
📄 Tabla: calificacion_evaluaciones
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
formulario_id	int(10) unsigned	NO	MUL		
jurado_id	int(10) unsigned	NO	MUL		
competidor_numero	varchar(30)	NO	MUL		
competidor_nombre	varchar(180)	NO			
categoria	varchar(180)	NO	MUL		
evento_nombre	varchar(180)	NO	MUL		
puntaje_total	decimal(6,2)	NO			
promedio	decimal(6,2)	NO			
creado_en	timestamp	YES		current_timestamp()	

🔗 Relaciones:
Columna formulario_id referencia a calificacion_formularios.id
Columna jurado_id referencia a auth.id
📄 Tabla: calificacion_formulario_criterios
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
formulario_id	int(10) unsigned	NO	MUL		
criterio_clave	varchar(80)	NO			
criterio_nombre	varchar(120)	NO			
puntaje_maximo	int(10) unsigned	NO			
orden_visual	tinyint(3) unsigned	NO			

🔗 Relaciones:
Columna formulario_id referencia a calificacion_formularios.id
📄 Tabla: calificacion_formularios
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
nombre	varchar(180)	NO			
categoria	varchar(180)	NO	MUL		
evento_nombre	varchar(180)	NO	MUL		
activo	tinyint(1)	NO	MUL	1	
creado_por	int(10) unsigned	NO	MUL		
creado_en	timestamp	YES		current_timestamp()	
actualizado_en	timestamp	YES		current_timestamp()	on update current_timestamp()

🔗 Relaciones:
Columna creado_por referencia a auth.id
📄 Tabla: informacion_usuarios
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
user_auth_id	int(10) unsigned	NO	UNI		
nombre	varchar(150)	NO			
creado_en	timestamp	YES		current_timestamp()	
actualizado_en	timestamp	YES		current_timestamp()	on update current_timestamp()

🔗 Relaciones:
Columna user_auth_id referencia a auth.id