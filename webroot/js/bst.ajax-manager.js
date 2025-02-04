/**
 * AjaxFormManager - Maneja envíos de formularios vía AJAX de forma independiente.
 * 
 * Configuración:
 * - autoRender: boolean, renderiza HTML en el elemento objetivo (default: true)
 * - autoRedirect: boolean, redirige automáticamente (default: false)
 * - redirectKey: string, clave en JSON para redirección (default: 'redirect')
 * - redirectHeader: string, header en HTML para redirección (default: 'X-Redirect-URL')
 * - defaultTarget: string|Element, selector o elemento donde renderizar respuestas (default: 'ajax-content')
 * - csrfToken: string, token CSRF para cabeceras
 */
class AjaxFormManager {
    constructor(config) {
        const defaultConfig = {
            autoRender: true,
            autoRedirect: false,
            redirectKey: 'redirect',
            redirectHeader: 'X-Redirect-URL',
            defaultTarget: 'ajax-content',
            csrfToken: null
        };

        this.config = { ...defaultConfig, ...config };
        this.setupEventDelegation();
    }

    setupEventDelegation(container = document) {
        container.addEventListener('submit', e => {
            if (e.target.tagName === 'FORM') {
                this.handleSubmit(e);
            }
        });
    }

    async handleSubmit(event) {
        event.preventDefault();
        const form = event.target;
        
        this.dispatchEvent('ajaxFormSubmit', { form });

        try {
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
            
            this.dispatchEvent('ajaxFormResponse', { 
                data: result, 
                form,
                error: null 
            });

            if (this.config.autoRedirect && result.redirectUrl) {
                window.location.href = result.redirectUrl;
                return;
            }

            if (this.config.autoRender) {
                const target = this.resolveTarget(form);
                if (target) {
                    target.innerHTML = result.html;
                    this.executeScripts(target);
                }
            }

        } catch (error) {
            this.dispatchEvent('ajaxFormResponse', { 
                error: error.message, 
                form,
                data: null 
            });
        }
    }

    async processResponse(response) {
        const contentType = response.headers.get('Content-Type');
        let result = { html: '', redirectUrl: null };

        if (contentType.includes('application/json')) {
            const data = await response.json();
            result.redirectUrl = data[this.config.redirectKey];
            result.html = data.html || '';
        } 
        else if (contentType.includes('text/html')) {
            result.html = await response.text();
            result.redirectUrl = response.headers.get(this.config.redirectHeader);
        }

        return result;
    }

    resolveTarget(form) {
        const customTarget = form.dataset.target;
        return customTarget 
            ? document.querySelector(customTarget)
            : (typeof this.config.defaultTarget === 'string'
                ? document.querySelector(this.config.defaultTarget)
                : this.config.defaultTarget);
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

/**
 * ModalAjaxManager - Maneja modales dinámicos con contenido AJAX
 * 
 * Configuración:
 * - target: string, ID del modal (default: 'ajax-modal')
 * - title: string, título por defecto (default: 'Modal Form')
 * - classes: { title: string, body: string }, clases para elementos internos
 * - csrfToken: string, token CSRF para formularios
 */
class ModalAjaxManager {
    constructor(config) {
        const defaultConfig = {
            target: 'ajax-modal',
            title: 'Modal Form',
            classes: {
                title: '.modal-title',
                body: '.modal-body'
            },
            csrfToken: null
        };

        this.config = { ...defaultConfig, ...config };
        this.modal = document.getElementById(this.config.target);
        this.init();
    }

    init() {
        this.initFormManager();
        this.bindModalEvents();
    }

    initFormManager() {
        this.formManager = new AjaxFormManager({
            csrfToken: this.config.csrfToken,
            defaultTarget: this.modal.querySelector(this.config.classes.body)
        });

        document.addEventListener('ajaxFormResponse', e => {
            if (e.detail.form.closest(`#${this.config.target}`)) {
                if (!e.detail.error) {
                    // Lógica específica del modal después de éxito
                    this.stopLoading();
                } else {
                    this.handleError(e.detail.error);
                }
            }
        });
    }

    bindModalEvents() {
        this.modal.addEventListener('show.bs.modal', e => {
            const url = e.relatedTarget?.dataset?.url;
            if (url) this.loadContent(url);
        });
    }

    async loadContent(url) {
        try {
            this.dispatchEvent('modalAjaxLoad', { url });
            this.startLoading();

            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const { title, html } = await this.processResponse(response);
            this.updateModal(title, html);
            this.dispatchEvent('modalAjaxLoaded', { data: { title, html } });

        } catch (error) {
            this.handleError(error);
        } finally {
            this.stopLoading();
        }
    }

    async processResponse(response) {
        const contentType = response.headers.get('Content-Type');
        let title = this.config.title;
        let html = '';

        if (contentType.includes('application/json')) {
            const data = await response.json();
            title = data.title || title;
            html = data.html;
        } 
        else if (contentType.includes('text/html')) {
            html = await response.text();
            title = response.headers.get('X-Modal-Title') 
                || this.extractTitle(html) 
                || title;
        }

        return { title, html };
    }

    updateModal(title, html) {
        this.modal.querySelector(this.config.classes.title).textContent = title;
        const body = this.modal.querySelector(this.config.classes.body);
        body.innerHTML = html;
        this.formManager.executeScripts(body);
    }

    startLoading() {
        this.modal.classList.add('loading');
    }

    stopLoading() {
        this.modal.classList.remove('loading');
    }

    extractTitle(html) {
        const match = html.match(/<h1[^>]*>(.*?)<\/h1>/i);
        return match?.[1];
    }

    handleError(error) {
        this.updateModal('Error', `<p>${error.message}</p>`);
        console.error('Modal Error:', error);
    }

    dispatchEvent(name, detail) {
        document.dispatchEvent(new CustomEvent(name, { 
            detail: { ...detail, modal: this.modal, target: this.config.target }
        }));
    }
}

window.AjaxFormManager = AjaxFormManager;
window.ModalAjaxManager = ModalAjaxManager;