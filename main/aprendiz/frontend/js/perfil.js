// perfil.js - Lógica para la gestión del perfil de usuario
const API_URL = '/invitrosoft/main/aprendiz/backend/perfil.php';

let perfilData = null;

document.addEventListener('DOMContentLoaded', async function() {
    await cargarPerfil();
    await cargarEstadisticas();
    await cargarActividad();
    
    // Event listeners
    document.getElementById('btnEditarPerfil').addEventListener('click', abrirModalEditar);
    document.getElementById('modalEditarClose').addEventListener('click', cerrarModalEditar);
    document.getElementById('btnCancelarEdit').addEventListener('click', cerrarModalEditar);
    document.getElementById('formEditarPerfil').addEventListener('submit', guardarPerfil);
    
    document.getElementById('modalEditarPerfil').addEventListener('click', (e) => {
        if (e.target === e.currentTarget) cerrarModalEditar();
    });
    
    // Preferencias
    document.getElementById('toggleDarkMode').addEventListener('change', toggleDarkMode);
    document.getElementById('toggleNotificaciones').addEventListener('change', toggleNotificaciones);
    document.getElementById('toggleEmailNotif').addEventListener('change', toggleEmailNotif);
    
    // Cargar preferencias guardadas
    cargarPreferencias();
    
    // Cambiar foto
    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = 'image/*';
    fileInput.style.display = 'none';
    fileInput.id = 'perfilUploadHidden';
    document.body.appendChild(fileInput);
    
    document.addEventListener('click', (e) => {
        const t = e.target.closest('#btnCambiarFoto');
        if (t) { 
            e.preventDefault(); 
            fileInput.click(); 
        }
    });
    
    fileInput.addEventListener('change', async () => {
        if (!fileInput.files || !fileInput.files[0]) return;
        
        const fd = new FormData();
        fd.append('avatar', fileInput.files[0]);
        
        try {
            const res = await fetch('/invitrosoft/main/aprendiz/backend/auth/perfil_upload.php', { 
                method: 'POST',
                credentials: 'same-origin',
                body: fd 
            });
            
            let data;
            if (!res.ok) {
                const txt = await res.text();
                throw new Error(`HTTP ${res.status}: ${txt}`);
            }
            
            try { 
                data = await res.json(); 
            } catch (err) { 
                throw new Error('Respuesta no válida del servidor'); 
            }
            
            if (data && data.success) {
                const bust = data.foto + (data.foto.includes('?') ? '&' : '?') + 'v=' + Date.now();
                actualizarAvatarUI(bust);
                mostrarNotificacion('Foto actualizada correctamente', 'success');
            } else {
                mostrarNotificacion((data && data.error) || 'No se pudo actualizar la foto', 'error');
            }
        } catch (error) {
            console.error('Upload error:', error);
            mostrarNotificacion('Error subiendo imagen', 'error');
        } finally {
            fileInput.value = '';
        }
    });
    
    // Formulario cambiar contraseña (en la card de seguridad)
    const formCambiarContrasenaCard = document.getElementById('formCambiarContrasenaCard');
    if (formCambiarContrasenaCard) {
        formCambiarContrasenaCard.addEventListener('submit', cambiarContrasenaCard);
        
        // Listener para mostrar fortaleza mientras se escribe
        const newPasswordCard = document.getElementById('newPasswordCard');
        if (newPasswordCard) {
            newPasswordCard.addEventListener('input', (e) => mostrarFortalezaContrasenaCard(e.target.value));
        }
    }
});

async function cargarPerfil() {
    try {
        const response = await fetch('/invitrosoft/main/aprendiz/backend/auth/perfil_get.php', {
            method: 'GET',
            credentials: 'same-origin'
        });
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const data = await response.json();
        
        if (data.success) {
            perfilData = data.user;
            renderizarPerfil(perfilData);
            if (perfilData.foto) actualizarAvatarUI(perfilData.foto);
        } else {
            mostrarNotificacion('Error al cargar perfil', 'error');
        }
    } catch (error) {
        console.error(error);
        mostrarNotificacion('Error al cargar perfil', 'error');
    }
}

