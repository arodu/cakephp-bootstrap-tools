import { BaseManager } from './base-manager.js';
import { FormAjaxManager } from './form-ajax-manager.js';

export class ContainerAjax extends BaseManager {
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