$(document).ready(function()
{
document.getElementById('txt_otro').style.display = 'none';
});

function f_check() {

if (document.getElementById("control_01").checked) {
  document.getElementById('txt_otro').style.display = 'none';
  $('#donacion').val(document.getElementById("control_01").value);
window.location.href="#sec-dona";
}else {
  document.getElementById('txt_otro').style.display = 'block';

}

if (document.getElementById("control_02").checked) {
  document.getElementById('txt_otro').style.display = 'none';
  $('#donacion').val(document.getElementById("control_02").value);
  window.location.href="#sec-dona";
}else {
  document.getElementById('txt_otro').style.display = 'block';
}

if (document.getElementById("control_03").checked) {
  document.getElementById('txt_otro').style.display = 'none';
  $('#donacion').val(document.getElementById("control_03").value);
  window.location.href="#sec-dona";
}else {
  document.getElementById('txt_otro').style.display = 'block';
}

if (document.getElementById("control_04").checked) {
  document.getElementById('txt_otro').style.display = 'none';
  $('#donacion').val(document.getElementById("control_04").value);
  window.location.href="#sec-dona";
}else {
  document.getElementById('txt_otro').style.display = 'block';
}

if (document.getElementById("control_05").checked) {
  document.getElementById('txt_otro').style.display = 'block';
  document.getElementById('txt_otro').focus();
    $('#donacion').val('');
}else {
  document.getElementById('txt_otro').style.display = 'none';
}

}



function submitForm(action) {

  	if ( $( "#donacion" ).val().trim() == '' &&  $( "#txt_otro" ).val().length < 1 )
    {
      alert('Debes seleccionar un monto a donar');
    }



							if ( $( "#donacion" ).val().trim() != '' ) {
								var form = document.getElementById('formDonacion');
								form.action = action;
								form.submit();
						  }else{
							if ( $( "#txt_otro" ).val().length < 1 ) {
							$( "#txt_otro" ).focus();
						  }else{if(isNaN($( "#txt_otro" ).val())){
                  alert('Ingrese solo números.');
                  $( "#txt_otro" ).val('');
                 $( "#txt_otro" ).focus();
                }else{
                  if($( "#txt_otro" ).val()<499){
                    alert('Monto minimo $500');
                  $( "#txt_otro" ).focus();
                    return false;
                  }else{
                    var form = document.getElementById('formDonacion');
    								form.action = action;
    								form.submit();
                  }

                }

						}
					}
					}
