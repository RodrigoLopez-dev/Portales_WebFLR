<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db_admin.php';

require_login();

$currentPage = 'usuarios';

$pdo = admin_db();

$stmt = $pdo->query("
  SELECT id, mail, name, lastname, picture, cod_privilegio, estado, created, updated
  FROM usuarios
  ORDER BY created DESC
");

$usuarios = $stmt->fetchAll();
$csrf = csrf_token();

require __DIR__ . '/partials/header.php';
?>

<div class="pageHead">
    <div>
        <h1>Usuarios</h1>
        <div class="pageSub">Gestión de acceso al panel admin</div>
    </div>
</div>

<?php if (!empty($_SESSION['flash_ok'])): ?>
    <div class="flash ok">
        <?= htmlspecialchars($_SESSION['flash_ok'], ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php unset($_SESSION['flash_ok']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="flash err">
        <?= htmlspecialchars($_SESSION['flash_error'], ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<section class="formCard">
    <div class="formHd">
        <strong>Listado de usuarios</strong>
    </div>

    <div class="formBd">
        <table class="usersTable">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Privilegio</th>
                    <th>Estado</th>
                    <th>Creado</th>
                    <th>Actualizado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                    <?php
                    $fullName = trim(($u['name'] ? $u['name'] : '') . ' ' . ($u['lastname'] ? $u['lastname'] : ''));
                    $displayName = $fullName !== '' ? $fullName : $u['mail'];
                    $isAdmin = (int) $u['cod_privilegio'] > 0;
                    $isActive = (int) $u['estado'] === 1;
                    ?>
                    <tr>
                        <td>
                            <div class="userCell">
                                <?php if (!empty($u['picture'])): ?>
                                    <img class="avatar" src="<?= htmlspecialchars($u['picture'], ENT_QUOTES, 'UTF-8') ?>"
                                        alt="avatar">
                                <?php else: ?>
                                    <div class="avatar"></div>
                                <?php endif; ?>

                                <div class="nameBlock">
                                    <strong><?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?></strong>
                                    <span><?= htmlspecialchars($u['mail'], ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                            </div>
                        </td>

                        <td>
                            <?php if ($isAdmin): ?>
                                <span class="badge ok">Administrador</span>
                            <?php else: ?>
                                <span class="badge warn">Sin acceso</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php if ($isActive): ?>
                                <span class="badge ok">Activo</span>
                            <?php else: ?>
                                <span class="badge off">Desactivado</span>
                            <?php endif; ?>
                        </td>

                        <td><?= htmlspecialchars($u['created'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($u['updated'] ? $u['updated'] : '-', ENT_QUOTES, 'UTF-8') ?></td>

                        <td>
                            <div class="actionsWrap">
                                <?php if ($isAdmin): ?>
                                    <form class="inlineForm" method="post" action="usuarios_actions.php"
                                        onsubmit="return confirm('¿Quitar privilegio de administrador a este usuario?');">
                                        <input type="hidden" name="csrf"
                                            value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="action" value="revoke_admin">
                                        <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                                        <button type="submit" class="btnMini warn">Quitar admin</button>
                                    </form>
                                <?php else: ?>
                                    <form class="inlineForm" method="post" action="usuarios_actions.php">
                                        <input type="hidden" name="csrf"
                                            value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="action" value="grant_admin">
                                        <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                                        <button type="submit" class="btnMini primary">Dar admin</button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($isActive): ?>
                                    <form class="inlineForm" method="post" action="usuarios_actions.php"
                                        onsubmit="return confirm('¿Desactivar este usuario?');">
                                        <input type="hidden" name="csrf"
                                            value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="action" value="deactivate">
                                        <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                                        <button type="submit" class="btnMini danger">Desactivar</button>
                                    </form>
                                <?php else: ?>
                                    <form class="inlineForm" method="post" action="usuarios_actions.php">
                                        <input type="hidden" name="csrf"
                                            value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="action" value="activate">
                                        <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                                        <button type="submit" class="btnMini">Activar</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$usuarios): ?>
                    <tr>
                        <td colspan="6">No hay usuarios registrados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>