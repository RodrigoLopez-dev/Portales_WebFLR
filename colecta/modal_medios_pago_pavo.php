<div class="modal fade" id="myModal" role="dialog" style="margin-top:100px;">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title"><img src="images/botones/cvecina-logo.jpg"></h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body" style="text-align: left;">
        <p>Número de cuenta: 3450
          <br>Banco Estado
          <br>Titular: Fundación Las Rosas
          <br>Rut: 70.543.600-2
        </p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">
          <h4>Cerrar</h4>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="myModal2" role="dialog" style="margin-top:50px;">
  <div class="modal-dialog modal-sm">
    <div class="modal-content" style="width:400px;">
      <div class="modal-header">
        <h4>Transferencia Bancaria</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body" style="text-align: center;">
        <h4>Ingresa a tu banco y haz una transferencia bancaria a la siguiente cuenta:</h4>
        <p>
          <br>Número cuenta corriente: 400 400 03
          <br>Banco: BCI
          <br>Titular: Fundación Las Rosas
          <br>Rut: 70.543.600-2
          <br>Email: maria.kneer@flrosas.cl
        </p>
        <br><br>
        <script>
          function copyToClipboard(element) {
            var text = $(element).clone().find('br').prepend('\r\n').end().text()
            element = $('<textarea>').appendTo('body').val(text).select()
            document.execCommand('copy')
            element.remove()
            alert('Datos copiados con exito')
          }
        </script>
        <datos contenteditable="true" hidden>Fundacion Las Rosas<br>70.543.600-2<br>Banco Crédito e
          Inversiones<br>Cuenta Corriente<br>40001041<br>maria.kneer@flrosas.cl</datos>
        <button onclick="copyToClipboard('datos')" class="btn btn-flr">Click aquí para copiar datos</button>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">
          <h4>Cerrar</h4>
        </button>
      </div>
    </div>
  </div>
</div>