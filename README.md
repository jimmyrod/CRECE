# Portal de datos Fundación Ecuador Crece Contigo

Aplicación web construida con Flask para gestionar el catálogo de datasets de investigación de la Fundación Ecuador Crece Contigo. Permite que el equipo interno cargue nuevos estudios y que investigadores externos soliciten acceso a los archivos.

## Requisitos

- Python 3.10+
- Servidor MySQL 8 o compatible
- Herramientas de compilación para instalar dependencias de `pip`

## Configuración local

1. Crea un entorno virtual e instala las dependencias:

   ```bash
   python -m venv .venv
   source .venv/bin/activate
   pip install -r requirements.txt
   ```

2. Copia el archivo `docs/example.env` como `.env` y ajusta las variables necesarias. El proyecto utiliza la variable `DATABASE_URL`, por ejemplo:

   ```env
   FLASK_APP=wsgi.py
   FLASK_ENV=development
   SECRET_KEY=una-clave-segura
   DATABASE_URL=mysql+pymysql://creceportaluser:Crece2k!!!@localhost:3306/crece_portal
   UPLOAD_FOLDER=/ruta/absoluta/a/uploads
   ```

3. Inicializa la base de datos utilizando el esquema provisto en `db/schema_mysql.sql`:

   ```bash
   mysql -u creceportaluser -p crece_portal < db/schema_mysql.sql
   ```

4. Ejecuta la aplicación:

   ```bash
   flask --app wsgi.py run
   ```

## Crear un administrador

Con el entorno virtual activo, ejecuta el comando personalizado para crear un usuario administrador:

```bash
flask --app wsgi.py create-admin admin@fundacioncrece.org --first-name "Nombre" --last-name "Apellido"
```

## Despliegue en cPanel

- Sube el código del repositorio a tu cuenta (por Git o archivo comprimido).
- Crea un entorno Python en cPanel e instala los requisitos con `pip install -r requirements.txt`.
- Configura una aplicación Python (WSGI) apuntando a `wsgi.py`.
- Define las variables de entorno (`SECRET_KEY`, `DATABASE_URL`, `UPLOAD_FOLDER`) en la interfaz de cPanel.
- Asegúrate de que la ruta de `UPLOAD_FOLDER` sea escribible por el proceso web.

## Estructura destacada

- `app/models.py`: modelos SQLAlchemy alineados al esquema MySQL existente.
- `app/blueprints/`: vistas para autenticación, catálogo y panel administrativo.
- `app/templates/`: plantillas HTML con Bootstrap 5.
- `app/utils/`: utilidades para manejo de archivos y permisos de acceso.
- `db/schema_mysql.sql`: definición de tablas de base de datos.

## Pruebas

Actualmente no se incluyen pruebas automatizadas. Se recomienda añadir pruebas unitarias para reglas de acceso y flujos críticos antes de ir a producción.
