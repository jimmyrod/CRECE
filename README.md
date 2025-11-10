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

- `APP_URL`: URL pública del portal (por ejemplo, `https://crece.ecuadorcrececontigo.org`).
- `APP_TIMEZONE`: zona horaria PHP (por defecto `America/Guayaquil`).
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`: credenciales MySQL.
- `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`: datos básicos del remitente utilizados al integrar un servicio de correo en el futuro.

## Creación de cuentas

1. Regístrate desde `/register`. Las cuentas quedan en estado `pending` hasta que un administrador las revise en el panel.
2. Ingresa con la cuenta administrativa creada por defecto al importar `db/schema_mysql.sql`:

   | Rol | Correo | Contraseña |
   | --- | --- | --- |
   | Administrador | `admin@crece.ecuadorcrececontigo.org` | `CreceAdmin2024!` |

   > **Importante:** cambia la contraseña en tu primer inicio de sesión. Puedes generar un nuevo hash con `php -r "echo password_hash('nueva', PASSWORD_BCRYPT);"` y actualizarlo en la base (`UPDATE users SET password_hash='<hash>' WHERE email='admin@crece.ecuadorcrececontigo.org';`) o desarrollar un formulario interno para realizar el cambio.

3. Desde el panel de administración podrás aprobar o rechazar nuevos usuarios, asignarles un rol (investigador interno/externo, revisor) y activar o suspender cuentas según corresponda.

## Panel administrativo

- `/admin/users`: listado de registros pendientes con controles para aprobar/rechazar y un historial de los últimos cambios.
- `/admin/requests`: bandeja para evaluar las solicitudes de descarga de datasets. Al aprobar una solicitud se registrará el acuerdo de uso y se habilitan las descargas para el solicitante.

## Despliegue en cPanel (PHP)

1. Sube el código al hosting (Git, FTP o administrador de archivos).
2. Si tu panel permite definir el directorio raíz del sitio, apunta a `public/`. Si trabajas dentro de un subdirectorio (por ejemplo `public_html/crece`), conserva el `.htaccess` incluido, que se encarga de redirigir todo hacia `public/index.php` y expone los activos desde `assets/`.
3. Crea la base de datos `crece_portal`, asigna el usuario `creceportaluser` con su contraseña y ejecuta `db/schema_mysql.sql` desde phpMyAdmin o la terminal.
4. En la sección **Setup Python/Setup Node.js** no es necesario configurar nada; basta con PHP.
5. En **Configuración de PHP** → **Variables de entorno** define `APP_URL`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD`.
   - Si publicas el portal en `https://crece.ecuadorcrececontigo.org/`, usa exactamente esa URL (sin barra final) para `APP_URL` y confirma que `RewriteBase` en `.htaccess` se mantiene como `/`.
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
