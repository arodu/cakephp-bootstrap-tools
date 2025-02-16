export class BaseManager {
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