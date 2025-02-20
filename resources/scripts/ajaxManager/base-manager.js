export class BaseManager {
    mergeConfig(defaults, config) {
        const isObject = (obj) => obj && typeof obj === 'object' && !Array.isArray(obj);

        const result = { ...defaults };

        for (const key in config) {
            if (config.hasOwnProperty(key)) {
                const defaultVal = result[key];
                const configVal = config[key];

                if (isObject(defaultVal) && isObject(configVal)) {
                    result[key] = this.mergeConfig(defaultVal, configVal);
                } else {
                    result[key] = configVal !== undefined ? configVal : defaultVal;
                }
            }
        }

        return result;
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