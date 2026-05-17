const UNREAD_POLL_INTERVAL_MS = 5000;
const UNREAD_POLL_TIMEOUT_MS = 8000;
const USER_IDLE_LIMIT_MS = 30 * 1000; // testimiseks 30 sekundit; tootmises nt 10 * 60 * 1000

let lastUnreadCount = null;
let lastUserActivityAt = Date.now();

const unreadCardClasses = [
    'border-emerald-900/20',
    'bg-white',
    'shadow-sm',
    'hover:border-emerald-900/30',
    'hover:bg-emerald-50/40',
    'hover:shadow-md',
];

const readCardClasses = [
    'border-emerald-950/10',
    'bg-white/90',
    'hover:border-emerald-900/20',
    'hover:bg-white',
    'hover:shadow-md',
];

function markUserActive() {
    lastUserActivityAt = Date.now();
}

function userIsRecentlyActive() {
    return Date.now() - lastUserActivityAt < USER_IDLE_LIMIT_MS;
}

['click', 'keydown', 'mousemove', 'scroll', 'touchstart'].forEach((eventName) => {
    window.addEventListener(eventName, markUserActive, { passive: true });
});

function loginRedirectUrl() {
    return '/login?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
}

function updateUnreadBadges(count) {
    if (lastUnreadCount === count) {
        return;
    }

    lastUnreadCount = count;

    document.querySelectorAll('[data-unread-badge]').forEach((badge) => {
        if (count > 0) {
            badge.classList.remove('hidden');
            badge.classList.add('inline-flex');
        } else {
            badge.classList.remove('inline-flex');
            badge.classList.add('hidden');
        }
    });

    document.querySelectorAll('[data-unread-count]').forEach((counter) => {
        counter.textContent = count > 99 ? '99+' : String(count);
    });
}

function updateConversationListItems(conversationCounts) {
    document.querySelectorAll('[data-conversation-list-item]').forEach((item) => {
        const conversationId = item.dataset.conversationId;
        const isActive = item.dataset.active === '1';
        const unreadCount = Number(conversationCounts?.[conversationId] || 0);
        const hasUnread = unreadCount > 0;

        const unreadLabel = item.querySelector('[data-conversation-unread-label]');
        const title = item.querySelector('[data-conversation-title]');
        const preview = item.querySelector('[data-conversation-preview]');

        if (unreadLabel) {
            unreadLabel.classList.toggle('hidden', !hasUnread);
        }

        if (title) {
            title.classList.toggle('text-emerald-950', hasUnread);
            title.classList.toggle('text-zinc-900', !hasUnread);
        }

        if (preview) {
            preview.classList.toggle('font-bold', hasUnread);
            preview.classList.toggle('text-emerald-950', hasUnread);

            preview.classList.toggle('font-medium', !hasUnread);
            preview.classList.toggle('text-zinc-600', !hasUnread);
        }

        if (!isActive) {
            item.classList.remove(...unreadCardClasses, ...readCardClasses);
            item.classList.add(...(hasUnread ? unreadCardClasses : readCardClasses));
        }
    });
}

function stopUnreadPolling(state) {
    if (!state.intervalId) {
        return;
    }

    window.clearInterval(state.intervalId);
    state.intervalId = null;
}

async function refreshUnreadCount(unreadCountUrl, state, force = false) {
    if (
        document.hidden ||
        state.isPolling ||
        (!force && !userIsRecentlyActive())
    ) {
        return;
    }

    state.isPolling = true;

    const abortController = new AbortController();
    const timeoutId = window.setTimeout(() => abortController.abort(), UNREAD_POLL_TIMEOUT_MS);

    try {
        const response = await fetch(unreadCountUrl, {
            method: 'GET',
            credentials: 'same-origin',
            signal: abortController.signal,
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (response.status === 401 || response.status === 419) {
            stopUnreadPolling(state);
            window.location.href = loginRedirectUrl();
            return;
        }

        if (!response.ok) {
            return;
        }

        const data = await response.json();

        updateUnreadBadges(Number(data.count || 0));
        updateConversationListItems(data.conversations || {});
    } catch (error) {
        if (error.name !== 'AbortError') {
            console.error('Lugemata sõnumite arvu uuendamine ebaõnnestus:', error);
        }
    } finally {
        window.clearTimeout(timeoutId);
        state.isPolling = false;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const header = document.querySelector('[data-messages-unread-url]');

    if (!header) {
        return;
    }

    const unreadCountUrl = header.dataset.messagesUnreadUrl;

    if (!unreadCountUrl) {
        return;
    }

    const state = {
        isPolling: false,
        intervalId: null,
    };

    const startPolling = () => {
        if (state.intervalId) {
            return;
        }

        refreshUnreadCount(unreadCountUrl, state, true);

        state.intervalId = window.setInterval(() => {
            refreshUnreadCount(unreadCountUrl, state);
        }, UNREAD_POLL_INTERVAL_MS);
    };

    const stopPolling = () => {
        stopUnreadPolling(state);
    };

    if (!document.hidden) {
        startPolling();
    }

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopPolling();
        } else {
            markUserActive();
            startPolling();
        }
    });

    document.addEventListener('messages:refresh-unread', () => {
        refreshUnreadCount(unreadCountUrl, state, true);
    });
});