<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /invitrosoft/src/index.html');
    exit;
}
// Cache busting for CSS/JS
$cssPathWeb = '../styles/perfil.css';
$cssPathFs = __DIR__ . '/../styles/perfil.css';
$jsPathWeb = '../js/perfil.js';
$jsPathFs = __DIR__ . '/../js/perfil.js';
$cssVer = file_exists($cssPathFs) ? filemtime($cssPathFs) : time();
$jsVer = file_exists($jsPathFs) ? filemtime($jsPathFs) : time();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil - Invitrosoft</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo $cssPathWeb . '?v=' . $cssVer; ?>">
</head>
<body>
    <!-- Input hidden para subir avatar -->
    <input type="file" id="avatarUpload" style="display: none;" accept="image/*">
    
    <main class="main-panel">
        <section class="panel-header">
            <h1>Mi Perfil</h1>
            <button class="btn-primary" id="btnEditarPerfil">
                <svg width="20" height="20" fill="currentColor">
                    <path d="M4 13.5V16h2.5l7.06-7.06-2.5-2.5L4 13.5zM17.71 7.04a1 1 0 000-1.41l-2.34-2.34a1 1 0 00-1.41 0l-1.13 1.13 3.75 3.75 1.13-1.13z"/>
                </svg>
                Editar Perfil
            </button>
        </section>

        <div class="perfil-container">
            <!-- Card principal del perfil -->
            <div class="perfil-card perfil-main">
                <div class="perfil-header">
                    <div class="perfil-avatar-section">
                        <div class="perfil-avatar-large">
                            <svg width="120" height="120" viewBox="0 0 120 120" fill="none">
                                <circle cx="60" cy="60" r="60" fill="#e8e8e8"/>
                                <path d="M60 65c-13 0-23 7-23 13v7h46v-7c0-6-10-13-23-13zm0-7a13 13 0 100-26 13 13 0 000 26z" fill="#007832"/>
                            </svg>
                            <button class="btn-cambiar-foto" id="btnCambiarFoto">
                                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 9a2 2 0 012-2h.93a2 2 0 001.66-.9l.82-1.2A2 2 0 0110.07 4h3.86a2 2 0 011.66.9l.82 1.2a2 2 0 001.66.9H19a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                    <circle cx="12" cy="13" r="3"/>
                                </svg>
                            </button>
                        </div>
                        <div class="perfil-info-principal">
                            <h2 class="perfil-nombre" id="perfilNombre">Cargando...</h2>
                            <p class="perfil-rol" id="perfilRol">Aprendiz</p>
                            <div class="perfil-badges">
                                <span class="badge badge-activo">
                                    <svg width="16" height="16" fill="currentColor">
                                        <circle cx="8" cy="8" r="3"/>
                                    </svg>
                                    Activo
                                </span>
                                <span class="badge badge-verificado">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M3 8l3 3 6-6"/>
                                    </svg>
                                    Verificado
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="perfil-stats">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z"/>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-value" id="totalPlantas">0</span>
                            <span class="stat-label">Plantas Registradas</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M5 12l5 5 9-9"/>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-value" id="fasesCompletadas">0</span>
                            <span class="stat-label">Fases Completadas</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2v20M2 12h20"/>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-value" id="proyectosActivos">0</span>
                            <span class="stat-label">Proyectos Activos</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="16" rx="2"/>
                                <path d="M3 8h18"/>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-value" id="diasActivo">0</span>
                            <span class="stat-label">Días Activo</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grid de información -->
            <div class="perfil-grid">
                <!-- Información Personal -->
                <div class="perfil-card">
                    <div class="card-header-perfil">
                        <h3>Información Personal</h3>
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <div class="card-body-perfil">
                        <div class="info-row">
                            <span class="info-label">Nombre Completo</span>
                            <span class="info-value" id="nombreCompleto">-</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Correo Electrónico</span>
                            <span class="info-value" id="correo">-</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Teléfono</span>
                            <span class="info-value" id="telefono">-</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Fecha de Registro</span>
                            <span class="info-value" id="fechaRegistro">-</span>
                        </div>
                    </div>
                </div>

                <!-- Información del Programa -->
                <div class="perfil-card">
                    <div class="card-header-perfil">
                        <h3>Programa de Formación</h3>
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2V3z"/>
                            <path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7V3z"/>
                        </svg>
                    </div>
                    <div class="card-body-perfil">
                        <div class="info-row">
                            <span class="info-label">Programa</span>
                            <span class="info-value" id="programa">Biotecnología Vegetal</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Ficha</span>
                            <span class="info-value" id="ficha">-</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Centro de Formación</span>
                            <span class="info-value" id="centro">SENA - Regional</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Instructor Líder</span>
                            <span class="info-value" id="instructor">-</span>
                        </div>
                    </div>
                </div>

                <!-- Actividad Reciente -->
                <div class="perfil-card perfil-actividad">
                    <div class="card-header-perfil">
                        <h3>Actividad Reciente</h3>
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 6v6l4 2"/>
                        </svg>
                    </div>
                    <div class="card-body-perfil">
                        <div class="actividad-lista" id="actividadReciente">
                            <div class="actividad-item">
                                <div class="actividad-icon">
                                    <svg width="20" height="20" fill="currentColor">
                                        <circle cx="10" cy="10" r="3"/>
                                    </svg>
                                </div>
                                <div class="actividad-content">
                                    <p class="actividad-texto">Cargando actividad...</p>
                                    <span class="actividad-fecha">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preferencias -->
                <div class="perfil-card">
                    <div class="card-header-perfil">
                        <h3>Preferencias</h3>
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M12 1v6m0 6v6m5.66-16.66l-4.24 4.24m-2.83 2.83l-4.24 4.24M23 12h-6m-6 0H1m16.66 5.66l-4.24-4.24m-2.83-2.83l-4.24-4.24"/>
                        </svg>
                    </div>
                    <div class="card-body-perfil">
                        <div class="preferencia-item">
                            <div class="preferencia-info">
                                <span class="preferencia-label">Modo Oscuro</span>
                                <span class="preferencia-desc">Tema visual del sistema</span>
                            </div>
                            <label class="switch">
                                <input type="checkbox" id="toggleDarkMode">
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div class="preferencia-item">
                            <div class="preferencia-info">
                                <span class="preferencia-label">Notificaciones</span>
                                <span class="preferencia-desc">Recibir alertas del sistema</span>
                            </div>
                            <label class="switch">
                                <input type="checkbox" id="toggleNotificaciones" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div class="preferencia-item">
                            <div class="preferencia-info">
                                <span class="preferencia-label">Notificaciones Email</span>
                                <span class="preferencia-desc">Recibir correos de actualización</span>
                            </div>
                            <label class="switch">
                                <input type="checkbox" id="toggleEmailNotif">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Seguridad -->
                <div class="perfil-card perfil-seguridad">
                    <div class="card-header-perfil">
                        <h3>Seguridad</h3>
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                    </div>
                    <div class="card-body-perfil">
                        <form id="formCambiarContrasenaCard" class="password-change-form">
                            <div class="form-group">
                                <label for="currentPasswordCard">Contraseña Actual</label>
                                <div class="password-input-wrapper">
                                    <input type="password" id="currentPasswordCard" class="form-input" placeholder="••••••••" required>
                                    <button type="button" class="toggle-password" onclick="togglePasswordVisibility('currentPasswordCard')">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="newPasswordCard">Nueva Contraseña</label>
                                <div class="password-input-wrapper">
                                    <input type="password" id="newPasswordCard" class="form-input" placeholder="••••••••" required>
                                    <button type="button" class="toggle-password" onclick="togglePasswordVisibility('newPasswordCard')">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </button>
                                </div>
                                <div id="passwordStrengthCard" class="password-strength"></div>
                            </div>

                            <div class="form-group">
                                <label for="confirmPasswordCard">Confirmar Contraseña</label>
                                <div class="password-input-wrapper">
                                    <input type="password" id="confirmPasswordCard" class="form-input" placeholder="••••••••" required>
                                    <button type="button" class="toggle-password" onclick="togglePasswordVisibility('confirmPasswordCard')">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div id="passwordErrorCard" class="password-error" style="display: none;"></div>

                            <div class="form-actions">
                                <button type="submit" class="btn-primary btn-small" id="btnGuardarContrasena">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 1C6.48 1 2 5.48 2 11s4.48 10 10 10 10-4.48 10-10S17.52 1 12 1zm-2 15l-5-5 1.41-1.41L10 13.17l7.59-7.59L19 7l-9 9z"/>
                                    </svg>
                                    Cambiar Contraseña
                                </button>
                                <button type="reset" class="btn-secondary btn-small">Limpiar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Editar Perfil -->
    <div class="modal-overlay" id="modalEditarPerfil">
        <div class="modal">
            <div class="modal-header">
                <h2>Editar Perfil</h2>
                <button type="button" class="modal-close" id="modalEditarClose">&times;</button>
            </div>
            <form id="formEditarPerfil">
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstName">Nombre Completo *</label>
                            <input type="text" id="firstName" name="nombre" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Correo Electrónico *</label>
                            <input type="email" id="email" name="correo" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">Teléfono</label>
                            <input type="tel" id="phone" name="telefono">
                        </div>
                        <div class="form-group">
                            <label for="bio">Ficha</label>
                            <input type="text" id="bio" name="ficha">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group" style="flex: 1;">
                            <label for="editPassword">Confirmar Contraseña *</label>
                            <div class="password-input-wrapper">
                                <input type="password" id="editPassword" name="password" placeholder="••••••••" required>
                                <button type="button" class="toggle-password" onclick="togglePasswordVisibility('editPassword')">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>
                            </div>
                            <small style="color: #999; font-size: 0.85rem; display: block; margin-top: 4px;">Ingresa tu contraseña para confirmar cambios</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" id="btnCancelarEdit">Cancelar</button>
                    <button type="submit" class="btn-primary">
                        <svg width="18" height="18" fill="currentColor">
                            <path d="M2 10 L7 15 L16 3" stroke="currentColor" stroke-width="2" fill="none"/>
                        </svg>
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="<?php echo $jsPathWeb . '?v=' . $jsVer; ?>"></script>
</body>
</html>