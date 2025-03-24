function cargarRegistros() {
    fetch('obtener_registros.php')
        .then(response => response.json())
        .then(data => {
            let html = `
                <h2>Registros de Acciones</h2>
                <table>
                    <tr>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>Acción</th>
                        <th>Nivel</th>
                    </tr>`;
            
            data.forEach(registro => {
                html += `
                    <tr>
                        <td>${registro.fecha}</td>
                        <td>${registro.usuario}</td>
                        <td>${registro.accion}</td>
                        <td>${registro.nivel}</td>
                    </tr>`;
            });
            
            html += '</table>';
            document.getElementById('resultados').innerHTML = html;
        })
        .catch(error => console.error('Error:', error));
}

function cargarEdicionRegistros() {
    fetch('obtener_registros.php')
        .then(response => response.json())
        .then(data => {
            let html = `
                <h2>Editar Registros de Acciones</h2>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>Acción</th>
                        <th>Nivel</th>
                        <th>Acciones</th>
                    </tr>`;
            
            data.forEach(registro => {
                html += `
                    <tr id="fila-${registro.id}">
                        <td>${registro.id}</td>
                        <td>${registro.fecha}</td>
                        <td>${registro.usuario}</td>
                        <td>${registro.accion}</td>
                        <td>${registro.nivel}</td>
                        <td>
                            <button onclick="editarRegistro(${registro.id})">Editar</button>
                            <button onclick="eliminarRegistro(${registro.id})">Eliminar</button>
                        </td>
                    </tr>`;
            });
            
            html += '</table>';
            document.getElementById('resultados').innerHTML = html;
        })
        .catch(error => console.error('Error:', error));
}

function editarRegistro(id) {
    // Primero obtenemos los datos actuales del registro
    fetch(`editar_registro.php?id=${id}`)
        .then(response => response.json())
        .then(registro => {
            let formulario = `
                <h2>Editar Registro #${id}</h2>
                <form id="formEdicion" onsubmit="guardarEdicion(event, ${id})">
                    <div class="form-group">
                        <label for="usuario">Usuario:</label>
                        <input type="text" id="usuario" name="usuario" value="${registro.usuario}" required>
                    </div>
                    <div class="form-group">
                        <label for="accion">Acción:</label>
                        <textarea id="accion" name="accion" required>${registro.accion}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="nivel">Nivel:</label>
                        <select id="nivel" name="nivel" required>
                            <option value="AVISO" ${registro.nivel === 'AVISO' ? 'selected' : ''}>AVISO</option>
                            <option value="MOVIMIENTO" ${registro.nivel === 'MOVIMIENTO' ? 'selected' : ''}>MOVIMIENTO</option>
                            <option value="ATAQUE" ${registro.nivel === 'ATAQUE' ? 'selected' : ''}>ATAQUE</option>
                        </select>
                    </div>
                    <div class="buttons-group">
                        <button type="submit">Guardar</button>
                        <button type="button" onclick="cargarEdicionRegistros()">Cancelar</button>
                    </div>
                </form>
            `;
            document.getElementById('resultados').innerHTML = formulario;
        })
        .catch(error => console.error('Error:', error));
}

function guardarEdicion(event, id) {
    event.preventDefault();
    
    // Obtener valores del formulario
    const usuario = document.getElementById('usuario').value;
    const accion = document.getElementById('accion').value;
    const nivel = document.getElementById('nivel').value;
    
    // Crear objeto FormData para enviar datos
    const formData = new FormData();
    formData.append('id', id);
    formData.append('usuario', usuario);
    formData.append('accion', accion);
    formData.append('nivel', nivel);
    
    // Mostrar indicador de carga
    document.getElementById('resultados').innerHTML += `
        <div id="loadingIndicator" class="loading">
            <p>Guardando cambios...</p>
        </div>
    `;
    
    // Enviar solicitud para actualizar el registro
    fetch('editar_registro.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Eliminar indicador de carga
        const loadingIndicator = document.getElementById('loadingIndicator');
        if (loadingIndicator) {
            loadingIndicator.remove();
        }
        
        if (data.success) {
            // Mostrar mensaje de éxito
            document.getElementById('resultados').innerHTML = `
                <div class="alert alert-success">
                    <p>${data.mensaje}</p>
                    <div class="registro-actualizado">
                        <h3>Registro Actualizado</h3>
                        <ul>
                            <li><strong>ID:</strong> ${data.registro.id}</li>
                            <li><strong>Usuario:</strong> ${data.registro.usuario}</li>
                            <li><strong>Acción:</strong> ${data.registro.accion}</li>
                            <li><strong>Nivel:</strong> ${data.registro.nivel}</li>
                            <li><strong>Fecha:</strong> ${data.registro.fecha}</li>
                        </ul>
                    </div>
                    <button type="button" onclick="cargarEdicionRegistros()">Volver a la lista</button>
                </div>
            `;
            
            // Opcional: Actualizar la lista de registros en segundo plano
            actualizarListaRegistros();
        } else {
            // Mostrar mensaje de error
            document.getElementById('resultados').innerHTML = `
                <div class="alert alert-danger">
                    <p>Error: ${data.mensaje}</p>
                    <button type="button" onclick="editarRegistro(${id})">Intentar de nuevo</button>
                    <button type="button" onclick="cargarEdicionRegistros()">Volver a la lista</button>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('resultados').innerHTML = `
            <div class="alert alert-danger">
                <p>Error de conexión. Por favor intente más tarde.</p>
                <button type="button" onclick="cargarEdicionRegistros()">Volver a la lista</button>
            </div>
        `;
    });
}

// Función auxiliar para actualizar la lista de registros
function actualizarListaRegistros() {
    // Si existe una función para cargar registros, la llamamos
    if (typeof cargarRegistros === 'function') {
        setTimeout(() => {
            cargarRegistros();
        }, 1000);
    }
}

// Función auxiliar para actualizar la lista de registros
function actualizarListaRegistros() {
    // Si existe una función para cargar registros, la llamamos
    if (typeof cargarRegistros === 'function') {
        setTimeout(() => {
            cargarRegistros();
        }, 1000);
    }
}
function eliminarRegistro(id) {
    if (confirm('¿Está seguro de eliminar este registro? Esta acción no se puede deshacer.')) {
        fetch('eliminar_registro.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Registramos esta acción
                registrarAccionJS('Eliminación de registro #' + id, 'MOVIMIENTO');
                // Removemos la fila de la tabla
                document.getElementById(`fila-${id}`).remove();
            } else {
                alert('Error al eliminar: ' + data.mensaje);
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

function registrarAccionJS(accion, nivel) {
    fetch('registrar_accion.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `accion=${encodeURIComponent(accion)}&nivel=${nivel}`
    });
}

function cargarLog() {
    fetch('obtener_log.php')
        .then(response => response.text())
        .then(data => {
            document.getElementById('resultados').innerHTML = `
                <h2>Archivo Log</h2>
                <pre>${data}</pre>`;
        })
        .catch(error => console.error('Error:', error));
}

// Validación del formulario de login
document.getElementById('loginForm')?.addEventListener('submit', function(e) {
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    
    if (!username || !password) {
        e.preventDefault();
        document.getElementById('error-message').textContent = 'Por favor complete todos los campos';
    }
});




