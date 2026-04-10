📚 Estructura completa de la base de datos: u104036906_gestionImpulsa
📄 Tabla: contacto_landing
Columna	Tipo	Nulo	Clave	Default	Extra
id	bigint(20) unsigned	NO	PRI		auto_increment
nombre	varchar(150)	NO			
empresa	varchar(150)	NO			
email	varchar(190)	NO	MUL		
telefono	varchar(80)	NO			
equipo	varchar(80)	YES			
objetivo	varchar(120)	YES			
mensaje	text	YES			
form_source	varchar(100)	NO		landing-impulsa	
ip_address	varchar(45)	YES			
user_agent	varchar(255)	YES			
created_at	timestamp	NO	MUL	current_timestamp()	

📄 Tabla: correos_log
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
user_auth_id	int(10) unsigned	YES	MUL		
correo	varchar(255)	NO			
asunto	varchar(255)	NO			
template	varchar(100)	YES			
mensaje_html	longtext	YES			
mensaje_text	text	YES			
estado	enum('enviado','fallido')	NO	MUL	fallido	
error	text	YES			
meta	longtext	YES			
created_at	timestamp	NO		current_timestamp()	

📄 Tabla: emprendedor_buyer_persona
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
user_auth_id	int(10) unsigned	NO	UNI		
cliente_ideal	text	NO			
edad_etapa_vida	text	NO			
ocupacion_realidad_diaria	text	NO			
problema_necesidad	text	NO			
preocupacion_frustracion	text	NO			
objetivo_mejora	text	NO			
motivacion_busqueda	text	NO			
freno_dudas	text	NO			
criterio_eleccion	text	NO			
busqueda_informacion	text	NO			
decision_compra	text	NO			
motivo_eleccion	text	NO			
buyer_persona_estructura	longtext	NO			
completado	tinyint(1)	NO		0	
created_at	timestamp	YES		current_timestamp()	
updated_at	timestamp	YES		current_timestamp()	on update current_timestamp()

🔗 Relaciones:
Columna user_auth_id referencia a user_auth.id
📄 Tabla: emprendedor_mision
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
user_auth_id	int(10) unsigned	NO	UNI		
a_quien_ayudo	varchar(255)	NO			
que_problema_resuelvo	text	NO			
como_lo_resuelvo	text	NO			
mision_estructura	text	NO			
completado	tinyint(1)	NO		0	
created_at	timestamp	YES		current_timestamp()	
updated_at	timestamp	YES		current_timestamp()	on update current_timestamp()

🔗 Relaciones:
Columna user_auth_id referencia a user_auth.id
📄 Tabla: emprendedor_vision
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
user_auth_id	int(10) unsigned	NO	UNI		
conversion_futura	text	NO			
lugar_mercado	text	NO			
impacto_generado	text	NO			
vision_estructura	text	NO			
completado	tinyint(1)	NO		0	
created_at	timestamp	YES		current_timestamp()	
updated_at	timestamp	YES		current_timestamp()	on update current_timestamp()

🔗 Relaciones:
Columna user_auth_id referencia a user_auth.id
📄 Tabla: landing_page_request
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
user_auth_id	int(10) unsigned	NO	UNI		
nombre_emprendimiento	varchar(255)	NO			
fecha_inicio	date	NO			
descripcion	text	NO			
dominio_registrado	tinyint(1)	NO		0	
hosting_propio	tinyint(1)	NO		0	
cantidad_colaboradores	int(10) unsigned	NO		1	
nombre_fundador	varchar(255)	NO			
vende_productos	tinyint(1)	NO		0	
vende_servicios	tinyint(1)	NO		0	
ya_factura	tinyint(1)	NO		0	
espacio_fisico	tinyint(1)	NO		0	
rubro_categoria_id	int(10) unsigned	YES	MUL		
rubro_subcategoria_id	int(10) unsigned	YES	MUL		
pais	varchar(100)	YES			
provincia	varchar(100)	YES			
localidad	varchar(100)	YES			
calle	varchar(255)	YES			
numero	varchar(20)	YES			
telefono_contacto	varchar(30)	NO			
completado	tinyint(1)	NO		0	
created_at	timestamp	YES		current_timestamp()	
updated_at	timestamp	YES		current_timestamp()	on update current_timestamp()

🔗 Relaciones:
Columna rubro_categoria_id referencia a rubro_emprendedor_categoria.id
Columna rubro_subcategoria_id referencia a rubro_emprendedor_subcategoria.id
Columna user_auth_id referencia a user_auth.id
📄 Tabla: rubro_emprendedor_categoria
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
nombre	varchar(150)	NO	UNI		
descripcion	text	YES			

📄 Tabla: rubro_emprendedor_relaciones
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
categoria_id	int(10) unsigned	NO	MUL		
subcategoria_id	int(10) unsigned	NO	MUL		

🔗 Relaciones:
Columna categoria_id referencia a rubro_emprendedor_categoria.id
Columna subcategoria_id referencia a rubro_emprendedor_subcategoria.id
📄 Tabla: rubro_emprendedor_subcategoria
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
nombre	varchar(150)	NO	UNI		
descripcion	text	YES			

📄 Tabla: user_auth
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
correo	varchar(255)	NO	UNI		
password	varchar(255)	NO			
rol	enum('impulsa_administrador','impulsa_emprendedor')	NO		impulsa_emprendedor	
verification_token	varchar(100)	YES			
email_verified_at	timestamp	YES			
created_at	timestamp	NO		current_timestamp()	
updated_at	timestamp	NO		current_timestamp()	on update current_timestamp()

📄 Tabla: user_contacto
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
user_auth_id	int(10) unsigned	NO	UNI		
correo	varchar(255)	NO			
check_correo	tinyint(1)	NO		0	
permison_correo	tinyint(1)	NO		1	
whatsapp	varchar(30)	YES			
check_whatsapp	tinyint(1)	NO		0	
permison_whatsapp	tinyint(1)	NO		1	
created_at	timestamp	NO		current_timestamp()	
updated_at	timestamp	NO		current_timestamp()	on update current_timestamp()

🔗 Relaciones:
Columna user_auth_id referencia a user_auth.id
📄 Tabla: user_info
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
user_auth_id	int(10) unsigned	NO	UNI		
nombre	varchar(100)	YES			
apellido	varchar(100)	YES			
apodo	varchar(100)	YES			
avatar_path	varchar(255)	YES			
fecha_nacimiento	date	YES			
created_at	timestamp	NO		current_timestamp()	
updated_at	timestamp	NO		current_timestamp()	on update current_timestamp()

🔗 Relaciones:
Columna user_auth_id referencia a user_auth.id