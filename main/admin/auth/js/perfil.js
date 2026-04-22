class PerfilManager {
    constructor() {
        this.userId = document.body.dataset.userId || 0;
        this.avatarInput = document.getElementById('avatar-upload');
        this.avatarPreview = document.querySelector('.avatar-preview img');
        this.avatarInitials = document.querySelector('.avatar-initials');
        
        // Modal elements
        this.modal = document.getElementById('passwordModal');
        this.passwordInput = document.getElementById('modalPassword');
        this.passwordError = document.getElementById('passwordError');
        this.currentAction = null;
        this.formData = null;
        
        this.initEventListeners();
        this.cargarPerfil();
        this.loadStats();
        this.initModalEvents();
    }

    initEventListeners() {
        if (this.avatarInput) {
            this.avatarInput.addEventListener('change', (e) => this.subirFoto(e));
        }

        // Profile form submit - CON modal de confirmación
        const profileForm = document.getElementById('profileForm');
        if (profileForm) {
            profileForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.prepararActualizacionPerfil();
            });
        }

        // Password change button - SIN modal de confirmación
        const changePasswordBtn = document.querySelector('button[onclick="changePassword()"]');
        if (changePasswordBtn) {
            changePasswordBtn.removeAttribute('onclick');
            changePasswordBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.cambiarContrasena();
            });
        }
    }

    async cargarPerfil() {
        try {
            const response = await fetch('perfil_datos.php');
            const data = await response.json();
            
            if (data.success) {
                this.actualizarUI(data.usuario);
                return data.usuario;
            } else {
                throw new Error(data.error || 'Error al cargar el perfil');
            }
        } catch (error) {
            console.error('Error al cargar el perfil:', error);
            this.mostrarNotificacion('Error al cargar el perfil: ' + error.message, 'error');
            throw error;
        }
    }

    actualizarUI(usuario) {
        if (!usuario) return;
        
        const nombreCompleto = usuario.nombre_completo || usuario.nombre || '';
        
        const updateElement = (selector, value, defaultValue = '') => {
            const element = document.querySelector(selector);
            if (element) {
                element.textContent = value || defaultValue;
            }
        };
        
        updateElement('.profile-name', nombreCompleto);
        updateElement('.profile-email', usuario.email);
        updateElement('.profile-phone', usuario.telefono, 'No especificado');
        updateElement('.profile-role', usuario.rol ? usuario.rol.charAt(0).toUpperCase() + usuario.rol.slice(1) : '');
        updateElement('.profile-join-date', usuario.fecha_registro);

        // Actualizar campos del formulario
        const firstNameInput = document.getElementById('firstName');
        const emailInput = document.getElementById('email');
        const phoneInput = document.getElementById('phone');
        const bioInput = document.getElementById('bio');

        if (firstNameInput) firstNameInput.value = usuario.nombre || '';
        if (emailInput) emailInput.value = usuario.email || '';
        if (phoneInput) phoneInput.value = usuario.telefono || '';
        if (bioInput) bioInput.value = usuario.bibliografia || '';

        this.actualizarAvatar(usuario.foto_url || usuario.foto, usuario.nombre);
        this.actualizarAvatarSidebar(usuario.foto_url || usuario.foto, nombreCompleto);
    }
    
    actualizarAvatarSidebar(fotoUrl, nombreCompleto) {
        const sidebarAvatar = document.querySelector('.user-avatar');
        if (!sidebarAvatar) return;
        
        const sidebarImg = sidebarAvatar.querySelector('img');
        const sidebarInitials = sidebarAvatar.querySelector('.user-initials, .initials');
        
        if (fotoUrl) {
            const cacheBuster = '?t=' + new Date().getTime();
            const url = fotoUrl + (fotoUrl.includes('?') ? '&' : '?') + cacheBuster;
            
            if (sidebarImg) {
                sidebarImg.src = url;
                sidebarImg.alt = `Foto de perfil de ${nombreCompleto}`;
                sidebarImg.style.display = 'block';
            }
            
            if (sidebarInitials) {
                sidebarInitials.style.display = 'none';
            }
            
            if (sidebarImg) {
                sidebarImg.onerror = () => {
                    sidebarImg.style.display = 'none';
                    if (sidebarInitials) {
                        this.mostrarInicialesSidebar(sidebarInitials, nombreCompleto);
                    }
                };
            }
        } else if (sidebarInitials) {
            this.mostrarInicialesSidebar(sidebarInitials, nombreCompleto);
        }
    }
    
    mostrarInicialesSidebar(element, nombreCompleto) {
        if (!element) return;
        
        element.style.display = 'flex';
        
        if (nombreCompleto) {
            const initials = nombreCompleto
                .split(' ')
                .map(nombre => nombre[0])
                .join('')
                .toUpperCase()
                .substring(0, 2);
                
            element.textContent = initials || 'US';
        } else {
            element.textContent = 'US';
        }
    }

    actualizarAvatar(fotoUrl, nombre) {
        const avatarImg = document.getElementById('profile-avatar-img');
        if (!avatarImg || !this.avatarInitials) return;
        
        if (!fotoUrl) {
            this.mostrarIniciales(nombre);
            return;
        }

        const cacheBuster = '?t=' + new Date().getTime();
        const url = fotoUrl + (fotoUrl.includes('?') ? '&' : '?') + cacheBuster;

        const img = new Image();
        img.onload = () => {
            avatarImg.src = url;
            avatarImg.style.display = 'block';
            if (this.avatarInitials) {
                this.avatarInitials.style.display = 'none';
            }
        };
        img.onerror = () => {
            this.mostrarIniciales(nombre);
        };
        img.src = url;
    }

    mostrarIniciales(nombre) {
        const avatarImg = document.getElementById('profile-avatar-img');
        if (!avatarImg || !this.avatarInitials) return;
        
        if (avatarImg) {
            avatarImg.style.display = 'none';
        }
        
        if (this.avatarInitials) {
            this.avatarInitials.style.display = 'flex';
            
            let initials = 'US';
            if (nombre && nombre.length > 0) {
                initials = nombre
                    .trim()
                    .split(' ')
                    .map(n => n[0])
                    .join('')
                    .toUpperCase()
                    .substring(0, 2);
            }
            this.avatarInitials.textContent = initials;
        }
    }

    async subirFoto(event) {
        const file = event.target.files[0];
        if (!file) return;

        const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!validTypes.includes(file.type)) {
            this.mostrarNotificacion('Formato de archivo no permitido. Use JPG, PNG, GIF o WebP', 'error');
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            this.mostrarNotificacion('La imagen es demasiado grande. Tamaño máximo: 5MB', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('foto', file);

        try {
            const response = await fetch('subir_foto.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.mostrarNotificacion('Foto de perfil actualizada correctamente', 'success');
                
                if (data.foto_url) {
                    const usuario = await this.cargarPerfil();
                    this.actualizarAvatar(data.foto_url, usuario.nombre);
                    this.actualizarAvatarSidebar(data.foto_url, usuario.nombre_completo || usuario.nombre);
                }
            } else {
                throw new Error(data.error || 'Error al subir la imagen');
            }
        } catch (error) {
            console.error('Error al subir la imagen:', error);
            this.mostrarNotificacion('Error al subir la imagen: ' + (error.message || 'Intente nuevamente'), 'error');
        } finally {
            if (this.avatarInput) {
                this.avatarInput.value = '';
            }
        }
    }

    mostrarNotificacion(mensaje, tipo = 'success') {
        console.log(`[${tipo.toUpperCase()}] ${mensaje}`);
        
        const notification = document.getElementById('notification');
        const notificationText = document.getElementById('notificationText');
        
        if (notification && notificationText) {
            notification.className = `notification ${tipo} show`;
            notificationText.textContent = mensaje;
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }
        
        // Fallback to alert if notification element doesn't exist
        if (!notification) {
            alert(`[${tipo}] ${mensaje}`);
        }
    }

    async loadStats() {
        try {
            const response = await fetch('perfil_stats.php');
            const data = await response.json();
            
            if (data.success) {
                const stats = data.stats;
                const statValues = document.querySelectorAll('.stat-value');
                
                if (statValues.length >= 4) {
                    statValues[0].textContent = stats.formulaciones || '0';
                    statValues[1].textContent = stats.protocolos || '0';
                    statValues[2].textContent = stats.reactivos || '0';
                    statValues[3].textContent = stats.horas ? `${stats.horas}h` : '0h';
                    
                    statValues.forEach(stat => {
                        stat.classList.add('updated');
                        setTimeout(() => stat.classList.remove('updated'), 1000);
                    });
                }
            }
        } catch (error) {
            console.error('Error loading stats:', error);
        }
    }

    initModalEvents() {
        // Close modal when clicking X or cancel
        document.querySelectorAll('.close-modal').forEach(btn => {
            btn.addEventListener('click', () => this.closeModal());
        });

        // Close when clicking outside modal
        if (this.modal) {
            this.modal.addEventListener('click', (e) => {
                if (e.target === this.modal) {
                    this.closeModal();
                }
            });
        }

        // Handle confirm button click
        const confirmBtn = document.getElementById('confirmSave');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', () => {
                this.verifyPassword();
            });
        }

        // Submit form on Enter key in password field
        if (this.passwordInput) {
            this.passwordInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.verifyPassword();
                }
            });
        }
    }

    showModal() {
        if (this.modal) {
            this.modal.style.display = 'flex';
            if (this.passwordInput) {
                this.passwordInput.value = '';
                this.passwordInput.focus();
            }
            if (this.passwordError) {
                this.passwordError.style.display = 'none';
            }
        }
    }

    closeModal() {
        if (this.modal) {
            this.modal.style.display = 'none';
        }
        if (this.passwordInput) {
            this.passwordInput.value = '';
        }
        if (this.passwordError) {
            this.passwordError.style.display = 'none';
        }
        this.currentAction = null;
        this.formData = null;
    }

    async verifyPassword() {
        const password = this.passwordInput ? this.passwordInput.value.trim() : '';
        if (!password) {
            this.showError('Por favor ingresa tu contraseña actual');
            return;
        }

        // Disable button to prevent double-clicks
        const confirmBtn = document.getElementById('confirmSave');
        if (confirmBtn) {
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = 'Verificando...';
        }

        try {
            const response = await fetch('verificar_password.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ password })
            });

            const data = await response.json();

            if (data.success) {
                this.closeModal();
                // Execute the original action - SOLO para actualizar perfil
                if (this.currentAction === 'updateProfile') {
                    await this.actualizarPerfil(this.formData);
                }
            } else {
                this.showError(data.error || 'Contraseña incorrecta');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showError('Error al verificar la contraseña');
        } finally {
            // Re-enable button
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = `
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" style="margin-right: 6px;">
                        <path d="M21,7L9,19L3.5,13.5L4.91,12.09L9,16.17L19.59,5.59L21,7Z"/>
                    </svg>
                    Confirmar
                `;
            }
        }
    }

    showError(message) {
        if (this.passwordError) {
            this.passwordError.textContent = message;
            this.passwordError.style.display = 'block';
        }
        if (this.passwordInput) {
            this.passwordInput.focus();
            this.passwordInput.classList.add('error-shake');
            setTimeout(() => {
                this.passwordInput.classList.remove('error-shake');
            }, 500);
        }
    }

    // ACTUALIZAR PERFIL - CON confirmación de contraseña
    prepararActualizacionPerfil() {
        const formData = {
            nombre: document.getElementById('firstName').value,
            email: document.getElementById('email').value,
            telefono: document.getElementById('phone').value,
            bibliografia: document.getElementById('bio').value
        };

        // Store the action and form data
        this.currentAction = 'updateProfile';
        this.formData = formData;
        this.showModal();
    }

    // Actualizar perfil después de verificar contraseña
    async actualizarPerfil(formData) {
        try {
            const response = await fetch('actualizar_perfil.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();
            
            if (data.success) {
                this.mostrarNotificacion('Perfil actualizado correctamente', 'success');
                await this.cargarPerfil();
            } else {
                throw new Error(data.message || 'Error al actualizar el perfil');
            }
        } catch (error) {
            console.error('Error al actualizar el perfil:', error);
            this.mostrarNotificacion(error.message || 'Error al actualizar el perfil', 'error');
        }
    }

    // CAMBIAR CONTRASEÑA - SIN confirmación (directo)
    async cambiarContrasena() {
        const currentPassword = document.getElementById('currentPassword').value;
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;

        // Validaciones
        if (!currentPassword || !newPassword || !confirmPassword) {
            this.mostrarNotificacion('Todos los campos son obligatorios', 'error');
            return;
        }

        if (newPassword !== confirmPassword) {
            this.mostrarNotificacion('Las contraseñas no coinciden', 'error');
            return;
        }

        if (newPassword.length < 8) {
            this.mostrarNotificacion('La contraseña debe tener al menos 8 caracteres', 'error');
            return;
        }

        const formData = {
            currentPassword,
            newPassword,
            confirmPassword
        };

        // Mostrar indicador de carga
        const button = document.querySelector('button[onclick="changePassword()"]') || 
                      document.querySelector('.btn-primary:has(svg path[d*="M12,17A2"])');
        
        if (button) {
            button.disabled = true;
            const originalHTML = button.innerHTML;
            button.innerHTML = `
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" class="spinner">
                    <path d="M12,4V2A10,10 0 0,0 2,12H4A8,8 0 0,1 12,4Z"/>
                </svg> Procesando...
            `;

            try {
                console.log('Enviando solicitud de cambio de contraseña...');
                
                const response = await fetch('cambiar_contraseña.php', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                });

                console.log('Response status:', response.status);
                
                const data = await response.json();
                console.log('Response data:', data);
                
                if (data.success) {
                    this.mostrarNotificacion('Contraseña actualizada correctamente', 'success');
                    
                    // Limpiar los campos
                    document.getElementById('currentPassword').value = '';
                    document.getElementById('newPassword').value = '';
                    document.getElementById('confirmPassword').value = '';
                    
                    // Limpiar el indicador de fortaleza
                    const passwordStrength = document.getElementById('password-strength');
                    if (passwordStrength) {
                        passwordStrength.textContent = '';
                    }
                } else {
                    throw new Error(data.message || data.error || 'Error al actualizar la contraseña');
                }
            } catch (error) {
                console.error('Error al cambiar la contraseña:', error);
                this.mostrarNotificacion(error.message || 'Error al cambiar la contraseña', 'error');
            } finally {
                // Restaurar el botón
                button.disabled = false;
                button.innerHTML = originalHTML;
            }
        }
    }

    togglePasswordVisibility(inputId) {
        const input = document.getElementById(inputId);
        const button = input ? input.nextElementSibling : null;
        const icon = button ? button.querySelector('svg') : null;
        
        if (input && icon) {
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = '<path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46A11.804 11.804 0 0 0 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/>';
            } else {
                input.type = 'password';
                icon.innerHTML = '<path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>';
            }
        }
    }
}

// Función global para toggle password
function togglePassword(inputId) {
    if (window.perfilManager) {
        window.perfilManager.togglePasswordVisibility(inputId);
    }
}

function toggleModalPassword() {
    const input = document.getElementById('modalPassword');
    const icon = document.getElementById('modalEyeIcon');
    
    if (input && icon) {
        if (input.type === 'password') {
            input.type = 'text';
            icon.innerHTML = '<path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46A11.804 11.804 0 0 0 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/>';
        } else {
            input.type = 'password';
            icon.innerHTML = '<path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>';
        }
    }
}

// Función global para cambiar contraseña (mantener compatibilidad)
function changePassword() {
    if (window.perfilManager) {
        window.perfilManager.cambiarContrasena();
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    if (!window.perfilManager) {
        window.perfilManager = new PerfilManager();
    } else {
        window.perfilManager.cargarPerfil().catch(console.error);
    }
});