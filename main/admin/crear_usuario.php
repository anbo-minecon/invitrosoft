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
    <title>Crear Usuario - Invitrosoft</title>
    <!-- Reemplaza el <style> interno con este link -->
    <link rel="stylesheet" href="css/crear_usuario.css">
    <link rel="stylesheet" href="css/header-footer.css">
    <link rel="stylesheet" href="css/dark-mode.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="brand">
                <div class="brand-icon">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg>
                </div>
                <div class="brand-info">
                    <h1>Creación de Usuarios</h1>
                    <p>Gestiona usuarios para el sistema Invitrosoft</p>
                </div>
            </div>
            <a href="index.php" class="btn-back" id="btnVolverHeader">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
                </svg>
                Volver al Panel
            </a>
        </div>

        <!-- User Type Selection Panel -->
        <section class="user-type-panel" id="user-type-panel">
            <h2 class="user-type-title">Selecciona el Tipo de Usuario</h2>
            <p class="user-type-subtitle">Elige el rol que deseas asignar al nuevo usuario</p>
            
            <div class="user-type-options">
                <button class="user-type-btn" data-type="admin">
                    <div class="user-type-icon">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                        </svg>
                    </div>
                    <h3>Administrador</h3>
                    <p>Acceso total a la plataforma con permisos de gestión completos</p>
                </button>

                <button class="user-type-btn" data-type="aprendiz">
                    <div class="user-type-icon">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/>
                        </svg>
                    </div>
                    <h3>Aprendiz</h3>
                    <p>Acceso a gestión de fases de micropropagación y funciones básicas</p>
                </button>

                <button class="user-type-btn" data-type="pasante">
                    <div class="user-type-icon">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M20 6h-4V4c0-1.11-.89-2-2-2h-4c-1.11 0-2 .89-2 2v2H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-6 0h-4V4h4v2z"/>
                        </svg>
                    </div>
                    <h3>Pasante</h3>
                    <p>Acceso temporal a gestión de fases de micropropagación</p>
                </button>
            </div>
        </section>

        <!-- User Form Panel -->
        <section id="user-form-panel" class="hidden"></section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const userTypePanel = document.getElementById('user-type-panel');
            const userFormPanel = document.getElementById('user-form-panel');
            const btnVolverHeader = document.getElementById('btnVolverHeader');
            let generos = [];

            async function cargarGeneros() {
                try {
                    const res = await fetch('db/parametros.php?accion=listar&tipo=genero');
                    generos = await res.json();
                } catch (err) {
                    console.error('Error cargando géneros:', err);
                    generos = [];
                }
            }

            document.querySelectorAll('.user-type-btn').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const tipo = btn.dataset.type;
                    await cargarGeneros();
                    mostrarFormulario(tipo);
                });
            });

            function mostrarFormulario(tipo) {
                userTypePanel.classList.add('hidden');
                userFormPanel.classList.remove('hidden');
                userFormPanel.className = 'user-form-panel';

                let extraCampos = '';
                if (tipo === 'aprendiz') {
                    extraCampos = `
                        <div class="form-group">
                            <label>
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/>
                                </svg>
                                Tiempo de Uso
                            </label>
                            <input type="text" id="tiempo_uso" name="tiempo_uso" placeholder="Ej: 6 meses" required>
                        </div>
                        <div class="form-group">
                            <label>
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                                </svg>
                                Ficha de Formación
                            </label>
                            <input type="text" id="ficha_formacion" name="ficha_formacion" placeholder="Número de ficha" required>
                        </div>
                    `;
                } else if (tipo === 'pasante') {
                    extraCampos = `
                        <div class="form-group full-width">
                            <label>
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/>
                                </svg>
                                Tiempo de Uso
                            </label>
                            <input type="text" id="tiempo_uso" name="tiempo_uso" placeholder="Ej: 3 meses" required>
                        </div>
                    `;
                }

                const tipoNombre = tipo.charAt(0).toUpperCase() + tipo.slice(1);

                userFormPanel.innerHTML = `
                    <form class="user-form" id="userForm">
                        <h2>Registro de ${tipoNombre}</h2>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label>
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                    </svg>
                                    Género
                                </label>
                                <select id="genero" name="genero" required>
                                    <option value="">Seleccione un género</option>
                                    ${generos.map(g => `<option value="${g.id_parametro}">${g.nombre}</option>`).join('')}
                                </select>
                            </div>

                            <div class="form-group">
                                <label>
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 1.99 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4V8l8 5 8-5v10zm-8-7L4 6h16l-8 5z"/>
                                    </svg>
                                    Número de Identidad
                                </label>
                                <input type="text" id="identidad" name="identidad" placeholder="123456789" required>
                            </div>

                            <div class="form-group full-width">
                                <label>
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                                    </svg>
                                    Nombre Completo
                                </label>
                                <input type="text" id="nombre" name="nombre" placeholder="Juan Pérez García" required>
                            </div>

                            <div class="form-group">
                                <label>
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
                                    </svg>
                                    Teléfono
                                </label>
                                <input type="text" id="telefono" name="telefono" placeholder="+57 300 123 4567" required>
                            </div>

                            <div class="form-group">
                                <label>
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.89 2 1.99 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                                </svg>
                                    Email
                                </label>
                                <input type="email" id="email" name="email" placeholder="usuario@ejemplo.com" required>
                            </div>

                            <div class="form-group">
                                <label>
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
                                    </svg>
                                    Contraseña
                                </label>
                                <input type="password" id="password" name="password" placeholder="••••••••" required>
                            </div>

                            <div class="form-group">
                                <label>
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
                                    </svg>
                                    Repetir Contraseña
                                </label>
                                <input type="password" id="password2" name="password2" placeholder="••••••••" required>
                            </div>

                            ${extraCampos}
                        </div>

                        <div class="form-actions">
                            <button type="button" onclick="location.reload()" class="btn btn-secondary">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
                                </svg>
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                </svg>
                                Registrar Usuario
                            </button>
                        </div>

                        <div id="notificacion" class="notification"></div>
                    </form>
                `;

                document.getElementById('userForm').addEventListener('submit', enviarFormulario);
            }

            async function enviarFormulario(e) {
                e.preventDefault();
                const form = e.target;
                const data = Object.fromEntries(new FormData(form));
                const notificacion = document.getElementById('notificacion');

                if (data.password !== data.password2) {
                    notificacion.className = 'notification error';
                    notificacion.innerHTML = `
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                        </svg>
                        Las contraseñas no coinciden
                    `;
                    return;
                }

                let tipo = 'admin';
                if (data.ficha_formacion) tipo = 'aprendiz';
                else if (data.tiempo_uso) tipo = 'pasante';
                data.tipo = tipo;

                try {
                    const res = await fetch('db/crear_usuario.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(data)
                    });
                    let resultText = await res.text();
                    let result;
                    try {
                      result = JSON.parse(resultText);
                    } catch (err) {
                      notificacion.className = 'notification error';
                      notificacion.innerText = 'Respuesta inválida del servidor. Revisa la consola.';
                      console.error(resultText);
                      return;
                    }
                    
                    if (res.ok && result.success) {
                        notificacion.className = 'notification success';
                        notificacion.innerHTML = `
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                            </svg>
                            Usuario registrado correctamente. Redirigiendo...
                        `;
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        notificacion.className = 'notification error';
                        notificacion.innerHTML = `
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                            </svg>
                            ${result.error || 'Error al registrar el usuario'}
                        `;
                    }
                } catch (err) {
                    notificacion.className = 'notification error';
                    notificacion.innerHTML = `
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                        </svg>
                        Error de conexión con el servidor
                    `;
                }
            }
        });
    </script>
    <script src="js/dark-mode.js"></script>
</body>
</html>