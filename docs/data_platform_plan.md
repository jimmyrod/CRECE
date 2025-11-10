# Plataforma de Gestión de Investigaciones para Fundación Ecuador Crece Contigo

## 1. Contexto y Objetivo
La Fundación Ecuador Crece Contigo gestiona proyectos de investigación que generan conjuntos de datos en formatos CSV, DTA (Stata) y RDS (R). Actualmente desean un portal centralizado que permita:

- Recibir y organizar los archivos y metadatos asociados a cada investigación.
- Controlar el acceso a estos datos por parte de investigadores externos mediante un flujo de registro, solicitud y aprobación.
- Recabar información sobre el propósito del uso de los datos antes de autorizar descargas.

El objetivo es definir una plataforma segura, escalable y conforme con mejores prácticas de gobernanza de datos.

## 2. Perfiles de Usuario
- **Administrador de la Fundación**: carga investigaciones, gestiona catálogos, aprueba o rechaza solicitudes, asigna permisos.
- **Revisor de la Fundación** (opcional): evalúa solicitudes y asegura cumplimiento de políticas.
- **Investigador Externo**: se registra, consulta catálogo público y solicita acceso a conjuntos de datos específicos.
- **Investigador Interno** (opcional): miembro de la Fundación con acceso extendido sin proceso de aprobación.

## 3. Capacidades Clave
1. **Gestión de Investigaciones**
   - Carga de datasets en CSV, DTA, RDS con validación de formato.
   - Formulario de metadatos (título, resumen, palabras clave, responsables, año, variables, tamaño, restricciones legales).
   - Versionado y control de cambios.
2. **Catálogo Público**
   - Búsqueda y filtrado por categorías, región, año y palabras clave.
   - Ficha detallada con metadatos visibles y requisitos de acceso.
3. **Registro y Autenticación**
   - Autoregistro con verificación de correo.
   - Integración futura con ORCID o SSO institucional opcional.
4. **Flujo de Solicitud de Acceso**
   - Formulario donde el investigador describe objetivo, institución, metodología, plazo y protección de datos.
   - Estado de solicitud (en revisión, aprobada, rechazada, información adicional).
   - Generación de acuerdos de uso de datos (DUA) digitales.
5. **Aprobación y Auditoría**
   - Panel para revisores con historial y trazabilidad.
   - Notificaciones por correo para cambios de estado.
   - Registro de descargas con sello de tiempo y usuario.
6. **Descarga Segura**
   - Enlace temporal firmado con expiración configurable.
   - Limitación de número de descargas, marca de agua digital opcional.

## 4. Arquitectura Propuesta
### 4.1 Componentes Principales
- **Frontend Web**: React/Next.js o Vue con Tailwind para interfaz responsiva.
- **Backend API**: Node.js (NestJS) o Python (Django REST/FastAPI) según capacidades del equipo.
- **Base de Datos Relacional**: PostgreSQL para usuarios, solicitudes, metadatos y auditoría. Para despliegues en hosting compartido (p. ej. cPanel) es viable utilizar MySQL 8+ usando el esquema descrito en este repositorio.
- **Almacenamiento de Archivos**: Amazon S3, Google Cloud Storage o MinIO on-premise con cifrado en reposo.
- **Sistema de Colas (opcional)**: Para procesar cargas pesadas, validaciones y envíos de notificaciones (ej. AWS SQS, RabbitMQ).
- **Servicio de Correos**: SendGrid, Amazon SES o equivalente.

### 4.2 Modelo de Datos Inicial
- `users`: información del investigador/administrador, rol, institución.
- `datasets`: metadatos generales, estado de publicación, visibilidad.
- `dataset_versions`: referencia a archivos físicos, checksums, tamaño.
- `access_requests`: solicitud de acceso, finalidad declarada, estado, notas del revisor.
- `request_reviews`: historial de revisiones y decisiones.
- `downloads`: log de descargas para auditoría.

### 4.3 Seguridad y Cumplimiento
- Cifrado en tránsito (HTTPS) y en reposo (almacenamiento cifrado).
- Controles de autorización basados en roles (RBAC) y políticas por dataset.
- Auditoría completa y retención de logs.
- Políticas de retención de datos y eliminación segura.
- Cumplimiento con normativas de protección de datos locales (ej. Ley Orgánica de Protección de Datos Personales en Ecuador).

## 5. Flujo de Usuario Resumido
1. El administrador crea un proyecto y sube archivos con metadatos.
2. El investigador externo se registra, confirma su correo y completa su perfil.
3. Desde el catálogo, el investigador selecciona un dataset y llena el formulario de solicitud.
4. El revisor recibe notificación, analiza la solicitud y aprueba/rechaza (puede pedir más información).
5. Si se aprueba, el sistema genera un enlace de descarga y registra el evento.
6. El enlace expira y debe solicitarse nuevamente si se necesitan más descargas.

