import { BaseManager } from './base-manager.js';

export class ContainerAjax extends BaseManager {

    static instances = {};

    static getInstance(key) {
        return this.instances[key];
    }

    static keys() {
        return Object.keys(this.instances);
    }

    constructor(containerElement, config = {}) {
        super();

        if (!containerElement || !containerElement.id) {
            console.error("ContainerAjax Error: El elemento contenedor debe tener un ID para ser registrado.", containerElement);
            super();
            return;
        }

        this.key = containerElement.id;

        if (ContainerAjax.instances[this.key]) {
            console.warn(`ContainerAjax: Ya existe una instancia para el ID "${this.key}". Se retorna la instancia existente.`);
            return ContainerAjax.instances[this.key];
        }

        const defaultConfig = {
            autoLoad: true,
            csrfToken: null,
            form: {
                autoRender: true,
                onSuccess: null,
                onError: null,
                onFailed: null,
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


        ContainerAjax.instances[this.key] = this;

        this.initialize();

        this.dispatchEvent("bst:container-ajax:initialized");
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
            this.dispatchEvent("bst:container-ajax:loading-start", {
                container: this.container
            });

            const response = await fetch(url, {
                headers: { "X-Requested-With": "XMLHttpRequest" }
            });

            //if (!response.ok) throw new Error(`HTTP Error ${response.status}`);

            this.currentUrl = url;

            if (this.config.links.updateHistory) {
                window.history.pushState({ containerUrl: url }, "", url);
            }

            const result = await this.processResponseData(response);
            this.updateContainer(result.html);

            this.dispatchEvent("bst:container-ajax:loaded", result.source || result);

        } catch (error) {
            this.handleError(error);
            this.dispatchEvent("bst:container-ajax:error", {
                error: error.message,
                container: this.container
            });
        } finally {
            this.dispatchEvent("bst:container-ajax:loading-end", {
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
            const method = form.method.toUpperCase();
            const isGetOrHead = method === 'GET' || method === 'HEAD';

            const fetchOptions = {
                method: method,
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "Accept": "application/json, text/html, text/plain",
                    "X-CSRF-Token": this.config.csrfToken
                }
            };

            let url = form.action;
            if (isGetOrHead) {
                const urlObj = new URL(url, window.location.origin);
                new FormData(form).forEach((_, key) => {
                    urlObj.searchParams.delete(key);
                });
                new FormData(form).forEach((value, key) => {
                    urlObj.searchParams.append(key, value);
                });

                url = urlObj.toString();
            } else {
                fetchOptions.body = new FormData(form);
            }

            this.dispatchEvent("bst:container-ajax:loading-start", {
                container: this.container
            });

            const response = await fetch(url, fetchOptions);
            const result = await this.processResponseData(response);

            if (this.config.form.autoRender) {
                this.updateContainer(result.html);
            }

            console.log("Form submission result:", result);

            if (result.success) {
                this.dispatchEvent("bst:container-ajax:form-success", {
                    result: result.source || result,
                    form,
                    response
                });
                this.config.form.onSuccess?.(result);
            } else {
                this.dispatchEvent("bst:container-ajax:form-failed", {
                    result: result.source || result,
                    form,
                    response
                });
                this.config.form.onFailed?.(result);
            }
        } catch (error) {
            this.handleFormError(error, form);
            this.dispatchEvent("bst:container-ajax:form-error", { error });
            this.config.form.onError?.(error);
        } finally {
            this.dispatchEvent("bst:container-ajax:loading-end");
        }
    }

    async processResponseData(response) {
        const contentType = response.headers.get("Content-Type") || "";
        let result = {
            success: response.ok,
            html: "",
            source: null,
        };

        if (!response.ok) {
            result.success = false;
        }

        if (contentType.includes("application/json")) {
            const data = await response.json();
            result.success = (data.status == 'success') || false;
            result.html = data.data.html || "";
            result.source = data || null;
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
            form.removeEventListener("submit", this.boundHandleFormSubmit);
            form.addEventListener("submit", this.boundHandleFormSubmit);

            form.submit = () => {
                this.handleFormSubmit({
                    target: form,
                    currentTarget: form,
                    preventDefault: () => { } // Función dummy
                });
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

    destroy() {
        this.container.removeEventListener("click", this.boundHandleLinkClick);
        this.container.querySelectorAll("form").forEach(form => {
            form.removeEventListener("submit", this.boundHandleFormSubmit);
        });

        delete ContainerAjax.instances[this.key];

        this.dispatchEvent("bst:container-ajax:destroyed");
    }

    dispatchEvent(name, paypload = {}) {
        const detail = {
            instance: this,
            key: this.key,
            container: this.container,
            timestamp: (new Date()).toISOString(),
            payload: paypload,
        };

        document.dispatchEvent(new CustomEvent(name, { detail }));
    }
}