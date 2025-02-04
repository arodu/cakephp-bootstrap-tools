class ModalAjaxManager {
    constructor(config) {
        const defaultConfig = {
            target: 'ajax-modal',
            title: 'Modal Form',
            classes: {
                title: '.modal-title',
                body: '.modal-body'
            },
            form: {
                autoRender: true,      // Renderizar HTML en el modal
                autoRedirect: false,  // Redirigir automáticamente
                redirectKey: 'redirect', // Clave en JSON para redirección
                redirectHeader: 'X-Redirect-URL', // Header en HTML para redirección
                closeOnSuccess: false, // Cerrar modal tras éxito
                overwriteOnLoading: false, // Sobrescribir contenido al cargar
            },
            csrfToken: null
        };

        this.config = {
            ...defaultConfig,
            ...config,
            form: {
                ...defaultConfig.form,
                ...(config.form || {})
            }
        };

        this.modal = document.getElementById(this.config.target);
        this.loading = {
            title: this.modal.querySelector(this.config.classes.title).innerHTML,
            html: this.modal.querySelector(this.config.classes.body).innerHTML
        };

        this.init();
    }

    init() {
        this.setupEventDelegation();
        this.bindEvents();
    }

    setupEventDelegation() {
        this.modal.addEventListener('submit', e => {
            if (e.target.tagName === 'FORM') {
                e.preventDefault();
                this.handleForm(e);
            }
        });
    }

    bindEvents() {
        this.modal.addEventListener('show.bs.modal', (e) => {
            const url = e.relatedTarget?.dataset?.url;
            if (url) this.loadContent(url);
        });
    }

    async loadContent(url) {
        try {
            this.dispatchEvent('modalAjaxLoad', {
                url: url,
                modal: this.modal,
                target: this.config.target,
                relatedTarget: document.activeElement,
            });

            this.startLoading();
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const result = await this.processResponse(response);
            this.stopLoading();

            this.updateModal(result);

            this.dispatchEvent('modalAjaxLoaded', {
                data: result,
                modal: this.modal,
                target: this.config.target,
            });

        } catch (error) {
            this.handleError(error);
        }
    }

    async processResponse(response) {
        const contentType = response.headers.get('Content-Type');
        let result = { title: this.config.title, html: '', redirectUrl: null };

        if (contentType.includes('application/json')) {
            const data = await response.json();
            result = { ...result, ...data };
            result.redirectUrl = data[this.config.form.redirectKey];
        } else if (contentType.includes('text/html')) {
            result.html = await response.text();
            result.title = response.headers.get('X-Modal-Title') || this.extractTitle(result.html) || result.title;
            result.redirectUrl = response.headers.get(this.config.form.redirectHeader);
        }

        return result;
    }

    updateModal({ title, html }) {
        this.modal.querySelector(this.config.classes.title).innerHTML = title ?? this.config.title;
        const body = this.modal.querySelector(this.config.classes.body);
        body.innerHTML = html;
        this.executeScripts(body);
    }

    executeScripts(container) {
        container.querySelectorAll('script').forEach(oldScript => {
            const newScript = document.createElement('script');
            newScript.textContent = oldScript.textContent;
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });
    }

    async handleForm(event) {
        event.preventDefault();
        const form = event.target;

        try {
            this.dispatchEvent('modalAjaxSubmit', {
                form: form,
                modal: this.modal,
                target: this.config.target,
            });

            this.startLoading();

            const response = await fetch(form.action, {
                method: form.method,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json, text/html',
                    'X-CSRF-Token': this.config.csrfToken
                },
                body: new FormData(form)
            });

            const result = await this.processResponse(response);

            this.stopLoading();

            // 1. Renderizar HTML si está habilitado
            if (this.config.form.autoRender) {
                this.updateModal(result);
            }

            // Disparar evento de respuesta
            this.dispatchEvent('modalAjaxResponse', {
                data: result,
                form: form,
                modal: this.modal,
                target: this.config.target,
            });

            // 2. Cerrar modal si está habilitado
            if (this.config.form.closeOnSuccess) {
                const modalInstance = bootstrap.Modal.getInstance(this.modal);
                if (modalInstance) modalInstance.hide();
            }

            // 3. Redirigir si está habilitado y hay URL
            if (this.config.form.autoRedirect && result.redirectUrl) {
                window.location.href = result.redirectUrl;
                return;
            }

        } catch (error) {
            this.dispatchEvent('modalAjaxResponse', {
                error: error.message,
                form: form,
                modal: this.modal,
                target: this.config.target,
            });
        }
    }

    startLoading(overwrite = false) {
        this.modal.classList.add('loading');

        if (this.config.form.overwriteOnLoading || overwrite) {
            this.modal.querySelector(this.config.classes.title).innerHTML = this.loading.title;
            this.modal.querySelector(this.config.classes.body).innerHTML = this.loading.html;
        }
    }

    stopLoading() {
        this.modal.classList.remove('loading');
    }

    extractTitle(html) {
        const match = html.match(/<h1[^>]*>(.*?)<\/h1>/i);
        return match ? match[1] : null;
    }

    handleError(error) {
        console.error('Modal Error:', error);
        this.updateModal({
            title: 'Error',
            html: `<p>${error.message}</p>`
        });
    }

    dispatchEvent(name, detail) {
        document.dispatchEvent(new CustomEvent(name, { detail }));
    }
}

window.ModalAjaxManager = ModalAjaxManager;
