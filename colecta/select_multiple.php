<?php

if (!$_POST) {
  ?>
  <form action="select_multiple.php" method="POST">
    Nombre: <input type="text" name="nombre"><br>
    Apellidos: <input type="text" name="apellidos"><br>
    Email: <input type="text" name="email"> <br>
    Cerveza: <br>
    <select multiple name="cerveza[]" id="cerveza">
      <option value="SanMiguel">San Miguel</option>
      <option value="Mahou">Mahou</option>
      <option value="Heineken">Heineken</option>
      <option value="Carlsberg">Carlsberg</option>
      <option value="Aguila">Aguila</option>
    </select><br>
    <input type="submit" value="Enviar datos!">
    <button type="button" onclick="ShowSelected()">prueba</button>
    resultado: <input type="text" name="resultado" id="resultado"><br>
  </form>
  <?php
} else {
  echo "Nombre: " . $_POST["nombre"];
  echo "<br>Apellidos: " . $_POST["apellidos"];
  echo "<br>E-mail: " . $_POST["email"];
  echo "cerve : " . $_POST["cerveza"];
  $cervezas = $_POST["cerveza"];
  //recorremos el array de cervezas seleccionadas. No olvidarse q la primera posición de un array es la 0

  for ($i = 0; $i < count($cervezas); $i++) {
    echo "<br> Cerveza " . $i . ": " . $cervezas[$i];
  }
}
?>

<script src="js/jquery-1.12.4.js"></script>
<script src="js/jquery-ui.js"></script>
<script src="js/jquery.validate.min.js"></script>
<script src="js/main.js"></script>
<!-- Include plugin -->
<script src="https://cdn.rawgit.com/wenzhixin/multiple-select/e14b36de/multiple-select.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"
  integrity="sha384-cs/chFZiN24E4KMATLdqdvsezGxaGsi4hLGOzlXwp5UZB1LY//20VyM2taTB4QvJ" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js"
  integrity="sha384-uefMccjFJAIv6A+rW+L4AHf99KvxDjWSu1z9VI8SKNVmz4sk7buKt/6v9KI65qnm" crossorigin="anonymous"></script>
<script type="text/javascript">
  jQuery('#cerveza').change(function () {
    var selected = $("#cerveza :selected").map((_, e) => e.value).get();
    $("#resultado").val(selected);
  });

  function ShowSelected() {
    var selected = $("#cerveza :selected").map((_, e) => e.value).get();
    $("#resultado").val(selected);
    // alert(selected);
  }

</script>