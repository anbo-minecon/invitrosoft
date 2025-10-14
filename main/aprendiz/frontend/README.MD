# Sistema de Componentes - Invitro Soft

Sistema modular de componentes reutilizables para la aplicación web de gestión de plantas in vitro.

## 📁 Estructura de Archivos

```
proyecto/
├── header.js          # Componente de cabecera
├── sidebar.js         # Componente de menú lateral
├── modal.js           # Componente de modal para nueva planta
├── styles.css         # Estilos base del sistema
├── dashboard.html     # Ejemplo: Panel principal
└── plantas.html       # Ejemplo: Lista de plantas
```

## 🚀 Uso Básico

### 1. Incluir archivos necesarios

En cualquier página HTML, incluye los componentes que necesites:

```html
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mi Página</title>
  <!-- Estilos base -->
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <!-- Contenedores para los componentes -->
  <div id="header-container"></div>
  <div id="sidebar-container"></div>
  
  <!-- Tu contenido aquí -->
  <main class="main-content">
    <h1>Contenido de la página</h1>
  </main>

  <!-- Scripts de componentes -->
  <script src="header.js"></script>
  <script src="sidebar.js"></script>
  <script src="modal.js"></script> <!-- Opcional -->
  
  <!-- Inicialización -->
  <script>
    // Inicializar header
    const header = new Header();
    document.getElementById('header-container').innerHTML = header.render();
    header.updatePageTitle('Mi Página');
    
    // Inicializar sidebar
    const sidebar = new Sidebar();
    document.getElementById('sidebar-container').innerHTML = sidebar.render();
    sidebar.setActivePage('panel'); // 'panel', 'plantas', 'estadisticas', 'notificaciones'
    sidebar.initEventListeners();
  </script>
</body>
</html>
```

## 📋 Componentes Disponibles

### Header

Barra superior con logo, título de página, búsqueda, notificaciones y menú de usuario.

**Métodos:**
```javascript
const header = new Header();

// Renderizar el componente
header.render(); // Retorna HTML string

// Actualizar título de la página
header.updatePageTitle('Nuevo Título');
```

### Sidebar

Menú lateral de navegación con 4 secciones principales.

**Métodos:**
```javascript
const sidebar = new Sidebar();

// Renderizar el componente
sidebar.render(); // Retorna HTML string

// Establecer página activa
sidebar.setActivePage('plantas'); // 'panel', 'plantas', 'estadisticas', 'notificaciones'

// Inicializar eventos (siempre llamar después de renderizar)
sidebar.initEventListeners();
```

**Eventos:**
```javascript
// Escuchar cambios de página
window.addEventListener('pageChange', (e) => {
  console.log('Nueva página:', e.detail.pageId);
  // Aquí puedes manejar la navegación
});
```

### Modal de Nueva Planta

Modal para crear nuevas plantas con formulario completo.

**Métodos:**
```javascript
const plantaModal = new PlantaModal();

// Renderizar el modal (agregar al body)
document.body.insertAdjacentHTML('beforeend', plantaModal.render());
document.head.insertAdjacentHTML('beforeend', plantaModal.getStyles());

// Abrir modal
plantaModal.open();

// Cerrar modal
plantaModal.close();

// Guardar (se llama automáticamente desde el botón)
plantaModal.save();
```

**Eventos:**
```javascript
// Escuchar cuando se crea una planta
window.addEventListener('plantaCreated', (e) => {
  console.log('Planta creada:', e.detail);
  // e.detail contiene todos los datos del formulario
});
```

**Ejemplo de uso completo:**
```html
<button onclick="plantaModal.open()">Crear Planta</button>

<script>
  // Inicializar modal
  const plantaModal = new PlantaModal();
  document.body.insertAdjacentHTML('beforeend', plantaModal.render());
  document.head.insertAdjacentHTML('beforeend', plantaModal.getStyles());
  
  // Escuchar creación de plantas
  window.addEventListener('plantaCreated', (e) => {
    console.log('Nueva planta:', e.detail);
    // Aquí puedes:
    // - Enviar datos al servidor
    // - Actualizar la lista de plantas
    // - Mostrar notificación de éxito
  });
</script>
```

## 🎨 Personalización de Estilos

### Variables CSS principales

Puedes personalizar los colores principales agregando estas variables en tu CSS:

```css
:root {
  --color-primary: #2d7a3e;
  --color-primary-dark: #236230;
  --color-success: #27ae60;
  --color-warning: #ff9800;
  --color-danger: #e74c3c;
  --color-info: #2196f3;
  --border-radius: 6px;
}
```

### Clases CSS útiles

