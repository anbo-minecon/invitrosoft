# InvitroSoft - Sistema de Gestión

Sistema de administración y gestión de formulaciones, protocolos y reactivos con panel administrativo, autenticación y notificaciones por correo.

## Características Principales

- 🔐 **Autenticación y Autorización** - Sistema de login seguro con roles de usuario
- 📊 **Panel Administrativo** - Gestión centralizada de usuarios, formulaciones, protocolos y reactivos
- 📧 **Notificaciones por Email** - Envío de alertas y notificaciones automáticas
- 🎓 **Área de Aprendizaje** - Módulo educativo con frontend y backend
- 🌙 **Modo Oscuro** - Interfaz adaptable a preferencias del usuario
- 📱 **Diseño Responsivo** - Interfaz compatible con dispositivos móviles

## Requisitos

- **PHP** >= 7.4
- **MySQL/MariaDB**
- **Composer** (gestor de dependencias PHP)
- **Apache** con módulo rewrite habilitado
- **Extensiones PHP**: mysqli, pdo, mbstring, ctype

## Instalación

### 1. Clonar el repositorio
```bash
git clone <repository-url>
cd invitrosoft-des
```

### 2. Instalar dependencias
```bash
composer install
```

### 3. Configurar variables de entorno
```bash
# Copiar el archivo de ejemplo
cp .env.example .env
```

Edita `.env` con tus configuraciones:
```env
# Base de datos
DB_HOST=localhost
DB_USER=root
DB_PASS=your_password
DB_NAME=invitrosoft

# Correo (Gmail SMTP)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USER=your_email@gmail.com
MAIL_PASS=your_app_password
MAIL_FROM=noreply@invitrosoft.com
```

### 4. Crear base de datos
```bash
# Ejecutar scripts SQL
mysql -u root -p invitrosoft < sql/01_create_tables.sql
mysql -u root -p invitrosoft < sql/02_insert_sample_data.sql
```

### 5. Configurar servidor Apache
Asegúrate de que el archivo `.htaccess` esté presente en la raíz con permisos de lectura.

## Estructura del Proyecto

```
invitrosoft-des/
├── main/
│   ├── admin/              # Panel administrativo
│   │   ├── db/            # Scripts de base de datos
│   │   ├── js/            # JavaScript del admin
│   │   └── css/           # Estilos del admin
│   ├── aprendiz/          # Área de aprendizaje
│   │   ├── backend/       # Lógica backend
│   │   └── frontend/      # Interfaz frontend
│   └── welcome/           # Página de bienvenida
├── src/                   # Autenticación y login
├── project/               # Página principal
├── includes/              # Funciones compartidas
├── assets/                # Activos estáticos (errores, iconos)
├── sql/                   # Scripts de base de datos
├── vendor/                # Dependencias (no subir a Git)
└── composer.json          # Dependencias del proyecto
```

## Uso

### Acceso al Sistema

1. **Inicio de Sesión**: Accede a `http://localhost/invitrosoft-des/src/` con tus credenciales
2. **Panel Admin**: Una vez autenticado, accede al panel administrativo
3. **Gestión**: Administra usuarios, formulaciones, protocolos y reactivos

### Operaciones Principales

- **Usuarios**: Crear, editar y eliminar usuarios
- **Formulaciones**: Gestionar formulaciones de productos
- **Protocolos**: Administrar protocolos de laboratorio
- **Reactivos**: Gestionar catálogo de reactivos
- **Parámetros**: Configurar parámetros del sistema

## Configuración Importante

### Archivos de Configuración Excluidos en Git

Los siguientes archivos contienen información sensible y **NO se deben subir a Git**:

- `.env` - Credenciales de base de datos y correo
- `.htaccess` - Configuración del servidor (personalizada por entorno)
- `**/config.php` - Archivos de configuración específicos
- `includes/send_alert_email.php` - Credenciales de Gmail

Estos archivos están listados en `.gitignore` para proteger tu información.

### Crear Archivo .env Local

Después de clonar, crea tu archivo `.env` local:

```env
# Database Configuration
DB_HOST=localhost
DB_USER=root
DB_PASS=your_password
DB_NAME=invitrosoft

# Mail Configuration (Gmail)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USER=your_email@gmail.com
MAIL_PASS=your_app_password  # Usar contraseña de aplicación, no la principal
MAIL_FROM=noreply@invitrosoft.com
```

## Guía de Credenciales

### Para Habilitar Notificaciones por Email (Gmail)

1. Ve a [Google Account Security](https://myaccount.google.com/security)
2. Habilita autenticación de dos factores
3. Genera una [Contraseña de Aplicación](https://myaccount.google.com/apppasswords)
4. Usa esta contraseña en tu archivo `.env`

## Desarrollo

### Estructura de Base de Datos

Ver `sql/README.md` para documentación de esquemas.

### Logs y Debugging

Los archivos de error se almacenan en:
- Logs de PHP: `/var/log/apache2/error.log` (Linux) o `C:\xampp\apache\logs\error.log` (Windows)
- Logs de aplicación: Verificar configuración de error logging en `.env`

### Modo Oscuro

El sistema incluye soporte para modo oscuro. Controla mediante:
- `main/admin/css/dark-mode.css`
- `main/admin/js/dark-mode.js`

## Seguridad

✅ **Recomendaciones de Seguridad**

- [ ] Cambiar credenciales por defecto
- [ ] Usar HTTPS en producción
- [ ] Configurar límites de tasa (rate limiting)
- [ ] Implementar CSRF protection
- [ ] Sanitizar todas las entradas del usuario
- [ ] Usar prepared statements en todas las consultas SQL
- [ ] Mantener dependencias actualizadas: `composer update`

## Troubleshooting

### Error: "No se pudo cargar la configuración de la base de datos"
- Verifica que `.env` existe y tiene permisos de lectura
- Confirma las credenciales de base de datos

### Error: "SMTP connection failed"
- Verifica credenciales de Gmail en `.env`
- Confirma que las aplicaciones menos seguras están habilitadas o usa contraseña de aplicación
- Comprueba la conexión a internet

### Error 404 en rutas
- Verifica que `.htaccess` existe y está configurado correctamente
- Asegúrate que Apache tiene módulo rewrite habilitado: `a2enmod rewrite`

## Colaboración

Para contribuir al proyecto:

1. Crea una rama para tu feature: `git checkout -b feature/nueva-funcionalidad`
2. Realiza commits descriptivos
3. Pushea tu rama: `git push origin feature/nueva-funcionalidad`
4. Abre un Pull Request

## Licencia

Este proyecto es propiedad de InvitroSoft.

## Contacto

Para preguntas o soporte, accede a la sección de contacto en `project/contacto.html`

---

**Última actualización**: Abril 2026
**Versión**: 1.0