function renderizarPerfil(perfil) {
    // Información principal
    document.getElementById('perfilNombre').textContent = perfil.nombre || 'Usuario';
    document.getElementById('perfilRol').textContent = perfil.tipo ? 
        perfil.tipo.charAt(0).toUpperCase() + perfil.tipo.slice(1) : 'Aprendiz';
    
    // Información personal
    document.getElementById('nombreCompleto').textContent = perfil.nombre || '-';
    document.getElementById('correo').textContent = perfil.email || '-';
    document.getElementById('telefono').textContent = perfil.telefono || '-';
    
    if (perfil.created_at) {
        const fecha = new Date(perfil.created_at);
        document.getElementById('fechaRegistro').textContent = fecha.toLocaleDateString('es-CO', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }
    
    // Información del programa
    document.getElementById('ficha').textContent = perfil.ficha_formacion || '-';
    document.getElementById('programa').textContent = 'Biotecnología Vegetal';
}

// NUEVA FUNCIÓN: Actualizar avatar en UI
function actualizarAvatarUI(fotoUrl) {
    // Actualizar avatar grande en el perfil
    const avatarLarge = document.querySelector('.perfil-avatar-large');
    if (avatarLarge) {
        // Buscar si ya existe una imagen
        let imgElement = avatarLarge.querySelector('img');
        
        if (!imgElement) {
            // Crear nueva imagen si no existe
            imgElement = document.createElement('img');
            imgElement.alt = 'Avatar del usuario';
            avatarLarge.appendChild(imgElement);
        }
        
        // Actualizar la URL con cache busting
        imgElement.src = fotoUrl;
    }
    
    // Actualizar avatar en el header (si existe)
    const headerAvatar = document.querySelector('.user-avatar');
    if (headerAvatar) {
        let imgElement = headerAvatar.querySelector('img');
        
        if (!imgElement) {
            imgElement = document.createElement('img');
            imgElement.alt = 'Avatar del usuario';
            headerAvatar.appendChild(imgElement);
        }
        
        imgElement.src = fotoUrl;
    }
    
    // Actualizar avatar en menú móvil (si existe)
    const mobileAvatar = document.querySelector('.mobile-user-menu .user-avatar');
    if (mobileAvatar) {
        let imgElement = mobileAvatar.querySelector('img');
        
        if (!imgElement) {
            imgElement = document.createElement('img');
            imgElement.alt = 'Avatar del usuario';
            mobileAvatar.appendChild(imgElement);
        }
        
        imgElement.src = fotoUrl;
    }
}

async function cargarEstadisticas() {
    try {
        const response = await fetch(`${API_URL}?action=getEstadisticas`);
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const data = await response.json();
        
        if (data.success) {
            const stats = data.estadisticas;
            
            // Animar contadores
            animarContador('totalPlantas', stats.total_plantas || 0);
            animarContador('fasesCompletadas', stats.fases_completadas || 0);
            animarContador('proyectosActivos', stats.proyectos_activos || 0);
            animarContador('diasActivo', stats.dias_activo || 0);
        }
    } catch (error) {
        console.error(error);
    }
}

async function cargarActividad() {
    try {
        const response = await fetch(`${API_URL}?action=getActividad`);
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const data = await response.json();
        
        if (data.success && data.actividad.length > 0) {
            renderizarActividad(data.actividad);
        } else {
            document.getElementById('actividadReciente').innerHTML = `
                <div class="actividad-item">
                    <div class="actividad-icon">
                        <svg width="20" height="20" fill="currentColor">
                            <circle cx="10" cy="10" r="3"/>
                        </svg>
                    </div>
                    <div class="actividad-content">
                        <p class="actividad-texto">No hay actividad reciente</p>
                        <span class="actividad-fecha">-</span>
                    </div>
                </div>
            `;
        }
    } catch (error) {
        console.error(error);
    }
}

function renderizarActividad(actividades) {
    const contenedor = document.getElementById('actividadReciente');
    contenedor.innerHTML = '';
    
    actividades.forEach(act => {
        const fecha = new Date(act.fecha);
        const fechaRelativa = obtenerFechaRelativa(fecha);
        
        const item = document.createElement('div');
        item.className = 'actividad-item';
        item.innerHTML = `
            <div class="actividad-icon">
                <svg width="20" height="20" fill="currentColor">
                    ${obtenerIconoActividad(act.tipo)}
                </svg>
            </div>
            <div class="actividad-content">
                <p class="actividad-texto">${act.descripcion}</p>
                <span class="actividad-fecha">${fechaRelativa}</span>
            </div>
        `;
        contenedor.appendChild(item);
    });
}

function obtenerIconoActividad(tipo) {
    const iconos = {
        'planta': '<path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z"/>',
        'fase': '<path d="M5 12l5 5 9-9"/>',
        'edicion': '<path d="M4 13.5V16h2.5l7.06-7.06-2.5-2.5L4 13.5zM17.71 7.04a1 1 0 000-1.41l-2.34-2.34a1 1 0 00-1.41 0l-1.13 1.13 3.75 3.75 1.13-1.13z"/>',
        'default': '<circle cx="10" cy="10" r="3"/>'
    };
    return iconos[tipo] || iconos.default;
}

function obtenerFechaRelativa(fecha) {
    const ahora = new Date();
    const diferencia = ahora - fecha;
    const minutos = Math.floor(diferencia / 60000);
    const horas = Math.floor(diferencia / 3600000);
    const dias = Math.floor(diferencia / 86400000);
    
    if (minutos < 1) return 'Justo ahora';
    if (minutos < 60) return `Hace ${minutos} ${minutos === 1 ? 'minuto' : 'minutos'}`;
    if (horas < 24) return `Hace ${horas} ${horas === 1 ? 'hora' : 'horas'}`;
    if (dias < 7) return `Hace ${dias} ${dias === 1 ? 'día' : 'días'}`;
    
    return fecha.toLocaleDateString('es-CO', { day: 'numeric', month: 'short' });
}

function animarContador(elementId, valorFinal) {
    const elemento = document.getElementById(elementId);
    const duracion = 1500;
    const incremento = valorFinal / (duracion / 16);
    let valorActual = 0;
    
    const animacion = setInterval(() => {
        valorActual += incremento;
        if (valorActual >= valorFinal) {
            elemento.textContent = valorFinal;
            clearInterval(animacion);
        } else {
            elemento.textContent = Math.floor(valorActual);
        }
    }, 16);
}

function abrirModalEditar() {
    if (!perfilData) {
        mostrarNotificacion('Datos no cargados', 'error');
        return;
    }
    
    document.getElementById('firstName').value = perfilData.nombre || '';
    document.getElementById('email').value = perfilData.email || '';
    document.getElementById('phone').value = perfilData.telefono || '';
    document.getElementById('bio').value = perfilData.ficha_formacion || '';
    document.getElementById('editPassword').value = '';
    
    document.getElementById('modalEditarPerfil').classList.add('active');
}

function cerrarModalEditar() {
    document.getElementById('modalEditarPerfil').classList.remove('active');
}

async function guardarPerfil(e) {
    e.preventDefault();
    
    const nombre = document.getElementById('firstName').value.trim();
    const correo = document.getElementById('email').value.trim();
    const telefono = document.getElementById('phone').value.trim();
    const ficha = document.getElementById('bio').value.trim();
    const password = document.getElementById('editPassword').value.trim();

    if (!nombre || !correo) {
        mostrarNotificacion('Nombre y correo son requeridos', 'error');
        return;
    }
    
    if (!password) {
        mostrarNotificacion('Debes confirmar tu contraseña para guardar cambios', 'error');
        return;
    }

    try {
        // Primero verificar la contraseña
        const verifyRes = await fetch('/invitrosoft/main/aprendiz/backend/auth/verificar_password.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ password })
        });

        const verifyData = await verifyRes.json();
        if (!verifyData.success) {
            mostrarNotificacion(verifyData.error || 'Contraseña incorrecta', 'error');
            document.getElementById('editPassword').focus();
            return;
        }

        // Si la contraseña es correcta, proceder con la actualización
        const response = await fetch('/invitrosoft/main/aprendiz/backend/auth/perfil_update.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                nombre,
                correo,
                telefono,
                ficha
            })
        });

        const data = await response.json();

        if (data.success) {
            mostrarNotificacion('Perfil actualizado correctamente', 'success');
            document.getElementById('editPassword').value = '';
            cerrarModalEditar();
            await cargarPerfil();
        } else {
            mostrarNotificacion(data.error || 'Error al guardar', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('Error al guardar: ' + error.message, 'error');
    }
}

