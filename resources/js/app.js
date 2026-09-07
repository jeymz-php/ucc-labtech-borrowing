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


const notificationSound = {
    context: null,
    unlocked: false,

    unlock() {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;

            if (!AudioContext) {
                return;
            }

            if (!this.context) {
                this.context = new AudioContext();
            }

            if (this.context.state === 'suspended') {
                this.context.resume();
            }

            this.unlocked = true;
        } catch (error) {
            console.debug('Notification sound could not be initialized.', error);
        }
    },

    play() {
        if (!this.unlocked || !this.context) {
            return;
        }

        try {
            const now = this.context.currentTime;
            const gain = this.context.createGain();
            gain.gain.setValueAtTime(0.0001, now);
            gain.gain.exponentialRampToValueAtTime(0.16, now + 0.02);
            gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.55);
            gain.connect(this.context.destination);

            [784, 1046].forEach((frequency, index) => {
                const oscillator = this.context.createOscillator();
                oscillator.type = 'sine';
                oscillator.frequency.setValueAtTime(frequency, now + (index * 0.11));
                oscillator.connect(gain);
                oscillator.start(now + (index * 0.11));
                oscillator.stop(now + 0.5);
            });
        } catch (error) {
            console.debug('Notification sound could not be played.', error);
        }
    },
};

window.UCCNotificationSound = notificationSound;

['pointerdown', 'keydown', 'touchstart'].forEach((eventName) => {
    window.addEventListener(eventName, () => notificationSound.unlock(), {
        once: true,
        passive: true,
    });
});

window.UCCNotifyToast = function UCCNotifyToast(title, message, url = null) {
    let container = document.getElementById('uccNotificationToastContainer');

    if (!container) {
        container = document.createElement('div');
        container.id = 'uccNotificationToastContainer';
        container.className = 'fixed right-4 top-20 z-[9998] flex w-[min(92vw,390px)] flex-col gap-3';
        document.body.appendChild(container);
    }

    const toast = document.createElement(url ? 'a' : 'div');

    if (url) {
        toast.href = url;
    }

    toast.className = 'block rounded-2xl border border-green-200 bg-white p-4 shadow-2xl ring-1 ring-black/5 transition';
    toast.innerHTML = `
        <div class="flex items-start gap-3">
            <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-green-100 text-green-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </span>
            <span class="min-w-0 flex-1">
                <span class="block text-sm font-bold text-gray-900"></span>
                <span class="mt-1 block text-sm leading-5 text-gray-600"></span>
            </span>
        </div>
    `;

    const textNodes = toast.querySelectorAll('span.block');
    textNodes[0].textContent = title || 'New notification';
    textNodes[1].textContent = message || 'You have a new update.';

    container.prepend(toast);

    window.setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-y-2');
        window.setTimeout(() => toast.remove(), 300);
    }, 6500);
};

Alpine.data('uccNotifications', (config) => ({
    open: false,
    unreadCount: Number(config.initialUnreadCount || 0),
    notifications: Array.isArray(config.initialNotifications)
        ? config.initialNotifications
        : [],
    knownIds: Array.isArray(config.initialNotifications)
        ? config.initialNotifications.map(notification => notification.id)
        : [],
    timer: null,
    requestRunning: false,

    start() {
        if (this.timer) {
            return;
        }

        this.timer = window.setInterval(() => {
            if (!document.hidden) {
                this.refresh();
            }
        }, 4000);

        window.setTimeout(() => this.refresh(), 1500);
    },

    toggle() {
        this.open = !this.open;

        if (this.open) {
            this.refresh();
        }
    },

    async refresh() {
        if (this.requestRunning) {
            return;
        }

        this.requestRunning = true;

        try {
            const response = await fetch(config.feedUrl, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                cache: 'no-store',
                credentials: 'same-origin',
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();
            const incoming = Array.isArray(data.notifications)
                ? data.notifications
                : [];
            const newNotifications = incoming.filter(
                notification => !this.knownIds.includes(notification.id)
            );

            this.unreadCount = Number(data.unread_count || 0);
            this.notifications = incoming;
            this.knownIds = Array.from(new Set([
                ...incoming.map(notification => notification.id),
                ...this.knownIds,
            ])).slice(0, 100);

            if (newNotifications.length > 0) {
                window.UCCNotificationSound?.play();

                newNotifications
                    .slice()
                    .reverse()
                    .forEach(notification => {
                        window.UCCNotifyToast?.(
                            notification.title,
                            notification.message,
                            notification.read_url
                        );
                    });
            }
        } catch (error) {
            console.debug('Real-time notifications are temporarily unavailable.', error);
        } finally {
            this.requestRunning = false;
        }
    },

    destroy() {
        if (this.timer) {
            window.clearInterval(this.timer);
            this.timer = null;
        }
    },
}));

Alpine.start();
