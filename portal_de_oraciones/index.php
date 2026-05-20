<?php $currentPage = 'inicio'; ?>

<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Portal de Oración — Fundación Las Rosas</title>
  <link rel="stylesheet" href="./assets/style.css">
</head>

<body class="home-page">

  <header class="home-hero hero-home-video">
    <div class="hero-media-bg" aria-hidden="true">
      <video class="hero-bg-video" autoplay muted loop playsinline preload="auto"
        poster="./assets/img/home_hero_bg.png">
        <source src="./assets/video/video sitio oracion 1.mp4" type="video/mp4">
        <!-- opcional si luego generas webm -->
        <!-- <source src="./assets/video/candle_loop.webm" type="video/webm"> -->
      </video>
    </div>

    <div class="hero-bg-fallback" aria-hidden="true"></div>
    <div class="hero-bg-darken" aria-hidden="true"></div>
    <div class="hero-bg-gradient" aria-hidden="true"></div>
    <div class="hero-bg-heart" aria-hidden="true"></div>

    <div class="site-shell hero-home-shell">

      <?php include './partials/navbar.php'; ?>

      <div class="hero-home-layout">
        <div class="hero-home-spacer" aria-hidden="true"></div>

        <div class="hero-copy hero-copy-home text-justified">
          <h1>Portal de oración de<br>Fundación Las Rosas</h1>

          <p>
            Únete al portal de oración de Fundación Las Rosas y deja tu intención iluminada.
            Tu vela virtual estará encendida por 48 horas y cada semana, en nuestros hogares y misa,
            rezaremos por todas las peticiones recibidas.
          </p>

          <p class="hero-copy-strong">
            Es gratuito, es simple, es un acto de amor.
          </p>

          <a class="btn-cta hero-home-cta" href="./enciende.php">
            ¡ENCIENDE TU VELA HOY!
          </a>
        </div>
      </div>
    </div>
  </header>

  <main>
    <section class="home-grid two-col section-first">
      <article class="home-card home-card-text">
        <h2>Santuario a María Santísima</h2>
        <p>
          El Santuario a María Santísima, ubicado en Rivera 2005 comuna de Independencia, es el lugar desde donde la
          Santísima Virgen entrega el carisma del servicio fraterno hacia las personas mayores de los Hogares de
          Fundación Las Rosas, siendo el encuentro con el Señor la base de los cuidados de sus residentes.
        </p>
        <p>
          ¡Invitamos a toda la comunidad a conocer este hermoso Templo dedicado a la Persona Mayor, fuente de la misión
          y espiritualidad de Fundación Las Rosas!
        </p>
        <a class="home-link" href="https://www.fundacionlasrosas.cl/espiritualidad/historia" target="_blank"
          rel="noopener noreferrer">
          Más información del Santuario
        </a>
        <a class="home-link" href="https://drive.google.com/file/d/1Ohry1hAAR3apsARUFc1XsmPEImdj-Zci/view?usp=sharing"
          target="_blank" rel="noopener noreferrer">
          Descarga la maqueta del Santuario
        </a>
        <br>
        <p>
          - Horarios de Misa: <br>
          Lunes a Sábado: 12:00 hrs. <br>
          Domingo: 11:00 hrs.
        </p>
        <p>
          - Visitas al museo: <br>
          Lunes a Viernes de 10:00 a 16:00 hrs. <br>
          Entrada liberada.
        </p>
        <p>
          - Celebración de Matrimonios o aniversarios: <br>
          Contactar a: Isabel Margarita Vicuña - imvicuna@flrosas.cl
        </p>
      </article>

      <article class="home-card home-card-media no-pad">
        <img src="./assets/img/home_santuario.png" alt="Santuario a María Santísima">
      </article>
    </section>

    <!--     <section class="home-grid two-col alt-row">
      <article class="home-card home-card-media home-card-media-soft">
        <img src="./assets/img/home_jubileo.png" alt="Jubileo 2025">
      </article>

      <article class="home-card home-card-text">
        <h2>¡Somos templo Jubilar, te esperamos!</h2>
        <p>
          El Jubileo es una celebración espiritual convocada por la Iglesia Católica para conmemorar un evento
          significativo. Durante un jubileo, se ofrece a los fieles la posibilidad de obtener la indulgencia plenaria.
          Aprovecha esta gran oportunidad visitando nuestro Santuario a María Santísima donde podrás visitar a los
          residentes de Fundación Las Rosas entregando además tu tiempo como servicio a los demás.
        </p>
        <p>
          Para visitar el Santuario con tu delegación:<br>
          Escríbenos a: comisionjubilar@flrosas.cl<br>
          Dirección: Rivera 2005, Independencia
        </p>
        <a class="home-link" href="https://www.fundacionlasrosas.cl/" target="_blank" rel="noopener noreferrer">
          Más información del Santuario
        </a>
      </article>
    </section> -->
  </main>

  <?php include './partials/footer.php'; ?>

  <script src="./js/navbar.js"></script>
</body>

</html>