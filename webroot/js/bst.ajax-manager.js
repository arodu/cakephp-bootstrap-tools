class FormAjaxManager {
    constructor(formElement, config = {}) {
        const defaultConfig = {
            autoRender: true,
            autoRedirect: false,
            redirectKey: 'redirect',
            redirectHeader: 'X-Redirect-URL',
            target: formElement.closest('.form-container') || document.body,
            csrfToken: null,
            onSuccess: null,
            onError: null
        };

        this.config = { ...defaultConfig, ...config };
        this.form = formElement;
        this.init();
    }

    init() {
        this.bindEvents();
    }

    bindEvents() {
        this.form.addEventListener('submit', this.handleSubmit.bind(this));
    }

    async handleSubmit(event) {
        event.preventDefault();
        
        this.dispatchEvent('formAjaxSubmit', { form: this.form });

        try {
            const response = await fetch(this.form.action, {
                method: this.form.method,
                redirect: 'manual',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json, text/html',
                    'X-CSRF-Token': this.config.csrfToken
                },
                body: new FormData(this.form)
            });

            //if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

            const result = await this.processResponse(response);

            if (this.config.autoRender) {
                this.updateTarget(result.html);
                this.executeScripts(this.config.target);
            }

            if (this.config.autoRedirect && result.redirectUrl) {
                window.location.href = result.redirectUrl;
                return;
            }

            this.dispatchEvent('formAjaxSuccess', {
                data: result,
                form: this.form,
                target: this.config.target
            });

            if (this.config.onSuccess) {
                this.config.onSuccess(result);
            }

        } catch (error) {
            this.handleError(error);
            this.dispatchEvent('formAjaxError', {
                error: error.message,
                form: this.form,
                target: this.config.target
            });
            
            if (this.config.onError) {
                this.config.onError(error);
            }
        }
    }

    async processResponse(response) {
        const contentType = response.headers.get('Content-Type') || '';
        let result = { html: '', redirectUrl: null };

        console.log('Response', response);

        // Detectar redirecciones HTTP 3xx
        if (response.status >= 300 && response.status < 400) {
            result.redirectUrl = response.headers.get('Location');
            
            if (!result.redirectUrl) {
                throw new Error('Redirect response missing Location header');
            }
            
            // Si es redirección pero viene contenido, preservarlo
            if (contentType.includes('application/json')) {
                const data = await response.json();
                result = { ...result, ...data };
            } else if (contentType.includes('text/html')) {
                result.html = await response.text();
            }
            
            return result;
        }

        // Manejar errores HTTP 4xx/5xx
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        // Procesar respuesta exitosa
        if (contentType.includes('application/json')) {
            const data = await response.json();
            result.html = data.html || '';
            result.redirectUrl = data[this.config.redirectKey];
        } else if (contentType.includes('text/html')) {
            result.html = await response.text();
            result.redirectUrl = response.headers.get(this.config.redirectHeader);
        }

        return result;
    }

    updateTarget(html) {
        this.config.target.innerHTML = html;
    }

    executeScripts(container) {
        container.querySelectorAll('script').forEach(oldScript => {
            const newScript = document.createElement('script');
            newScript.textContent = oldScript.textContent;
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });
    }

    handleError(error) {
        console.error('Form Error:', error);
        this.config.target.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
    }

    dispatchEvent(name, detail) {
        document.dispatchEvent(new CustomEvent(name, { detail }));
    }
}

class ModalAjaxManager {
    constructor(config) {
        const defaultConfig = {
            target: 'ajax-modal',
            modal: {
                title: '.modal-title',
                body: '.modal-body',
                closeOnSuccess: true,
            },
            form: {
                autoRender: true,
                overwriteOnLoading: false,
            },
            csrfToken: null
        };

        this.config = {
            ...defaultConfig,
            ...config,
            modal: {
                ...defaultConfig.modal,
                ...(config.modal || {})
            },
            form: {
                ...defaultConfig.form,
                ...(config.form || {})
            }
        };

        this.modal = document.getElementById(this.config.target);
        this.loading = {
            title: this.modal.querySelector(this.config.modal.title).innerHTML,
            html: this.modal.querySelector(this.config.modal.body).innerHTML
        };

        this.init();
    }

    init() {
        this.bindEvents();
    }

