// navbar
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const toggleIcon = document.getElementById('collapseIcon');
    const isMobile = window.innerWidth <= 768;

    if (isMobile) {
        sidebar.classList.toggle('open');
    } else {
        sidebar.classList.toggle('collapsed');
        // Cambia el ícono según el estado
        if (sidebar.classList.contains('collapsed')) {
            toggleIcon.textContent = 'chevron_right';
        } else {
            toggleIcon.textContent = 'chevron_left';
        }
    }
}

// Modales

function openModal() {
    document.getElementById("modal").classList.remove("hidden");
}

function closeModal() {
    document.getElementById("modal").classList.add("hidden");
}

// spinner
function showSpinner() {
    document.getElementById("globalSpinner").classList.remove("hidden");
}

function hideSpinner() {
    document.getElementById("globalSpinner").classList.add("hidden");
}


// Tabs
document.addEventListener("DOMContentLoaded", () => {
    const tabButtons = document.querySelectorAll(".tab-button");

    tabButtons.forEach(button => {
        button.addEventListener("click", () => {
            const tabId = button.getAttribute("data-tab");

            // Activar botón
            tabButtons.forEach(btn => btn.classList.remove("active"));
            button.classList.add("active");

            // Mostrar contenido
            const contents = document.querySelectorAll(".tab-content");
            contents.forEach(content => {
                content.classList.remove("active");
                if (content.id === tabId) {
                    content.classList.add("active");
                }
            });
        });
    });
    initInputIcons();

    document.querySelectorAll('textarea[maxlength]').forEach(textarea => {
        const id = textarea.id;
        const max = parseInt(textarea.getAttribute('maxlength'), 10);
        const counter = document.querySelector(`.char-count[data-for="${id}"]`);

        if (!counter) return;

        const updateCount = () => {
            const currentLength = textarea.value.length;
            const remaining = max - currentLength;
            counter.textContent = `Quedan ${remaining} caracteres.`;
            counter.classList.toggle('warning', remaining <= 20);
        };

        textarea.addEventListener('input', updateCount);
        updateCount(); // inicial
    });

    // Habilitar botón de publicación si campos requeridos están completos
    const formPublicacion = document.getElementById('form-publicacion');
    const btnGuardar = document.getElementById('btn-guardar');

    if (formPublicacion && btnGuardar) {
        const camposRequeridos = formPublicacion.querySelectorAll('[required]');

        const validarCampos = () => {
            let completo = true;
            camposRequeridos.forEach(campo => {
                if (!campo.value.trim()) {
                    completo = false;
                }
            });

            if (completo) {
                btnGuardar.disabled = false;
                btnGuardar.classList.remove('btn-disabled');
                btnGuardar.classList.add('btn-aceptar');
                btnGuardar.textContent = 'Publicar';
            } else {
                btnGuardar.disabled = true;
                btnGuardar.classList.add('btn-disabled');
                btnGuardar.classList.remove('btn-aceptar');
                btnGuardar.textContent = 'Completar datos...';
            }
        };

        formPublicacion.addEventListener('input', validarCampos);
        validarCampos(); // Verifica al cargar
    }
});

