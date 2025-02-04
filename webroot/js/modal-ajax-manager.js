class ModalManager {
    constructor(config) {
        this.config = {
            target: 'ajax-modal',
            ...config
        };

        this.modal = document.getElementById(this.config.target);
        this.loading = {
            title: this.modal.querySelector('.modal-title').innerHTML,
            html: this.modal.querySelector('.modal-body').innerHTML
        };

        this.init();
    }

    init() {
        this.setupEventDelegation();
        this.bindEvents();
        this.addCustomEvents();
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

    addCustomEvents() {
        document.addEventListener('modalAjaxResponse', (e) => {
            if (typeof this.config.callback === 'function') {
                this.config.callback(e, e.detail);
            }
        });
    }

    async loadContent(url) {
        try {
            this.modal.querySelector('.modal-title').innerHTML = this.loading.title;
            this.modal.querySelector('.modal-body').innerHTML = this.loading.html;

            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const result = await this.processResponse(response);
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
        let result = { title: 'Formulario', html: '' };

        if (contentType.includes('application/json')) {
            result = await response.json();
        } else if (contentType.includes('text/html')) {
            result.html = await response.text();
            result.title = response.headers.get('X-Modal-Title') || this.extractTitle(result.html) || result.title;
        }

        return result;
    }

    updateModal({ title, html }) {
        this.modal.querySelector('.modal-title').innerHTML = title;
        const body = this.modal.querySelector('.modal-body');
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
            this.updateModal(result);

            this.dispatchEvent('modalAjaxResponse', {
                data: result,
                form: form,
                modal: this.modal,
                target: this.config.target,
            });

        } catch (error) {
            this.dispatchEvent('modalAjaxResponse', {
                error: error.message,
                form: form,
                modal: this.modal,
                target: this.config.target,
            });
        }
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

window.ModalManager = ModalManager;
