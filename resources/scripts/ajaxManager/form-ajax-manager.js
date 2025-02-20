import { BaseManager } from './base-manager.js';

export class FormAjaxManager extends BaseManager {
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

        this.config = this.mergeConfig(defaultConfig, config);
        this.config.target = config.target || formElement.closest('.form-container') || document.body;
        this.form = formElement;

        if (typeof this.config.target === "string") {
            this.config.target = document.querySelector(this.config.target);
        }

        this.boundHandleSubmit = this.handleSubmit.bind(this);
        this.init();

        const originalSubmit = formElement.submit.bind(formElement);
        formElement.submit = () => {
            this.handleSubmit(new Event('submit'));
        };
    }

    init() {
        this.bindEvents();
    }

    bindEvents() {
        if (this.form) {
            this.form.addEventListener('submit', this.boundHandleSubmit);
        }
    }

    updateTarget(html) {
        if (this.form) {
            this.form.removeEventListener('submit', this.boundHandleSubmit);
        }
        this.config.target.innerHTML = html;
        this.form = this.config?.target.querySelector('form');
        if (!this.form) {
            console.warn('New HTML does not contain a form');
        }

        this.bindEvents();
    }

    async handleSubmit(event) {
        event.preventDefault();
        this.dispatchEvent('formAjaxSubmit', { form: this.form });
        let response;

        try {
            response = await fetch(this.form.action, {
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
            throw new Error(`HTTP Error ${response.status}: ${response.statusText}`);
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
        const message = error.message || 'Error processing request';
        console.error('Form Error:', message);
        this.config.target.innerHTML = `<div class="alert alert-danger">${message}</div>`;
    }
}