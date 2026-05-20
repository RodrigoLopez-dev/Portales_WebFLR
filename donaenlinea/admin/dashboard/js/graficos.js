function showGraphDiario(mes, agno) {
  {
    $.post(
      "crud/traeGraficoMes.php?mes=" + mes + "&agno=" + agno,

      function (data) {
        //  console.log(data);

        var aporte = [];

        var dia_creado = [];

        var mes = "";

        var total = 0;

        for (var i in data) {
          aporte.push(data[i].aporte);

          dia_creado.push(data[i].dia);

          mes = data[i].mes;

          cantidad = data[i].cantidad;

          total = data[i].total;
        }

        $("#totalmes").html("$" + formatNumber(total));

        $("#mes").html("&nbsp;" + mes);

        $("#cant_mes").html("&nbsp;" + mes);

        $("#cant_donaciones").html("&nbsp;" + cantidad);

        $("#dato_mes").html(
          "Mes :&nbsp;" +
            mes +
            "&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp; Total donaciones : " +
            formatNumber(total),
        );

        var chartdata = {
          labels: dia_creado,

          datasets: [
            {
              label: "Aporte",

              backgroundColor: "#ffffff",

              borderColor: "#000000",

              hoverBackgroundColor: "#CCCCCC",

              hoverBorderColor: "#666666",

              data: aporte,
            },
          ],
        };

        var graphTarget = $("#graphCanvasDiario");

        var barGraph = new Chart(graphTarget, {
          type: "bar",

          data: chartdata,

          options: {
            title: {
              display: true,

              //    text: 'Donaciones mes: '+ mes,

              fontColor: "#ffffff",
            },

            legend: {
              display: false,
            },

            tooltips: {
              mode: "label",

              label: "mylabel",

              callbacks: {
                label: function (tooltipItem, data) {
                  return (
                    "$" +
                    tooltipItem.yLabel
                      .toString()
                      .replace(/\B(?=(\d{3})+(?!\d))/g, ".")
                  );
                },
              },
            },

            scales: {
              yAxes: [
                {
                  ticks: {
                    callback: function (label, index, labels) {
                      return (
                        "$" +
                        label.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
                      );
                    },

                    beginAtZero: true,

                    fontSize: 10,

                    fontColor: "#ffffff",
                  },

                  gridLines: {
                    display: false,

                    color: "#ffffff",
                  },

                  scaleLabel: {
                    display: true,

                    labelString: "Aporte",

                    fontSize: 10,

                    fontColor: "#ffffff",
                  },
                },
              ],

              xAxes: [
                {
                  ticks: {
                    beginAtZero: true,

                    fontSize: 10,

                    fontColor: "#ffffff",
                  },

                  gridLines: {
                    display: false,
                  },

                  scaleLabel: {
                    display: true,

                    labelString: "Día",

                    fontSize: 10,

                    fontColor: "#ffffff",
                  },
                },
              ],
            },
          },
        });
      },
    );
  }
}

