/**
 * BootstrapTools - AJAX Form and Modal Management Utilities
 * 
 * Provides FormAjaxManager and ModalAjaxManager classes for handling AJAX form submissions
 * and dynamic modal content loading with Bootstrap compatibility.
 * 
 * ========================================================================================
 * 
 * FORM AJAX MANAGER USAGE:
 * 
 * Initialize:
 * const formManager = new FormAjaxManager(document.querySelector('form'), {
 *   target: '.form-container',  // Container to update with response (default: form's parent)
 *   autoRender: true,           // Automatically render HTML responses (default: true)
 *   csrfToken: 'your_token',    // CSRF token for form submissions
 *   onSuccess: (result) => {},  // Custom success callback
 *   onError: (error) => {}      // Custom error callback
 * });
 * 
 * Events:
 * - formAjaxSubmit: Dispatched before submission
 *   detail: { form }
 * 
 * - formAjaxSuccess: Dispatched on successful response
 *   detail: { data, form, target, response }
 * 
 * - formAjaxError: Dispatched on error
 *   detail: { error, form, target, response }
 * 
 * Methods:
 * - updateTarget(html): Replace target content with new HTML and rebind form
 * - executeScripts(container): Reinitialize scripts in updated content
 * 
 * ========================================================================================
 * 
 * MODAL AJAX MANAGER USAGE:
 * 
 * Initialize:
 * const modalManager = new ModalAjaxManager({
 *   target: 'ajax-modal',       // Modal element ID (required)
 *   csrfToken: 'your_token',    // CSRF token for modal forms
 *   modal: {
 *     closeOnSuccess: true,     // Close modal after successful form submission
 *     reloadPageOnClose: true   // Reload page when modal closes (if flagged)
 *   },
 *   form: {
 *     autoRender: true          // Auto-render form responses in modal
 *   }
 * });
 * 
 * Trigger Content Loading:
 * Add data-url="/your-endpoint" to any element that triggers the modal
 * 
 * Events:
 * - modalAjaxLoad: Dispatched before content load
 *   detail: { url, modal }
 * 
 * - modalAjaxLoaded: Dispatched after successful content load
 *   detail: { data, modal }
 * 
 * - modalAjaxError: Dispatched on error
 *   detail: { error }
 * 
 * Methods:
 * - loadContent(url): Manually load content into modal
 * - updateModal({title, html}): Update modal content programmatically
 * 
 * ========================================================================================
 * 
 * REQUIREMENTS:
 * - Bootstrap 5+ (for modal functionality)
 * - Fetch API support
 * - Server responses should include either:
 *   - JSON with {html, success} for forms
 *   - HTML with optional X-Modal-Title header for modals
 * 
 * SETUP EXAMPLE:
 * <!-- Modal Structure -->
 * <div id="ajax-modal" class="modal">
 *   <div class="modal-content">
 *     <div class="modal-title"></div>
 *     <div class="modal-body"></div>
 *   </div>
 * </div>
 * 
 * <!-- Trigger -->
 * <button data-bs-toggle="modal" 
 *         data-bs-target="#ajax-modal"
 *         data-url="/load-content">
 *   Open Modal
 * </button>
 * 
 * Listeners Example:
 * document.addEventListener('formAjaxSuccess', (e) => {
 *   if (e.detail.form.id === 'search-form') {
 *     // Update search results
 *   }
 * });
 */

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
        this.form = this.config.target.querySelector('form');
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

class ModalAjaxManager extends BaseManager {
    constructor(config) {
        super();
        const defaultConfig = {
            target: 'ajax-modal',
            modal: {
                title: '.modal-title',
                body: '.modal-body',
                closeOnSuccess: false,
                reloadPageOnClose: false
            },
            containerAjaxConfig: { // Nueva configuración para ContainerAjax
                autoLoad: false,
                links: {
                    enabled: true,
                    updateHistory: false
                },
                form: {
                    autoRender: true
                }
            },
            csrfToken: null
        };

        this.config = BaseManager.mergeConfig(defaultConfig, config);
        this.modal = document.getElementById(this.config.target);
        this.containerAjax = this.initContainerAjax(); // Inicializar ContainerAjax
        this.shouldReloadPageOnClose = false;
        
        this.init();
    }

    initContainerAjax() {
        const modalBody = this.modal.querySelector(this.config.modal.body);
        return new ContainerAjax(modalBody, {
            ...this.config.containerAjaxConfig,
            csrfToken: this.config.csrfToken,
            onFormSuccess: (result) => this.handleFormSuccess(result)
        });
    }

