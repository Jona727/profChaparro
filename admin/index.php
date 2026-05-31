<?php
require_once 'auth.php';
require_once '../includes/db.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin | Prof. Chaparro</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <style>
        /* Revertir a estética blanca/editorial */
        :root {
            --bg-color: #ffffff;
            --bg-alt: #f9f9f9;
            --text-primary: #111111;
            --text-secondary: #666666;
            --border-color: #e0e0e0;
        }
        body { background: var(--bg-color); color: var(--text-primary); }
        
        .admin-nav { 
            padding: 2rem 4rem; 
            border-bottom: 1px solid var(--border-color); 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            background: #fff;
        }
        .admin-nav h3 { font-family: 'Syne', sans-serif; text-transform: uppercase; letter-spacing: 0.1em; font-size: 1rem; }
        .nav-links a { margin-left: 2rem; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 700; color: #666; text-decoration: none; }
        .nav-links a:hover, .nav-links a.active { color: #111; }
        
        .admin-container { padding: 4rem; max-width: 1400px; margin: 0 auto; }
        
        .admin-grid { 
            display: grid; 
            grid-template-columns: 400px 1fr; 
            gap: 4rem; 
        }
        
        .card { 
            background: #fff; 
            padding: 3rem; 
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 30px rgba(0,0,0,0.02);
        }
        
        .card h2 { font-family: 'Playfair Display', serif; margin-bottom: 2rem; font-size: 2rem; }
        
        /* Selectores tipo editorial */
        .custom-select {
            width: 100%;
            padding: 1rem 0;
            background: transparent;
            border: none;
            border-bottom: 1px solid var(--border-color);
            font-family: var(--font-body);
            font-size: 0.9rem;
            color: var(--text-primary);
            outline: none;
            margin-bottom: 1.5rem;
            cursor: pointer;
            text-transform: uppercase;
        }
        
        .form-input-admin {
            width: 100%;
            border: none;
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 0;
            margin-bottom: 2rem;
            outline: none;
            font-size: 1rem;
        }
        
        .upload-area { 
            border: 1px dashed var(--border-color); 
            padding: 2rem; 
            text-align: center; 
            margin: 2rem 0; 
            cursor: pointer; 
            transition: 0.3s;
            background: var(--bg-alt);
        }
        .upload-area:hover { border-color: #000; background: #eee; }
        
        table { width: 100%; border-collapse: collapse; }
        th { 
            text-align: left; 
            text-transform: uppercase; 
            letter-spacing: 0.1em; 
            font-size: 0.7rem; 
            padding: 1rem; 
            border-bottom: 2px solid #111; 
            font-family: 'Syne', sans-serif;
        }
        td { padding: 1.5rem 1rem; border-bottom: 1px solid var(--border-color); font-size: 0.9rem; }
        
        .btn-delete { color: #ff4d4d; text-decoration: none; font-size: 0.7rem; text-transform: uppercase; font-weight: 800; letter-spacing: 0.1em; cursor: pointer; background: none; border: none; }
        .btn-delete:hover { text-decoration: underline; }
        .btn-edit { color: #111; font-size: 0.7rem; text-transform: uppercase; font-weight: 800; letter-spacing: 0.1em; cursor: pointer; background: none; border: none; margin-right: 1rem; }
        .btn-edit:hover { text-decoration: underline; }
        .btn-save { color: #276749; font-size: 0.7rem; text-transform: uppercase; font-weight: 800; letter-spacing: 0.1em; cursor: pointer; background: none; border: none; margin-right: 0.5rem; }
        .btn-cancel { color: #888; font-size: 0.7rem; text-transform: uppercase; font-weight: 800; letter-spacing: 0.1em; cursor: pointer; background: none; border: none; }
        .inline-edit-input { border: none; border-bottom: 2px solid #111; outline: none; font-size: 0.9rem; font-weight: 700; width: 100%; padding: 0.2rem 0; font-family: 'Inter', sans-serif; }

        .btn-publish {
            background: #111;
            color: #fff;
            border: none;
            padding: 1.2rem;
            width: 100%;
            font-family: 'Syne', sans-serif;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.1em;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-publish:hover { background: #333; transform: translateY(-2px); }

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
            table {
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
        <h3>Chaparro · Admin</h3>
        <div class="nav-links">
            <a href="index.php" class="active">Subir Material</a>
            <a href="manage.php">Gestionar Estructura</a>
            <a href="debates.php">Debates</a>
            <a href="students.php">Alumnos</a>
            <a href="logout.php">Salir</a>
        </div>
    </nav>

    <div class="admin-container">
        <div class="admin-grid">
            <!-- Sidebar: Carga -->
            <div class="card">
                <h2>Cargar Material</h2>
                <form id="upload-form" action="process.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="upload">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <select name="escuela_id" id="school-select" class="custom-select" required>
                        <option value="">Escuela</option>
                        <?php
                        $stmt = $pdo->query("SELECT * FROM escuelas ORDER BY nombre");
                        while($r = $stmt->fetch()) echo "<option value='{$r['id']}'>" . htmlspecialchars($r['nombre']) . "</option>";
                        ?>
                    </select>
                    <select name="anio_id" id="year-select" class="custom-select" required><option value="">Año</option></select>
                    <select name="materia_id" id="subject-select" class="custom-select" required><option value="">Materia</option></select>
                    <select name="tema_id" id="topic-select" class="custom-select" required><option value="">Tema</option></select>
                    
                    <label style="font-size: 0.6rem; text-transform: uppercase; letter-spacing: 0.1em; color: #888;">Título del Material</label>
                    <input type="text" name="titulo" class="form-input-admin" required placeholder="Ej: Apunte de Clase N1">
                    
                    <div id="drop-zone" class="upload-area">
                        <p style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.1em; color: #666;">Arrastra el PDF aquí</p>
                        <input type="file" name="pdf" id="pdf-input" hidden accept=".pdf">
                        <div id="file-name" style="margin-top: 1rem; color: #111; font-weight: 700; font-size: 0.8rem;"></div>
                    </div>
                    
                    <button type="submit" class="btn-publish">Publicar en Archivo</button>
                </form>
            </div>

            <!-- Content: Lista -->
            <div class="card">
                <h2>Publicaciones</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Tema</th>
                            <th>Fecha</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->query("SELECT a.*, t.nombre as tema FROM archivos a JOIN temas t ON a.tema_id = t.id ORDER BY a.id DESC");
                        while($row = $stmt->fetch()):
                        ?>
                        <tr id="row-<?php echo $row['id']; ?>">
                            <td id="title-cell-<?php echo $row['id']; ?>">
                                <strong><?php echo htmlspecialchars($row['titulo']); ?></strong>
                            </td>
                            <td style="color: var(--text-secondary); text-transform: uppercase; font-size: 0.7rem;"><?php echo htmlspecialchars($row['tema']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['fecha_subida'])); ?></td>
                            <td>
                                <button class="btn-edit" onclick="editTitle(<?php echo $row['id']; ?>, '<?php echo addslashes($row['titulo']); ?>')">Editar</button>
                                <a href="process.php?action=delete&id=<?php echo $row['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" class="btn-delete" onclick="return confirm('¿Eliminar material definitivamente?')">Eliminar</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Scripts de Filtrado Dinámico (Reutilizados del index) -->
    <script>
        const csrfToken = "<?php echo $_SESSION['csrf_token']; ?>";
        const schoolSelect = document.getElementById('school-select');
        const yearSelect = document.getElementById('year-select');
        const subjectSelect = document.getElementById('subject-select');
        const topicSelect = document.getElementById('topic-select');

        schoolSelect.onchange = async () => {
            yearSelect.innerHTML = '<option value="">Año</option>';
            subjectSelect.innerHTML = '<option value="">Materia</option>';
            topicSelect.innerHTML = '<option value="">Tema</option>';
            if(schoolSelect.value) {
                const data = await fetchData('anios', schoolSelect.value);
                populateSelect(yearSelect, data, 'Año');
            }
        };

        yearSelect.onchange = async () => {
            subjectSelect.innerHTML = '<option value="">Materia</option>';
            topicSelect.innerHTML = '<option value="">Tema</option>';
            if(yearSelect.value) {
                const data = await fetchData('materias', yearSelect.value);
                populateSelect(subjectSelect, data, 'Materia');
            }
        };

        subjectSelect.onchange = async () => {
            topicSelect.innerHTML = '<option value="">Tema</option>';
            if(subjectSelect.value) {
                const data = await fetchData('temas', subjectSelect.value);
                populateSelect(topicSelect, data, 'Tema');
            }
        };

        async function fetchData(type, parentId) {
            const response = await fetch(`../api/get_data.php?type=${type}&parent_id=${parentId}`);
            return await response.json();
        }

        function populateSelect(select, data, label) {
            select.innerHTML = `<option value="">${label}</option>`;
            data.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.id;
                opt.textContent = item.nombre || item.numero_anio;
                select.appendChild(opt);
            });
        }

        // Drag & Drop
        const dropZone = document.getElementById('drop-zone');
        const pdfInput = document.getElementById('pdf-input');
        const fileName = document.getElementById('file-name');

        dropZone.onclick = () => pdfInput.click();
        pdfInput.onchange = (e) => { if(e.target.files.length) fileName.textContent = e.target.files[0].name; };
        dropZone.ondragover = (e) => { e.preventDefault(); dropZone.style.background = '#eee'; };
        dropZone.ondragleave = () => { dropZone.style.background = 'var(--bg-alt)'; };
        dropZone.ondrop = (e) => {
            e.preventDefault();
            pdfInput.files = e.dataTransfer.files;
            fileName.textContent = e.dataTransfer.files[0].name;
            dropZone.style.background = 'var(--bg-alt)';
        };

        // --- Edición Inline de Título ---
        function editTitle(id, currentTitle) {
            const cell = document.getElementById(`title-cell-${id}`);
            // Reemplazar el texto por un input editable
            cell.innerHTML = `
                <input type="text" class="inline-edit-input" id="edit-input-${id}" value="${currentTitle}">
            `;
            // Reemplazar el botón Editar por Guardar/Cancelar
            const actionCell = cell.nextElementSibling.nextElementSibling.nextElementSibling;
            actionCell.innerHTML = `
                <button class="btn-save" onclick="saveTitle(${id})">Guardar</button>
                <button class="btn-cancel" onclick="cancelEdit(${id}, '${currentTitle.replace(/'/g, "\\'")}')">Cancelar</button>
            `;
            document.getElementById(`edit-input-${id}`).focus();
        }

        async function saveTitle(id) {
            const input = document.getElementById(`edit-input-${id}`);
            const newTitle = input.value.trim();
            if (!newTitle) return;

            const body = new URLSearchParams({ action: 'edit_title', id, titulo: newTitle, csrf_token: csrfToken });
            const res = await fetch('process.php', { method: 'POST', body });
            const data = await res.json();

            if (data.success) {
                // Actualizar la vista sin recargar
                const cell = document.getElementById(`title-cell-${id}`);
                cell.innerHTML = `<strong>${newTitle}</strong>`;
                const actionCell = cell.nextElementSibling.nextElementSibling.nextElementSibling;
                actionCell.innerHTML = `
                    <button class="btn-edit" onclick="editTitle(${id}, '${newTitle.replace(/'/g, "\\'")}')">Editar</button>
                    <a href="process.php?action=delete&id=${id}&csrf_token=${csrfToken}" class="btn-delete" onclick="return confirm('¿Eliminar material definitivamente?')">Eliminar</a>
                `;
            } else {
                alert('Error al guardar el título.');
            }
        }

        function cancelEdit(id, originalTitle) {
            const cell = document.getElementById(`title-cell-${id}`);
            cell.innerHTML = `<strong>${originalTitle}</strong>`;
            const actionCell = cell.nextElementSibling.nextElementSibling.nextElementSibling;
            actionCell.innerHTML = `
                <button class="btn-edit" onclick="editTitle(${id}, '${originalTitle.replace(/'/g, "\\'")}')">Editar</button>
                <a href="process.php?action=delete&id=${id}&csrf_token=${csrfToken}" class="btn-delete" onclick="return confirm('¿Eliminar material definitivamente?')">Eliminar</a>
            `;
        }
    </script>
</body>
</html>
