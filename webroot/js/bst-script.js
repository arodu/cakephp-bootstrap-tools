class BaseManager {
  mergeConfig(defaults, config) {
    const isObject = (obj) => obj && typeof obj === "object" && !Array.isArray(obj);
    const result = { ...defaults };
    for (const key in config) {
      if (config.hasOwnProperty(key)) {
        const defaultVal = result[key];
        const configVal = config[key];
        if (isObject(defaultVal) && isObject(configVal)) {
          result[key] = this.mergeConfig(defaultVal, configVal);
        } else {
          result[key] = configVal !== void 0 ? configVal : defaultVal;
        }
      }
    }
    return result;
  }
  executeScripts(container) {
    container.querySelectorAll("script").forEach((oldScript) => {
      const newScript = document.createElement("script");
      newScript.textContent = oldScript.textContent;
      oldScript.parentNode.replaceChild(newScript, oldScript);
    });
  }
  dispatchEvent(name, detail) {
    document.dispatchEvent(new CustomEvent(name, { detail }));
  }
}
class ContainerAjax extends BaseManager {
  constructor(containerElement, config = {}) {
    var _a, _b;
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
    this.initialUrl = ((_b = (_a = this.container) == null ? void 0 : _a.dataset) == null ? void 0 : _b.url) || null;
    this.currentUrl = this.initialUrl;
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
    var _a, _b, _c, _d;
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
      (_b = (_a = this.config.form).onSuccess) == null ? void 0 : _b.call(_a, result);
    } catch (error) {
      this.handleFormError(error, form);
      this.dispatchEvent("bst:container-ajax:form-error", {
        error: error.message,
        form,
        container: this.container
      });
      (_d = (_c = this.config.form).onError) == null ? void 0 : _d.call(_c, error);
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
    container.querySelectorAll("form").forEach((form) => {
      form.removeEventListener("submit", this.boundHandleFormSubmit);
      form.addEventListener("submit", this.boundHandleFormSubmit);
      form.submit.bind(form);
      form.submit = () => {
        this.handleFormSubmit(new Event("submit"));
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
class ModalAjaxManager extends BaseManager {
  constructor(config) {
    super();
    const defaultConfig = {
      target: "ajax-modal",
      modal: {
        title: ".modal-title",
        body: ".modal-body",
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
    this.modal.addEventListener("show.bs.modal", (e) => {
      var _a, _b;
      const url = (_b = (_a = e.relatedTarget) == null ? void 0 : _a.dataset) == null ? void 0 : _b.url;
      if (url) this.loadContent(url);
    });
    this.modal.addEventListener("hidden.bs.modal", () => {
      if (this.config.modal.reloadPageOnClose && this.shouldReloadPageOnClose) {
        window.location.reload();
      }
    });
  }
  bindContainerEvents() {
    this.containerAjax.container.addEventListener("containerAjaxLoaded", (e) => {
      const title = e.detail.data.title || this.extractTitle(e.detail.data.html);
      if (title) this.updateModalTitle(title);
    });
  }
  async loadContent(url) {
    this.dispatchEvent("modalAjaxLoad", { url, modal: this.modal });
    await this.containerAjax.loadContent(url);
  }
  handleFormSuccess(result) {
    var _a;
    this.shouldReloadPageOnClose = true;
    if (this.config.modal.closeOnSuccess) {
      (_a = bootstrap.Modal.getInstance(this.modal)) == null ? void 0 : _a.hide();
    }
  }
  updateModalTitle(title) {
    this.modal.querySelector(this.config.modal.title).textContent = title;
  }
  extractTitle(html) {
    var _a;
    const tempDiv = document.createElement("div");
    tempDiv.innerHTML = html;
    return (_a = tempDiv.querySelector("h1")) == null ? void 0 : _a.textContent;
  }
}
window.ContainerAjax = ContainerAjax;
window.ModalAjaxManager = ModalAjaxManager;
