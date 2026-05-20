<div class="ventana_flotante" id="ventana_ayuda">
  <a class='cerrar' href='javascript:void(0);'
    onclick='document.getElementById("ventana_ayuda").style.display = "none";document.getElementById("icono_ayuda").style.display = "block";'>
    <span style="color:#af0a3d;">(X)</span>
  </a>
  <div class="panel-heading">
    <br>
    <b style="color:#af0a3d;">Necesitas ayuda?</b><br><br>
    LLamanos <i class="fa fa-phone" style="font-size:16px"></i><br>
    <a href="tel:227307140" style="font-size:18px"> 22 730 71 40 </a><br><b> o</b> <br>
    Envianos un e-mail<br>
    <a href="mailto:amigos@flrosas.cl">amigos@flrosas.cl</a>
    <br><br><br><br>
  </div>
</div>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("ventana_ayuda").style.display = "none";
    document.getElementById("icono_ayuda").style.display = "block";
  });
</script>

<div class="icono_flotante" id="icono_ayuda"
  onclick='document.getElementById("ventana_ayuda").style.display = "block";document.getElementById("icono_ayuda").style.display = "none";'>
  <div class="panel-heading">
    <i class="fa fa-info-circle" aria-hidden="true"></i><br>
    <div style="line-height: normal;font-size: 12px;">
      Necesitas ayuda?</div>
  </div>
</div>