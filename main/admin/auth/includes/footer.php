<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body> 

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
    // Function to mark notification as read
    function marcarNotificacionLeida(id, elemento) {
        // Mark as read visually
        if (elemento) {
            elemento.classList.add('text-muted');
            elemento.querySelector('h6').classList.remove('fw-bold');
        }

        // Send request to server
        fetch('marcar_notificacion_leida.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + id
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update notification counter
                const contador = document.getElementById('contadorNotificaciones');
                if (contador) {
                    const nuevoTotal = parseInt(contador.textContent) - 1;
                    if (nuevoTotal > 0) {
                        contador.textContent = nuevoTotal > 9 ? '9+' : nuevoTotal;
                    } else {
                        contador.style.display = 'none';
                    }
                }
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // Update notifications every 30 seconds
    function actualizarNotificaciones() {
        fetch('obtener_notificaciones.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update notification counter
                    const contador = document.getElementById('contadorNotificaciones');
                    if (contador) {
                        if (data.total > 0) {
                            contador.textContent = data.total > 9 ? '9+' : data.total;
                            contador.style.display = 'inline-block';
                        } else {
                            contador.style.display = 'none';
                        }
                    }

                    // Update notification list if dropdown is open
                    const dropdown = document.querySelector('.dropdown-menu.show');
                    if (dropdown && data.notificaciones) {
                        const lista = document.getElementById('listaNotificaciones');
                        if (lista) {
                            let html = '';
                            
                            if (data.notificaciones.length === 0) {
                                html = `
                                    <a class="dropdown-item text-center py-3" href="#">
                                        No hay notificaciones nuevas
                                    </a>
                                    <div class="dropdown-divider"></div>
                                `;
                            } else {
                                data.notificaciones.forEach(notif => {
                                    html += `
                                        <a class="dropdown-item" href="#" onclick="marcarNotificacionLeida(${notif.id}, this)">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1">${escapeHtml(notif.titulo)}</h6>
                                                <small>${notif.tiempo || ''}</small>
                                            </div>
                                            <p class="mb-1 small">${escapeHtml(notif.mensaje)}</p>
                                        </a>
                                        <div class="dropdown-divider"></div>
                                    `;
                                });
                            }
                            
                            // Add "View all" link
                            html += `
                                <a class="dropdown-item text-center" href="notificaciones/">
                                    <i class="fas fa-history me-1"></i> Ver todas las notificaciones
                                </a>
                            `;
                            
                            lista.innerHTML = html;
                        }
                    }
                }
            })
            .catch(error => console.error('Error al actualizar notificaciones:', error));
    }

    // Helper function to escape HTML
    function escapeHtml(unsafe) {
        return unsafe
            .toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Start updating notifications every 30 seconds
    let notificacionInterval = setInterval(actualizarNotificaciones, 30000);

    // Also update when the page loads
    document.addEventListener('DOMContentLoaded', function() {
        // Initial update after a short delay
        setTimeout(actualizarNotificaciones, 1000);
    });
    </script>
</body>
</html>