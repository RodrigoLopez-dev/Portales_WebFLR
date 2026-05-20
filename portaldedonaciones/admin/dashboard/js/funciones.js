// function formatNumber(num) {

//     if (!num || num == 'NaN') return '-';

//     if (num == 'Infinity') return '&#x221e;';

//     num = num.toString().replace(/\$|\,/g, '');

//     if (isNaN(num))

//         num = "0";

//     sign = (num == (num = Math.abs(num)));

//     num = Math.floor(num * 100 + 0.50000000001);

//     cents = num % 100;

//     num = Math.floor(num / 100).toString();

//     if (cents < 10)

//         cents = "0" + cents;

//     for (var i = 0; i < Math.floor((num.length - (1 + i)) / 3) ; i++)

//         num = num.substring(0, num.length - (4 * i + 3)) + '.' + num.substring(num.length - (4 * i + 3));

//     return (((sign) ? '' : '-') + num );

// }

function formatNumber(num) {
  if (isNaN(num)) return "-";
  if (!isFinite(num)) return "&#x221e;";
  return Number(num).toLocaleString("en", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
}

// function startTime() {

//     var today = new Date();

//     var hr = today.getHours();

//     var min = today.getMinutes();

//     var sec = today.getSeconds();

//     //Add a zero in front of numbers<10

//     min = checkTime(min);

//     sec = checkTime(sec);

//     document.getElementById("clock").innerHTML = hr + " : " + min + " : " + sec;

//     var time = setTimeout(function(){ startTime() }, 500);

// }

// function checkTime(i) {

//     if (i < 10) {

//         i = "0" + i;

//     }

//     return i;

// }

function startTime() {
  var today = new Date();
  var hr = today.getHours();
  var min = today.getMinutes();
  var sec = today.getSeconds();

  // Agregar un cero delante de números menores a 10
  min = checkTime(min);
  sec = checkTime(sec);

  document.getElementById("clock").innerHTML = hr + " : " + min + " : " + sec;
  setTimeout(startTime, 500);
}

function checkTime(i) {
  return i < 10 ? "0" + i : i;
}
