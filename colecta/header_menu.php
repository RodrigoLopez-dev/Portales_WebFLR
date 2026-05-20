<div class="ventana_flotante" id="ventana_ayuda">
  <a class='cerrar' href='javascript:void(0);'
    onclick='document.getElementById("ventana_ayuda").style.display = "none";document.getElementById("icono_ayuda").style.display = "block";'>
    <p style="color:#af0a3d;">(X)</p>
  </a><br>
  <div class="panel-heading">
    <br>
    <b style="color:#af0a3d;">¿Necesitas ayuda?</b><br><br>
    Llámanos <i class="fa fa-phone" style="font-size:16px"></i><br>
    <a href="tel:800719711" style="font-size:18px"> 800 719 711 </a><br><b> o</b> <br>
    Env&iacute;anos un e-mail<br>
    <a href="mailto:productos@flrosas.cl">productos@flrosas.cl</a>
    <br><br><br><br>
  </div>
</div>

<script>
  window.addEventListener('load', function () {
    document.getElementById("ventana_ayuda").style.display = "none";
    document.getElementById("icono_ayuda").style.display = "block";
  });
</script>

<div class="icono_flotante" id="icono_ayuda"
  onclick='document.getElementById("ventana_ayuda").style.display = "block";document.getElementById("icono_ayuda").style.display = "none";'>
  <div class="panel-heading">
    <i class="fa fa-info-circle" aria-hidden="true"></i><br>
    <div style="line-height: normal;font-size: 12px;">
      ¿Necesitas ayuda?</div>
  </div>
</div>