    bindEvents() {
        this.modal.addEventListener('show.bs.modal', e => {
            const url = e.relatedTarget?.dataset?.url;
            if (url) this.loadContent(url);
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
        let result = { title: this.config.title, html: '' };

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

    executeScripts(container) {
        container.querySelectorAll('script').forEach(oldScript => {
            const newScript = document.createElement('script');
            newScript.textContent = oldScript.textContent;
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });
    }

    attachForms() {
        const body = this.modal.querySelector(this.config.modal.body);
        body.querySelectorAll('form').forEach(form => {
            new FormAjaxManager(form, {
                target: body,
                autoRender: this.config.form.autoRender,
                csrfToken: this.config.csrfToken,
                onSuccess: () => {
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

    dispatchEvent(name, detail) {
        document.dispatchEvent(new CustomEvent(name, { detail }));
    }
}

class LinkAjaxManager {
    constructor(linkElement, config = {}) {
        const defaultConfig = {
            autoRender: true,
            loadingClass: 'loading',
            csrfToken: null,
            onBeforeLoad: null,
            onAfterLoad: null,
            onError: null
        };

        this.config = { ...defaultConfig, ...config };
        this.link = linkElement;
        this.targetSelector = this.link.dataset.target;
        this.targetElement = document.querySelector(this.targetSelector);
        
        if (!this.targetElement) {
            throw new Error(`Target element not found: ${this.targetSelector}`);
        }

        this.init();
    }

    init() {
        this.bindEvents();
    }

    bindEvents() {
        this.link.addEventListener('click', this.handleClick.bind(this));
    }

    async handleClick(event) {
        event.preventDefault();
        
        try {
            this.dispatchEvent('linkAjaxLoad', {
                link: this.link,
                target: this.targetElement
            });

            if (this.config.onBeforeLoad) {
                this.config.onBeforeLoad(this.link, this.targetElement);
            }

            this.showLoading();
            
            const response = await fetch(this.link.href, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const result = await this.processResponse(response);
            
            this.hideLoading();
            
            if (result.redirectUrl) {
                window.location.href = result.redirectUrl;
                return;
            }

            if (this.config.autoRender) {
                this.updateContent(result.html);
                this.attachForms();
            }

            this.dispatchEvent('linkAjaxLoaded', {
                data: result,
                link: this.link,
                target: this.targetElement
            });

            if (this.config.onAfterLoad) {
                this.config.onAfterLoad(result, this.link, this.targetElement);
            }

        } catch (error) {
            this.handleError(error);
            
            if (this.config.onError) {
                this.config.onError(error, this.link, this.targetElement);
            }
        }
    }

    async processResponse(response) {
        const contentType = response.headers.get('Content-Type') || '';
        let result = { html: '', redirectUrl: null };

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        if (contentType.includes('application/json')) {
            const data = await response.json();
            result.html = data.html || '';
            result.redirectUrl = data.redirect;
        } else if (contentType.includes('text/html')) {
            result.html = await response.text();
            result.redirectUrl = response.headers.get('X-Redirect-URL');
        }

        return result;
    }

    updateContent(html) {
        this.targetElement.innerHTML = html;
        this.executeScripts(this.targetElement);
    }

    executeScripts(container) {
        container.querySelectorAll('script').forEach(oldScript => {
            const newScript = document.createElement('script');
            newScript.textContent = oldScript.textContent;
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });
    }

    attachForms() {
        this.targetElement.querySelectorAll('form').forEach(form => {
            new FormAjaxManager(form, {
                target: this.targetElement,
                autoRender: this.config.autoRender,
                csrfToken: this.config.csrfToken
            });
        });
    }

    showLoading() {
        this.targetElement.classList.add(this.config.loadingClass);
    }

    hideLoading() {
        this.targetElement.classList.remove(this.config.loadingClass);
    }

    handleError(error) {
        console.error('Link Error:', error);
        this.targetElement.innerHTML = `
            <div class="alert alert-danger">
                Error loading content: ${error.message}
            </div>
        `;
    }

    dispatchEvent(name, detail) {
        document.dispatchEvent(new CustomEvent(name, { detail }));
    }
}

window.FormAjaxManager = FormAjaxManager;
window.ModalAjaxManager = ModalAjaxManager;
window.LinkAjaxManager = LinkAjaxManager;