function showGraphCampanas(mes, agno) {
  {
    $.post(
      "crud/traeGraficoCampanasMes.php?mes=" + mes + "&agno=" + agno,

      function (data) {
        //console.log(data);

        var totales = [];

        var campana = [];

        var mes = "";

        for (var i in data) {
          totales.push(data[i].totales);

          campana.push(data[i].utm_campaign);

          mes = data[i].mes;
        }

        var chartdata = {
          labels: campana,

          datasets: [
            {
              label: "Totales",

              backgroundColor: "#ffffff",

              borderColor: "#000000",

              hoverBackgroundColor: "#CCCCCC",

              hoverBorderColor: "#666666",

              data: totales,
            },
          ],
        };

        var graphTarget = $("#graphCanvasCampana");

        $("#dato_mes_campana").html("Mes :&nbsp;" + mes);

        var barGraph = new Chart(graphTarget, {
          type: "bar",

          data: chartdata,

          options: {
            title: {
              display: true,

              //    text: 'Donaciones mes: '+ mes,

              fontColor: "#ffffff",
            },

            legend: {
              display: false,
            },

            tooltips: {
              mode: "label",

              label: "mylabel",

              callbacks: {
                label: function (tooltipItem, data) {
                  return (
                    "$" +
                    tooltipItem.yLabel
                      .toString()
                      .replace(/\B(?=(\d{3})+(?!\d))/g, ".")
                  );
                },
              },
            },

            scales: {
              yAxes: [
                {
                  ticks: {
                    callback: function (label, index, labels) {
                      return (
                        "$" +
                        label.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
                      );
                    },

                    beginAtZero: true,

                    fontSize: 10,

                    fontColor: "#ffffff",
                  },

                  gridLines: {
                    display: false,

                    color: "#ffffff",
                  },

                  scaleLabel: {
                    display: true,

                    labelString: "Total aporte",

                    fontSize: 10,

                    fontColor: "#ffffff",
                  },
                },
              ],

              xAxes: [
                {
                  ticks: {
                    beginAtZero: true,

                    fontSize: 10,

                    fontColor: "#ffffff",
                  },

                  gridLines: {
                    display: false,
                  },

                  scaleLabel: {
                    display: true,

                    labelString: "Campañas",

                    fontSize: 10,

                    fontColor: "#ffffff",
                  },
                },
              ],
            },
          },
        });
      },
    );
  }
}

function showGraphFuentes(mes, agno) {
  {
    $.post(
      "crud/traeGraficoFuenteMes.php?mes=" + mes + "&agno=" + agno,

      function (data) {
        //  console.log(data);

        var totales = [];

        var fuente = [];

        var mes = "";

        for (var i in data) {
          totales.push(data[i].totales);

          fuente.push(data[i].fuente);

          mes = data[i].mes;
        }

        var chartdata = {
          labels: fuente,

          datasets: [
            {
              label: "Totales",

              backgroundColor: "#ffffff",

              borderColor: "#000000",

              hoverBackgroundColor: "#CCCCCC",

              hoverBorderColor: "#666666",

              data: totales,
            },
          ],
        };

        var graphTarget = $("#graphCanvasFuentes");

        $("#dato_mes_fuente").html("Mes :&nbsp;" + mes);

        var barGraph = new Chart(graphTarget, {
          type: "bar",

          data: chartdata,

          options: {
            title: {
              display: true,

              //    text: 'Donaciones mes: '+ mes,

              fontColor: "#ffffff",
            },

            legend: {
              display: false,
            },

            tooltips: {
              mode: "label",

              label: "mylabel",

              callbacks: {
                label: function (tooltipItem, data) {
                  return (
                    "$" +
                    tooltipItem.yLabel
                      .toString()
                      .replace(/\B(?=(\d{3})+(?!\d))/g, ".")
                  );
                },
              },
            },

            scales: {
              yAxes: [
                {
                  ticks: {
                    callback: function (label, index, labels) {
                      return (
                        "$" +
                        label.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
                      );
                    },

                    beginAtZero: true,

                    fontSize: 10,

                    fontColor: "#ffffff",
                  },

                  gridLines: {
                    display: false,

                    color: "#ffffff",
                  },

                  scaleLabel: {
                    display: true,

                    labelString: "Total aporte",

                    fontSize: 10,

                    fontColor: "#ffffff",
                  },
                },
              ],

              xAxes: [
                {
                  ticks: {
                    beginAtZero: true,

                    fontSize: 10,

                    fontColor: "#ffffff",
                  },

                  gridLines: {
                    display: false,
                  },

                  scaleLabel: {
                    display: true,

                    labelString: "Campañas",

                    fontSize: 10,

                    fontColor: "#ffffff",
                  },
                },
              ],
            },
          },
        });
      },
    );
  }
}