// Preferencias
function cargarPreferencias() {
    const darkMode = localStorage.getItem('darkMode') === 'enabled';
    const notificaciones = localStorage.getItem('notificaciones') !== 'disabled';
    const emailNotif = localStorage.getItem('emailNotif') === 'enabled';
    
    document.getElementById('toggleDarkMode').checked = darkMode;
    document.getElementById('toggleNotificaciones').checked = notificaciones;
    document.getElementById('toggleEmailNotif').checked = emailNotif;
    
    // Aplicar modo oscuro si está habilitado
    if (darkMode) {
        document.body.classList.add('dark-mode');
    }
}

function toggleDarkMode(e) {
    if (e.target.checked) {
        document.body.classList.add('dark-mode');
        localStorage.setItem('darkMode', 'enabled');
        mostrarNotificacion('Modo oscuro activado', 'success');
    } else {
        document.body.classList.remove('dark-mode');
        localStorage.setItem('darkMode', 'disabled');
        mostrarNotificacion('Modo claro activado', 'success');
    }
}

function toggleNotificaciones(e) {
    if (e.target.checked) {
        localStorage.setItem('notificaciones', 'enabled');
        mostrarNotificacion('Notificaciones activadas', 'success');
    } else {
        localStorage.setItem('notificaciones', 'disabled');
        mostrarNotificacion('Notificaciones desactivadas', 'success');
    }
}

