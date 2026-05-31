<?php require_once 'auth.php'; require_once '../includes/db.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar Estructura | Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Playfair+Display:wght@700&family=Syne:wght@400;700;800&display=swap">
    <style>
        :root {
            --bg: #ffffff;
            --bg-alt: #f9f9f9;
            --text: #111111;
            --text-muted: #666666;
            --border: #e0e0e0;
            --accent: #111111;
            --danger: #e53e3e;
            --success: #276749;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: var(--bg); color: var(--text); font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }

        /* --- Nav --- */
        .admin-nav {
            padding: 1.5rem 4rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .admin-nav .logo { font-family: 'Syne', sans-serif; font-weight: 800; font-size: 1rem; text-transform: uppercase; letter-spacing: 0.1em; }
        .nav-links a { margin-left: 2rem; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 700; color: var(--text-muted); text-decoration: none; }
        .nav-links a:hover { color: var(--text); }
        .nav-links a.active { color: var(--text); border-bottom: 2px solid var(--text); padding-bottom: 2px; }

        /* --- Layout --- */
        .container { max-width: 1400px; margin: 0 auto; padding: 4rem; }

        /* --- Tabs --- */
        .tabs { display: flex; gap: 0; border-bottom: 1px solid var(--border); margin-bottom: 3rem; }
        .tab-btn {
            padding: 1rem 2rem;
            font-family: 'Syne', sans-serif;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-weight: 700;
            background: none;
            border: none;
            border-bottom: 2px solid transparent;
            margin-bottom: -1px;
            cursor: pointer;
            color: var(--text-muted);
            transition: color 0.3s;
        }
        .tab-btn.active { color: var(--text); border-bottom-color: var(--text); }

        /* --- FIX Layout Shift ---
           Todos los paneles están en el flujo normal (display: grid).
           Los inactivos se ocultan con opacity + visibility para que
           NO cambien el tamaño del contenedor y evitar el CLS. */
        .tabs-wrapper {
            position: relative;
            /* min-height fijo reserva espacio y elimina el salto */
            min-height: 520px;
        }
        .tab-panel {
            display: grid;
            grid-template-columns: 380px 1fr;
            gap: 4rem;
            align-items: start;
            /* Oculto: invisible pero ocupa espacio */
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.25s ease, visibility 0.25s ease;
        }
        .tab-panel.active {
            position: relative; /* vuelve al flujo normal */
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        /* --- Cards --- */
        .card { border: 1px solid var(--border); padding: 2.5rem; }
        .card-title { font-family: 'Playfair Display', serif; font-size: 1.8rem; margin-bottom: 2rem; }

        /* --- Form Elements --- */
        .form-label { display: block; font-size: 0.6rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 700; color: var(--text-muted); margin-bottom: 0.5rem; }
        .form-input {
            width: 100%;
            border: none;
            border-bottom: 1px solid var(--border);
            padding: 0.8rem 0;
            font-size: 0.9rem;
            outline: none;
            margin-bottom: 1.5rem;
            transition: border-color 0.3s;
            font-family: 'Inter', sans-serif;
        }
        .form-input:focus { border-color: var(--text); }
        
        select.form-input { cursor: pointer; }

        .btn-primary {
            background: var(--text);
            color: #fff;
            border: none;
            padding: 1rem 2rem;
            width: 100%;
            font-family: 'Syne', sans-serif;
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.1em;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-primary:hover { background: #333; }

        /* --- Table --- */
        .list-table { width: 100%; border-collapse: collapse; }
        .list-table th { text-align: left; font-size: 0.6rem; text-transform: uppercase; letter-spacing: 0.1em; padding: 0.8rem 1rem; border-bottom: 2px solid var(--text); font-family: 'Syne', sans-serif; }
        .list-table td { padding: 1.2rem 1rem; border-bottom: 1px solid var(--border); font-size: 0.85rem; vertical-align: middle; }
        .list-table tr:hover td { background: var(--bg-alt); }

        .badge { display: inline-block; font-size: 0.6rem; text-transform: uppercase; letter-spacing: 0.1em; padding: 0.2rem 0.6rem; background: var(--bg-alt); border: 1px solid var(--border); font-weight: 700; }
        .btn-del { background: none; border: none; color: var(--danger); font-size: 0.65rem; text-transform: uppercase; font-weight: 800; letter-spacing: 0.1em; cursor: pointer; }
        .btn-del:hover { text-decoration: underline; }

        /* --- Feedback Toast --- */
        .toast {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: var(--text);
            color: #fff;
            padding: 1rem 2rem;
            font-family: 'Syne', sans-serif;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.4s ease;
            pointer-events: none;
            z-index: 999;
        }
        .toast.show { opacity: 1; transform: translateY(0); }
        .toast.error { background: var(--danger); }

        /* Estilos Responsivos para Dispositivos Móviles */
        @media (max-width: 1023px) {
            .admin-nav {
                padding: 1.5rem !important;
                flex-direction: column !important;
                gap: 1.2rem !important;
                text-align: center !important;
            }
            .admin-nav .nav-links {
                display: flex !important;
                flex-wrap: wrap !important;
                justify-content: center !important;
                gap: 0.8rem 1rem !important;
            }
            .admin-nav .nav-links a {
                margin: 0 !important;
                font-size: 0.7rem !important;
            }
            .admin-container {
                padding: 2rem 1.2rem !important;
            }
            .admin-grid {
                grid-template-columns: 1fr !important;
                gap: 2rem !important;
            }
            .card {
                padding: 2rem 1.2rem !important;
            }
            /* Adaptar tabla para evitar roturas de diseño */
            .list-table {
                display: block !important;
                width: 100% !important;
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch !important;
                white-space: nowrap !important;
            }
        }
    </style>
</head>
<body>

    <nav class="admin-nav">
        <div class="logo">Chaparro · Admin</div>
        <div class="nav-links">
            <a href="index.php">Subir Material</a>
            <a href="manage.php" class="active">Gestionar Estructura</a>
            <a href="debates.php">Debates</a>
            <a href="students.php">Alumnos</a>
            <a href="logout.php">Salir</a>
        </div>
    </nav>

    <div class="container">
        <div class="tabs-wrapper">

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('escuelas', this)">Escuelas</button>
            <button class="tab-btn" onclick="switchTab('anios', this)">Cursos / Años</button>
            <button class="tab-btn" onclick="switchTab('materias', this)">Materias</button>
            <button class="tab-btn" onclick="switchTab('temas', this)">Temas</button>
        </div>

        <!-- Tab: Escuelas -->
        <div id="tab-escuelas" class="tab-panel active">
            <div class="card">
                <h2 class="card-title">Nueva Escuela</h2>
                <label class="form-label">Nombre de la Institución</label>
                <input type="text" id="escuela-nombre" class="form-input" placeholder="Ej: Colegio Nacional Roca">
                <button class="btn-primary" onclick="addItem('escuela')">Agregar Escuela</button>
            </div>
            <div class="card">
                <h2 class="card-title">Escuelas Registradas</h2>
                <table class="list-table">
                    <thead><tr><th>#</th><th>Institución</th><th></th></tr></thead>
                    <tbody id="list-escuelas"></tbody>
                </table>
            </div>
        </div>

        <!-- Tab: Años -->
        <div id="tab-anios" class="tab-panel">
            <div class="card">
                <h2 class="card-title">Nuevo Curso / Año</h2>
                <label class="form-label">Institución</label>
                <select id="anio-escuela" class="form-input"></select>
                <label class="form-label">Nombre del Año / Curso</label>
                <input type="text" id="anio-nombre" class="form-input" placeholder="Ej: 5to Año, 1er Año, etc.">
                <button class="btn-primary" onclick="addItem('anio')">Agregar Curso</button>
            </div>
            <div class="card">
                <h2 class="card-title">Cursos Registrados</h2>
                <table class="list-table">
                    <thead><tr><th>Curso</th><th>Escuela</th><th></th></tr></thead>
                    <tbody id="list-anios"></tbody>
                </table>
            </div>
        </div>

        <!-- Tab: Materias -->
        <div id="tab-materias" class="tab-panel">
            <div class="card">
                <h2 class="card-title">Nueva Materia</h2>
                <label class="form-label">Institución</label>
                <select id="materia-escuela" class="form-input" onchange="filterAniosForMateria()"></select>
                <label class="form-label">Año / Curso</label>
                <select id="materia-anio" class="form-input"></select>
                <label class="form-label">Nombre de la Materia</label>
                <input type="text" id="materia-nombre" class="form-input" placeholder="Ej: Historia, Geografía, etc.">
                <button class="btn-primary" onclick="addItem('materia')">Agregar Materia</button>
            </div>
            <div class="card">
                <h2 class="card-title">Materias Registradas</h2>
                <table class="list-table">
                    <thead><tr><th>Materia</th><th>Curso · Escuela</th><th></th></tr></thead>
                    <tbody id="list-materias"></tbody>
                </table>
            </div>
        </div>

        <!-- Tab: Temas -->
        <div id="tab-temas" class="tab-panel">
            <div class="card">
                <h2 class="card-title">Nuevo Tema</h2>
                <label class="form-label">Materia</label>
                <select id="tema-materia" class="form-input"></select>
                <label class="form-label">Nombre del Tema</label>
                <input type="text" id="tema-nombre" class="form-input" placeholder="Ej: La Revolución Francesa">
                <button class="btn-primary" onclick="addItem('tema')">Agregar Tema</button>
            </div>
            <div class="card">
                <h2 class="card-title">Temas Registrados</h2>
                <table class="list-table">
                    <thead><tr><th>Tema</th><th>Materia</th><th></th></tr></thead>
                    <tbody id="list-temas"></tbody>
                </table>
            </div>
        </div>
        </div><!-- /tabs-wrapper -->

    </div>

    <div class="toast" id="toast"></div>

    <script>
    const csrfToken = "<?php echo $_SESSION['csrf_token']; ?>";

    // Función auxiliar global para escapar HTML y mitigar inyecciones XSS en el cliente
    function escapeHTML(str) {
        if (!str) return '';
        return str.toString().replace(/[&<>'"]/g, 
            tag => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                "'": '&#39;',
                '"': '&quot;'
            }[tag] || tag)
        );
    }

    let allData = { escuelas: [], anios: [], materias: [], temas: [] };

    // --- Init ---
    document.addEventListener('DOMContentLoaded', loadAll);

    async function loadAll() {
        const res = await fetch('manage_api.php', { method: 'POST', body: new URLSearchParams({ action: 'get_all' }) });
        allData = await res.json();
        renderAll();
    }

    function renderAll() {
        renderEscuelas();
        renderAnios();
        renderMaterias();
        renderTemas();
        populateSelects();
    }

    // --- Render Tables ---
    function renderEscuelas() {
        const tbody = document.getElementById('list-escuelas');
        tbody.innerHTML = allData.escuelas.length === 0 
            ? '<tr><td colspan="3" style="color:#999; font-size:0.8rem;">Sin escuelas registradas.</td></tr>'
            : allData.escuelas.map(e => `
            <tr>
                <td style="color:#999; font-size:0.7rem;">${e.id}</td>
                <td><strong>${escapeHTML(e.nombre)}</strong></td>
                <td><button class="btn-del" onclick="deleteItem('escuela', ${e.id}, this)">Eliminar</button></td>
            </tr>`).join('');
    }

    function renderAnios() {
        const tbody = document.getElementById('list-anios');
        tbody.innerHTML = allData.anios.length === 0 
            ? '<tr><td colspan="3" style="color:#999; font-size:0.8rem;">Sin cursos registrados.</td></tr>'
            : allData.anios.map(a => `
            <tr>
                <td><strong>${escapeHTML(a.numero_anio)}</strong></td>
                <td><span class="badge">${escapeHTML(a.escuela_nombre)}</span></td>
                <td><button class="btn-del" onclick="deleteItem('anio', ${a.id}, this)">Eliminar</button></td>
            </tr>`).join('');
    }

    function renderMaterias() {
        const tbody = document.getElementById('list-materias');
        tbody.innerHTML = allData.materias.length === 0 
            ? '<tr><td colspan="3" style="color:#999; font-size:0.8rem;">Sin materias registradas.</td></tr>'
            : allData.materias.map(m => `
            <tr>
                <td><strong>${escapeHTML(m.nombre)}</strong></td>
                <td><span class="badge">${escapeHTML(m.numero_anio)} · ${escapeHTML(m.escuela_nombre)}</span></td>
                <td><button class="btn-del" onclick="deleteItem('materia', ${m.id}, this)">Eliminar</button></td>
            </tr>`).join('');
    }

    function renderTemas() {
        const tbody = document.getElementById('list-temas');
        tbody.innerHTML = allData.temas.length === 0 
            ? '<tr><td colspan="2" style="color:#999; font-size:0.8rem;">Sin temas registrados.</td></tr>'
            : allData.temas.map(t => `
            <tr>
                <td><strong>${escapeHTML(t.nombre)}</strong></td>
                <td><span class="badge">${escapeHTML(t.materia_nombre)}</span></td>
                <td><button class="btn-del" onclick="deleteItem('tema', ${t.id}, this)">Eliminar</button></td>
            </tr>`).join('');
    }

    // --- Populate Selects ---
    function populateSelects() {
        // Escuela selects
        ['anio-escuela', 'materia-escuela'].forEach(id => {
            const sel = document.getElementById(id);
            sel.innerHTML = '<option value="">— Seleccionar —</option>' + 
                allData.escuelas.map(e => `<option value="${e.id}">${escapeHTML(e.nombre)}</option>`).join('');
        });

        // Materia select for temas
        const temSel = document.getElementById('tema-materia');
        temSel.innerHTML = '<option value="">— Seleccionar —</option>' + 
            allData.materias.map(m => `<option value="${m.id}">${escapeHTML(m.nombre)} (${escapeHTML(m.numero_anio)})</option>`).join('');
    }

    function filterAniosForMateria() {
        const escuelaId = document.getElementById('materia-escuela').value;
        const sel = document.getElementById('materia-anio');
        const filtered = allData.anios.filter(a => a.escuela_id == escuelaId);
        sel.innerHTML = '<option value="">— Seleccionar —</option>' + 
            filtered.map(a => `<option value="${a.id}">${escapeHTML(a.numero_anio)}</option>`).join('');
    }

    // --- Add Items ---
    async function addItem(type) {
        let body = new URLSearchParams();
        body.set('csrf_token', csrfToken);

        if (type === 'escuela') {
            const nombre = document.getElementById('escuela-nombre').value.trim();
            if (!nombre) return showToast('Escribe el nombre de la institución.', true);
            body.set('action', 'add_escuela');
            body.set('nombre', nombre);
        } else if (type === 'anio') {
            const nombre = document.getElementById('anio-nombre').value.trim();
            const escuela_id = document.getElementById('anio-escuela').value;
            if (!nombre || !escuela_id) return showToast('Completa todos los campos.', true);
            body.set('action', 'add_anio');
            body.set('nombre', nombre);
            body.set('escuela_id', escuela_id);
        } else if (type === 'materia') {
            const nombre = document.getElementById('materia-nombre').value.trim();
            const anio_id = document.getElementById('materia-anio').value;
            if (!nombre || !anio_id) return showToast('Selecciona el año y escribe la materia.', true);
            body.set('action', 'add_materia');
            body.set('nombre', nombre);
            body.set('anio_id', anio_id);
        } else if (type === 'tema') {
            const nombre = document.getElementById('tema-nombre').value.trim();
            const materia_id = document.getElementById('tema-materia').value;
            if (!nombre || !materia_id) return showToast('Selecciona la materia y escribe el tema.', true);
            body.set('action', 'add_tema');
            body.set('nombre', nombre);
            body.set('materia_id', materia_id);
        }

        const res = await fetch('manage_api.php', { method: 'POST', body });
        const data = await res.json();
        if (data.success) {
            showToast('¡Agregado correctamente!');
            // Clear input
            document.querySelectorAll('.form-input[type="text"], .form-input:not(select)').forEach(i => { if(!i.tagName || i.tagName === 'INPUT') i.value = ''; });
            await loadAll();
        } else {
            showToast(data.message || 'Error al guardar.', true);
        }
    }

    // --- Delete Items ---
    async function deleteItem(type, id, btn) {
        if (!confirm('¿Confirmas que deseas eliminar este elemento? Se eliminarán también todos sus contenidos relacionados.')) return;
        const body = new URLSearchParams({ action: `delete_${type}`, id, csrf_token: csrfToken });
        const res = await fetch('manage_api.php', { method: 'POST', body });
        const data = await res.json();
        if (data.success) {
            showToast('Eliminado correctamente.');
            await loadAll();
        } else {
            showToast('Error al eliminar.', true);
        }
    }

    // --- Tab Switching (sin Layout Shift) ---
    function switchTab(tab, btn) {
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById(`tab-${tab}`).classList.add('active');
        btn.classList.add('active');
    }

    // --- Toast ---
    function showToast(msg, isError = false) {
        const toast = document.getElementById('toast');
        toast.textContent = msg;
        toast.className = 'toast' + (isError ? ' error' : '') + ' show';
        setTimeout(() => { toast.classList.remove('show'); }, 3000);
    }
    </script>

</body>
</html>
