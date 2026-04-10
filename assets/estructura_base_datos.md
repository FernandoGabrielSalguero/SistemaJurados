📚 Estructura completa de la base de datos: u104036906_sistemaJurado
📄 Tabla: auth
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
usuario	varchar(120)	NO	UNI		
contrasena	varchar(255)	NO			
codigo_acceso	varchar(255)	NO			
rol	enum('impulsa_administrador','impulsa_jurado')	NO		impulsa_jurado	
acceso_habilitado	tinyint(1)	NO		1	
creado_en	timestamp	YES		current_timestamp()	
