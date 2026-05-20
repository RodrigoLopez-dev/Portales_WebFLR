<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$pageTitle = 'Configuración Inicio';
$currentPage = 'configuracion';

function h($value)
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-panel">

    <nav class="navbar navbar-expand-lg navbar-transparent navbar-absolute fixed-top">
        <div class="container-fluid">
            <div class="navbar-wrapper">
                <a class="navbar-brand" href="#">Configuración Inicio</a>
            </div>
        </div>
    </nav>

    <div class="content">
        <div class="container-fluid">

            <div class="card">
                <div class="card-header card-header-primary">
                    <h4 class="card-title">Parámetros del sistema</h4>
                    <p class="card-category">
                        Administra valores generales del dashboard
                    </p>
                </div>

                <div class="card-body">
                    <?php
                    function getPortalConfig($db, $key, $default)
                    {
                        $value = null;
                        
                        $stmt = $db->prepare("SELECT config_value FROM portal_config WHERE config_key = ? LIMIT 1");
                        $stmt->bind_param("s", $key);
                        $stmt->execute();
                        $stmt->bind_result($value);

                        if ($stmt->fetch()) {
                            $stmt->close();
                            return $value;
                        }

                        $stmt->close();
                        return $default;
                    }

                    $indexImage = getPortalConfig($db, 'index_image', 'imagen/imgMayo.jpg');
                    $imagenesDisponibles = array();

                    $uploadDir = __DIR__ . '/../../uploads/config/';
                    $uploadUrl = '../../uploads/config/';

                    if (is_dir($uploadDir)) {
                        $files = scandir($uploadDir);

                        foreach ($files as $file) {
                            if ($file == '.' || $file == '..') {
                                continue;
                            }

                            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                            if (in_array($ext, array('jpg', 'jpeg', 'png', 'webp'))) {
                                $imagenesDisponibles[] = 'uploads/config/' . $file;
                            }
                        }
                    }
                    $amount1 = getPortalConfig($db, 'donation_amount_1', '25000');
                    $amount2 = getPortalConfig($db, 'donation_amount_2', '50000');
                    $amount3 = getPortalConfig($db, 'donation_amount_3', '75000');
                    $amount4 = getPortalConfig($db, 'donation_amount_4', '100000');

                    $text1 = getPortalConfig($db, 'donation_text_1', '= 1 mt²');
                    $text2 = getPortalConfig($db, 'donation_text_2', '= 2 mt²');
                    $text3 = getPortalConfig($db, 'donation_text_3', '= 3 mt²');
                    $text4 = getPortalConfig($db, 'donation_text_4', '= 4 mt²');

                    $textEnabled1 = getPortalConfig($db, 'donation_text_enabled_1', '1');
                    $textEnabled2 = getPortalConfig($db, 'donation_text_enabled_2', '1');
                    $textEnabled3 = getPortalConfig($db, 'donation_text_enabled_3', '1');
                    $textEnabled4 = getPortalConfig($db, 'donation_text_enabled_4', '1');

                    if (empty($_SESSION['csrf_token'])) {
                        $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
                    }

                    $csrfToken = $_SESSION['csrf_token'];
                    ?>

                    <?php if (isset($_GET['ok']) && $_GET['ok'] == 'saved'): ?>
                        <div class="alert alert-success">
                            Configuración guardada correctamente.
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger">
                            No se pudo guardar la configuración.
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="configuracion_save.php" enctype="multipart/form-data">

                        <input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>">

                        <h4>Imagen principal del index</h4>

                        <div class="form-group">
                            <label>Seleccionar imagen</label>

                            <select name="image_mode" id="image_mode" class="form-control">
                                <option value="upload">Subir nueva imagen</option>

                                <?php foreach ($imagenesDisponibles as $img): ?>
                                    <option value="<?php echo h($img); ?>" <?php echo ($img == $indexImage ? 'selected' : ''); ?>>
                                        <?php echo h(basename($img)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group" id="uploadImageBox" style="display:none;">
                            <label>Nueva imagen</label><br>

                            <input type="file" name="index_image" id="index_image"
                                accept="image/jpeg,image/png,image/webp" style="display:none;">

                            <label for="index_image" class="btn btn-primary btn-sm">
                                Buscar imagen
                            </label>

                            <span id="fileName" style="margin-left:10px;">
                                Ningún archivo seleccionado
                            </span>

                            <br>
                            <small>Formatos permitidos: JPG, PNG, WEBP. Tamaño máximo recomendado: 2MB.</small>
                        </div>

                        <div class="form-group">
                            <label>Vista previa</label><br>

                            <img id="imagePreview" src="" style="max-width: 300px; border-radius: 8px; display:block;">
                        </div>

                        <hr>

                        <h4>Valores de botones de donación</h4>

                        <div class="form-group">
                            <div class="form-group">
                                <label>Botón 1</label>
                                <input type="number" name="donation_amount_1" class="form-control"
                                    value="<?php echo h($amount1); ?>">
                            </div>

                            <div class="form-group">
                                <label>Texto secundario botón 1</label>
                                <input type="text"
                                    name="donation_text_1"
                                    class="form-control"
                                    value="<?php echo h($text1); ?>">
                            </div>

                            <div class="checkbox">
                                <label>
                                    <input type="checkbox"
                                        name="donation_text_enabled_1"
                                        value="1"
                                        <?php echo ($textEnabled1 == '1' ? 'checked' : ''); ?>>
                                    Mostrar texto secundario botón 1
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-group">
                                <label>Botón 2</label>
                                <input type="number" name="donation_amount_2" class="form-control"
                                    value="<?php echo h($amount2); ?>">
                            </div>

                            <div class="form-group">
                                <label>Texto secundario botón 2</label>
                                <input type="text"
                                    name="donation_text_2"
                                    class="form-control"
                                    value="<?php echo h($text2); ?>">
                            </div>

                            <div class="checkbox">
                                <label>
                                    <input type="checkbox"
                                        name="donation_text_enabled_2"
                                        value="1"
                                        <?php echo ($textEnabled2 == '1' ? 'checked' : ''); ?>>
                                    Mostrar texto secundario botón 2
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-group">
                                <label>Botón 3</label>
                                <input type="number" name="donation_amount_3" class="form-control"
                                    value="<?php echo h($amount3); ?>">
                            </div>

                            <div class="form-group">
                                <label>Texto secundario botón 3</label>
                                <input type="text"
                                    name="donation_text_3"
                                    class="form-control"
                                    value="<?php echo h($text3); ?>">
                            </div>

                            <div class="checkbox">
                                <label>
                                    <input type="checkbox"
                                        name="donation_text_enabled_3"
                                        value="1"
                                        <?php echo ($textEnabled3 == '1' ? 'checked' : ''); ?>>
                                    Mostrar texto secundario botón 3
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-group">
                                <label>Botón 4</label>
                                <input type="number" name="donation_amount_4" class="form-control"
                                    value="<?php echo h($amount4); ?>">
                            </div>

                            <div class="form-group">
                                <label>Texto secundario botón 4</label>
                                <input type="text"
                                    name="donation_text_4"
                                    class="form-control"
                                    value="<?php echo h($text4); ?>">
                            </div>

                            <div class="checkbox">
                                <label>
                                    <input type="checkbox"
                                        name="donation_text_enabled_4"
                                        value="1"
                                        <?php echo ($textEnabled4 == '1' ? 'checked' : ''); ?>>
                                    Mostrar texto secundario botón 4
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success">
                            Guardar configuración
                        </button>

                        <a href="configuracion.php" class="btn btn-default">
                            Volver
                        </a>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var preview = document.getElementById('imagePreview');
    var uploadBox = document.getElementById('uploadImageBox');
    var select = document.getElementById('image_mode');

    function updateImageMode() {
        if (select.value === 'upload') {
            uploadBox.style.display = 'block';

            // ocultar preview si no hay archivo seleccionado
            preview.style.display = 'none';
            preview.src = '';
        } else {
            uploadBox.style.display = 'none';

            // mostrar preview de imagen existente
            preview.src = '../../' + select.value;
            preview.style.display = 'block';
        }
    }

    select.addEventListener('change', function () {
        updateImageMode();
    });

    document.getElementById('index_image').addEventListener('change', function () {
        var fileName = 'Ningún archivo seleccionado';

        if (this.files && this.files.length > 0) {
            fileName = this.files[0].name;

            var reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(this.files[0]);
        } else {
            preview.style.display = 'none';
            preview.src = '';
        }

        document.getElementById('fileName').innerHTML = fileName;
    });

    // inicializar estado
    updateImageMode();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>