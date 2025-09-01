document.addEventListener("DOMContentLoaded", () => {
    const botones = document.querySelectorAll(".param-btn");
    const contenido = document.getElementById("contenido");
    const btnNuevo = document.getElementById("btn-nuevo");

    let seccionActiva = "genero";
    let tipos = [];
    let parametros = [];

    async function cargarTipos() {
        const res = await fetch("db/parametros.php?accion=tipos");
        tipos = await res.json();
    }

    async function cargarParametros() {
        const res = await fetch(`db/parametros.php?accion=listar&tipo=${seccionActiva}`);
        parametros = await res.json();
    }

    function obtenerNombreColumna() {
        return (seccionActiva === "genero" || seccionActiva === "estado") ? "Usuarios" : "Reactivos";
    }

    function getTipoIdByNombre(nombre) {
        const tipo = tipos.find(t => t.nombre.toLowerCase() === nombre.toLowerCase());
        return tipo ? tipo.id_tipo : null;
    }

    function renderTabla() {
        const nombreColumna = obtenerNombreColumna();
        let filas = parametros.map(item => `
            <tr>
                <td>${item.id_parametro}</td>
                <td>${item.nombre}</td>
                <td>${item.usuarios ?? 0}</td>
                <td>${item.descripcion ?? ''}</td>
                <td class="acciones">
                    <img src="parametros/icons/edit.png" alt="Editar" data-id="${item.id_parametro}" class="btn-editar">
                    <img src="parametros/icons/delete.png" alt="Eliminar" data-id="${item.id_parametro}" class="btn-eliminar">
                </td>
            </tr>
        `).join("");
        if (!filas) {
            filas = `<tr><td colspan="5" style="text-align:center;color:#777">Sin registros</td></tr>`;
        }
        contenido.innerHTML = `
            <h3>${seccionActiva.charAt(0).toUpperCase() + seccionActiva.slice(1)} registrados</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>${nombreColumna}</th>
                        <th>Descripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>${filas}</tbody>
            </table>
        `;
        btnNuevo.innerHTML = `<img src="parametros/icons/add.png"> Nuevo ${seccionActiva}`;
        // Eventos editar/eliminar
        contenido.querySelectorAll('.btn-editar').forEach(btn => {
            btn.onclick = () => abrirModalEditar(btn.dataset.id);
        });
        contenido.querySelectorAll('.btn-eliminar').forEach(btn => {
            btn.onclick = () => eliminarParametro(btn.dataset.id);
        });
    }

    async function abrirModal() {
        const modal = document.createElement("div");
        modal.classList.add("modal");
        modal.innerHTML = `
            <div class="modal-content">
                <h3>Nuevo ${seccionActiva}</h3>
                <label>Nombre:</label>
                <input type="text" id="nombre">
                <label>Descripción:</label>
                <textarea id="descripcion"></textarea>
                <div class="modal-actions">
                    <button class="btn-cancelar">Cancelar</button>
                    <button class="btn-guardar">Guardar</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        modal.style.display = "flex";
        modal.querySelector(".btn-cancelar").onclick = () => modal.remove();
        modal.querySelector(".btn-guardar").onclick = async () => {
            const nombre = document.getElementById("nombre").value.trim();
            const descripcion = document.getElementById("descripcion").value.trim();
            if (!nombre) {
                alert("El nombre es obligatorio");
                return;
            }
            const id_tipo = getTipoIdByNombre(seccionActiva);
            const res = await fetch('db/parametros.php?accion=crear', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_tipo, nombre, descripcion })
            });
            const result = await res.json();
            if (result.success) {
                mostrarToast(`${seccionActiva} creado correctamente`);
                await recargar();
                modal.remove();
            } else {
                alert(result.error || 'Error al crear');
            }
        };
    }

    async function abrirModalEditar(id) {
        const param = parametros.find(p => p.id_parametro == id);
        if (!param) return;
        const modal = document.createElement("div");
        modal.classList.add("modal");
        modal.innerHTML = `
            <div class="modal-content">
                <h3>Editar ${seccionActiva}</h3>
                <label>Nombre:</label>
                <input type="text" id="nombre" value="${param.nombre}">
                <label>Descripción:</label>
                <textarea id="descripcion">${param.descripcion ?? ''}</textarea>
                <div class="modal-actions">
                    <button class="btn-cancelar">Cancelar</button>
                    <button class="btn-guardar">Guardar</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        modal.style.display = "flex";
        modal.querySelector(".btn-cancelar").onclick = () => modal.remove();
        modal.querySelector(".btn-guardar").onclick = async () => {
            const nombre = document.getElementById("nombre").value.trim();
            const descripcion = document.getElementById("descripcion").value.trim();
            if (!nombre) {
                alert("El nombre es obligatorio");
                return;
            }
            const res = await fetch('db/parametros.php?accion=editar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_parametro: id, nombre, descripcion })
            });
            const result = await res.json();
            if (result.success) {
                mostrarToast(`${seccionActiva} editado correctamente`);
                await recargar();
                modal.remove();
            } else {
                alert(result.error || 'Error al editar');
            }
        };
    }

    async function eliminarParametro(id) {
        if (!confirm('¿Seguro que deseas eliminar este registro?')) return;
        const res = await fetch('db/parametros.php?accion=eliminar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_parametro: id })
        });
        const result = await res.json();
        if (result.success) {
            mostrarToast(`${seccionActiva} eliminado correctamente`);
            await recargar();
        } else {
            alert(result.error || 'Error al eliminar');
        }
    }

    function mostrarToast(msg) {
        const toast = document.createElement("div");
        toast.className = "toast";
        toast.textContent = msg;
        document.body.appendChild(toast);
        setTimeout(() => toast.classList.add("show"), 50);
        setTimeout(() => {
            toast.classList.remove("show");
            setTimeout(() => toast.remove(), 300);
        }, 2500);
    }

    async function recargar() {
        await cargarParametros();
        renderTabla();
    }

    botones.forEach(btn => {
        btn.addEventListener("click", async () => {
            botones.forEach(b => b.classList.remove("active"));
            btn.classList.add("active");
            seccionActiva = btn.dataset.section;
            await recargar();
        });
    });

    btnNuevo.addEventListener("click", abrirModal);

    (async function init() {
        await cargarTipos();
        await recargar();
    })();
});