# Portal de datos Fundación Ecuador Crece Contigo

Aplicación web escrita en PHP 8 que permite a la Fundación administrar su catálogo de investigaciones, subir nuevas versiones de datasets y gestionar las solicitudes de acceso por parte de investigadores externos.

## Requisitos

- PHP 8.1 o superior (probado con PHP 8.3 en cPanel)
- Extensión PDO con driver `pdo_mysql`
- Servidor MySQL 8.0 o compatible
- Acceso a la línea de comandos para ejecutar importaciones SQL

## Configuración local

1. Clona el repositorio e instala las dependencias del sistema (no se requiere Composer).
2. Copia `docs/example.env` como `.env` en la raíz del proyecto y ajusta los parámetros según tu entorno.
3. Importa el esquema de base de datos:

   ```bash
   mysql -u creceportaluser -p crece_portal < db/schema_mysql.sql
   ```

4. Levanta un servidor PHP de desarrollo apuntando al directorio `public/`:

   ```bash
   php -S 0.0.0.0:8000 -t public/
   ```

5. Abre `http://localhost:8000` en tu navegador.

> **Nota:** el servidor embebido de PHP entrega directamente los archivos estáticos del directorio `public/assets`.

## Variables de entorno

La aplicación lee las variables desde el entorno del servidor (puedes cargarlas mediante `.env` usando tu manejador favorito o configurándolas en cPanel):

- `APP_URL`: URL pública del portal (por ejemplo, `https://datos.fundacioncrececontigo.org`).
- `APP_TIMEZONE`: zona horaria PHP (por defecto `America/Guayaquil`).
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`: credenciales MySQL.
- `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`: datos básicos del remitente utilizados al integrar un servicio de correo en el futuro.

## Creación de cuentas

1. Regístrate desde `/register`. Las cuentas quedan en estado `pending` hasta que un administrador las active manualmente desde la base de datos (`UPDATE users SET status='active' WHERE email='correo@dominio';`).
2. Crea el usuario administrador insertando un registro con `role='administrator'` directamente en la tabla `users` o actualizando un usuario existente.

## Despliegue en cPanel (PHP)

1. Sube el código al hosting (Git, FTP o administrador de archivos).
2. En el administrador de dominios/subdominios, apunta el directorio raíz del sitio a `public/`.
3. Crea la base de datos `crece_portal`, asigna el usuario `creceportaluser` con su contraseña y ejecuta `db/schema_mysql.sql` desde phpMyAdmin o la terminal.
4. En la sección **Setup Python/Setup Node.js** no es necesario configurar nada; basta con PHP.
5. En **Configuración de PHP** → **Variables de entorno** define `APP_URL`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD`.
6. Asegúrate de que el directorio `storage/uploads` tenga permisos de escritura (755 o 775 según tu proveedor).

## Estructura destacada

- `public/index.php`: front controller con el enrutador mínimo y la definición de rutas.
- `src/Core`: inicialización de configuración y router ligero.
- `src/Controllers`: controladores para autenticación, datasets y panel administrativo.
- `src/Repositories`: consultas PDO alineadas al esquema MySQL.
- `src/Services/FileStorageService.php`: manejo de archivos subidos al directorio `storage/uploads`.
- `views/`: plantillas PHP con Bootstrap 5.
- `db/schema_mysql.sql`: definición completa de tablas e índices recomendados.

## Pruebas

Actualmente no se incluyen pruebas automatizadas. Se recomienda añadir pruebas end-to-end (por ejemplo con Pest/PHPUnit) para validar los flujos críticos antes de llegar a producción.
