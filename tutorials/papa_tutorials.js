(() => {
    const spotlightId = 'papa-tour-spotlight';
    const tooltipId = 'papa-tour-tooltip';
    let currentIndex = 0;
    let active = false;

    let steps = []; 
    const mobileBreakpoint = 768;

    const getSpotlight = () => document.getElementById(spotlightId);
    const getTooltip = () => document.getElementById(tooltipId);

    const isMobile = () => window.innerWidth <= mobileBreakpoint;

    const setSidebarOpen = (shouldOpen) => {
        const sidebar = document.getElementById('sidebar');
        const icon = document.getElementById('collapseIcon');
        if (!sidebar) return;

        if (isMobile()) {
            sidebar.classList.toggle('open', Boolean(shouldOpen));
            return;
        }

        sidebar.classList.toggle('collapsed', !shouldOpen);
        if (icon) {
            icon.textContent = shouldOpen ? 'chevron_left' : 'chevron_right';
        }
    };

    const ensureSidebarOpen = () => setSidebarOpen(true);

    const openSaldoModal = () => {
        if (typeof window.abrirModalSaldo === 'function') {
            window.abrirModalSaldo();
        } else {
            clickFirst('[data-tutorial="btn-cargar-saldo"]');
        }
    };

    const closeSaldoModal = () => {
        if (typeof window.cerrarModalSaldo === 'function') {
            window.cerrarModalSaldo();
        }
    };

    const closeViandaModal = () => {
        if (typeof window.cerrarModalVianda === 'function') {
            window.cerrarModalVianda();
        }
    };

    const closeCancelModal = () => {
        if (typeof window.cerrarModalCancelacion === 'function') {
            window.cerrarModalCancelacion();
        }
    };

    const openCalendarModal = () => {
        if (typeof window.abrirModalCalendario === 'function') {
            window.abrirModalCalendario();
        }
    };

    const closeCalendarModal = () => {
        if (typeof window.cerrarModalCalendario === 'function') {
            window.cerrarModalCalendario();
        }
    };

    const closeCursosModal = () => {
        if (typeof window.cerrarModalActualizarCursos === 'function') {
            window.cerrarModalActualizarCursos();
        }
    };

    const openCursosModal = () => {
        if (typeof window.abrirModalActualizarCursos === 'function') {
            window.abrirModalActualizarCursos();
            return;
        }
        clickFirst('[data-tutorial="btn-actualizar-curso"]');
    };

    const clickFirst = (selector) => {
        const el = document.querySelector(selector);
        if (el) el.click();
    };

    const createSpotlight = () => {
        if (getSpotlight()) return;
        const spotlight = document.createElement('div');
        spotlight.id = spotlightId;
        spotlight.className = 'papa-tour-spotlight';
        document.body.appendChild(spotlight);
    };

    const createTooltip = (step, index, totalSteps, isLast, hasPrev) => {
        const existing = getTooltip();
        if (existing) existing.remove();
        const tooltip = document.createElement('div');
        tooltip.id = tooltipId;
        tooltip.className = 'papa-tour-tooltip';
        tooltip.innerHTML = `
            <div class="papa-tour-step-count">Paso ${index + 1} de ${totalSteps}</div>
            <h4>${step.title}</h4>
            <p>${step.message}</p>
            <div class="papa-tour-actions">
                ${hasPrev ? '<button class="papa-tour-prev" type="button">Atras</button>' : ''}
                <button class="${isLast ? 'papa-tour-end' : 'papa-tour-next'}" type="button">
                    ${isLast ? 'Finalizar' : 'Siguiente'}
                </button>
            </div>
        `;
        document.body.appendChild(tooltip);
        const prevBtn = tooltip.querySelector('.papa-tour-prev');
        const nextBtn = tooltip.querySelector('.papa-tour-next');
        const endBtn = tooltip.querySelector('.papa-tour-end');
        if (prevBtn) prevBtn.addEventListener('click', prevStep);
        if (nextBtn) nextBtn.addEventListener('click', nextStep);
        if (endBtn) endBtn.addEventListener('click', endTour);
    };

    const positionSpotlight = (target) => {
        const spotlight = getSpotlight();
        if (!spotlight) return;
        const rect = target.getBoundingClientRect();
        const padding = 8;
        spotlight.style.top = `${Math.max(rect.top - padding, 8)}px`;
        spotlight.style.left = `${Math.max(rect.left - padding, 8)}px`;
        spotlight.style.width = `${Math.max(rect.width + padding * 2, 16)}px`;
        spotlight.style.height = `${Math.max(rect.height + padding * 2, 16)}px`;
    };

    const positionTooltip = (target, position) => {
        const tooltip = getTooltip();
        if (!tooltip) return;
        const rect = target.getBoundingClientRect();
        const ttRect = tooltip.getBoundingClientRect();
        let top = rect.top;
        let left = rect.left;

        switch (position) {
            case 'right':
                top = rect.top + rect.height / 2 - ttRect.height / 2;
                left = rect.right + 14;
                break;
            case 'bottom':
                top = rect.bottom + 14;
                left = rect.left + rect.width / 2 - ttRect.width / 2;
                break;
            case 'top':
                top = rect.top - ttRect.height - 14;
                left = rect.left + rect.width / 2 - ttRect.width / 2;
                break;
            default:
                top = rect.bottom + 14;
                left = rect.left + rect.width / 2 - ttRect.width / 2;
        }

        const safeTop = Math.max(12, Math.min(top, window.innerHeight - ttRect.height - 12));
        const safeLeft = Math.max(12, Math.min(left, window.innerWidth - ttRect.width - 12));
        tooltip.style.top = `${safeTop}px`;
        tooltip.style.left = `${safeLeft}px`;
    };

    const scrollIntoView = (target) => {
        target.scrollIntoView({ behavior: 'smooth', block: 'center' });
    };

    const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

    const isVisible = (el) => {
        if (!el) return false;
        const style = window.getComputedStyle(el);
        if (style.display === 'none' || style.visibility === 'hidden' || style.opacity === '0') {
            return false;
        }
        return el.getClientRects().length > 0;
    };

    const findStepTarget = (step) => {
        const target = document.querySelector(step.element);
        return isVisible(target) ? target : null;
    };

    const waitForTarget = async (step) => {
        let target = findStepTarget(step);
        if (target) return target;

        for (let i = 0; i < 12; i += 1) {
            await sleep(120);
            target = findStepTarget(step);
            if (target) return target;
        }

        return null;
    };

    const openCancelModal = () => {
        const btn = document.querySelector('[data-cancelar-pedido]');
        if (!btn) return;
        const pedidoId = btn.getAttribute('data-pedido-id') || '';
        if (typeof window.abrirModalCancelacion === 'function') {
            window.abrirModalCancelacion(pedidoId);
            return;
        }
        btn.click();
    };

    const buildSteps = (variant) => {
        const isMobileVariant = variant === 'mobile';

        return [
            {
                title: 'Menu lateral',
                element: '[data-tutorial="sidebar"]',
                message: 'Desde aca navegas por Inicio, Cargar saldo, Viandas y Calendario.',
                position: 'right',
                onEnter: () => ensureSidebarOpen()
            },
            {
                title: 'Contraer menu',
                element: '#sidebar-collapse-btn',
                message: 'Usa este boton para mostrar u ocultar el menu lateral.',
                position: 'right',
                onEnter: () => ensureSidebarOpen(),
                onExit: () => {
                    if (isMobileVariant) {
                        setSidebarOpen(false);
                    }
                }
            },
            {
                title: 'Saldo prepago',
                element: '[data-tutorial="saldo-card"]',
                message: 'Primero carga saldo y espera la aprobacion. Con saldo aprobado podras pedir viandas.',
                position: 'bottom'
            },
            {
                title: 'Cargar saldo',
                element: '[data-tutorial="btn-cargar-saldo"]',
                message: 'Abre el formulario para cargar el comprobante y solicitar la recarga.',
                position: 'bottom'
            },
            {
                title: 'Formulario de saldo',
                element: '#saldo-modal .modal-content',
                message: 'Completa el monto, adjunta comprobante y envia la solicitud.',
                position: 'right',
                onEnter: () => openSaldoModal()
            },
            {
                title: 'Pedir vianda',
                element: '[data-tutorial="btn-pedir-vianda"]',
                message: 'Con saldo aprobado, selecciona un hijo y arma el pedido de viandas.',
                position: 'bottom',
                onEnter: () => closeSaldoModal()
            },
            {
                title: 'Actualizar curso',
                element: '[data-tutorial="btn-actualizar-curso"]',
                message: 'El icono del lapiz te permite actualizar el curso del hijo.',
                position: 'bottom'
            },
            {
                title: 'Detalle del pedido',
                element: '#vianda-modal .modal-content',
                message: 'Selecciona menus por dia, revisa el total y confirma el pedido.',
                position: 'right',
                onEnter: () => clickFirst('[data-tutorial="btn-pedir-vianda"]')
            },
            {
                title: 'Pedidos de comida',
                element: '#tabla-pedidos-comida',
                message: 'Aqui ves tus pedidos, la fecha de entrega y el estado.',
                position: 'top',
                onEnter: () => closeViandaModal()
            },
            {
                title: 'Cancelar pedido',
                element: '[data-cancelar-pedido]',
                message: 'Si el pedido esta habilitado, puedes cancelarlo desde este boton.',
                position: 'bottom',
                optional: true
            },
            {
                title: 'Motivo de cancelacion',
                element: '#cancelar-modal .modal-content',
                message: 'Indica el motivo y confirma para registrar la cancelacion.',
                position: 'right',
                optional: true,
                onEnter: () => openCancelModal(),
                onExit: () => closeCancelModal()
            },
            {
                title: 'Pedidos de saldo',
                element: '#tabla-pedidos-saldo',
                message: 'Revisa el estado de tus recargas y las observaciones.',
                position: 'top'
            },
            {
                title: 'Calendario',
                element: '[data-tutorial="menu-calendario"]',
                message: 'Abre el calendario para ver pedidos por semana o mes.',
                position: 'right',
                onEnter: () => ensureSidebarOpen()
            },
            {
                title: 'Vista calendario',
                element: '#calendario-modal .modal-content',
                message: 'Navega por fechas y cambia la vista mensual o semanal.',
                position: 'right',
                onEnter: () => openCalendarModal(),
                onExit: () => closeCalendarModal()
            }
        ];
    };

    const showStep = async (index) => {
        if (!active) return;
        const step = steps[index];
        if (!step) return;

        if (typeof step.onEnter === 'function') {
            step.onEnter();
        }

        let target = await waitForTarget(step);
        if (!target && step.optional) {
            return nextStep();
        }

        if (!target) return;

        scrollIntoView(target);
        createSpotlight();
        createTooltip(step, index, steps.length, index === steps.length - 1, index > 0);

        requestAnimationFrame(() => {
            positionSpotlight(target);
            positionTooltip(target, step.position);
        });
    };

    const nextStep = () => {
        if (!active) return;
        const currentStep = steps[currentIndex];
        if (currentStep && typeof currentStep.onExit === 'function') {
            currentStep.onExit();
        }
        if (currentIndex < steps.length - 1) {
            currentIndex += 1;
            showStep(currentIndex);
        } else {
            endTour();
        }
    };

    const prevStep = () => {
        if (!active) return;
        const currentStep = steps[currentIndex];
        if (currentStep && typeof currentStep.onExit === 'function') {
            currentStep.onExit();
        }
        if (currentIndex > 0) {
            currentIndex -= 1;
            showStep(currentIndex);
        }
    };

    const endTour = () => {
        active = false;
        const spotlight = getSpotlight();
        const tooltip = getTooltip();
        if (spotlight) spotlight.remove();
        if (tooltip) tooltip.remove();
        closeSaldoModal();
        closeViandaModal();
        closeCalendarModal();
        closeCancelModal();
    };

    const onResize = () => {
        if (!active) return;
        const step = steps[currentIndex];
        const target = step ? findStepTarget(step) : null;
        if (!target) return;
        positionSpotlight(target);
        positionTooltip(target, step.position);
    };

    const startTour = () => {
        active = true;
        currentIndex = 0;
        steps = buildSteps(isMobile() ? 'mobile' : 'desktop');
        showStep(currentIndex);
    };

    const bindStartButton = () => {
        const button = document.getElementById('tutorial-start-btn');
        if (!button) return;
        button.addEventListener('click', startTour);
    };

    document.addEventListener('keydown', (event) => {
        if (!active) return;
        if (event.key === 'Escape') {
            endTour();
        }
    });

    window.addEventListener('resize', onResize);
    window.addEventListener('scroll', onResize, true);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindStartButton);
    } else {
        bindStartButton();
    }

    window.startPapaTutorial = startTour;
})();
