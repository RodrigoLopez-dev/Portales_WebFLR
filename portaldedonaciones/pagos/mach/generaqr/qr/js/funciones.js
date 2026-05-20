function generarQR(url) {
  // URL de la API de Google Charts para generar códigos QR
  var apiUrl =
    "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=" +
    encodeURIComponent(url);
  // Crea un elemento de imagen y establece la fuente como la URL de la API de Google Charts
  var img = document.createElement("img");
  img.src = apiUrl;
  // Agrega la imagen al contenedor div
  var qrContainer = document.getElementById("qr-container");
  qrContainer.appendChild(img);
}