// input icons
function initInputIcons() {
    const mappings = {
        "input-icon-name": "person",
        "input-icon-dni": "badge",
        "input-icon-age": "hourglass_bottom",
        "input-icon-email": "mail",
        "input-icon-phone": "phone",
        "input-icon-date": "event",
        "input-icon-address": "home",
        "input-icon-cuit": "badge",
        "input-icon-cp": "markunread_mailbox",
        "input-icon-globe": "public",
        "input-icon-city": "location_city",
        "input-icon-comment": "comment",
        "input-icon-persona": "person_search",
        "input-icon-representante": "groups",
        "input-icon-tension": "flash_on",
        "input-icon-airport": "flight_takeoff",
        "input-icon-electric": "electrical_services",
        "input-icon-agua": "water_drop",
        "input-icon-campo": "terrain",
        "input-icon-despegue": "near_me",
        "input-icon-hectareas": "square_foot",
        "input-icon-pago": "payments",
        "input-icon-coop": "diversity_3",
        "input-icon-motivo": "pest_control",
        "input-icon-calle": "map",
        "input-icon-numero": "format_list_numbered"
    };

    document.querySelectorAll(".input-icon").forEach(wrapper => {
        const input = wrapper.querySelector("input, select, textarea");
        if (!input) return;

        for (const cls in mappings) {
            if (wrapper.classList.contains(cls)) {
                // Añadir ícono si no existe
                if (!wrapper.querySelector("span.material-icons")) {
                    const icon = document.createElement("span");
                    icon.classList.add("material-icons");
                    icon.textContent = mappings[cls];
                    wrapper.prepend(icon);
                }

                // Aplicar atributos extra por tipo
                switch (cls) {
                    case "input-icon-dni":
                        input.setAttribute("type", "text");
                        input.setAttribute("maxlength", "8");
                        input.setAttribute("pattern", "^[0-9]{7,8}$");
                        input.setAttribute("inputmode", "numeric");
                        input.setAttribute("required", "true");
                        break;

                    case "input-icon-age":
                        input.setAttribute("type", "number");
                        input.setAttribute("min", "0");
                        input.setAttribute("max", "120");
                        input.setAttribute("required", "true");
                        break;

                    case "input-icon-name":
                        input.setAttribute("type", "text");
                        input.setAttribute("minlength", "2");
                        input.setAttribute("maxlength", "255");
                        input.setAttribute("required", "true");
                        break;

                    case "input-icon-email":
                        input.setAttribute("type", "email");
                        input.setAttribute("required", "true");
                        break;

                    case "input-icon-phone":
                        input.setAttribute("type", "tel");
                        input.setAttribute("pattern", "^[0-9]{10}$");
                        input.setAttribute("maxlength", "10");
                        input.setAttribute("inputmode", "tel");
                        input.setAttribute("required", "true");
                        break;

                    case "input-icon-date":
                        input.setAttribute("type", "date");
                        input.setAttribute("required", "true");
                        break;

                    case "input-icon-cuit":
                        input.setAttribute("type", "text");
                        input.setAttribute("inputmode", "numeric");
                        input.setAttribute("pattern", "^[0-9]{11}$");
                        input.setAttribute("maxlength", "11");
                        input.setAttribute("required", "true");
                        break;

                    case "input-icon-cp":
                        input.setAttribute("type", "text");
                        input.setAttribute("pattern", "^[0-9]{4,6}$");
                        input.setAttribute("required", "true");
                        break;

                    case "input-icon-address":
                        input.setAttribute("type", "text");
                        input.setAttribute("required", "true");
                        break;

                    case "input-icon-city":
                        input.setAttribute("type", "text");
                        input.setAttribute("required", "true");
                        break;

                    case "input-icon-comment":
                        input.setAttribute("maxlength", "233");
                        input.setAttribute("required", "true");
                        break;

                    case "input-icon-globe":
                        // Generalmente es un <select>, validamos que tenga "required"
                        input.setAttribute("required", "true");
                        break;
                }

            }
        }
    });
}


// select pro
function toggleCustomSelect(container) {
    const options = container.querySelector('.custom-options');
    options.style.display = options.style.display === 'block' ? 'none' : 'block';
}

function selectOption(li) {
    const input = li.closest('.custom-select').querySelector('input');
    input.value = li.textContent;
    li.closest('.custom-options').style.display = 'none';
}

function filterOptions(input) {
    const filter = input.value.toLowerCase();
    const options = input.closest('.custom-select').querySelectorAll('li');
    options.forEach(li => {
        li.style.display = li.textContent.toLowerCase().includes(filter) ? 'block' : 'none';
    });
}

function validateNombre() {
    const input = document.getElementById("nombre-validado");
    const error = document.getElementById("error-nombre");
    error.style.display = input.value.trim().length < 3 ? "block" : "none";
}

function validateEdad() {
    const input = document.getElementById("edad-validada");
    const error = document.getElementById("error-edad");
    const value = parseInt(input.value);
    error.style.display = isNaN(value) || value < 18 || value > 30 ? "block" : "none";
}


// acordeon
function toggleAccordion(element) {
    const body = element.nextElementSibling;
    body.style.display = body.style.display === "block" ? "none" : "block";
}

// objeto selector con buscador interno
document.querySelectorAll('.smart-selector').forEach(selector => {
    const input = selector.querySelector('.smart-selector-search');
    const options = selector.querySelectorAll('.smart-selector-list label');

    input.addEventListener('input', () => {
        const search = input.value.toLowerCase();
        options.forEach(label => {
            const text = label.textContent.toLowerCase();
            label.style.display = text.includes(search) ? 'flex' : 'none';
        });
    });
});

// Floating Alert
// ShowAlert
function showAlert(type, message) {
    const alert = document.createElement('div');
    alert.className = `floating-alert ${type}`;
    alert.textContent = message;
    document.body.appendChild(alert);

    // Nueva duración: 5 segundos (5000ms)
    setTimeout(() => {
        alert.remove();
    }, 6000);
}
// ShowToast
function showToast(type = 'info', message = 'Mensaje') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');

    toast.className = `toast ${type}`;
    toast.textContent = message;

    container.appendChild(toast);

    // Eliminar el toast después de 15 segundos
    setTimeout(() => {
        toast.remove();
    }, 5000);
}

