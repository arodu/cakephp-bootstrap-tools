import { BaseManager } from './base-manager.js';
import { FormAjaxManager } from './form-ajax-manager.js';
import { ContainerAjax } from './container-ajax.js';
import { ModalAjaxManager } from './modal-ajax-manager.js';

export {
  BaseManager,
  FormAjaxManager,
  ContainerAjax,
  ModalAjaxManager
};

window.ContainerAjax = ContainerAjax;
window.FormAjaxManager = FormAjaxManager;
window.ModalAjaxManager = ModalAjaxManager;