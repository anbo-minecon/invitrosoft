<?php
require_once '../includes/auth_check.php';
?>

<!DOCTYPE html>
<html lang="es"> 
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel Principal - Invitrosoft</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/header-footer.css">
  <link rel="icon" href="/invitrosoft/img/logo.svg" type="image/svg+xml">
  <link rel="stylesheet" href="../welcome/welcome-message.css">

</head>
<body>
  <div class="container">
    <!-- Hero Section -->
    <section class="hero">
      <div class="hero-content">
        <div class="title-container">
          <img src="../../img/hojita.gif" alt="Logo Invitrosoft" class="header-logo">
          <h1>Panel de Control Invitrosoft</h1>
        </div>
        <p>Selecciona un módulo para comenzar</p>
      </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-header">
            <div class="stat-icon">
              <svg viewBox="0 0 24 24" fill="currentColor" width="24" height="24">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
              </svg>
            </div>
            <span class="stat-label">Usuarios</span>
          </div>
          <div class="stat-value" id="total-usuarios">Cargando...</div>
        </div>

        <div class="stat-card">
          <div class="stat-header">
            <div class="stat-icon">
              <svg viewBox="0 0 24 24" fill="currentColor" width="24" height="24">
                <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2zm-2 1H8v-6c0-2.48 1.51-4.5 4-4.5s4 2.02 4 4.5v6z"/>
              </svg>
            </div>
            <span class="stat-label">Plantas en Multiplicación</span>
          </div>
          <div class="stat-value" id="plantas-multiplicacion">Cargando...</div>
        </div>

        <div class="stat-card">
          <div class="stat-header">
            <div class="stat-icon">
              <svg viewBox="0 0 24 24" fill="currentColor" width="24" height="24">
                <path d="M19.9 10.1c-.4-.4-1-.4-1.4 0l-1.5 1.5c-.2.2-.3.4-.3.7v5.2c0 .6-.4 1-1 1h-11c-.6 0-1-.4-1-1v-11c0-.6.4-1 1-1h5.2c.3 0 .5-.1.7-.3l1.5-1.5c.4-.4.4-1 0-1.4s-1-.4-1.4 0l-1.5 1.5c-.6.6-1.4.9-2.3.9h-5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2v-5c0-.9-.3-1.7-.9-2.3l1.5-1.5c.4-.4.4-1 0-1.4l-1.3-1.3z"/>
                <path d="M23.7.3c-.4-.4-1-.4-1.4 0l-3.7 3.7-1.4-1.4c-.4-.4-1-.4-1.4 0s-.4 1 0 1.4l1.4 1.4-8.5 8.5c-.2.2-.3.4-.3.7v3c0 .6.4 1 1 1h3c.3 0 .5-.1.7-.3l8.5-8.5 1.4 1.4c.2.2.4.3.7.3s.5-.1.7-.3c.4-.4.4-1 0-1.4l-1.4-1.4 3.7-3.7c.4-.4.4-1 0-1.4l-1.3-1.3z"/>
              </svg>
            </div>
            <span class="stat-label">Reactivos</span>
          </div>
          <div class="stat-value" id="total-reactivos">Cargando...</div>
        </div>

        <div class="stat-card">
          <div class="stat-header">
            <div class="stat-icon">
              <svg viewBox="0 0 24 24" fill="currentColor" width="24" height="24">
                <path d="M12 2L4 5v6.09c0 5.05 3.41 9.76 8 10.91 4.59-1.15 8-5.86 8-10.91V5l-8-3zm6 9.09c0 4-2.55 7.7-6 8.83-3.45-1.13-6-4.83-6-8.83V6.31l6-2.12 6 2.12v4.78zm-9.18-.5L7.4 12l3.54 3.54 5.66-5.66-1.41-1.41-4.24 4.24-2.13-2.12z"/>
              </svg>
            </div>
            <span class="stat-label">Plantas</span>
          </div>
          <div class="stat-value" id="total-plantas">Cargando...</div>
        </div>

        <div class="stat-card">
          <div class="stat-header">
            <div class="stat-icon">
              <svg viewBox="0 0 24 24" fill="currentColor" width="24" height="24">
                <path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/>
              </svg>
            </div>
            <span class="stat-label">Contaminaciones</span>
          </div>
          <div class="stat-value"></div>
        </div>
      </div>
    </section>

    <!-- Module Navigation -->
    <section class="modules-nav">
      <h2 class="modules-title">Módulos del Sistema</h2>
      <div class="nav-tabs">
        <button class="nav-tab active" data-module="reactivos">Reactivos</button>
        <button class="nav-tab" data-module="formulaciones">Formulaciones</button>
        <button class="nav-tab" data-module="protocolos">Protocolos</button>
        <button class="nav-tab" data-module="categorias">Categorías</button>
        <button class="nav-tab" data-module="parametros">Parámetros</button>
        <button class="nav-tab" data-module="historial">Historial</button>
      </div>
    </section>

    <!-- Module Preview -->
    <div id="modulePreview" class="module-preview">
      <!-- Content will be dynamically inserted here -->
    </div>
  </div>

  <script>
    // Module data
    const modules = {
      reactivos: {
        title: 'Reactivos',
        category: 'Gestión de Inventario',
        description: 'Sistema completo para gestionar y controlar el inventario de reactivos químicos del laboratorio. Mantén un registro detallado de todos los compuestos y materiales.',
        features: [
          'Control de stock en tiempo real',
          'Alertas de vencimiento automáticas',
          'Historial de uso y consumo'
        ],
        icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="40" height="40">
          <path d="M19.9 10.1c-.4-.4-1-.4-1.4 0l-1.5 1.5c-.2.2-.3.4-.3.7v5.2c0 .6-.4 1-1 1h-11c-.6 0-1-.4-1-1v-11c0-.6.4-1 1-1h5.2c.3 0 .5-.1.7-.3l1.5-1.5c.4-.4.4-1 0-1.4s-1-.4-1.4 0l-1.5 1.5c-.6.6-1.4.9-2.3.9h-5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2v-5c0-.9-.3-1.7-.9-2.3l1.5-1.5c.4-.4.4-1 0-1.4l-1.3-1.3z"/>
          <path d="M23.7.3c-.4-.4-1-.4-1.4 0l-3.7 3.7-1.4-1.4c-.4-.4-1-.4-1.4 0s-.4 1 0 1.4l1.4 1.4-8.5 8.5c-.2.2-.3.4-.3.7v3c0 .6.4 1 1 1h3c.3 0 .5-.1.7-.3l8.5-8.5 1.4 1.4c.2.2.4.3.7.3s.5-.1.7-.3c.4-.4.4-1 0-1.4l-1.4-1.4 3.7-3.7c.4-.4.4-1 0-1.4l-1.3-1.3z"/>
        </svg>`,
        image: '<img src="../../img/reactivos.png" alt="Reactivos" class="module-image">',
        url: 'reactivos.php'
      },
      formulaciones: {
        title: 'Formulaciones',
        category: 'Medios de Cultivo',
        description: 'Administra y crea formulaciones personalizadas, medios de cultivo y composiciones químicas específicas para tus experimentos.',
        features: [
          'Biblioteca de formulaciones',
          'Calculadora de concentraciones',
          'Composiciones químicas detalladas'
        ],
        icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="40" height="40">
          <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14z"/>
          <path d="M7 12h10v2H7z"/>
          <path d="M7 8h10v2H7z"/>
          <path d="M7 16h7v2H7z"/>
        </svg>`,
        image: '<img src="../../img/formulaciones.png" alt="Formulaciones" class="module-image">',
        url: 'formulaciones.html'
      },
      protocolos: {
        title: 'Protocolos',
        category: 'Control de Procesos',
        description: 'Gestiona protocolos de desinfección, control sanitario y manejo especializado de explantes vegetales con procedimientos estandarizados.',
        features: [
          'Protocolos paso a paso',
          'Registro de desinfecciones',
          'Control de contaminaciones'
        ],
        icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="40" height="40">
          <path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/>
        </svg>`,
        image: '<img src="../../img/protocolos.png" alt="Protocolos" class="module-image">',
        url: 'protocolo.html'
      },
      categorias: {
        title: 'Categorías',
        category: 'Organización',
        description: 'Organiza y clasifica todos los elementos del sistema mediante categorías personalizadas para una mejor gestión y búsqueda.',
        features: [
          'Categorías personalizables',
          'Sistema de etiquetas',
          'Clasificación jerárquica'
        ],
        icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="40" height="40">
          <path d="M10 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z"/>
        </svg>`,
        image: '<img src="../../img/categorias.png" alt="Categorías" class="module-image">',
        url: 'categoria.html'
      },
      parametros: {
        title: 'Parámetros',
        category: 'Configuración',
        description: 'Configura parámetros del sistema, permisos de usuarios y ajustes generales para personalizar el funcionamiento de la plataforma.',
        features: [
          'Gestión de permisos',
          'Configuración de usuarios',
          'Parámetros del sistema'
        ],
        icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="40" height="40">
          <path d="M19.5 12c0-.23-.01-.45-.03-.68l1.86-1.41c.11-.08.16-.22.12-.35l-1.75-3.03c-.06-.11-.19-.15-.31-.11l-2.19.88c-.48-.36-1.01-.67-1.58-.9l-.33-2.32c-.01-.12-.12-.21-.24-.21h-3.5c-.12 0-.23.09-.25.21l-.33 2.32c-.57.23-1.1.54-1.58.9l-2.19-.88c-.12-.05-.25 0-.31.11l-1.75 3.03c-.04.12 0 .27.12.35l1.86 1.41c-.02.23-.03.45-.03.68s.01.45.03.68l-1.86 1.41c-.11.08-.16.22-.12.35l1.75 3.03c.06.11.19.15.31.11l2.19-.88c.48.36 1.01.67 1.58.9l.33 2.32c.02.12.12.21.25.21h3.5c.12 0 .23-.09.25-.21l.33-2.32c.57-.23 1.1-.54 1.58-.9l2.19.88c.12.05.25 0 .31-.11l1.75-3.03c.04-.12 0-.27-.12-.35l-1.83-1.39c.02-.23.03-.45.03-.68zm-7.5 2.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
          <circle cx="12" cy="12" r="1.5" fill="currentColor"/>
        </svg>`,
        image: '<img src="../../img/parametros.png" alt="Parámetros" class="module-image">',
        url: 'parametros.html'
      },
      historial: {
        title: 'Historial',
        category: 'Auditoría',
        description: 'Revisa el registro completo de todas las actividades, acciones y modificaciones realizadas por los usuarios del sistema.',
        features: [
          'Registro de actividades',
          'Trazabilidad completa',
          'Filtros avanzados de búsqueda'
        ],
        icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="40" height="40">
          <path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/>
        </svg>`,
        image: '<img src="../../img/historial.png" alt="Historial" class="module-image">',
        url: 'administrar.php'
      }
    };

    // Render module preview
    function renderModule(moduleKey) {
      const module = modules[moduleKey];
      const preview = document.getElementById('modulePreview');
      
      const featuresHTML = module.features.map(f => 
        `<div class="feature-item">${f}</div>`
      ).join('');
      
      // Create icon HTML
      const iconHTML = module.icon;
      
      // Create image HTML (only if not the default 404 image)
      const imageHTML = module.image !== '../../assets/errors/404.png' ? module.image : '';
      
      preview.innerHTML = `
        <div class="module-preview-content">
          <div class="module-info">
            <div class="module-header">
              <div class="module-icon-large">
                ${iconHTML}
              </div>
              <div class="module-text">
                <h2>${module.title}</h2>
                <span class="module-category">${module.category}</span>
              </div>
            </div>
            <p class="module-description">${module.description}</p>
            <div class="module-features">
              ${featuresHTML}
            </div>
            <div class="module-actions">
              <a href="${module.url}" class="btn-access">Acceder al módulo</a>
            </div>
          </div>
          <div class="module-visual">
            <div class="decorative-dots">
              <div class="dot"></div>
              <div class="dot"></div>
              <div class="dot"></div>
              <div class="dot"></div>
              <div class="dot"></div>
              <div class="dot"></div>
              <div class="dot"></div>
              <div class="dot"></div>
              <div class="dot"></div>
              <div class="dot"></div>
              <div class="dot"></div>
              <div class="dot"></div>
              <div class="dot"></div>
              <div class="dot"></div>
              <div class="dot"></div>
              <div class="dot"></div>
              <div class="dot"></div>
              <div class="dot"></div>
              <div class="dot"></div>
              <div class="dot"></div>
            </div>
            <div class="module-image-wrapper">
              ${imageHTML}
            </div>
          </div>
        </div>
      `;

      // Trigger animation
      preview.style.animation = 'none';
      setTimeout(() => {
        preview.style.animation = 'fadeInUp 0.5s ease forwards';
      }, 10);
    }

    // Tab navigation
    const tabs = document.querySelectorAll('.nav-tab');
    tabs.forEach(tab => {
      tab.addEventListener('click', () => {
        tabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        renderModule(tab.dataset.module);
      });
    });

    // Load initial module
    renderModule('reactivos');

    // Load stats (simulate with mock data)
    setTimeout(() => {
      const statValues = document.querySelectorAll('.stat-value');
      const mockData = [42, 15, 128, 87, 3];
      statValues.forEach((el, i) => {
        el.textContent = mockData[i];
      });
    }, 500);
  </script>
  <script src="js/header-footer.js"></script>
  <script src="../welcome/welcome-message.js"></script>
  <script>
    // Cargar estadísticas al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
      fetch('includes/get_stats.php')
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Actualizar los valores de las tarjetas
            document.getElementById('total-usuarios').textContent = data.data.total_usuarios || '0';
            document.getElementById('plantas-multiplicacion').textContent = data.data.plantas_multiplicacion || '0';
            document.getElementById('total-reactivos').textContent = data.data.total_reactivos || '0';
            document.getElementById('reactivos-bajos').textContent = data.data.reactivos_bajos || '0';
            document.getElementById('total-plantas').textContent = data.data.total_plantas || '0';
            document.getElementById('total-formulaciones').textContent = data.data.total_formulaciones || '0';
          } else {
            console.error('Error al cargar estadísticas:', data.error);
          }
        })
        .catch(error => {
          console.error('Error al cargar estadísticas:', error);
        });
    });
  </script>
  <script src="../welcome/init-welcome.js"></script>
</body>
</html>