// ShowToastBoton
function showToastBoton(type = 'info', message = 'Mensaje') {
    console.log('Ejecutando showToastBoton:', type, message);
    const container = document.getElementById('toast-container-boton');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast toast-boton ${type}`;
    toast.innerHTML = `
        <button class="toast-close material-icons" onclick="this.parentElement.remove()">close</button>
        <span>${message}</span>
    `;
    container.appendChild(toast);
}


// categorias de productos
function toggleSubcategorias(btn) {
    const all = document.querySelectorAll('.subcategorias');
    all.forEach(ul => {
        if (ul !== btn.nextElementSibling) {
            ul.style.display = 'none';
        }
    });

    const sub = btn.nextElementSibling;
    sub.style.display = sub.style.display === 'block' ? 'none' : 'block';
}

// === TUTORIAL GUIADO POR PASOS ===

const tourSteps = [
    {
        element: ".sidebar", // menú lateral
        message: "Este es el menú lateral. Desde aquí navegás por todo el dashboard.",
        position: "right"
    },
    {
        element: "#btn-guardar", // botón guardar del formulario
        message: "Este botón te permite guardar la publicación cuando los datos están completos.",
        position: "top"
    },
];

let currentTourIndex = 0;

function startTour() {
    currentTourIndex = 0;
    createOverlay();
    showTourStep(currentTourIndex);
}

function createOverlay() {
    if (!document.getElementById("tour-overlay")) {
        const overlay = document.createElement("div");
        overlay.id = "tour-overlay";
        document.body.appendChild(overlay);
    }
}

function removeTour() {
    const existing = document.querySelector(".tour-tooltip");
    if (existing) existing.remove();
    const overlay = document.getElementById("tour-overlay");
    if (overlay) overlay.remove();
}

function showTourStep(index) {
    removeTour();

    const step = tourSteps[index];
    const target = document.querySelector(step.element);
    if (!target) return;

    const tooltip = document.createElement("div");
    tooltip.className = "tour-tooltip";
    tooltip.innerHTML = `
    <p>${step.message}</p>
    <div class="tour-actions">
      ${index > 0 ? `<button onclick="prevTourStep()">Anterior</button>` : ""}
      <button onclick="${index < tourSteps.length - 1 ? "nextTourStep()" : "endTour()"}">
        ${index < tourSteps.length - 1 ? "Siguiente" : "Finalizar"}
      </button>
    </div>
  `;

    document.body.appendChild(tooltip);

    // Posicionar tooltip
    const rect = target.getBoundingClientRect();
    const tt = tooltip.getBoundingClientRect();
    let top = 0, left = 0;

    switch (step.position) {
        case "top":
            top = rect.top - tt.height - 10;
            left = rect.left + rect.width / 2 - tt.width / 2;
            break;
        case "right":
            top = rect.top + rect.height / 2 - tt.height / 2;
            left = rect.right + 10;
            break;
        case "bottom":
            top = rect.bottom + 10;
            left = rect.left + rect.width / 2 - tt.width / 2;
            break;
        default:
            top = rect.top - tt.height - 10;
            left = rect.left + rect.width / 2 - tt.width / 2;
    }

    tooltip.style.top = `${Math.max(top, 20)}px`;
    tooltip.style.left = `${Math.max(left, 20)}px`;
}

function nextTourStep() {
    if (currentTourIndex < tourSteps.length - 1) {
        currentTourIndex++;
        showTourStep(currentTourIndex);
    }
}

function prevTourStep() {
    if (currentTourIndex > 0) {
        currentTourIndex--;
        showTourStep(currentTourIndex);
    }
}

function endTour() {
    removeTour();
    showToast("success", "¡Tutorial finalizado!");
}


// funciones de las metricas

function toggleRoundMetric(btn) {
    const card = btn.closest('.metric-round');
    const panel = card.querySelector('.metric-extra-round');
    const expanded = btn.getAttribute('aria-expanded') === 'true';

    // Toggle aria
    btn.setAttribute('aria-expanded', String(!expanded));

    // Abrir/cerrar con altura animada
    if (!expanded) {
        panel.classList.add('open');
        panel.style.maxHeight = panel.scrollHeight + 'px';
    } else {
        panel.style.maxHeight = panel.scrollHeight + 'px'; // set actual height first
        // force reflow para que la transición ocurra hacia 0
        void panel.offsetHeight;
        panel.style.maxHeight = '0px';
        // al terminar, removemos .open para resetear opacidad
        panel.addEventListener('transitionend', function onEnd(e) {
            if (e.propertyName === 'max-height') {
                panel.classList.remove('open');
                panel.removeEventListener('transitionend', onEnd);
            }
        });
    }
}


/* ===== Google Forms-like ===== */
document.addEventListener('DOMContentLoaded', () => {
    initGForm();
});

document.addEventListener('DOMContentLoaded', () => {
    initGForm('#form-dron');     // <— TU form real
});

function initGForm(formSelector = '#form-dron') {
    const form = document.querySelector(formSelector) || document.querySelector('form.gform-grid');
    if (!form) return;

    // “Otros” -> mostrar/ocultar input
    const otrosChk = form.querySelector('#g_motivo_otros_chk');
    const otrosInp = form.querySelector('#g_motivo_otros');

    function toggleOtros() {
        const show = otrosChk.checked;
        otrosInp.classList.toggle('oculto', !show);
        otrosInp.disabled = !show;
        if (!show) otrosInp.value = '';
        if (show) otrosInp.focus();
    }

    if (otrosChk && otrosInp) {
        otrosChk.addEventListener('change', toggleOtros);
        toggleOtros(); // estado inicial coherente
    }
}

function clearError(input) {
    const q = input.closest('.gform-question');
    if (q) q.classList.remove('is-error');
}

function validateGForm(form) {
    let valid = true;

    // Cada bloque con data-required=true
    form.querySelectorAll('.gform-question[data-required="true"]').forEach(q => {
        let blockValid = true;

        // 1) Si hay radio dentro, exige uno tildado
        const radios = q.querySelectorAll('input[type="radio"]');
        if (radios.length) {
            blockValid = Array.from(radios).some(r => r.checked);
        }

        // 2) Si hay checkboxes dentro (ej: quincenas/motivos), exige al menos uno
        const checks = q.querySelectorAll('input[type="checkbox"]');
        if (checks.length && !radios.length) {
            blockValid = Array.from(checks).some(c => c.checked);
        }

        // 3) Inputs/select/textarea individuales
        const single = q.querySelector('input.gform-input:not([type="radio"]):not([type="checkbox"]), select, textarea');
        if (single && !single.value.trim()) {
            blockValid = false;
        }

        if (!blockValid) {
            q.classList.add('is-error');
            valid = false;
        }
    });

    return valid;
}

(function () {
    function toggle(el, show) {
        el.hidden = !show;
        // deshabilito/rehabilito inputs dentro para no mandar basura al submit
        el.querySelectorAll('input, select, textarea').forEach(i => i.disabled = !show);
    }

    // Mostrar/ocultar el "complemento" cuando tildan/destildan la quincena
    document.addEventListener('change', function (e) {
        // a) checkbox principal
        if (e.target.matches('.gform-option input[type="checkbox"][data-complement]')) {
            const comp = document.querySelector(e.target.dataset.complement);
            if (!comp) return;

            if (e.target.checked) {
                toggle(comp, true);
                // Por defecto SVE seleccionado; oculto marca
                const brandWrap = comp.querySelector('.gform-brand');
                const yo = comp.querySelector('input[type="radio"][value="yo"]');
                const sve = comp.querySelector('input[type="radio"][value="sve"]');
                if (sve) sve.checked = true;
                if (brandWrap) {
                    toggle(brandWrap, false);
                    const brandInput = brandWrap.querySelector('input');
                    if (brandInput) brandInput.value = '';
                }
            } else {
                // Al destildar, cierro todo y limpio
                toggle(comp, false);
                const brandWrap = comp.querySelector('.gform-brand');
                if (brandWrap) {
                    toggle(brandWrap, false);
                    const brandInput = brandWrap.querySelector('input');
                    if (brandInput) brandInput.value = '';
                }
                // reseteo radios a SVE
                const sve = comp.querySelector('input[type="radio"][value="sve"]');
                if (sve) sve.checked = true;
            }
        }

        // b) radios SVE / YO dentro del complemento
        if (e.target.matches('.gform-complement input[type="radio"]')) {
            const comp = e.target.closest('.gform-complement');
            const brandWrap = comp.querySelector('.gform-brand');
            toggle(brandWrap, e.target.value === 'yo');
            if (e.target.value !== 'yo') {
                const brandInput = brandWrap?.querySelector('input');
                if (brandInput) brandInput.value = '';
            }
        }
    });

    // Inicializo estados por si el form viene con valores
    document.querySelectorAll('.gform-option input[type="checkbox"][data-complement]').forEach(cb => {
        cb.dispatchEvent(new Event('change', { bubbles: true }));
    });
})();


// Geolocalización
(function () {
    const yesSel = 'input[name="en-finca"][value="si"]';
    const noSel = 'input[name="en-finca"][value="no"]';

    const statusEl = document.getElementById('ubicacion_status');
    const latEl = document.getElementById('ubicacion_lat');
    const lngEl = document.getElementById('ubicacion_lng');
    const accEl = document.getElementById('ubicacion_acc');
    const tsEl = document.getElementById('ubicacion_ts');

    function resetCoords() {
        latEl.value = lngEl.value = accEl.value = tsEl.value = '';
    }

    function captureCoords() {
        if (!('geolocation' in navigator)) {
            statusEl.textContent = 'Tu navegador no soporta geolocalización.';
            return;
        }
        statusEl.textContent = 'Capturando ubicación…';

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const { latitude, longitude, accuracy } = pos.coords;
                latEl.value = latitude.toFixed(6);
                lngEl.value = longitude.toFixed(6);
                accEl.value = Math.round(accuracy);
                tsEl.value = new Date(pos.timestamp).toISOString();

                statusEl.innerHTML =
                    `Coordenadas guardadas: ${latitude.toFixed(6)}, ${longitude.toFixed(6)} · ±${Math.round(accuracy)} m. ` +
                    `<a href="https://maps.google.com/?q=${latitude},${longitude}" target="_blank" rel="noopener">Ver en Maps</a>`;
            },
            (err) => {
                resetCoords();
                let msg = 'No se pudo capturar la ubicación.';
                if (err.code === 1) msg = 'Permiso de ubicación denegado.';
                if (err.code === 2) msg = 'No se pudo determinar la ubicación (señal débil).';
                if (err.code === 3) msg = 'Tiempo de espera agotado al obtener la ubicación.';
                statusEl.textContent = msg + ' Podés intentar nuevamente.';
            },
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
        );
    }

    const yes = document.querySelector(yesSel);
    const no = document.querySelector(noSel);

    if (yes && no) {
        yes.addEventListener('change', (e) => { if (e.target.checked) captureCoords(); });
        no.addEventListener('change', (e) => { if (e.target.checked) { resetCoords(); statusEl.textContent = 'No se capturarán coordenadas.'; } });
    }
})();


// ==== Matriz: toggle por producto + validación (nuevo id) ====
document.addEventListener('DOMContentLoaded', () => {
    const formMatriz = document.getElementById('form_nuevo_servicio_matriz');
    if (!formMatriz) return;

    const toggles = formMatriz.querySelectorAll('.gfm-row-toggle');

    function setRowState(rowId, checked) {
        const radios = formMatriz.querySelectorAll(`input[name="m_${rowId}"]`);
        radios.forEach(r => {
            r.disabled = !checked;
            if (!checked) r.checked = false;
        });
    }

    toggles.forEach(t => {
        const rowId = t.dataset.row;
        setRowState(rowId, t.checked);
        t.addEventListener('change', (e) => setRowState(rowId, e.target.checked));
    });

    formMatriz.addEventListener('submit', (e) => {
        const q = formMatriz.querySelector('.gform-question');
        let ok = [...toggles].some(t => t.checked);
        toggles.forEach(t => {
            if (t.checked) {
                const rowId = t.dataset.row;
                if (!formMatriz.querySelector(`input[name="m_${rowId}"]:checked`)) ok = false;
            }
        });
        q.classList.toggle('is-error', !ok);
        if (!ok) { e.preventDefault(); q.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
    });
});



/* ====== Typeahead: Buscar persona en el mismo input ====== */
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('nombre-buscador');
    const list = document.getElementById('ta-list-nombres');
    if (!input || !list) return;

    // --- MOCK: "base de datos" local (reemplazá por fetch a tu API) ---
    const NOMBRES_DB = [
        { id: 1, nombre: 'Juan Pérez', doc: 'DNI 30.123.456' },
        { id: 2, nombre: 'Juana Paez', doc: 'DNI 31.222.111' },
        { id: 3, nombre: 'Julia Pereyra', doc: 'DNI 32.456.789' },
        { id: 4, nombre: 'Julio Peralta', doc: 'DNI 29.876.543' },
        { id: 5, nombre: 'Pedro Juárez', doc: 'DNI 25.987.321' },
        { id: 6, nombre: 'Pablo López', doc: 'DNI 28.555.888' },
        { id: 7, nombre: 'Carla Paredes', doc: 'DNI 33.100.200' },
        { id: 8, nombre: 'Carlos Peralta', doc: 'DNI 24.000.999' },
    ];

    // Simulá una consulta async (reemplazá por tu endpoint)
    async function queryNombres(text) {
        const q = text.trim().toLowerCase();
        if (!q) return [];
        // filtro simple (nombre incluye el texto)
        return NOMBRES_DB
            .filter(r => r.nombre.toLowerCase().includes(q))
            .slice(0, 8);
    }

    // --- utilidades ---
    const debounce = (fn, ms = 200) => {
        let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
    };

    function clearList() {
        list.innerHTML = '';
        list.hidden = true;
        input.setAttribute('aria-expanded', 'false');
        input.setAttribute('aria-activedescendant', '');
        activeIndex = -1;
    }

    function renderList(items) {
        list.innerHTML = '';
        if (!items.length) {
            list.innerHTML = `<li class="typeahead-empty" role="option" aria-disabled="true">Sin resultados…</li>`;
            list.hidden = false;
            input.setAttribute('aria-expanded', 'true');
            return;
        }

        items.forEach((it, i) => {
            const li = document.createElement('li');
            li.id = `ta-opt-${i}`;
            li.className = 'typeahead-item';
            li.setAttribute('role', 'option');
            li.innerHTML = `
        <span class="material-icons">person</span>
        <div>
          <strong>${it.nombre}</strong><br>
          <small>${it.doc ?? ''}</small>
        </div>
      `;
            li.addEventListener('mousedown', (e) => { // mousedown evita blur antes del click
                e.preventDefault();
                choose(it);
            });
            list.appendChild(li);
        });

        list.hidden = false;
        input.setAttribute('aria-expanded', 'true');
    }

    function choose(item) {
        input.value = item.nombre;
        input.dataset.personId = item.id; // por si querés enviar el id
        clearList();
        // si querés disparar algún evento:
        input.dispatchEvent(new Event('change', { bubbles: true }));
    }

    // --- lógica de tipeo ---
    const onType = debounce(async () => {
        const text = input.value;
        if (!text.trim()) return clearList();
        const items = await queryNombres(text);
        renderList(items);
        resultsCache = items;            // para navegación con teclado
        activeIndex = -1;
    }, 220);

    let resultsCache = [];
    let activeIndex = -1;

    input.addEventListener('input', onType);

    input.addEventListener('keydown', (e) => {
        const max = resultsCache.length - 1;
        if (list.hidden || !resultsCache.length) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            activeIndex = Math.min(max, activeIndex + 1);
            highlight();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            activeIndex = Math.max(0, activeIndex - 1);
            highlight();
        } else if (e.key === 'Enter') {
            if (activeIndex >= 0) {
                e.preventDefault();
                choose(resultsCache[activeIndex]);
            }
        } else if (e.key === 'Escape') {
            clearList();
        }
    });

    function highlight() {
        [...list.querySelectorAll('.typeahead-item')].forEach((li, i) => {
            const sel = i === activeIndex;
            li.setAttribute('aria-selected', sel ? 'true' : 'false');
            if (sel) {
                input.setAttribute('aria-activedescendant', li.id);
                li.scrollIntoView({ block: 'nearest' });
            }
        });
    }

    // Ocultar al perder foco (dejo un tick para permitir el click)
    input.addEventListener('blur', () => setTimeout(clearList, 120));
});

/* ===== Theme manager ===== */
(function () {
    const STORAGE_KEYS = {
        mode: 'impulsa_theme_mode',
        accent: 'impulsa_theme_accent',
        surface: 'impulsa_theme_surface',
        motion: 'impulsa_theme_motion'
    };

    const DEFAULTS = {
        mode: 'dark',
        accent: 'indigo',
        surface: 'glass',
        motion: 'smooth'
    };

    const media = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;

    function readSetting(key) {
        try {
            return localStorage.getItem(STORAGE_KEYS[key]) || DEFAULTS[key];
        } catch (error) {
            return DEFAULTS[key];
        }
    }

    function writeSetting(key, value) {
        try {
            localStorage.setItem(STORAGE_KEYS[key], value);
        } catch (error) {
            // ignore storage errors
        }
    }

    function resolveTheme(mode) {
        if (mode === 'auto' && media) {
            return media.matches ? 'dark' : 'light';
        }
        return mode === 'light' ? 'light' : 'dark';
    }

    function getThemeState() {
        return {
            mode: readSetting('mode'),
            accent: readSetting('accent'),
            surface: readSetting('surface'),
            motion: readSetting('motion')
        };
    }

    function applyThemeState(state) {
        const root = document.documentElement;

        root.dataset.theme = resolveTheme(state.mode);
        root.dataset.themeMode = state.mode;
        root.dataset.accent = state.accent;
        root.dataset.surface = state.surface;
        root.dataset.motion = state.motion === 'reduced' ? 'reduced' : 'smooth';

        if (!root.classList.contains('theme-ready')) {
            requestAnimationFrame(() => root.classList.add('theme-ready'));
        }
    }

    function syncThemeControls(panel, state) {
        if (!panel) return;

        panel.querySelectorAll('[data-theme-mode]').forEach((button) => {
            button.classList.toggle('is-active', button.dataset.themeMode === state.mode);
        });

        panel.querySelectorAll('[data-theme-accent]').forEach((button) => {
            button.classList.toggle('is-active', button.dataset.themeAccent === state.accent);
        });

        panel.querySelectorAll('[data-theme-surface]').forEach((button) => {
            button.classList.toggle('is-active', button.dataset.themeSurface === state.surface);
        });

        panel.querySelectorAll('[data-theme-motion]').forEach((button) => {
            const active = state.motion === 'reduced'
                ? button.dataset.themeMotion === 'reduced'
                : button.dataset.themeMotion === 'smooth';
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
    }

    function buildThemePanel() {
        if (!document.body || document.getElementById('themeSettingsDrawer')) {
            return;
        }

        const wrapper = document.createElement('div');
        wrapper.innerHTML = `
            <button type="button" class="theme-settings-btn" id="themeSettingsToggle" aria-label="Personalizar tema" aria-controls="themeSettingsDrawer" aria-expanded="false">
                <span class="material-icons" aria-hidden="true">settings</span>
            </button>
            <div class="theme-settings-overlay" id="themeSettingsOverlay" hidden></div>
            <aside class="theme-drawer" id="themeSettingsDrawer" aria-hidden="true" tabindex="-1">
                <div class="theme-drawer-header">
                    <div class="theme-drawer-title">
                        <h3>Personalizar tema</h3>
                        <p>Modo oscuro por defecto, con ajustes persistentes para cada usuario del navegador.</p>
                    </div>
                    <button type="button" class="theme-drawer-close" id="themeSettingsClose" aria-label="Cerrar panel">
                        <span class="material-icons" aria-hidden="true">close</span>
                    </button>
                </div>
                <div class="theme-drawer-body">
                    <section class="theme-section">
                        <h4>Modo</h4>
                        <div class="theme-choice-grid">
                            <button type="button" class="theme-choice-btn" data-theme-mode="dark">
                                <strong>Oscuro</strong>
                                <span>Continuidad con la landing</span>
                            </button>
                            <button type="button" class="theme-choice-btn" data-theme-mode="light">
                                <strong>Claro</strong>
                                <span>Mayor brillo general</span>
                            </button>
                            <button type="button" class="theme-choice-btn" data-theme-mode="auto">
                                <strong>Auto</strong>
                                <span>Sigue el sistema</span>
                            </button>
                        </div>
                    </section>
                    <section class="theme-section">
                        <h4>Acento</h4>
                        <div class="theme-accent-list">
                            <button type="button" class="theme-accent-btn" data-theme-accent="indigo" aria-label="Acento índigo">
                                <span class="theme-accent-swatch" style="background:linear-gradient(135deg,#6366f1,#818cf8)"></span>
                                <span>Índigo</span>
                            </button>
                            <button type="button" class="theme-accent-btn" data-theme-accent="cyan" aria-label="Acento cian">
                                <span class="theme-accent-swatch" style="background:linear-gradient(135deg,#06b6d4,#67e8f9)"></span>
                                <span>Cian</span>
                            </button>
                            <button type="button" class="theme-accent-btn" data-theme-accent="emerald" aria-label="Acento esmeralda">
                                <span class="theme-accent-swatch" style="background:linear-gradient(135deg,#10b981,#6ee7b7)"></span>
                                <span>Verde</span>
                            </button>
                            <button type="button" class="theme-accent-btn" data-theme-accent="amber" aria-label="Acento ámbar">
                                <span class="theme-accent-swatch" style="background:linear-gradient(135deg,#f59e0b,#fcd34d)"></span>
                                <span>Ámbar</span>
                            </button>
                        </div>
                    </section>
                    <section class="theme-section">
                        <h4>Superficie</h4>
                        <div class="theme-choice-grid">
                            <button type="button" class="theme-choice-btn" data-theme-surface="glass">
                                <strong>Glass</strong>
                                <span>Paneles translúcidos</span>
                            </button>
                            <button type="button" class="theme-choice-btn" data-theme-surface="solid">
                                <strong>Solid</strong>
                                <span>Más contraste y lectura</span>
                            </button>
                        </div>
                    </section>
                    <section class="theme-section">
                        <h4>Movimiento</h4>
                        <button type="button" class="theme-toggle-row" data-theme-motion="smooth" aria-pressed="false">
                            <div class="theme-option-copy">
                                <strong>Transiciones suaves</strong>
                                <p class="theme-option-hint">Activa cambios visuales entre tema y tema.</p>
                            </div>
                            <span class="theme-switch" aria-hidden="true"></span>
                        </button>
                        <button type="button" class="theme-toggle-row" data-theme-motion="reduced" aria-pressed="false">
                            <div class="theme-option-copy">
                                <strong>Movimiento reducido</strong>
                                <p class="theme-option-hint">Desactiva animaciones globales del sistema.</p>
                            </div>
                            <span class="theme-switch" aria-hidden="true"></span>
                        </button>
                    </section>
                </div>
                <div class="theme-drawer-footer">
                    <p>Los cambios se guardan en este navegador y se aplican en todas las vistas del dashboard.</p>
                </div>
            </aside>
        `;

        while (wrapper.firstChild) {
            document.body.appendChild(wrapper.firstChild);
        }
    }

    function initThemeManager() {
        buildThemePanel();

        const drawer = document.getElementById('themeSettingsDrawer');
        const toggle = document.getElementById('themeSettingsToggle');
        const overlay = document.getElementById('themeSettingsOverlay');
        const close = document.getElementById('themeSettingsClose');

        if (!drawer || !toggle || !overlay || !close) {
            return;
        }

        const state = getThemeState();
        applyThemeState(state);
        syncThemeControls(drawer, state);

        const openDrawer = () => {
            drawer.classList.add('is-open');
            overlay.hidden = false;
            requestAnimationFrame(() => overlay.classList.add('is-open'));
            drawer.setAttribute('aria-hidden', 'false');
            toggle.setAttribute('aria-expanded', 'true');
            drawer.focus();
        };

        const closeDrawer = () => {
            drawer.classList.remove('is-open');
            overlay.classList.remove('is-open');
            drawer.setAttribute('aria-hidden', 'true');
            toggle.setAttribute('aria-expanded', 'false');
            window.setTimeout(() => {
                if (!overlay.classList.contains('is-open')) {
                    overlay.hidden = true;
                }
            }, 240);
        };

        toggle.addEventListener('click', () => {
            if (drawer.classList.contains('is-open')) {
                closeDrawer();
            } else {
                openDrawer();
            }
        });

        close.addEventListener('click', closeDrawer);
        overlay.addEventListener('click', closeDrawer);

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && drawer.classList.contains('is-open')) {
                closeDrawer();
            }
        });

        drawer.querySelectorAll('[data-theme-mode]').forEach((button) => {
            button.addEventListener('click', () => {
                state.mode = button.dataset.themeMode;
                writeSetting('mode', state.mode);
                applyThemeState(state);
                syncThemeControls(drawer, state);
            });
        });

        drawer.querySelectorAll('[data-theme-accent]').forEach((button) => {
            button.addEventListener('click', () => {
                state.accent = button.dataset.themeAccent;
                writeSetting('accent', state.accent);
                applyThemeState(state);
                syncThemeControls(drawer, state);
            });
        });

        drawer.querySelectorAll('[data-theme-surface]').forEach((button) => {
            button.addEventListener('click', () => {
                state.surface = button.dataset.themeSurface;
                writeSetting('surface', state.surface);
                applyThemeState(state);
                syncThemeControls(drawer, state);
            });
        });

        drawer.querySelectorAll('[data-theme-motion]').forEach((button) => {
            button.addEventListener('click', () => {
                state.motion = button.dataset.themeMotion === 'reduced' ? 'reduced' : 'smooth';
                writeSetting('motion', state.motion);
                applyThemeState(state);
                syncThemeControls(drawer, state);
            });
        });

        if (media && typeof media.addEventListener === 'function') {
            media.addEventListener('change', () => {
                if (state.mode === 'auto') {
                    applyThemeState(state);
                    syncThemeControls(drawer, state);
                }
            });
        }
    }

    applyThemeState(getThemeState());
    document.addEventListener('DOMContentLoaded', initThemeManager);
})();
