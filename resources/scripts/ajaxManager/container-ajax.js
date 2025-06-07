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

            this.dispatchEvent("bst:container-ajax:loading-start", {
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

            const result = await this.processResponseData(response);
            this.updateContainer(result.html);

            this.dispatchEvent("bst:container-ajax:loaded", {
                result: result,
                container: this.container
            });

        } catch (error) {
            this.handleError(error);
            this.dispatchEvent("bst:container-ajax:error", {
                error: error.message,
                container: this.container
            });
        } finally {
            this.dispatchEvent("bst:container-ajax:loading-start", {
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
        } finally {
            this.dispatchEvent("bst:container-ajax:loading-end", {
                container: this.container
            });
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
            throw new Error(`HTTP Error ${response.status}: ${response.statusText}`);
        }

        if (contentType.includes("application/json")) {
            const data = await response.json();
            result.success = data.success || false;
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
}