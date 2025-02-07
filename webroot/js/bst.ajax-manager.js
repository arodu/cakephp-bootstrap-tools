class BaseManager {
    static mergeConfig(defaults, config) {
        return { ...defaults, ...config };
    }

    executeScripts(container) {
        container.querySelectorAll('script').forEach(oldScript => {
            const newScript = document.createElement('script');
            newScript.textContent = oldScript.textContent;
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });
    }

    dispatchEvent(name, detail) {
        document.dispatchEvent(new CustomEvent(name, { detail }));
    }
}

class FormAjaxManager extends BaseManager {
    constructor(formElement, config = {}) {
        super();
        const defaultConfig = {
            autoRender: true,
            target: formElement.closest('.form-container') || document.body,
            csrfToken: null,
            onSuccess: null,
            onError: null
        };

        if (typeof defaultConfig.target === 'string') {
            defaultConfig.target = document.querySelector(defaultConfig.target);
        }

        this.config = BaseManager.mergeConfig(defaultConfig, config);
        this.form = formElement;

        this.boundHandleSubmit = this.handleSubmit.bind(this);
        this.init();
    }

    init() {
        this.bindEvents();
    }

    bindEvents() {
        // Adjuntar evento al formulario, no al contenedor
        this.form.addEventListener('submit', this.boundHandleSubmit);
    }

    updateTarget(html) {
        // Remover evento del formulario antiguo
        if (this.form) {
            this.form.removeEventListener('submit', this.boundHandleSubmit);
        }
        this.config.target.innerHTML = html;
        this.form = this.config.target.querySelector('form');
        // Adjuntar evento al nuevo formulario
        this.bindEvents();
    }

    async handleSubmit(event) {
        event.preventDefault();

        this.dispatchEvent('formAjaxSubmit', { form: this.form });

        try {
            const response = await fetch(this.form.action, {
                method: this.form.method,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json, text/html, text/plain',
                    'X-CSRF-Token': this.config.csrfToken
                },
                body: new FormData(this.form)
            });

            const result = await this.processResponse(response);

            if (this.config.autoRender) {
                this.updateTarget(result.html);
                this.executeScripts(this.config.target);
            }

            this.dispatchEvent('formAjaxSuccess', {
                data: result,
                form: this.form,
                target: this.config.target,
                response,
            });

            if (this.config.onSuccess) {
                this.config.onSuccess(result);
            }

        } catch (error) {
            this.handleError(error);
            this.dispatchEvent('formAjaxError', {
                error: error.message,
                form: this.form,
                target: this.config.target,
                response,
            });

            if (this.config.onError) {
                this.config.onError(error);
            }
        }
    }

    async processResponse(response) {
        const contentType = response.headers.get('Content-Type') || '';
        let result = { html: '', success: response.ok };

        if (!response.ok) {
            throw new Error(`Error HTTP ${response.status}: ${response.statusText}`);
        }

        if (contentType.includes('application/json')) {
            const data = await response.json();
            result.html = data.html || '';
            result.success = data.success || false;
        } else if (contentType.includes('text/html')) {
            result.html = await response.text();
        }

        return result;
    }

    handleError(error) {
        console.error('Form Error:', error);
        this.config.target.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
    }
}

class ModalAjaxManager extends BaseManager {
    constructor(config) {
        super();
        const defaultConfig = {
            target: 'ajax-modal',
            modal: {
                title: '.modal-title',
                body: '.modal-body',
                closeOnSuccess: false,
                reloadPageOnClose: false,
            },
            form: {
                autoRender: true,
                overwriteOnLoading: false,
            },
            csrfToken: null
        };

        let mergedConfig = BaseManager.mergeConfig(defaultConfig, config);
        mergedConfig.modal = { ...defaultConfig.modal, ...(config.modal || {}) };
        mergedConfig.form = { ...defaultConfig.form, ...(config.form || {}) };

        this.config = mergedConfig;
        this.modal = document.getElementById(this.config.target);
        this.loading = {
            title: this.modal.querySelector(this.config.modal.title).innerHTML,
            html: this.modal.querySelector(this.config.modal.body).innerHTML
        };
        this.shouldReloadPageOnClose = false;

        this.init();
    }

    init() {
        this.bindEvents();
    }

    bindEvents() {
        this.modal.addEventListener('show.bs.modal', e => {
            this.shouldReloadPageOnClose = false;
            const url = e.relatedTarget?.dataset?.url;
            if (url) this.loadContent(url);
        });

        this.modal.addEventListener('hidden.bs.modal', () => {
            if (this.config.modal.reloadPageOnClose && this.shouldReloadPageOnClose) {
                window.location.reload();
            }
        });
    }

    async loadContent(url) {
        try {
            this.dispatchEvent('modalAjaxLoad', { url, modal: this.modal });
            this.startLoading();

            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const result = await this.processResponse(response);
            this.stopLoading();
            this.updateModal(result);
            this.attachForms();

            this.dispatchEvent('modalAjaxLoaded', {
                data: result,
                modal: this.modal
            });

        } catch (error) {
            this.handleError(error);
        }
    }

    async processResponse(response) {
        const contentType = response.headers.get('Content-Type');
        let result = { title: '', html: '' };

        if (contentType.includes('application/json')) {
            const data = await response.json();
            result = { ...result, ...data };
        } else if (contentType.includes('text/html')) {
            result.html = await response.text();
            result.title = response.headers.get('X-Modal-Title') ||
                this.extractTitle(result.html) ||
                this.config.title;
        }

        return result;
    }

    updateModal({ title, html }) {
        this.modal.querySelector(this.config.modal.title).innerHTML = title;
        const body = this.modal.querySelector(this.config.modal.body);
        body.innerHTML = html;
        this.executeScripts(body);
    }

    attachForms() {
        const modalBody = this.modal.querySelector(this.config.modal.body);
        modalBody.querySelectorAll('form').forEach(form => {
            new FormAjaxManager(form, {
                target: modalBody,
                autoRender: this.config.form.autoRender,
                csrfToken: this.config.csrfToken,
                onSuccess: (result) => {
                    this.shouldReloadPageOnClose = true;

                    if (this.config.modal.closeOnSuccess) {
                        const modalInstance = bootstrap.Modal.getInstance(this.modal);
                        if (modalInstance) modalInstance.hide();
                    }
                }
            });
        });
    }

    startLoading() {
        this.modal.classList.add('loading');
        if (this.config.form.overwriteOnLoading) {
            this.modal.querySelector(this.config.modal.title).innerHTML = this.loading.title;
            this.modal.querySelector(this.config.modal.body).innerHTML = this.loading.html;
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
}

window.FormAjaxManager = FormAjaxManager;
window.ModalAjaxManager = ModalAjaxManager;