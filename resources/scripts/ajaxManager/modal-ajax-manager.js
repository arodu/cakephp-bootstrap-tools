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
            form: {
                onSuccess: (result) => this.handleFormSuccess(result)
            }
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
        document.addEventListener('bst:container-ajax:loaded', (e) => {
            if (e.detail.instance !== this.containerAjax) {
                return;
            }
            const payload = e.detail.payload;
            const title = this.extractTitle(payload.data ?? null);
            if (title) this.updateModalTitle(title);
        });
    }

    async loadContent(url) {
        this.dispatchEvent('bst:modal-ajax:load', { url });
        await this.containerAjax.loadContent(url);
    }

    handleFormSuccess(result) {
        //this.shouldReloadPageOnClose = true;
        if (this.config.modal.closeOnSuccess) {
            bootstrap.Modal.getInstance(this.modal)?.hide();
        }
    }

    updateModalTitle(title) {
        this.modal.querySelector(this.config.modal.title).textContent = title;
    }

    extractTitle(sourceData) {
        if (!sourceData) {
            return null;
        }

        if (typeof sourceData.title === 'string' && sourceData.title.trim() !== '') {
            return sourceData.title;
        }

        const html = sourceData.html;
        if (typeof html === 'string' && html.length > 0) {
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;
            return tempDiv.querySelector('#modal-title')?.textContent.trim() ?? null;
        }

        return null;
    }

    dispatchEvent(name, paypload = {}) {
        const detail = {
            instance: this,
            modal: this.modal,
            timestamp: (new Date()).toISOString(),
            payload: paypload,
        };

        document.dispatchEvent(new CustomEvent(name, { detail }));
    }
}