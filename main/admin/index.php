<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /invitrosoft/src/index.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/header-footer.css">
  <link rel="icon" href="../img/logo.svg" type="image/svg+xml">
  <title>Panel principal</title>
</head>
<body>
  <!--NOTIFICACIONES EN TIEMPO REAL-->
  <main class="wrap">
    <!-- ====== SCROLLER DE ESTADÍSTICAS ====== -->
    <section class="stats reveal" id="stats">
      <div class="stats-track" id="statsTrack">
        <article class="stat-card">
          <img class="stat-icon" src="/icons/usuario-notification.png" alt="Usuario">
          <div class="stat-title">Usuario</div>
          <div class="stat-value"></div>
        </article>
        <article class="stat-card">
          <img class="stat-icon" src="/icons/notificaciones-notification.png" alt="Notificaciones">
          <div class="stat-title">Notificaciones</div>
          <div class="stat-value"></div>
        </article>
        <article class="stat-card">
          <img class="stat-icon" src="/icons/reactivos-notification.png" alt="Reactivos">
          <div class="stat-title">Reactivos</div>
          <div class="stat-value"></div>
        </article>
        <article class="stat-card">
          <img class="stat-icon" src="/icons/planta-notification.png" alt="Plantas">
          <div class="stat-title">Plantas</div>
          <div class="stat-value"></div>
        </article>
        <article class="stat-card">
          <img class="stat-icon" src="/icons/contaminacion-notification.png" alt="Contaminaciones">
          <div class="stat-title">Contaminaciones</div>
          <div class="stat-value"></div>
        </article>
      </div>
    </section>
    <!-- ====== SECCIÓN: INTERACTUAR ====== -->
    <div class="section-title reveal">
      <h2>Es hora de interactuar con Invitrosoft</h2>
      <div class="leaf-gif-container">
        <img src="/img/hojita.gif" alt="Hojita animada">
    </div>
    </div>
    <section class="grid reveal">
      <a href="reactivos.html" class="card-link">
        <article class="card">
          <div class="card-head">
            <img src="/icons/reactivo.png" alt="">
            <h3>Reactivos</h3>
          </div>
          <p>Gestiona los reactivos y lleva su control.</p>
        </article>
      </a>
      <a href="formulaciones.html" class="card-link">
        <article class="card">
          <div class="card-head">
            <img src="/icons/formulaciones.png" alt="">
            <h3>Formulaciones</h3>
          </div>
          <p>Administra formulaciones y composiciones.</p>
        </article>
      </a>
      <a href="protocolo.html" class="card-link">
        <article class="card">
          <div class="card-head">
            <img src="/icons/protocolo.png" alt="">
            <h3>Protocolos</h3>
          </div>
          <p>Gestiona el control y desinfecciones de explantes.</p>
        </article>
      </a>
      <a href="categoria.html" class="card-link">
        <article class="card">
          <div class="card-head">
            <img src="/icons/categorias.png" alt="">
            <h3>Categorías</h3>
          </div>
          <p>Controla categorías o separación de elementos.</p>
        </article>
      </a>
      <a href="parametros.html" class="card-link">
        <article class="card">
          <div class="card-head">
            <img src="/icons/parametros.png" alt="">
            <h3>Parámetros</h3>
          </div>
          <p>Gestiona parámetros de usuarios para su control.</p>
        </article>
      </a>
      <a href="historial.html" class="card-link">
        <article class="card">
          <div class="card-head">
            <img src="/icons/historial.png" alt="">
            <h3>Historial</h3>
          </div>
          <p>Administra las acciones de tus usuarios.</p>
        </article>
      </a>
    </section>
  </main>
  <script src="js/script.js"></script>
  <script src="js/header-footer.js"></script>
  <script>
    if (localStorage.getItem('darkMode') === 'enabled') {
        document.body.classList.add('dark-mode');
    }
  </script>
</body>
</html>