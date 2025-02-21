import { BaseManager } from './base-manager.js';

export class ContainerAjax extends BaseManager {
    constructor(containerElement, config = {}) {
        super();
        const defaultConfig = {
            autoLoad: true,
            csrfToken: null,
            form: {
                autoRender: true,
                onSuccess: null,
                onError: null
            },
            links: {
                enabled: true,
                bypassAttribute: "data-ajax-bypass",
                updateHistory: false
            }
        };

        this.config = this.mergeConfig(defaultConfig, config);
        this.container = containerElement;
        this.initialUrl = this.container?.dataset?.url || null;
        this.currentUrl = this.initialUrl;

        // Bind handlers
        this.boundHandleLinkClick = this.handleLinkClick.bind(this);
        this.boundHandleFormSubmit = this.handleFormSubmit.bind(this);

        this.initialize();
    }

    initialize() {
        if (this.config.autoLoad && this.initialUrl) {
            this.loadContent(this.initialUrl);
        }

        if (this.config.links.enabled) {
            this.container.addEventListener("click", this.boundHandleLinkClick);
        }

        this.attachForms();
    }

    async loadContent(url) {
        try {
            this.dispatchEvent("bst:container-ajax:load", {
                url,
                container: this.container
            });

            const response = await fetch(url, {
                headers: { "X-Requested-With": "XMLHttpRequest" }
            });

            if (!response.ok) throw new Error(`HTTP Error ${response.status}`);

            this.currentUrl = url;

            if (this.config.links.updateHistory) {
                window.history.pushState({ containerUrl: url }, "", url);
            }

            const html = await response.text();
            this.updateContainer(html);

            this.dispatchEvent("bst:container-ajax:loaded", {
                data: html,
                container: this.container
            });

        } catch (error) {
            this.handleError(error);
            this.dispatchEvent("bst:container-ajax:error", {
                error: error.message,
                container: this.container
            });
        }
    }

    // Form handling
    async handleFormSubmit(event) {
        event.preventDefault();
        const form = event.target;

        this.dispatchEvent("bst:container-ajax:form-submit", {
            form,
            container: this.container
        });

        try {
            const response = await fetch(form.action, {
                method: form.method,
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "Accept": "application/json, text/html, text/plain",
                    "X-CSRF-Token": this.config.csrfToken
                },
                body: new FormData(form)
            });

            const result = await this.processFormResponse(response);

            if (this.config.form.autoRender) {
                this.updateContainer(result.html);
            }

            this.dispatchEvent("bst:container-ajax:form-success", {
                data: result,
                form,
                container: this.container,
                response
            });

            this.config.form.onSuccess?.(result);

        } catch (error) {
            this.handleFormError(error, form);
            this.dispatchEvent("bst:container-ajax:form-error", {
                error: error.message,
                form,
                container: this.container
            });
            this.config.form.onError?.(error);
        }
    }

    async processFormResponse(response) {
        const contentType = response.headers.get("Content-Type") || "";
        let result = { html: "", success: response.ok };

        if (!response.ok) {
            throw new Error(`HTTP Error ${response.status}: ${response.statusText}`);
        }

        if (contentType.includes("application/json")) {
            const data = await response.json();
            result.html = data.html || "";
            result.success = data.success || false;
        } else if (contentType.includes("text/html")) {
            result.html = await response.text();
        }

        return result;
    }

    handleFormError(error, form) {
        const errorContainer = this.container;
        errorContainer.innerHTML = `
        <div class="alert alert-danger">
          ${error.message || "Error processing form submission"}
        </div>
      `;
    }

    // Helper methods
    attachForms(container = this.container) {
        container.querySelectorAll("form").forEach(form => {
            console.log('attachForms');

            form.removeEventListener("submit", this.boundHandleFormSubmit);
            form.addEventListener("submit", this.boundHandleFormSubmit);

            form.submit.bind(form);
            form.submit = () => {
                this.handleFormSubmit(new Event('submit'));
            };
        });
    }

    handleLinkClick(event) {
        const link = event.target.closest("a");
        if (!link) return;

        const href = link.href;
        const bypass = link.hasAttribute(this.config.links.bypassAttribute);

        if (bypass || !this.isSameOrigin(href) || this.isFragmentLink(link)) {
            return;
        }

        event.preventDefault();
        this.loadContent(href);
    }

    updateContainer(html) {
        this.container.innerHTML = html;
        this.executeScripts(this.container);
        this.attachForms(this.container);
    }

    isSameOrigin(href) {
        try {
            return new URL(href).origin === window.location.origin;
        } catch {
            return false;
        }
    }

    isFragmentLink(link) {
        const href = link.getAttribute("href");
        return !href || href.startsWith("#");
    }

    handleError(error) {
        console.error("Container Error:", error);
        this.container.innerHTML = `
        <div class="alert alert-danger">
          ${error.message || "Error loading content"}
        </div>
      `;
    }

    reload() {
        if (this.initialUrl) {
            this.loadContent(this.initialUrl);
        }
    }
}