function showGraphRegiones(mes, agno) {
  {
    $.post(
      "crud/traeGraficoRegionesMes.php?mes=" + mes + "&agno=" + agno,

      function (data) {
        //console.log(data);

        var totales = [];

        var region = [];

        var mes = "";

        for (var i in data) {
          totales.push(data[i].totales);

          region.push(data[i].region);

          mes = data[i].mes;
        }

        $("#dato_mes_region").html("Mes :&nbsp;" + mes);

        var chartdata = {
          labels: region,

          datasets: [
            {
              label: "Aporte",

              backgroundColor: "#ffffff",

              borderColor: "#000000",

              hoverBackgroundColor: "#CCCCCC",

              hoverBorderColor: "#666666",

              data: totales,
            },
          ],
        };

        var graphTarget = $("#graphCanvasRegion");

        var barGraph = new Chart(graphTarget, {
          type: "bar",

          data: chartdata,

          options: {
            title: {
              display: true,

              //  text: 'Donaciones por hora fecha: '+ fecha,

              fontColor: "#ffffff",
            },

            legend: {
              display: false,
            },

            tooltips: {
              mode: "label",

              label: "mylabel",

              callbacks: {
                label: function (tooltipItem, data) {
                  return (
                    "$" +
                    tooltipItem.yLabel
                      .toString()
                      .replace(/\B(?=(\d{3})+(?!\d))/g, ".")
                  );
                },
              },
            },

            scales: {
              yAxes: [
                {
                  ticks: {
                    callback: function (label, index, labels) {
                      return (
                        "$" +
                        label.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
                      );
                    },

                    beginAtZero: true,

                    fontSize: 10,

                    fontColor: "#ffffff",
                  },

                  gridLines: {
                    display: false,

                    color: "#ffffff",
                  },

                  scaleLabel: {
                    display: true,

                    labelString: "Total aporte",

                    fontSize: 10,

                    fontColor: "#ffffff",
                  },
                },
              ],

              xAxes: [
                {
                  ticks: {
                    beginAtZero: true,

                    fontSize: 10,

                    fontColor: "#ffffff",
                  },

                  gridLines: {
                    display: false,
                  },

                  scaleLabel: {
                    display: true,

                    labelString: "Región",

                    fontSize: 10,

                    fontColor: "#ffffff",
                  },
                },
              ],
            },
          },
        });
      },
    );
  }
}

function showGraphDonantes(mes, agno) {
  {
    $.post(
      "crud/traeGraficoMesDonantes.php?mes=" + mes + "&agno=" + agno,

      function (data) {
        //console.log(data);

        var donantes = [];

        var dia_creado = [];

        var mes = "";

        var total = 0;

        for (var i in data) {
          donantes.push(data[i].donantes);

          dia_creado.push(data[i].dia);

          mes = data[i].mes;

          cantidad = data[i].cantidad;
        }

        $("#dato_mes_donante").html(
          "Mes :&nbsp;" +
            mes +
            "&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp; Total donantes : " +
            formatNumber(cantidad),
        );

        var chartdata = {
          labels: dia_creado,

          datasets: [
            {
              label: "Donantes",

              backgroundColor: "#ffffff",

              borderColor: "#000000",

              hoverBackgroundColor: "#CCCCCC",

              hoverBorderColor: "#666666",

              data: donantes,
            },
          ],
        };

        var graphTarget = $("#graphCanvasDonantes");

        var barGraph = new Chart(graphTarget, {
          type: "bar",

          data: chartdata,

          options: {
            title: {
              display: true,

              //    text: 'Donaciones mes: '+ mes,

              fontColor: "#ffffff",
            },

            legend: {
              display: false,
            },

            tooltips: {
              mode: "label",

              label: "mylabel",

              callbacks: {
                label: function (tooltipItem, data) {
                  return (
                    "" +
                    tooltipItem.yLabel
                      .toString()
                      .replace(/\B(?=(\d{3})+(?!\d))/g, ".") +
                    " Donantes"
                  );
                },
              },
            },

            scales: {
              yAxes: [
                {
                  ticks: {
                    callback: function (label, index, labels) {
                      return (
                        "" +
                        label.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
                      );
                    },

                    beginAtZero: true,

                    fontSize: 10,

                    fontColor: "#ffffff",
                  },

                  gridLines: {
                    display: false,

                    color: "#ffffff",
                  },

                  scaleLabel: {
                    display: true,

                    labelString: "Cantidad",

                    fontSize: 10,

                    fontColor: "#ffffff",
                  },
                },
              ],

              xAxes: [
                {
                  ticks: {
                    beginAtZero: true,

                    fontSize: 10,

                    fontColor: "#ffffff",
                  },

                  gridLines: {
                    display: false,
                  },

                  scaleLabel: {
                    display: true,

                    labelString: "Día",

                    fontSize: 10,

                    fontColor: "#ffffff",
                  },
                },
              ],
            },
          },
        });
      },
    );
  }
}

