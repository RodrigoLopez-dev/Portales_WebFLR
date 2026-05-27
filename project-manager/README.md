# Gestor de Proyectos - PHP + MySQL

Sistema base en PHP para la gestión, administración y control de solicitudes de proyectos.

## Qué quedó corregido
- Routing completo con front controller en la raíz
- URLs limpias sin `/public`
- `.htaccess` listo para Apache con `mod_rewrite`
- Login funcional
- Dashboard conectado a MySQL
- Configuración preparada para ambiente productivo
- Puerto de base de datos corregido a `3306`

## URL esperada
Si instalas la carpeta como `htdocs/project-manager`, la URL será:

```text
http://localhost:8080/project-manager/
```

No debes entrar a `/public`.

## Requisitos
- PHP 8.1 o superior
- Apache con `mod_rewrite`
- MySQL 8 o MariaDB compatible
- Extensión PDO MySQL habilitada

## Instalación
1. Copia la carpeta `project-manager` al directorio de tu servidor web.
2. Crea la base de datos ejecutando:
   - `database/migrations/001_schema.sql`
   - `database/seeds/001_seed.sql`
3. Revisa `config/database.php` o define variables de entorno:
   - `DB_HOST`
   - `DB_PORT`
   - `DB_DATABASE`
   - `DB_USERNAME`
   - `DB_PASSWORD`
4. Asegúrate de que Apache permita `.htaccess` con `AllowOverride All`.
5. Reinicia Apache.

## Apache
Verifica en `httpd.conf`:

```apache
LoadModule rewrite_module modules/mod_rewrite.so
```

Y en el directorio del sitio:

```apache
AllowOverride All
```

## Usuario demo
- Correo: `admin@demo.cl`
- Clave: `Admin123*`

## Estructura
- `index.php`: front controller principal
- `.htaccess`: URLs limpias y carga de assets
- `public/`: recursos estáticos
- `bootstrap/app.php`: arranque del sistema
- `app/`: controladores, modelos, vistas y núcleo
- `database/`: scripts SQL

## Nota de producción
Para un despliegue real, además de este ajuste conviene:
- usar HTTPS
- mover credenciales a variables de entorno
- desactivar `APP_DEBUG`
- restringir permisos de escritura a `storage/`
