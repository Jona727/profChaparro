<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db.php'; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prof. Chaparro | The Little Vanity Style</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <!-- GSAP -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollToPlugin.min.js"></script>
</head>
<body>

    <nav class="header-nav" id="main-nav">
        <div class="logo">CHAPARRO.</div>
        <div class="nav-links">
            <a href="#" onclick="scrollToPanel(0); return false;">Perfil</a>
            <a href="#" onclick="scrollToPanel(2); return false;">Biblioteca</a>
            <a href="#" onclick="scrollToPanel(3); return false;">Debates</a>
            <a href="#" onclick="scrollToPanel(4); return false;">Contacto</a>
        </div>
    </nav>

    <div class="scroll-container">
        <div class="horizontal-wrapper">
            
            <!-- Panel 1: Hero -->
            <section class="panel">
                <div class="hero-content">
                    <div class="hero-img-wrap">
                        <img class="parallax-img" src="assets/img/avatar.jpg" alt="Prof. Chaparro" fetchpriority="high">
                    </div>
                    <div class="hero-text">
                        <div class="text-sub">El Profesor</div>
                        <h1 class="text-huge">Bruno<br>Chaparro</h1>
                    </div>
                </div>
                <div class="section-index"></div>
            </section>

            <!-- Panel 2: About -->
            <section class="panel">
                <div class="about-grid">
                    <div class="about-text">
                        <h2 class="text-huge" style="font-size: 8rem; margin-bottom: 0.5rem;">Vision</h2>
                        <p class="text-sub" style="font-size: 0.8rem; margin-bottom: 2.5rem; color: var(--text-secondary); letter-spacing: 0.2em;">Prof. de Ciencia Política</p>
                        <div class="editorial-block">
                            <p style="font-family: var(--font-serif); font-style: italic; font-size: 1.5rem; line-height: 1.4;">"La docencia es un pilar fundamental para el crecimiento del estado, construimos mentes que construyen estados, mientras más poderosas las mentes construidas, más poderoso y próspero será el estado que lideren."</p>
                        </div>
                    </div>
                    <div class="hero-img-wrap" style="width: 100%; height: 60vh;">
                         <img class="parallax-img" src="assets/img/vision.png" alt="Editorial Concept - Liderazgo, Ciencia Política y Educación" loading="lazy">
                    </div>
                </div>
                <div class="section-index"></div>
            </section>

            <!-- Panel 3: Library -->
            <section class="panel library-panel">
                <div class="library-container">
                    <div class="filters-sidebar">
                        <h2 class="text-sub" style="margin-bottom: 3rem; font-size: 1.5rem;">Archivos</h2>
                        <select id="school-select" class="custom-select">
                            <option value="">Escuela</option>
                            <?php
                            $stmt = $pdo->query("SELECT * FROM escuelas ORDER BY nombre");
                            while ($row = $stmt->fetch()) echo "<option value='{$row['id']}'>" . htmlspecialchars($row['nombre']) . "</option>";
                            ?>
                        </select>
                        <select id="year-select" class="custom-select"><option value="">Año</option></select>
                        <select id="subject-select" class="custom-select"><option value="">Materia</option></select>
                        <select id="topic-select" class="custom-select"><option value="">Tema</option></select>
                        
                        <div class="hero-img-wrap" style="width: 100%; height: 30vh; margin-top: 5rem; opacity: 0.8;">
                             <img class="parallax-img" src="assets/img/machiavelli.png" alt="Niccolò Machiavelli - Pensamiento Político" loading="lazy">
                        </div>
                    </div>

                    <div class="files-area" id="file-grid">
                        <p style="grid-column: 1/-1; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-secondary);">Selecciona filtros para revelar el material.</p>
                    </div>

                    <div class="pagination" id="pagination" style="display:none;"></div>

                    <div class="hero-img-wrap" style="width: 30vw; height: 80vh; margin-left: 5vw; opacity: 0.9;">
                         <img class="parallax-img" src="assets/img/florence.png" alt="Florence Renaissance - Florencia Insurgente" loading="lazy">
                    </div>
                </div>
                <div class="section-index"></div>
            </section>

            <!-- Panel 4: Debates -->
            <section class="panel debates-panel">
                <div class="debates-container">
                    <div class="debates-sidebar">
                        <h2 class="text-sub" style="margin-bottom: 2rem; font-size: 1.5rem;">Debates</h2>
                        
                        <?php if (isset($_SESSION['student_logged_in'])): ?>
                            <div class="student-status-card">
                                <p style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-secondary);">Sesión Activa</p>
                                <h3 style="font-family: var(--font-serif); font-size: 1.3rem; margin: 0.5rem 0;"><?php echo htmlspecialchars($_SESSION['student_name']); ?></h3>
                                <a href="student/logout.php" class="btn-student-logout">Cerrar Sesión</a>
                            </div>
                        <?php else: ?>
                            <div class="student-login-invite">
                                <p style="font-size: 0.85rem; line-height: 1.6; color: var(--text-secondary); margin-bottom: 2rem;">Acceso restringido para alumnos de las cátedras del Prof. Chaparro.</p>
                                <a href="#" class="btn-student-login open-login-modal">Ingresar al Debate</a>
                            </div>
                        <?php endif; ?>
                        
                        <div class="debates-list-wrapper" style="margin-top: 3rem;">
                            <h4 style="font-family: var(--font-display); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 1rem; color: #888;">Debates Abiertos</h4>
                            <div id="debates-list">
                                <!-- Cargado dinámicamente -->
                            </div>
                        </div>
                    </div>

                    <div class="debate-main-area">
                        <div id="debate-content" style="width: 100%;">
                            <!-- Si no está logueado -->
                            <?php if (!isset($_SESSION['student_logged_in'])): ?>
                                <div class="editorial-debate-lock">
                                    <h3 style="font-family: var(--font-serif); font-size: 2.5rem; line-height: 1.3; margin-bottom: 2rem;">La palabra y el intercambio construyen conocimiento colectivo.</h3>
                                    <p style="font-size: 1rem; line-height: 1.8; color: var(--text-secondary); max-width: 500px; margin-bottom: 2rem;">La diversidad de pensamiento permite ver el mundo desde distintas perspectivas, sacándonos del sesgo ideológico.</p>
                                    <a href="#" class="btn-student-login open-login-modal" style="padding: 1rem 2.5rem; display: inline-block;">Ingresar con tus Credenciales</a>
                                </div>
                            <?php else: ?>
                                <!-- Si está logueado pero no hay debate seleccionado -->
                                <div id="debate-placeholder">
                                    <h3 style="font-family: var(--font-serif); font-size: 2.5rem; line-height: 1.3; margin-bottom: 1.5rem; font-style: italic;">Selecciona un debate a la izquierda.</h3>
                                    <p style="font-size: 0.95rem; color: var(--text-secondary);">Participa activamente proponiendo tus argumentos y analizando los de tus compañeros.</p>
                                </div>
                                
                                <!-- Detalle del Debate Activo -->
                                <div id="active-debate-view" style="display: none;">
                                    <h3 id="active-debate-title" style="font-family: var(--font-serif); font-size: 2.5rem; line-height: 1.2; margin-bottom: 1.5rem;"></h3>
                                    <p id="active-debate-desc" style="font-size: 0.95rem; line-height: 1.7; color: var(--text-secondary); margin-bottom: 3rem; background: var(--bg-alt); padding: 1.5rem; border-left: 3px solid var(--text-primary);"></p>

                                    <!-- Formulario para Opinar -->
                                    <div id="opinion-form-container">
                                        <h4 style="font-family: var(--font-display); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 1.5rem;">Plantea tu postura</h4>
                                        <form id="post-opinion-form">
                                            <input type="hidden" name="debate_id" id="form-debate-id">
                                            <div style="margin-bottom: 1.5rem;">
                                                <label style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.15em; font-weight: 700; color: #888; display: block; margin-bottom: 0.5rem;">Puntuación / Grado de Acuerdo (Opcional)</label>
                                                <div class="stars-rating-input">
                                                    <input type="radio" name="puntuacion" value="5" id="star5"><label for="star5" title="Totalmente de acuerdo">★</label>
                                                    <input type="radio" name="puntuacion" value="4" id="star4"><label for="star4" title="De acuerdo">★</label>
                                                    <input type="radio" name="puntuacion" value="3" id="star3"><label for="star3" title="Neutral / Indiferente">★</label>
                                                    <input type="radio" name="puntuacion" value="2" id="star2"><label for="star2" title="En desacuerdo">★</label>
                                                    <input type="radio" name="puntuacion" value="1" id="star1"><label for="star1" title="Totalmente en desacuerdo">★</label>
                                                </div>
                                            </div>
                                            <div style="margin-bottom: 2rem;">
                                                <label style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.15em; font-weight: 700; color: #888; display: block; margin-bottom: 0.5rem;">Tu Argumentación</label>
                                                <textarea name="opinion" id="opinion-text" required placeholder="Escribe tu opinión de forma respetuosa y fundamentada..." style="width: 100%; border: 1px solid var(--border-color); padding: 1rem; font-family: var(--font-body); font-size: 0.95rem; min-height: 120px; outline: none;"></textarea>
                                            </div>
                                            <button type="submit" class="btn-submit" style="font-size: 0.8rem; padding: 1rem 3rem;">Enviar Opinión</button>
                                        </form>
                                    </div>

                                    <!-- Mensaje de Participación Realizada -->
                                    <div id="already-participated-msg" style="display: none; background: #f0fff4; border: 1px solid #c6f6d5; color: #22543d; padding: 1.5rem; font-size: 0.9rem; margin-bottom: 3rem; text-transform: uppercase; font-family: var(--font-display); letter-spacing: 0.05em; font-weight: 700;">
                                        ✓ Ya has participado en este debate. Tus argumentos se han publicado.
                                    </div>

                                    <!-- Respuestas de Compañeros -->
                                    <div id="opinions-feed-container" style="margin-top: 3rem; border-top: 1px solid var(--border-color); padding-top: 3rem;">
                                        <h4 style="font-family: var(--font-display); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 2rem; color: #888;">Opiniones del Curso (Anónimas)</h4>
                                        <div id="opinions-feed" class="opinions-feed-list">
                                            <!-- Opiniones cargadas dinámicamente -->
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="hero-img-wrap" style="width: 22vw; height: 50vh; opacity: 0.85; align-self: flex-end;">
                         <img class="parallax-img" src="assets/img/debates.png" alt="Debates Académicos - Filosofía y Pensamiento" loading="lazy">
                    </div>
                </div>
                <div class="section-index"></div>
            </section>

            <!-- Panel 5: Contact -->
            <section class="panel contact-panel">
                <div class="contact-container" style="display: flex; width: 100%; align-items: center; justify-content: space-between; gap: 4vw;">
                    <div class="contact-left" style="width: 60vw; display: flex; align-items: center; gap: 4vw;">
                        <h2 class="text-huge" style="font-size: 6rem; white-space: nowrap; line-height: 0.9;">Let's<br>Talk</h2>
                        <form class="contact-form" action="contact_handler.php" method="POST" style="width: 100%; max-width: 500px; margin: 0;">
                            <input type="text" name="name" class="form-input" placeholder="Tu Nombre *" required>
                            <input type="email" name="email" class="form-input" placeholder="Tu Email *" required>
                            <input type="text" name="message" class="form-input" placeholder="Mensaje *" required>
                            <button type="submit" class="btn-submit">Enviar Request</button>
                        </form>
                    </div>
                    <div class="hero-img-wrap" style="width: 22vw; height: 50vh; opacity: 0.85; align-self: flex-end;">
                         <img class="parallax-img" src="assets/img/contact.png" alt="Escríbenos - Pluma y Tinta Clásica" loading="lazy">
                    </div>
                </div>

                <div class="section-index"></div>
            </section>

        </div>
    </div>

    <!-- Footer de Desarrollo -->
    <footer class="dev-footer">
        <p>&copy; <?php echo date('Y'); ?>. Desarrollado por <strong>Ramirez Jonatan</strong>. Todos los derechos reservados.</p>
        <div class="dev-contacts">
            <a href="mailto:support@jonaramdev.com">support@jonaramdev.com</a>
        </div>
    </footer>

    <!-- Modal Portal Alumnos -->
    <div class="student-modal" id="student-login-modal" style="display: none;">
        <div class="student-modal-overlay" id="modal-overlay-bg"></div>
        <div class="student-modal-card">
            <button class="student-modal-close" id="close-login-modal">&times;</button>
            <div class="student-modal-header">
                <p>Portal Alumnos</p>
                <h2>Debate<br>Académico</h2>
            </div>
            
            <div class="modal-error-msg" id="modal-login-error" style="display: none;"></div>

            <form id="modal-student-login-form" style="width: 100%;">
                <div class="modal-form-group">
                    <label>Usuario</label>
                    <input type="text" name="username" class="modal-form-input" required autocomplete="username">
                </div>
                <div class="modal-form-group" style="margin-bottom: 2rem;">
                    <label>Contraseña</label>
                    <input type="password" name="password" class="modal-form-input" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn-modal-login">Ingresar</button>
            </form>
            
            <p style="margin-top: 3rem; font-size: 0.6rem; color: #ccc; text-transform: uppercase; letter-spacing: 0.1em; text-align: center; width: 100%;">
                &copy; <?php echo date('Y'); ?> Prof. Chaparro Bruno Rafael
            </p>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