function showGraphDiaHora(dia, mes, agno) {
  {
    $.post(
      "crud/traeGraficoDiaHora.php?dia=" +
        dia +
        "&mes=" +
        mes +
        "&agno=" +
        agno,

      function (data) {
        //  console.log(data);

        var monto = [];

        var dia = [];

        var fecha = "";

        var total = 0;

        var cantidad = 0;

        for (var i in data) {
          monto.push(data[i].monto);

          dia.push(data[i].dia);

          fecha = data[i].fecha;

          total = data[i].total;

          cantidad = data[i].cantidad;
        }

        var fecha_dato = "";

        if (fecha == "") {
          fecha_dato = "No hay datos";
        } else {
          fecha_dato = "Día : &nbsp;" + fecha;
        }

        var total_dato = "";

        if (total == "") {
          total_dato = 0;
        } else {
          total_dato = total;
        }

        $("#donaciones_dia").html("$ &nbsp;" + formatNumber(total_dato));

        $("#dato_fecha").html(fecha_dato);

        $("#dato_fecha_diaHora").html(
          fecha_dato +
            "&nbsp;&nbsp;&nbsp; |&nbsp;&nbsp;&nbsp; Total donantes : " +
            cantidad +
            "&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp; Total donaciones : $ " +
            formatNumber(total_dato),
        );

        var chartdata = {
          labels: dia,

          datasets: [
            {
              label: "Aporte",

              backgroundColor: "#ffffff",

              borderColor: "#000000",

              hoverBackgroundColor: "#CCCCCC",

              hoverBorderColor: "#666666",

              data: monto,
            },
          ],
        };

        var graphTarget = $("#graphCanvasDiaHora");

        var barGraph = new Chart(graphTarget, {
          type: "line",

          data: chartdata,

          options: {
            title: {
              display: true,

              //  text: 'Donaciones por hora fecha: '+ fecha,

              fontColor: "#ffffff",
            },

            legend: {
              display: false,
            },

            tooltips: {
              mode: "label",

              label: "mylabel",

              callbacks: {
                label: function (tooltipItem, data) {
                  return (
                    "$" +
                    tooltipItem.yLabel
                      .toString()
                      .replace(/\B(?=(\d{3})+(?!\d))/g, ".")
                  );
                },
              },
            },

            scales: {
              yAxes: [
                {
                  ticks: {
                    callback: function (label, index, labels) {
                      return (
                        "$" +
                        label.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
                      );
                    },

                    beginAtZero: true,

                    fontSize: 10,

                    fontColor: "#ffffff",
                  },

                  gridLines: {
                    display: false,

                    color: "#ffffff",
                  },

                  scaleLabel: {
                    display: true,

                    labelString: "Aporte",

                    fontSize: 10,

                    fontColor: "#ffffff",
                  },
                },
              ],

              xAxes: [
                {
                  ticks: {
                    beginAtZero: true,

                    fontSize: 10,

                    fontColor: "#ffffff",
                  },

                  gridLines: {
                    display: false,
                  },

                  scaleLabel: {
                    display: true,

                    labelString: "Hora y fuente",

                    fontSize: 10,

                    fontColor: "#ffffff",
                  },
                },
              ],
            },
          },
        });
      },
    );
  }
}