## 6. Validación y Calidad de Datos
- Conversión automática a formatos estándares adicionales (ej. Parquet) para análisis.
- Validación de esquemas y perfiles estadísticos mediante herramientas como Great Expectations o frictionless data.
- Escaneo de datos sensibles (PII) con reglas automáticas y flujo de anonimización si aplica.

## 7. Módulos Futuros
- Dashboards de uso (solicitudes por mes, descargas, áreas de investigación).
- API pública para consultar metadatos.
- Integración con repositorios científicos (Dataverse, CKAN) o DOI.
- Soporte para contratos e-learning sobre buenas prácticas de uso de datos.

## 8. Roadmap de Implementación (Alta Nivel)
1. **Fase 0 – Preparación**: Definir políticas de datos, matrices de clasificación y términos legales.
2. **Fase 1 – MVP (3 meses)**:
   - Autenticación básica, gestión de usuarios.
   - Carga de datasets y metadatos.
   - Catálogo público.
   - Flujo de solicitud y aprobación con notificaciones.
   - Descarga segura con enlaces temporales.
3. **Fase 2 – Robustecimiento (3-4 meses)**:
   - Validaciones automatizadas, auditoría avanzada.
   - Paneles de métricas y reportes.
   - Integración con ORCID/SSO.
   - Versionado avanzado y workflows de publicación.
4. **Fase 3 – Expansión**:
   - API externa, automatización de contratos, integraciones con repositorios internacionales.

## 9. Consideraciones Operativas
- Implementar infraestructura como código (Terraform) para reproducibilidad.
- Despliegues continuos (CI/CD) con pruebas automatizadas.
- Backup automatizado y planes de recuperación ante desastres.
- Capacitación al personal de la Fundación en uso de la plataforma y políticas de datos.

## 10. Próximos Pasos
1. Validar requisitos con stakeholders de la Fundación.
2. Priorizar funcionalidades del MVP y definir criterios de aceptación.
3. Elaborar prototipos de interfaz (wireframes) y flujos de usuario detallados.
4. Diseñar contratos de uso de datos y flujos legales.
5. Estimar recursos técnicos y presupuesto para implementación.

## 11. Guía de Despliegue en cPanel y Base de Datos MySQL
Para equipos que operarán la plataforma desde un hosting compartido con cPanel, se recomienda el siguiente flujo:

1. **Preparar la aplicación web**
   - Empaquetar el frontend (React/Vue) como assets estáticos listos para servir desde `public_html/portal` o similar.
   - Subir el backend (por ejemplo, una API en Laravel o Symfony) a un subdirectorio como `~/apps/datos-api` y configurar un dominio/aplicación PHP en cPanel que apunte al `public/` correspondiente.
   - Ajustar el archivo `.env` con las credenciales del servidor MySQL, claves SMTP y configuraciones de almacenamiento (puede utilizarse el propio hosting o servicios externos como AWS S3 mediante SDK).

2. **Crear la base de datos MySQL en cPanel**
   - Desde cPanel ir a **MySQL® Database Wizard**.
   - Crear una nueva base de datos (ej. `crece_portal`).
   - Crear un usuario MySQL con contraseña robusta y asignarle privilegios **ALL PRIVILEGES** sobre la base de datos recién creada.

3. **Importar el esquema**
   - Dentro de cPanel abrir **phpMyAdmin**.
   - Seleccionar la base de datos creada y usar la pestaña **Importar**.
   - Cargar el archivo [`db/schema_mysql.sql`](../db/schema_mysql.sql) incluido en este repositorio para crear tablas, llaves foráneas e índices.

4. **Configurar almacenamiento de archivos**
   - Para volúmenes pequeños se puede usar un directorio protegido con `.htaccess` fuera de `public_html` (ej. `~/data_files`).
   - Para un enfoque más robusto, configurar un bucket externo (S3/Wasabi) y guardar en la base de datos únicamente el URI seguro.

5. **Automatizar tareas recurrentes**
   - Utilizar **Cron Jobs** de cPanel para ejecutar comandos de mantenimiento (ej. `php artisan schedule:run`) cada minuto.
   - Configurar tareas adicionales para limpiar enlaces de descarga expirados y enviar recordatorios pendientes.

6. **Seguridad operativa**
   - Forzar HTTPS mediante el gestor de certificados de cPanel.
   - Activar registro detallado de accesos y monitorear el panel de métricas de cPanel para detectar anomalías.
   - Mantener respaldos automáticos de la base de datos (cPanel Backup Wizard o scripts personalizados que exporten el esquema y datos a diario).

Siguiendo estos pasos, la Fundación puede desplegar un MVP funcional en cPanel reutilizando el esquema MySQL publicado y migrando posteriormente a infraestructura dedicada si se requieren mayores niveles de escalabilidad.

---
Este documento sirve como base para alinear al equipo técnico y a la Fundación sobre la visión de la plataforma y sus componentes esenciales.
