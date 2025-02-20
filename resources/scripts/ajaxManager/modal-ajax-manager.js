import { BaseManager } from './base-manager.js';
import { ContainerAjax } from './container-ajax.js';

export class ModalAjaxManager extends BaseManager {
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
            containerAjaxConfig: {
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

        this.config = this.mergeConfig(defaultConfig, config);
        this.modal = document.getElementById(this.config.target);
        this.containerAjax = this.initContainerAjax();
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