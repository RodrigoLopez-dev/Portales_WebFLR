<?php $currentPage = 'enciende'; ?>

<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Enciende tu vela</title>
  <link rel="stylesheet" href="./assets/style.css">
  <link rel="preload" as="video" href="./assets/video/candle_encender.webm" type="video/webm">
</head>

<body class="site-bg enciende-page">
  <div class="site-shell">
    <?php include './partials/navbar.php'; ?>
  </div>

  <section class="hero enciende-hero">
    <div class="heroGrid enciende-heroGrid">

      <div class="enciendeIntro enciende-intro">
        <div class="h1">Enciende una vela,<br>enciende una oración.</div>

        <div class="enciende-copy text-justified">
          <p class="p">
            Te invitamos a encender una vela virtual para elevar una oración, por una promesa personal o en memoria de
            algún ser querido, por un tema de salud, por una buena causa, para felicitar a un amigo por su aniversario
            matrimonial o cumpleaños, entre otros.
          </p>

          <p class="p">
            Cada semana, nuestros hogares a lo largo de Chile, rezan el rosario por todas las intenciones encomendadas
            en este portal. Además las intenciones estarán presentes en la Misa del día miércoles del Santuario A María
            Santísima transmitida en nuestro canal de Youtube.
          </p>

          <p class="p">
            Una vez registrada tu intención anónima, tu vela quedará encendida durante <strong>48 horas</strong>.
          </p>
        </div>
      </div>

      <div class="heroLeft enciende-left">
        <div class="candleVideoWrap" aria-hidden="true">
          <video id="candleVideo" class="candleVideo" muted playsinline preload="auto">
            <source src="./assets/video/candle_encender.webm" type="video/webm">
          </video>
        </div>
      </div>

      <div class="enciendeFormWrap enciende-form-wrap">
        <div class="formCard">
          <div class="formHd"></div>

          <div class="formBd">
            <form id="candleForm">
              <div class="row">
                <div>
                  <label for="name">Tu nombre</label>
                  <input id="name" name="name" type="text" placeholder="Nombre *" required>
                </div>
              </div>

              <div style="margin-top: 10px;">
                <label for="email">Tu correo electrónico</label>
                <input id="email" name="email" type="email" placeholder="Email *" required>
              </div>

              <div style="margin-top: 10px;">
                <label for="request">Intención para encender tu vela</label>
                <textarea id="request" name="request" placeholder="Escribe tu petición... *" required></textarea>
              </div>

              <div class="ctaRow">
                <button type="submit" class="btn-cta">ENCIENDE TU VELA</button>
              </div>

              <div id="notice" class="notice"></div>
            </form>
          </div>
        </div>
      </div>

    </div>
  </section>

  <?php include './partials/footer.php'; ?>

  <script>
    const $ = (id) => document.getElementById(id);
    const videoEl = $("candleVideo");
    const formEl = $("candleForm");
    const submitBtn = formEl ? formEl.querySelector('button[type="submit"], input[type="submit"]') : null;

    let isSubmitting = false;

    function showNotice(msg, type) {
      type = type || "ok";
      const el = $("notice");
      if (!el) return;

      el.textContent = msg;
      el.className = "notice show " + type;

      // Los mensajes de éxito o error se ocultan tras 1s; el de info permanece
      if (type !== "info") {
        setTimeout(function () {
          el.classList.remove("show");
        }, 1000); // 5000
      }
    }

    function setSubmittingState(state) {
      isSubmitting = state;

      if (submitBtn) {
        submitBtn.disabled = state;
        if (state) {
          submitBtn.setAttribute("data-original-text", submitBtn.textContent || submitBtn.value || "");
          if (submitBtn.tagName.toLowerCase() === "button") {
            submitBtn.textContent = "Procesando...";
          } else {
            submitBtn.value = "Procesando...";
          }
        } else {
          var originalText = submitBtn.getAttribute("data-original-text") || "Enviar";
          if (submitBtn.tagName.toLowerCase() === "button") {
            submitBtn.textContent = originalText;
          } else {
            submitBtn.value = originalText;
          }
        }
      }
    }

    if (formEl) {
      formEl.addEventListener("submit", async function (e) {
        e.preventDefault();

        // Evita múltiples submits por doble click o Enter repetido
        if (isSubmitting) {
          return;
        }

        setSubmittingState(true);

        // 1. Prioridad: Mostrar mensaje y arrancar video
        showNotice("Registrando la información y procediendo a encender la vela", "info");

        var videoFinished = Promise.resolve();

        if (videoEl) {
          try {
            videoEl.currentTime = 0;
            videoEl.play();

            videoFinished = new Promise(function (resolve) {
              var resolved = false;

              function finishOnce() {
                if (resolved) return;
                resolved = true;
                videoEl.onended = null;
                resolve();
              }

              videoEl.onended = finishOnce;
              setTimeout(finishOnce, 1500); // 4500: Seguridad
            });
          } catch (videoError) {
            videoFinished = Promise.resolve();
          }
        }

        // 2. Proceso de datos simultáneo
        try {
          var apiPromise = fetch("./api/create_candle.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
              name: $("name").value.trim(),
              email: $("email").value.trim(),
              request: $("request").value.trim()
            })
          }).then(function (r) {
            return r.json();
          });

          // Esperar a que el video termine y el servidor responda
          var results = await Promise.all([apiPromise, videoFinished]);
          var res = results[0];

          if (res.ok) {
            var MY_TOKENS_KEY = "portal_oraciones_owner_tokens_v1";
            var SHARE_TOKENS_KEY = "portal_oraciones_share_tokens_v1";

            var myTokens = {};
            var shareTokens = {};

            try {
              myTokens = JSON.parse(localStorage.getItem(MY_TOKENS_KEY) || "{}");
            } catch (storageError) {
              myTokens = {};
            }

            try {
              shareTokens = JSON.parse(localStorage.getItem(SHARE_TOKENS_KEY) || "{}");
            } catch (storageError) {
              shareTokens = {};
            }

            if (res.candle && res.candle.id) {
              if (res.owner_token) {
                myTokens[res.candle.id] = res.owner_token;
                localStorage.setItem(MY_TOKENS_KEY, JSON.stringify(myTokens));
              }

              if (res.share_token) {
                shareTokens[res.candle.id] = res.share_token;
                localStorage.setItem(SHARE_TOKENS_KEY, JSON.stringify(shareTokens));
              }
            }

            // 3. Mostrar éxito final
            var mailStatus = res.mail_sent ? "Se ha enviado un correo de confirmación." : "Vela registrada.";
            showNotice("✅ Registrando información y procediendo a encender la vela. " + mailStatus + " Redirigiendo...", "ok");

            setTimeout(function () {
              window.location.href = "velas.php?candle=" + encodeURIComponent(res.candle.id);
            }, 2000);

          } else {
            showNotice(res.error || "Error al procesar", "err");
            setSubmittingState(false);
          }

        } catch (err) {
          showNotice("Error de conexión con el servidor", "err");
          setSubmittingState(false);
        }
      });
    }
  </script>

  <script src="./js/navbar.js"></script>

</body>

</html>