    init() {
        this.bindModalEvents();
        this.bindContainerEvents();
    }

    bindModalEvents() {
        this.modal.addEventListener('show.bs.modal', e => {
            const url = e.relatedTarget?.dataset?.url;
            if (url) this.loadContent(url);
        });

        this.modal.addEventListener('hidden.bs.modal', () => {
            if (this.config.modal.reloadPageOnClose && this.shouldReloadPageOnClose) {
                window.location.reload();
            }
        });
    }

    bindContainerEvents() {
        // Escuchar eventos del ContainerAjax para actualizar el título del modal
        this.containerAjax.container.addEventListener('containerAjaxLoaded', (e) => {
            const title = e.detail.data.title || this.extractTitle(e.detail.data.html);
            if (title) this.updateModalTitle(title);
        });
    }

    async loadContent(url) {
        this.dispatchEvent('modalAjaxLoad', { url, modal: this.modal });
        await this.containerAjax.loadContent(url);
    }

    handleFormSuccess(result) {
        this.shouldReloadPageOnClose = true;
        if (this.config.modal.closeOnSuccess) {
            bootstrap.Modal.getInstance(this.modal)?.hide();
        }
    }

    updateModalTitle(title) {
        this.modal.querySelector(this.config.modal.title).textContent = title;
    }

    extractTitle(html) {
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;
        return tempDiv.querySelector('h1')?.textContent;
    }
}

class ContainerAjax extends BaseManager {
    constructor(containerElement, config = {}) {
        super();
        const defaultConfig = {
            autoLoad: true,
            csrfToken: null,
            form: {
                autoRender: true
            },
            links: {
                enabled: true,
                bypassAttribute: 'data-ajax-bypass',
                updateHistory: false,
            }
        };

        this.config = BaseManager.mergeConfig(defaultConfig, config);
        this.container = containerElement;
        this.initialUrl = this.container.dataset.url;
        this.currentUrl = this.initialUrl;

        this.boundHandleLinkClick = this.handleLinkClick.bind(this);

        if (this.config.autoLoad && this.initialUrl) {
            this.loadContent(this.initialUrl);
        }

        if (this.config.links.enabled) {
            this.container.addEventListener('click', this.boundHandleLinkClick);
        }
    }

    async loadContent(url) {
        try {
            this.dispatchEvent('containerAjaxLoad', { url, container: this.container });

            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!response.ok) throw new Error(`HTTP Error ${response.status}`);

            this.currentUrl = url;

            if (this.config.links.updateHistory) {
                window.history.pushState({ containerUrl: url }, '', url);
            }

            const html = await response.text();
            this.updateContainer(html);
            this.attachForms();

            this.dispatchEvent('containerAjaxLoaded', {
                data: html,
                container: this.container
            });

        } catch (error) {
            this.handleError(error);
            this.dispatchEvent('containerAjaxError', {
                error: error.message,
                container: this.container
            });
        }
    }

    handleLinkClick(event) {
        const link = event.target.closest('a');
        if (!link) return;

        const href = link.href;
        const bypass = link.hasAttribute(this.config.links.bypassAttribute);

        if (bypass || !this.isSameOrigin(href) || this.isFragmentLink(link)) {
            return;
        }

        event.preventDefault();
        this.loadContent(href);
    }

    isSameOrigin(href) {
        try {
            const url = new URL(href);
            return url.origin === window.location.origin;
        } catch {
            return false;
        }
    }

    isFragmentLink(link) {
        const href = link.getAttribute('href');
        return !href || href.startsWith('#');
    }

    updateContainer(html) {
        this.container.innerHTML = html;
        this.executeScripts(this.container);
    }

    reload() {
        if (this.currentUrl) {
            this.loadContent(this.currentUrl);
        }
    }

    attachForms() {
        this.container.querySelectorAll('form').forEach(form => {
            new FormAjaxManager(form, {
                target: this.container,
                autoRender: this.config.form.autoRender,
                csrfToken: this.config.csrfToken
            });
        });
    }

    handleError(error) {
        console.error('Container Error:', error);
        this.container.innerHTML = `
            <div class="alert alert-danger">
                ${error.message || 'Error loading content'}
            </div>
        `;
    }
}

window.ContainerAjax = ContainerAjax;
window.FormAjaxManager = FormAjaxManager;
window.ModalAjaxManager = ModalAjaxManager;