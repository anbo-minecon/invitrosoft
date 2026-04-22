// ubicacion: /main/welcome/init-welcome.js

// Obtener la ruta base del sitio
function getBaseUrl() {
    const pathParts = window.location.pathname.split('/');
    const invitrosoftIndex = pathParts.indexOf('invitrosoft');
    
    if (invitrosoftIndex !== -1) {
        // Si encontramos 'invitrosoft' en la ruta
        const basePath = pathParts.slice(0, invitrosoftIndex + 1);
        return window.location.origin + basePath.join('/') + '/';
    }
    
    // Fallback: buscar 'main'
    const mainIndex = pathParts.indexOf('main');
    if (mainIndex !== -1) {
        const basePath = pathParts.slice(0, mainIndex);
        return window.location.origin + basePath.join('/') + '/';
    }
    
    // Fallback final
    return window.location.origin + '/invitrosoft/';
}

const BASE_URL = getBaseUrl();
const API_BASE_URL = BASE_URL + 'main/welcome/api/';

async function fetchUserData() {
    
    try {
        const response = await fetch(API_BASE_URL + 'get_user_data.php', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache',
                'Expires': '0'
            }
        });
        
        const textResponse = await response.text();
        
        let data;
        try {
            data = JSON.parse(textResponse);
        } catch (parseError) {
            throw new Error('Respuesta inválida del servidor');
        }
        
        if (!response.ok) {
            
            // Si no está autenticado, redirigir al login
            if (response.status === 401) {
                setTimeout(() => {
                    window.location.href = BASE_URL + 'src/index.html';
                }, 2000);
            }
            
            throw new Error(data.error || `Error HTTP ${response.status}`);
        }
        
        return data;
        
    } catch (error) {
        console.error('[WELCOME] Error al cargar datos del usuario:', error);
        
        // Mostrar mensaje de error al usuario
        if (typeof showErrorModal === 'function') {
            showErrorModal('Error de conexión', 'No se pudieron cargar los datos del usuario. Por favor, recarga la página.');
        } else {
            console.error('[WELCOME] Función showErrorModal no disponible');
        }
        
        return null;
    }
}

// Función para mostrar errores en un modal
function showErrorModal(title, message) {
    if (typeof showWelcomeModal === 'function') {
        showWelcomeModal({
            title: title,
            message: message,
            type: 'error',
            imageUrl: BASE_URL + 'img/avatar/error.png',
            buttons: [{
                text: 'Aceptar',
                type: 'primary',
            }]
        });
    } else {
        // Fallback en caso de que el modal no esté disponible
        alert(`${title}: ${message}`);
    }
}

async function initializeWelcomeMessage() {

    const path = window.location.pathname;
    
    let moduleName = 'el sistema';
    let moduleTitle = 'Bienvenido al Sistema';
    let moduleMessage = 'Gracias por iniciar sesión. Estamos encantados de tenerte aquí.';
    let avatarImage = 'welcome.png';
    
    // Detectar módulo basado en la URL
    if (path.includes('/admin/')) {
        moduleName = 'el Panel de Administración';
        moduleTitle = 'Panel de Administración';
        moduleMessage = 'Gestiona todos los aspectos del sistema desde este panel centralizado.';
        avatarImage = 'welcome.png';
    } else if (path.includes('/aprendiz/')) {
        moduleName = 'el Módulo de Aprendiz';
        moduleTitle = 'Módulo de Aprendiz';
        moduleMessage = 'Explora y aprende con nuestras herramientas educativas.';
        avatarImage = 'welcome.png';
    }
    
    
    // Obtener datos del usuario
    const userData = await fetchUserData();
    
    if (!userData) {
        console.error('[WELCOME] No se pudieron obtener los datos del usuario');
        return;
    }
    
    // Datos por defecto si no se obtienen del servidor
    const defaultData = {
        nombre: 'Usuario',
        tipo: 'invitado'
    };
    
    const userInfo = userData.success !== false ? userData : defaultData;
    
    // Mapeo de roles
    const roleNames = {
        'admin': 'Administrador',
        'aprendiz': 'Aprendiz',
        'pasante': 'Pasante',
        'invitado': 'Invitado'
    };
    
    const userName = userInfo.nombre || 'Usuario';
    const userRole = userInfo.tipo || 'invitado';
    const roleName = roleNames[userRole.toLowerCase()] || 'Usuario';
    
    
    // Verificar si showWelcomeModal está disponible
    if (typeof showWelcomeModal !== 'function') {
        console.error('[WELCOME] showWelcomeModal no está definido. Asegúrate de cargar welcome-message.js primero.');
        return;
    }
    
    // Mostrar mensaje de bienvenida
    showWelcomeModal({
        title: `¡Bienvenido a ${moduleTitle}!`,
        message: `
            <p>Hola <strong>${userName}</strong>,</p>
            <p>${moduleMessage}</p>
            <p>Has ingresado como: <strong>${roleName}</strong></p>
        `,
        imageUrl: BASE_URL + 'img/avatar/' + avatarImage,
        buttons: [{
            text: 'Comenzar',
            type: 'primary',
        }]
    });
}

// Inicializar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeWelcomeMessage);
} else {
    // DOM ya está listo
    initializeWelcomeMessage();
}