function toggleEmailNotif(e) {
    if (e.target.checked) {
        localStorage.setItem('emailNotif', 'enabled');
        mostrarNotificacion('Notificaciones por email activadas', 'success');
    } else {
        localStorage.setItem('emailNotif', 'disabled');
        mostrarNotificacion('Notificaciones por email desactivadas', 'success');
    }
}

function mostrarNotificacion(mensaje, tipo) {
    // Remover notificaciones anteriores
    const notificacionesAnteriores = document.querySelectorAll('.notification-toast');
    notificacionesAnteriores.forEach(n => n.remove());
    
    const toast = document.createElement('div');
    toast.className = `notification-toast ${tipo}`;
    
    // Icono según el tipo
    const iconos = {
        'success': '<svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12l5 5 9-9"/></svg>',
        'error': '<svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/></svg>',
        'info': '<svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>'
    };
    
    toast.innerHTML = `
        ${iconos[tipo] || iconos.info}
        <span>${mensaje}</span>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('removing');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ============================================
// FUNCIONES PARA CAMBIAR CONTRASEÑA
// ============================================

function mostrarFortalezaContrasenaCard(password) {
    const strengthDiv = document.getElementById('passwordStrengthCard');
    if (!strengthDiv) return;

    let strength = 0;
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;

    const strengths = ['Muy débil', 'Débil', 'Normal', 'Fuerte', 'Muy fuerte'];
    const colors = ['#dc3545', '#fd7e14', '#ffc107', '#17a2b8', '#28a745'];

    strengthDiv.innerHTML = `<small style="color: ${colors[strength] || '#ccc'};">${strengths[strength] || ''}</small>`;
}

async function cambiarContrasenaCard(e) {
    e.preventDefault();

    const currentPassword = document.getElementById('currentPasswordCard').value.trim();
    const newPassword = document.getElementById('newPasswordCard').value.trim();
    const confirmPassword = document.getElementById('confirmPasswordCard').value.trim();
    const errorDiv = document.getElementById('passwordErrorCard');
    const btnGuardar = document.getElementById('btnGuardarContrasena');

    // Validaciones
    if (!currentPassword || !newPassword || !confirmPassword) {
        mostrarErrorContrasenaCard('Todos los campos son requeridos', errorDiv);
        return;
    }

    if (newPassword !== confirmPassword) {
        mostrarErrorContrasenaCard('Las contraseñas no coinciden', errorDiv);
        return;
    }

    if (newPassword.length < 8) {
        mostrarErrorContrasenaCard('La contraseña debe tener al menos 8 caracteres', errorDiv);
        return;
    }

    // Deshabilitar botón y mostrar spinner
    btnGuardar.disabled = true;
    const originalHTML = btnGuardar.innerHTML;
    btnGuardar.innerHTML = '<span class="spinner"></span> Procesando...';

    try {
        const response = await fetch('/invitrosoft/main/aprendiz/backend/auth/cambiar_contraseña.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                currentPassword,
                newPassword,
                confirmPassword
            })
        });

        const data = await response.json();

        if (data.success) {
            mostrarNotificacion('Contraseña cambiada correctamente', 'success');
            document.getElementById('formCambiarContrasenaCard').reset();
            document.getElementById('passwordStrengthCard').innerHTML = '';
        } else {
            mostrarErrorContrasenaCard(data.message || 'Error al cambiar la contraseña', errorDiv);
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarErrorContrasenaCard('Error: ' + error.message, errorDiv);
    } finally {
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = originalHTML;
    }
}

function mostrarErrorContrasenaCard(mensaje, errorDiv) {
    if (!errorDiv) return;
    errorDiv.textContent = mensaje;
    errorDiv.style.display = 'block';
    errorDiv.classList.add('error-shake');
    
    setTimeout(() => {
        errorDiv.classList.remove('error-shake');
    }, 300);
}

// Función global para toggle password
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    if (!input) return;

    const button = input.nextElementSibling;
    const icon = button?.querySelector('svg');

    if (input.type === 'password') {
        input.type = 'text';
        if (icon) {
            icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
        }
    } else {
        input.type = 'password';
        if (icon) {
            icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
        }
    }
}