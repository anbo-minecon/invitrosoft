document.addEventListener('DOMContentLoaded', () => {
    const userTypePanel = document.getElementById('user-type-panel');
    const userFormPanel = document.getElementById('user-form-panel');
    const btnVolverHeader = document.getElementById('btnVolverHeader');
    let generos = [];

    async function cargarGeneros() {
        const res = await fetch('db/parametros.php?accion=listar&tipo=genero');
        generos = await res.json();
    }

    document.querySelectorAll('.user-type-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const tipo = btn.dataset.type;
            await cargarGeneros();
            mostrarFormulario(tipo);
        });
    });

    function mostrarFormulario(tipo) {
        userTypePanel.style.display = 'none';
        userFormPanel.style.display = 'block';
        userFormPanel.className = 'user-form-panel';
        if(btnVolverHeader) btnVolverHeader.style.display = 'none';
        let extraCampos = '';
        if (tipo === 'aprendiz') {
            extraCampos = `
                <div class="form-group">
                    <label for="tiempo_uso">Tiempo de uso</label>
                    <input type="text" id="tiempo_uso" name="tiempo_uso" required>
                </div>
                <div class="form-group">
                    <label for="ficha_formacion">Ficha de formación</label>
                    <input type="text" id="ficha_formacion" name="ficha_formacion" required>
                </div>
            `;
        } else if (tipo === 'pasante') {
            extraCampos = `
                <div class="form-group">
                    <label for="tiempo_uso">Tiempo de uso</label>
                    <input type="text" id="tiempo_uso" name="tiempo_uso" required>
                </div>
            `;
        }
        userFormPanel.innerHTML = `
            <form class="user-form" id="userForm">
                <h2>Registro de ${tipo.charAt(0).toUpperCase() + tipo.slice(1)}</h2>
                <div class="form-group">
                    <label for="genero">Género</label>
                    <select id="genero" name="genero" required>
                        <option value="">Seleccione</option>
                        ${generos.map(g => `<option value="${g.id_parametro}">${g.nombre}</option>`).join('')}
                    </select>
                </div>
                <div class="form-group">
                    <label for="identidad">Número de identidad</label>
                    <input type="text" id="identidad" name="identidad" required>
                </div>
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="telefono">Número de teléfono</label>
                    <input type="text" id="telefono" name="telefono" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="password2">Repetir contraseña</label>
                    <input type="password" id="password2" name="password2" required>
                </div>
                ${extraCampos}
                <div style="display:flex; gap:10px; justify-content:center; margin-top:20px;">
                    <button type="button" onclick="location.reload()" class="btn-secondary">Volver</button>
                    <button type="submit" class="btn-primary">Registrar</button>
                </div>
            </form>
            <div id="notificacion" style="margin-top:15px;text-align:center;"></div>
        `;
        document.getElementById('userForm').addEventListener('submit', enviarFormulario);
    }

    async function enviarFormulario(e) {
        e.preventDefault();
        const form = e.target;
        const data = Object.fromEntries(new FormData(form));
        const notificacion = document.getElementById('notificacion');
        if (data.password !== data.password2) {
            notificacion.innerHTML = '<span style="color:#e74c3c;">Las contraseñas no coinciden</span>';
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
            const result = await res.json();
            if (res.ok && result.success) {
                notificacion.innerHTML = '<span style="color:#2ecc71;">Usuario registrado correctamente</span>';
                setTimeout(() => location.reload(), 1500);
            } else {
                notificacion.innerHTML = `<span style="color:#e74c3c;">${result.error || 'Error al registrar'}</span>`;
            }
        } catch (err) {
            notificacion.innerHTML = '<span style="color:#e74c3c;">Error de conexión</span>';
        }
    }

    if(btnVolverHeader) btnVolverHeader.style.display = 'flex';
});

