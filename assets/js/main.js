gsap.registerPlugin(ScrollTrigger);

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

// Variables globales para que scrollToPanel pueda acceder desde los links del nav
let scrollTween, panels;

document.addEventListener('DOMContentLoaded', () => {
    
    const wrapper = document.querySelector('.horizontal-wrapper');
    panels = gsap.utils.toArray('.panel');

    // 1. Horizontal Scroll Tween & Parallax setup (Desktop vs Mobile)
    ScrollTrigger.matchMedia({
        // Desktop
        "(min-width: 1024px)": function() {
            scrollTween = gsap.to(panels, {
                xPercent: -100 * (panels.length - 1),
                ease: "none",
                scrollTrigger: {
                    trigger: ".scroll-container",
                    pin: true,
                    scrub: 1,
                    snap: {
                        snapTo: 1 / (panels.length - 1),
                        duration: { min: 0.2, max: 0.6 },
                        delay: 0.4,
                        ease: "power1.inOut"
                    },
                    end: () => "+=" + (wrapper.offsetWidth * 1.4)
                }
            });

            // Parallax Images within Horizontal Scroll
            gsap.utils.toArray('.parallax-img').forEach(image => {
                gsap.to(image, {
                    xPercent: 30,
                    ease: "none",
                    scrollTrigger: {
                        trigger: image.parentElement,
                        containerAnimation: scrollTween,
                        start: "left right",
                        end: "right left",
                        scrub: true
                    }
                });
            });

            // Text entrance animations
            panels.forEach((panel) => {
                let textHuge = panel.querySelector('.text-huge');
                if (textHuge) {
                    gsap.from(textHuge, {
                        opacity: 0,
                        x: 100,
                        duration: 1,
                        scrollTrigger: {
                            trigger: panel,
                            containerAnimation: scrollTween,
                            start: "left center",
                            toggleActions: "play none none reverse"
                        }
                    });
                }
            });
        },
        
        // Mobile / Tablet (Simple vertical entries)
        "(max-width: 1023px)": function() {
            panels.forEach((panel) => {
                let textHuge = panel.querySelector('.text-huge');
                if (textHuge) {
                    gsap.from(textHuge, {
                        opacity: 0,
                        y: 50,
                        duration: 0.8,
                        scrollTrigger: {
                            trigger: panel,
                            start: "top 80%",
                            toggleActions: "play none none reverse"
                        }
                    });
                }
            });
        }
    });

    // 4. Dynamic Filtering
    const schoolSelect = document.getElementById('school-select');
    const yearSelect = document.getElementById('year-select');
    const subjectSelect = document.getElementById('subject-select');
    const topicSelect = document.getElementById('topic-select');
    const fileGrid = document.getElementById('file-grid');
    const paginationEl = document.getElementById('pagination');

    const FILES_PER_PAGE = 6;
    let allFiles = [];
    let currentPage = 1;

    const updateFiles = async () => {
        const params = new URLSearchParams({
            school: schoolSelect.value,
            year: yearSelect.value,
            subject: subjectSelect.value,
            topic: topicSelect.value
        });

        try {
            const response = await fetch(`./api/get_files.php?${params}`);
            allFiles = await response.json();
            currentPage = 1;
            renderPage(currentPage);
        } catch (error) {
            console.error('Error fetching files:', error);
        }
    };

    const renderPage = (page) => {
        fileGrid.innerHTML = '';

        if (allFiles.length === 0) {
            fileGrid.innerHTML = '<p style="grid-column: 1/-1; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-secondary);">No se encontraron archivos.</p>';
            paginationEl.style.display = 'none';
            return;
        }

        const totalPages = Math.ceil(allFiles.length / FILES_PER_PAGE);
        const start = (page - 1) * FILES_PER_PAGE;
        const pageFiles = allFiles.slice(start, start + FILES_PER_PAGE);

        pageFiles.forEach((file) => {
            const card = document.createElement('div');
            card.className = 'file-item';
            card.innerHTML = `
                <div class="meta">${escapeHTML(file.fecha_subida.split(' ')[0])}</div>
                <h3>${escapeHTML(file.titulo)}</h3>
                <a href="${escapeHTML(file.ruta_pdf)}" target="_blank">
                    Download
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 13l5 5 5-5M12 18V6"/></svg>
                </a>
            `;
            fileGrid.appendChild(card);
        });

        // Render pagination only if more than 1 page
        if (totalPages <= 1) {
            paginationEl.style.display = 'none';
            return;
        }

        paginationEl.style.display = 'flex';
        paginationEl.innerHTML = '';

        // Prev button
        const prevBtn = document.createElement('button');
        prevBtn.className = 'pagination-btn';
        prevBtn.textContent = '← Prev';
        prevBtn.disabled = page === 1;
        prevBtn.onclick = () => { currentPage--; renderPage(currentPage); };
        paginationEl.appendChild(prevBtn);

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            if (totalPages > 7 && i > 2 && i < totalPages - 1 && Math.abs(i - page) > 1) {
                if (i === 3 || i === totalPages - 2) {
                    const sep = document.createElement('span');
                    sep.className = 'pagination-sep';
                    sep.textContent = '···';
                    paginationEl.appendChild(sep);
                }
                continue;
            }
            const btn = document.createElement('button');
            btn.className = 'pagination-btn' + (i === page ? ' active' : '');
            btn.textContent = i;
            btn.onclick = ((p) => () => { currentPage = p; renderPage(currentPage); })(i);
            paginationEl.appendChild(btn);
        }

        // Next button
        const nextBtn = document.createElement('button');
        nextBtn.className = 'pagination-btn';
        nextBtn.textContent = 'Next →';
        nextBtn.disabled = page === totalPages;
        nextBtn.onclick = () => { currentPage++; renderPage(currentPage); };
        paginationEl.appendChild(nextBtn);
    };

    // Event Listeners for hierarchical selects
    schoolSelect?.addEventListener('change', async () => {
        yearSelect.innerHTML = '<option value="">Año</option>';
        subjectSelect.innerHTML = '<option value="">Materia</option>';
        topicSelect.innerHTML = '<option value="">Tema</option>';
        if (schoolSelect.value) {
            const data = await fetchData('anios', schoolSelect.value);
            populateSelect(yearSelect, data, 'Año');
        }
        updateFiles();
    });

    yearSelect?.addEventListener('change', async () => {
        subjectSelect.innerHTML = '<option value="">Materia</option>';
        topicSelect.innerHTML = '<option value="">Tema</option>';
        if (yearSelect.value) {
            const data = await fetchData('materias', yearSelect.value);
            populateSelect(subjectSelect, data, 'Materia');
        }
        updateFiles();
    });

    subjectSelect?.addEventListener('change', async () => {
        topicSelect.innerHTML = '<option value="">Tema</option>';
        if (subjectSelect.value) {
            const data = await fetchData('temas', subjectSelect.value);
            populateSelect(topicSelect, data, 'Tema');
        }
        updateFiles();
    });

    topicSelect?.addEventListener('change', updateFiles);

    const fetchData = async (type, parentId) => {
        const response = await fetch(`./api/get_data.php?type=${type}&parent_id=${parentId}`);
        return await response.json();
    };

    const populateSelect = (select, data, label) => {
        select.innerHTML = `<option value="">${label}</option>`;
        data.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item.id;
            opt.textContent = item.nombre || item.numero_anio;
            select.appendChild(opt);
        });
    };

    // --- MÓDULO DE DEBATES ---
    const debatesList = document.getElementById('debates-list');
    const debatePlaceholder = document.getElementById('debate-placeholder');
    const activeDebateView = document.getElementById('active-debate-view');
    const activeDebateTitle = document.getElementById('active-debate-title');
    const activeDebateDesc = document.getElementById('active-debate-desc');
    const formDebateId = document.getElementById('form-debate-id');
    const opinionFormContainer = document.getElementById('opinion-form-container');
    const alreadyParticipatedMsg = document.getElementById('already-participated-msg');
    const opinionsFeed = document.getElementById('opinions-feed');
    const postOpinionForm = document.getElementById('post-opinion-form');

    let currentSelectedDebateId = null;

    const loadDebates = async () => {
        if (!debatesList) return;
        
        try {
            const response = await fetch('./api/get_debates.php');
            const debates = await response.json();
            
            debatesList.innerHTML = '';
            if (debates.length === 0) {
                debatesList.innerHTML = '<p style="font-size:0.75rem; color:#888; text-transform:uppercase; letter-spacing:0.1em; padding: 1rem 0;">No hay debates abiertos.</p>';
                return;
            }

            debates.forEach(d => {
                const item = document.createElement('a');
                item.className = `debate-item-link ${currentSelectedDebateId === d.id ? 'active' : ''}`;
                item.innerHTML = `
                    <div class="meta">${d.participado ? '✓ Participado' : 'Pendiente'}</div>
                    <h5>${escapeHTML(d.titulo)}</h5>
                `;
                item.addEventListener('click', (e) => {
                    e.preventDefault();
                    document.querySelectorAll('.debate-item-link').forEach(el => el.classList.remove('active'));
                    item.classList.add('active');
                    selectDebate(d);
                });
                debatesList.appendChild(item);
            });
        } catch (error) {
            console.error('Error loading debates:', error);
        }
    };

    const selectDebate = async (debate) => {
        currentSelectedDebateId = debate.id;
        
        if (debatePlaceholder) debatePlaceholder.style.display = 'none';
        if (activeDebateView) activeDebateView.style.display = 'block';
        
        if (activeDebateTitle) activeDebateTitle.textContent = debate.titulo;
        if (activeDebateDesc) activeDebateDesc.textContent = debate.descripcion;
        if (formDebateId) formDebateId.value = debate.id;

        // Cargar opiniones del debate
        await loadOpinions(debate.id);

        if (debate.participado) {
            if (opinionFormContainer) opinionFormContainer.style.display = 'none';
            if (alreadyParticipatedMsg) alreadyParticipatedMsg.style.display = 'block';
        } else {
            if (opinionFormContainer) opinionFormContainer.style.display = 'block';
            if (alreadyParticipatedMsg) alreadyParticipatedMsg.style.display = 'none';
        }
    };

    const loadOpinions = async (debateId) => {
        if (!opinionsFeed) return;
        
        try {
            const response = await fetch(`./api/get_opiniones.php?debate_id=${debateId}`);
            const opinions = await response.json();

            opinionsFeed.innerHTML = '';
            if (opinions.length === 0) {
                opinionsFeed.innerHTML = '<p style="font-size:0.85rem; color:#888;">Nadie ha opinado todavía. Sé el primero.</p>';
                return;
            }

            opinions.forEach(op => {
                const card = document.createElement('div');
                card.className = `opinion-card ${op.is_mine ? 'is-mine' : ''}`;
                
                let starsHTML = '';
                if (op.puntuacion) {
                    starsHTML = '<span class="stars">' + '★'.repeat(op.puntuacion) + '☆'.repeat(5 - op.puntuacion) + '</span>';
                } else {
                    starsHTML = '<span style="font-size:0.65rem; color:#aaa; text-transform:uppercase; font-family:var(--font-display); letter-spacing:0.1em; font-weight:800;">Sin voto</span>';
                }

                card.innerHTML = `
                    <div class="header">
                        <span class="author">${op.autor}</span>
                        ${starsHTML}
                    </div>
                    <p class="body-text">${op.opinion}</p>
                    <div class="date">${op.fecha}</div>
                `;
                opinionsFeed.appendChild(card);
            });
        } catch (error) {
            console.error('Error loading opinions:', error);
        }
    };

    if (postOpinionForm) {
        postOpinionForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(postOpinionForm);
            
            try {
                const response = await fetch('./api/post_opinion.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    postOpinionForm.reset();
                    document.querySelectorAll('.stars-rating-input input').forEach(inp => inp.checked = false);
                    
                    await loadDebates();
                    
                    const resp = await fetch('./api/get_debates.php');
                    const debates = await resp.json();
                    const updatedDebate = debates.find(d => d.id === currentSelectedDebateId);
                    if (updatedDebate) {
                        selectDebate(updatedDebate);
                    }
                } else {
                    alert(result.message || 'Error al enviar opinión.');
                }
            } catch (error) {
                console.error('Error posting opinion:', error);
            }
        });
    }

    // --- VENTANA EMERGENTE (MODAL) PORTAL ALUMNOS ---
    const studentLoginModal = document.getElementById('student-login-modal');
    const closeLoginModal = document.getElementById('close-login-modal');
    const modalOverlayBg = document.getElementById('modal-overlay-bg');
    const modalStudentLoginForm = document.getElementById('modal-student-login-form');
    const modalLoginError = document.getElementById('modal-login-error');

    const openModal = (e) => {
        if (e) e.preventDefault();
        if (studentLoginModal) {
            studentLoginModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            if (modalLoginError) modalLoginError.style.display = 'none';
            if (modalStudentLoginForm) modalStudentLoginForm.reset();
        }
    };

    const closeModal = () => {
        if (studentLoginModal) {
            studentLoginModal.style.display = 'none';
            document.body.style.overflow = '';
        }
    };

    document.querySelectorAll('.open-login-modal').forEach(btn => {
        btn.addEventListener('click', openModal);
    });

    if (closeLoginModal) closeLoginModal.addEventListener('click', closeModal);
    if (modalOverlayBg) modalOverlayBg.addEventListener('click', closeModal);

    if (modalStudentLoginForm) {
        modalStudentLoginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (modalLoginError) modalLoginError.style.display = 'none';
            
            const formData = new FormData(modalStudentLoginForm);
            
            try {
                const response = await fetch('./api/student_login.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    location.reload();
                } else {
                    if (modalLoginError) {
                        modalLoginError.textContent = result.message || 'Error de credenciales.';
                        modalLoginError.style.display = 'block';
                    }
                }
            } catch (error) {
                console.error('Error in student login:', error);
                if (modalLoginError) {
                    modalLoginError.textContent = 'Ocurrió un error en el servidor.';
                    modalLoginError.style.display = 'block';
                }
            }
        });
    }

    loadDebates();
});

/**
 * scrollToPanel(index)
 * Desplaza la vista horizontal al panel indicado.
 * Funciona calculando la posición de scroll equivalente al panel
 * dentro del ScrollTrigger de GSAP.
 * @param {number} index - 0=*01 Hero, 1=*02 About, 2=*03 Library, 3=*04 Contact
 */
function scrollToPanel(index) {
    if (!panels || !panels[index]) return;

    if (window.innerWidth >= 1024) {
        if (!scrollTween) return;
        const st = scrollTween.scrollTrigger;
        const totalScrollLength = st.end - st.start;
        const targetScroll = st.start + (totalScrollLength / (panels.length - 1)) * index;

        gsap.to(window, {
            scrollTo: targetScroll,
            duration: 1.2,
            ease: "power2.inOut"
        });
    } else {
        // En móviles, hacer scroll vertical al panel correspondiente
        gsap.to(window, {
            scrollTo: panels[index].offsetTop,
            duration: 1.2,
            ease: "power2.inOut"
        });
    }
}

