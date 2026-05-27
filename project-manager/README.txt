Archivos incluidos para mejoras del módulo Usuarios:

- config/routes.php
- app/Controllers/UserController.php
- app/Models/User.php
- app/Views/users/index.php

Mejoras aplicadas:
- filtros por nombre/email, rol, estado y acceso Google/Local
- avatar o inicial en la columna Usuario
- columna Acceso con badges Local / Google / Google + Local
- exportación CSV compatible con Excel
- modal de confirmación para activar/desactivar
- bloqueo para no desactivar tu propio usuario
- acción para desvincular Google cuando existe google_id
- bloqueo para no desvincular Google de la propia cuenta desde el listado