- `.main-content` - Contenedor principal de contenido (ya incluye márgenes para header y sidebar)
- `.card` - Tarjeta con sombra y bordes redondeados
- `.btn-primary` - Botón primario verde
- `.btn-icon` - Botón circular con icono

## 📱 Responsive

Los componentes son completamente responsive:

- **Desktop:** Sidebar visible, header completo
- **Tablet:** Sidebar colapsable, elementos compactos
- **Mobile:** Sidebar oculto (toggle), búsqueda simplificada

## 🔧 Ejemplos de Páginas

### Página de Dashboard

```html
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div id="header-container"></div>
  <div id="sidebar-container"></div>
  
  <main class="main-content">
    <!-- Cards de métricas -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
      <div class="card">
        <h3>Total Plantas</h3>
        <div style="font-size: 36px; font-weight: bold;">6</div>
      </div>
    </div>
  </main>

  <script src="header.js"></script>
  <script src="sidebar.js"></script>
  <script>
    const header = new Header();
    const sidebar = new Sidebar();
    
    document.getElementById('header-container').innerHTML = header.render();
    document.getElementById('sidebar-container').innerHTML = sidebar.render();
    
    header.updatePageTitle('Panel Principal');
    sidebar.setActivePage('panel');
    sidebar.initEventListeners();
  </script>
</body>
</html>
```

### Página de Plantas

```html
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mis Plantas</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div id="header-container"></div>
  <div id="sidebar-container"></div>
  
  <main class="main-content">
    <button class="btn-primary" onclick="plantaModal.open()">
      Crear Planta
    </button>
    
    <!-- Lista de plantas aquí -->
  </main>

  <script src="header.js"></script>
  <script src="sidebar.js"></script>
  <script src="modal.js"></script>
  <script>
    const header = new Header();
    const sidebar = new Sidebar();
    const plantaModal = new PlantaModal();
    
    document.getElementById('header-container').innerHTML = header.render();
    document.getElementById('sidebar-container').innerHTML = sidebar.render();
    document.body.insertAdjacentHTML('beforeend', plantaModal.render());
    document.head.insertAdjacentHTML('beforeend', plantaModal.getStyles());
    
    header.updatePageTitle('Mis Plantas');
    sidebar.setActivePage('plantas');
    sidebar.initEventListeners();
    
    window.addEventListener('plantaCreated', (e) => {
      alert('Planta creada: ' + e.detail.nombre);
    });
  </script>
</body>
</html>
```

## 🎯 Características

### ✅ Ventajas del Sistema

1. **Modular:** Importa solo lo que necesites
2. **Reutilizable:** Usa los mismos componentes en todas las páginas
3. **Fácil mantenimiento:** Actualiza un archivo y se refleja en todas las páginas
4. **Sin dependencias:** JavaScript vanilla, no requiere frameworks
5. **Ligero:** Componentes optimizados y eficientes
6. **SVG Icons:** Todos los iconos son SVG inline (no requieren archivos externos)

### 🔒 Sin localStorage/sessionStorage

Los componentes NO usan `localStorage` ni `sessionStorage` por compatibilidad con el entorno de Claude.ai. Toda la información se mantiene en memoria durante la sesión.

## 📝 Notas Importantes

1. **Orden de carga:** Siempre carga los scripts de componentes antes de inicializarlos
2. **Event Listeners:** Llama a `initEventListeners()` después de renderizar el sidebar
3. **Título de página:** Usa `updatePageTitle()` en cada página para mostrar el título correcto
4. **Página activa:** Usa `setActivePage()` para marcar la sección actual del menú

## 🐛 Troubleshooting

### El sidebar no responde a clicks
```javascript
// Asegúrate de llamar initEventListeners() después de renderizar
sidebar.render();
sidebar.initEventListeners(); // ← No olvides esto
```

### El modal no se muestra
```javascript
// Verifica que hayas agregado tanto el HTML como los estilos
document.body.insertAdjacentHTML('beforeend', plantaModal.render());
document.head.insertAdjacentHTML('beforeend', plantaModal.getStyles()); // ← Importante
```

### Los estilos no se aplican correctamente
```html
<!-- Asegúrate de incluir styles.css -->
<link rel="stylesheet" href="styles.css">
```

## 🚀 Próximas Mejoras

Posibles mejoras para implementar:

- [ ] Sistema de notificaciones toast
- [ ] Componente de tabla reutilizable
- [ ] Filtros avanzados para listas
- [ ] Sistema de paginación
- [ ] Modo oscuro
- [ ] Exportación de datos (CSV, PDF)
- [ ] Gráficos interactivos

## 📞 Soporte

Para dudas o problemas con los componentes, revisa primero los ejemplos incluidos (`dashboard.html` y `plantas.html`).

---

**Invitro Soft © 2025** - Sistema de gestión de plantas in vitro