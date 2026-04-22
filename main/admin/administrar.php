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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar - Invitrosoft</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/header-footer.css">
    <link rel="stylesheet" href="css/administrar.css">
</head>
<body class="bg-gray-50">
    <div class="admin-layout">
        <!-- Sidebar Menu -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <i class="fas fa-cog"></i>
                <span class="sidebar-title">Administrar</span>
            </div>
            <nav class="sidebar-nav">
                <a href="#historial" class="sidebar-item active" data-section="historial">
                    <i class="fas fa-history"></i>
                    <span>Historial</span>
                </a>
                <a href="#usuarios" class="sidebar-item" data-section="usuarios">
                    <i class="fas fa-users"></i>
                    <span>Usuarios</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-content">
            <!-- Sección Historial -->
            <section id="historial-section" class="content-section active">
                <div class="section-header">
                    <div>
                        <h1 class="section-title">Historial de Actividades</h1>
                        <p class="section-subtitle">Registro detallado de todas las acciones en el sistema</p>
                    </div>
                    <button id="refreshBtn" class="btn-icon">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>

                <!-- Filtros -->
                <div class="filters-card">
                    <div class="filters-grid">
                        <div>
                            <label class="filter-label">Tipo de actividad</label>
                            <select id="filterType" class="filter-select">
                                <option value="">Todos los tipos</option>
                                <option value="success">Éxito</option>
                                <option value="warning">Advertencia</option>
                                <option value="error">Error</option>
                                <option value="info">Información</option>
                            </select>
                        </div>
                        <div>
                            <label class="filter-label">Usuario</label>
                            <select id="filterUser" class="filter-select">
                                <option value="">Todos los usuarios</option>
                            </select>
                        </div>
                        <div>
                            <label class="filter-label">Fecha</label>
                            <input type="date" id="filterDate" class="filter-select">
                        </div>
                    </div>
                    <div class="filters-actions">
                        <button id="resetFilters" class="btn-secondary">
                            <i class="fas fa-times"></i> Limpiar
                        </button>
                        <button id="applyFilters" class="btn-primary">
                            <i class="fas fa-filter"></i> Aplicar
                        </button>
                    </div>
                </div>

                <!-- Contenedor de actividades -->
                <div id="activitiesContainer" class="activities-container">
                    <div class="loading">
                        <div class="spinner"></div>
                    </div>
                </div>

                <!-- Botón de cargar más -->
                <div id="loadMoreContainer" class="load-more-container hidden">
                    <button id="loadMoreBtn" class="btn-load-more">
                        Cargar más actividades
                    </button>
                </div>
            </section>

            <!-- Sección Usuarios -->
            <section id="usuarios-section" class="content-section">
                <div class="section-header">
                    <div>
                        <h1 class="section-title">Gestión de Usuarios</h1>
                        <p class="section-subtitle">Administra los usuarios del sistema</p>
                    </div>
                    <button id="refreshUsersBtn" class="btn-icon">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>

                <!-- Barra de búsqueda -->
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchUsers" placeholder="Buscar usuarios por nombre, email o cédula...">
                </div>

                <!-- Tabla de usuarios -->
                <div id="usersContainer" class="users-container">
                    <div class="loading">
                        <div class="spinner"></div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Modal Editar Usuario -->
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Editar Usuario</h2>
                <button class="modal-close" id="closeEditModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" id="editUserId">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Nombre Completo</label>
                            <input type="text" id="editUserName" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" id="editUserEmail" required>
                        </div>
                        <div class="form-group">
                            <label>Teléfono</label>
                            <input type="tel" id="editUserPhone">
                        </div>
                        <div class="form-group">
                            <label>Tipo de Usuario</label>
                            <select id="editUserType" required>
                                <option value="admin">Administrador</option>
                                <option value="aprendiz">Aprendiz</option>
                                <option value="pasante">Pasante</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn-secondary" id="cancelEdit">Cancelar</button>
                        <button type="submit" class="btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="js/header-footer.js"></script>
    <script src="js/administrar.js"></script>
</body>
</html>