/**
 * ModalAjaxManager - A class to dynamically manage Bootstrap modals with AJAX-loaded content.
 *
 * Features:
 * - Dynamically loads modal content via AJAX when the modal is shown.
 * - Processes responses in both JSON and HTML formats.
 * - Extracts and updates modal title and body; executes inline scripts from loaded content.
 * - Handles AJAX form submissions within the modal.
 * - Dispatches custom events to allow hooking into various stages of the modal's lifecycle.
 *
 * Configuration Options:
 * - target (string): ID of the modal element in the DOM (default: 'ajax-modal').
 * - title (string): Default title to be used in the modal (default: 'Modal Form').
 * - callback (function): Function to be called when the 'modalAjaxResponse' event is fired.
 * - csrfToken (string): CSRF token to include in headers for secure form submissions.
 *
 * Custom Events:
 * - modalAjaxLoad:
 *   Triggered at the beginning of the loadContent() method, before the AJAX request is made.
 *   Detail: { url, modal, target, relatedTarget }
 *
 * - modalAjaxLoaded:
 *   Triggered after the AJAX content has been successfully loaded and processed.
 *   Detail: { data, modal, target }
 *
 * - modalAjaxSubmit:
 *   Triggered just before an AJAX form submission is made.
 *   Detail: { form, modal, target }
 *
 * - modalAjaxResponse:
 *   Triggered after receiving an AJAX response from a form submission (success or error).
 *   On success:
 *     Detail: { data, form, modal, target }
 *   On error:
 *     Detail: { error, form, modal, target }
 *
 * Usage Example:
 * const modalAjaxManager = new ModalAjaxManager({
 *   target: 'ajax-modal',
 *   title: 'My Dynamic Modal',
 *   csrfToken: 'YOUR_CSRF_TOKEN_HERE',
 *   callback: (event, detail) => console.log('AJAX Response:', detail)
 * });
 *
 * Note:
 * - This class expects a modal structure similar to Bootstrap's with elements having the
 *   classes .modal-title and .modal-body.
 * - The modal is activated by a trigger element with a data-url attribute that specifies
 *   the URL to load via AJAX.
 */

class ModalAjaxManager {
    constructor(config) {
        this.config = {
            target: 'ajax-modal',
            title: 'Modal Form',
            classes: {
                title: '.modal-title',
                body: '.modal-body'
            },
            ...config
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
        let result = { title: this.config.title, html: '' };

        if (contentType.includes('application/json')) {
            result = await response.json();
        } else if (contentType.includes('text/html')) {
            result.html = await response.text();
            result.title = response.headers.get('X-Modal-Title') || this.extractTitle(result.html) || result.title;
        }

        return result;
    }

    updateModal({ title, html }) {
        this.stopLoading();
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

    startLoading() {
        this.modal.classList.add('loading');
        this.modal.querySelector(this.config.classes.title).innerHTML = this.loading.title;
        this.modal.querySelector(this.config.classes.body).innerHTML = this.loading.html;
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
