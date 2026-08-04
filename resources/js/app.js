import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

const loader = {
    element: null,
    title: null,
    message: null,
    activeRequests: 0,
    safetyTimer: null,

    boot() {
        this.element = document.getElementById('uccGlobalLoader');
        this.title = document.getElementById('uccGlobalLoaderTitle');
        this.message = document.getElementById('uccGlobalLoaderMessage');
    },

    show(message = 'UCC LabTech is processing your request.', title = 'Please wait') {
        if (!this.element) {
            this.boot();
        }

        if (!this.element) {
            return;
        }

        if (this.title) {
            this.title.textContent = title;
        }

        if (this.message) {
            this.message.textContent = message;
        }

        this.element.classList.remove('hidden');
        this.element.classList.add('flex');
        this.element.setAttribute('aria-hidden', 'false');
        document.body.classList.add('ucc-is-loading');

        window.clearTimeout(this.safetyTimer);
        this.safetyTimer = window.setTimeout(() => this.hide(), 30000);
    },

    hide() {
        if (!this.element) {
            this.boot();
        }

        if (!this.element) {
            return;
        }

        window.clearTimeout(this.safetyTimer);
        this.safetyTimer = null;

        this.element.classList.add('hidden');
        this.element.classList.remove('flex');
        this.element.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('ucc-is-loading');
    },

    beginRequest(message) {
        this.activeRequests += 1;
        this.show(message);
    },

    endRequest() {
        this.activeRequests = Math.max(0, this.activeRequests - 1);

        if (this.activeRequests === 0) {
            this.hide();
        }
    },
};

window.UCCLoader = loader;

function installAxiosLoading() {
    const axiosInstance = window.axios;

    if (!axiosInstance || axiosInstance.__uccLoaderInstalled) {
        return;
    }

    axiosInstance.__uccLoaderInstalled = true;

    axiosInstance.interceptors.request.use((config) => {
        if (config.uccSilent !== true) {
            config.__uccLoading = true;
            loader.beginRequest(
                config.uccLoadingMessage || 'Communicating with UCC LabTech...'
            );
        }

        return config;
    }, (error) => {
        loader.endRequest();
        return Promise.reject(error);
    });

    const finish = (value) => {
        if (value?.config?.__uccLoading) {
            loader.endRequest();
        }

        return value;
    };

    axiosInstance.interceptors.response.use(
        (response) => finish(response),
        (error) => {
            finish(error);
            return Promise.reject(error);
        },
    );
}

installAxiosLoading();

function isNavigableLink(anchor, event) {
    if (!anchor || event.defaultPrevented) {
        return false;
    }

    if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
        return false;
    }

    if (anchor.dataset.noLoading !== undefined || anchor.hasAttribute('download')) {
        return false;
    }

    if (anchor.target && anchor.target !== '_self') {
        return false;
    }

    const href = anchor.getAttribute('href');

    if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) {
        return false;
    }

    let url;

    try {
        url = new URL(anchor.href, window.location.href);
    } catch {
        return false;
    }

    return url.origin === window.location.origin;
}

function formLoadingMessage(form) {
    return form.dataset.loadingMessage
        || (form.method?.toLowerCase() === 'get'
            ? 'Loading the requested information...'
            : 'Saving and processing your request...');
}

function prepareFormSubmission(form) {
    if (form.dataset.noLoading !== undefined) {
        return;
    }

    const submitter = form.querySelector('button[type="submit"], input[type="submit"]');

    if (submitter && !submitter.disabled) {
        submitter.dataset.originalDisabled = 'false';
        submitter.disabled = true;
        submitter.setAttribute('aria-busy', 'true');
    }

    loader.show(formLoadingMessage(form), 'Processing');
}

document.addEventListener('DOMContentLoaded', () => {
    loader.boot();
    loader.hide();

    document.addEventListener('click', (event) => {
        const anchor = event.target.closest('a[href]');

        if (!isNavigableLink(anchor, event)) {
            return;
        }

        loader.show(anchor.dataset.loadingMessage || 'Opening the selected page...', 'Loading');
    });

    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        if (!form.checkValidity()) {
            return;
        }

        prepareFormSubmission(form);
    });

    window.addEventListener('ucc-loading:start', (event) => {
        loader.beginRequest(event.detail?.message || 'Processing your request...');
    });

    window.addEventListener('ucc-loading:stop', () => {
        loader.endRequest();
    });
});

window.addEventListener('pageshow', () => {
    loader.activeRequests = 0;
    loader.hide();
});

window.addEventListener('load', () => loader.hide());

Alpine.start();
