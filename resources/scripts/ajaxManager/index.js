import { BaseManager } from './base-manager.js';
import { ContainerAjax } from './container-ajax.js';
import { ModalAjaxManager } from './modal-ajax-manager.js';

export {
  BaseManager,
  ContainerAjax,
  ModalAjaxManager
};

window.ContainerAjax = ContainerAjax;
window.ModalAjaxManager = ModalAjaxManager;