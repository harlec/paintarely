# Paintarely

App web para aprender a dibujar: plantillas con trazo guiado, coloreado por zonas, grupos, votación y ranking con monedas virtuales. Ver [PLAN_PROYECTO_APP_DIBUJO.md](PLAN_PROYECTO_APP_DIBUJO.md) para el plan completo.

Stack: PHP 8.x (sin framework, autoloader propio) + MySQL/MariaDB + HTML5 Canvas/SVG. Pensado para desplegarse en un panel **Plesk sobre LAMP**.

## Requisitos locales

- PHP >= 8.1 con extensiones `pdo_mysql` y `fileinfo`
- MySQL o MariaDB
- Apache con `mod_rewrite` (o el servidor embebido de PHP para desarrollo)

## Puesta en marcha local

```bash
cp .env.example .env        # edita credenciales de BD
mysql -u root -p tu_bd < database/schema.sql
mysql -u root -p tu_bd < database/seed.sql   # datos de ejemplo (plantilla "Estrella")

php -S 127.0.0.1:8000 -t public
```

Abre `http://127.0.0.1:8000`.

## Estructura

Ver sección 4 de [PLAN_PROYECTO_APP_DIBUJO.md](PLAN_PROYECTO_APP_DIBUJO.md). El **document root es `public/`**; todo lo demás (`src/`, `database/`, `.env`) queda fuera de la carpeta pública.

## Despliegue en Plesk (LAMP)

1. **Dominio/subdominio**: crea el dominio en Plesk apuntando a este repo (Git remoto en Plesk, o sube por SFTP/File Manager).
2. **Document root**: en *Hosting Settings* del dominio, cambia el *Document root* a `public` (no la raíz del proyecto). Así `src/`, `database/` y `.env` quedan fuera del alcance web aunque estén en el mismo hosting.
3. **PHP**: en *PHP Settings* del dominio, usa PHP 8.1+ y confirma que las extensiones `pdo_mysql` y `fileinfo` estén activas (vienen por defecto en la mayoría de imágenes Plesk).
4. **Apache / mod_rewrite**: Plesk (Apache) trae `mod_rewrite` habilitado por defecto; el `.htaccess` en `public/` ya enruta todo a `index.php`. Si usas Nginx como proxy delante de Apache (configuración típica de Plesk), no requiere cambios adicionales para PHP servido por Apache.
5. **Base de datos**: crea la base de datos y un usuario dedicado desde *Databases* en Plesk. Importa `database/schema.sql` (y opcionalmente `database/seed.sql`) usando phpMyAdmin (enlace directo desde la ficha de la BD en Plesk) o por SSH con `mysql`.
6. **Variables de entorno**: sube un `.env` (basado en `.env.example`) a la raíz del proyecto, **un nivel arriba de `public/`**, con las credenciales reales de la BD de Plesk. No lo subas al repo (ya está en `.gitignore`).
7. **Permisos de escritura**: `public/uploads/dibujos_usuarios/` debe ser escribible por el usuario del sistema de la suscripción Plesk (normalmente basta con 755/775; Plesk ya ejecuta PHP-FPM/Apache como el usuario del hosting, así que no se necesita `chmod 777`).
8. **HTTPS**: activa el certificado Let's Encrypt gratuito desde *SSL/TLS Certificates* en Plesk y fuerza redirección a HTTPS.
9. **Despliegues posteriores**: si usas la integración Git de Plesk, cada `git push` puede auto-desplegar (configurable en *Git* del dominio); si no, sube los archivos cambiados por SFTP y vuelve a ejecutar migraciones de `database/` manualmente si hay cambios de esquema.

## Roadmap

El desarrollo avanza fase por fase según la sección 8 del plan. Estado actual: **Fase 0 completa, Fase 1 (núcleo de dibujo) funcional** — galería de plantillas, canvas con trazo guía, paleta de colores por zonas SVG y guardado del PNG final en `uploads/` (sin login/grupos todavía).
