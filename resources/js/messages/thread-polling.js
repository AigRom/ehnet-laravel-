const THREAD_POLL_INTERVAL_MS = 5000;
const THREAD_POLL_TIMEOUT_MS = 8000;
const MARK_READ_TIMEOUT_MS = 8000;
const USER_IDLE_LIMIT_MS = 30 * 1000; // testimiseks 30 sekundit; tootmises nt 10 * 60 * 1000

let lastUserActivityAt = Date.now();

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

function isElementVisible(element) {
    return Boolean(
        element.offsetWidth ||
        element.offsetHeight ||
        element.getClientRects().length
    );
}

function isNearBottom(element, threshold = 180) {
    return element.scrollHeight - element.scrollTop - element.clientHeight < threshold;
}

function scrollToBottom(element) {
    element.scrollTop = element.scrollHeight;
}

function notifyUnreadRefresh() {
    document.dispatchEvent(new CustomEvent('messages:refresh-unread'));
}

function appendMessageHtml(messagesContainer, html) {
    const template = document.createElement('template');
    template.innerHTML = html.trim();

    const node = template.content.firstElementChild;

    if (!node) {
        return null;
    }

    const bottomMarker = messagesContainer.querySelector('[data-chat-bottom]');

    if (bottomMarker) {
        messagesContainer.insertBefore(node, bottomMarker);
    } else {
        messagesContainer.appendChild(node);
    }

    if (window.Alpine) {
        window.Alpine.initTree(node);
    }

    return node;
}

async function markThreadAsRead(thread) {
    if (
        document.hidden ||
        !userIsRecentlyActive() ||
        !isElementVisible(thread) ||
        thread.dataset.markingRead === '1'
    ) {
        return;
    }

    const markReadUrl = thread.dataset.chatMarkReadUrl;
    const csrfToken = thread.dataset.csrfToken;

    if (!markReadUrl || !csrfToken) {
        return;
    }

    thread.dataset.markingRead = '1';

    const abortController = new AbortController();
    const timeoutId = window.setTimeout(() => abortController.abort(), MARK_READ_TIMEOUT_MS);

    try {
        const response = await fetch(markReadUrl, {
            method: 'PATCH',
            credentials: 'same-origin',
            signal: abortController.signal,
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
            },
        });

        if (response.status === 401 || response.status === 419) {
            window.location.href = loginRedirectUrl();
            return;
        }

        if (response.ok) {
            notifyUnreadRefresh();
        }
    } catch (error) {
        if (error.name !== 'AbortError') {
            console.error('Vestluse loetuks märkimine ebaõnnestus:', error);
        }
    } finally {
        window.clearTimeout(timeoutId);
        thread.dataset.markingRead = '0';
    }
}

async function pollThread(thread) {
    if (
        document.hidden ||
        !userIsRecentlyActive() ||
        !isElementVisible(thread) ||
        thread.dataset.polling === '1'
    ) {
        return;
    }

    const pollUrl = thread.dataset.chatPollUrl;
    const messagesContainer = thread.querySelector('[data-chat-messages]');

    if (!pollUrl || !messagesContainer) {
        return;
    }

    thread.dataset.polling = '1';

    const abortController = new AbortController();
    const timeoutId = window.setTimeout(() => abortController.abort(), THREAD_POLL_TIMEOUT_MS);

    const lastMessageId = Number(thread.dataset.lastMessageId || 0);
    const shouldScroll = isNearBottom(thread);

    try {
        const url = new URL(pollUrl, window.location.origin);
        url.searchParams.set('after_id', String(lastMessageId));

        const response = await fetch(url.toString(), {
            method: 'GET',
            credentials: 'same-origin',
            signal: abortController.signal,
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (response.status === 401 || response.status === 419) {
            window.location.href = loginRedirectUrl();
            return;
        }

        if (!response.ok) {
            return;
        }

        const data = await response.json();

        if (!Array.isArray(data.messages) || data.messages.length === 0) {
            if (data.last_message_id) {
                thread.dataset.lastMessageId = String(data.last_message_id);
            }

            return;
        }

        const emptyState = messagesContainer.querySelector('[data-chat-empty]');

        if (emptyState) {
            emptyState.remove();
        }

        data.messages.forEach((message) => {
            if (!message.id || !message.html) {
                return;
            }

            if (messagesContainer.querySelector(`[data-message-id="${message.id}"]`)) {
                thread.dataset.lastMessageId = String(message.id);
                return;
            }

            const node = appendMessageHtml(messagesContainer, message.html);

            if (node) {
                thread.dataset.lastMessageId = String(message.id);
            }
        });

        if (data.last_message_id) {
            thread.dataset.lastMessageId = String(data.last_message_id);
        }

        markThreadAsRead(thread);
        notifyUnreadRefresh();

        if (shouldScroll) {
            scrollToBottom(thread);
        }
    } catch (error) {
        if (error.name !== 'AbortError') {
            console.error('Vestluse uuendamine ebaõnnestus:', error);
        }
    } finally {
        window.clearTimeout(timeoutId);
        thread.dataset.polling = '0';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const thread = document.querySelector('[data-chat-thread]');

    if (!thread) {
        return;
    }

    let intervalId = null;

    const startPolling = () => {
        if (intervalId) {
            return;
        }

        markThreadAsRead(thread);
        pollThread(thread);

        intervalId = window.setInterval(() => {
            markThreadAsRead(thread);
            pollThread(thread);
        }, THREAD_POLL_INTERVAL_MS);
    };

    const stopPolling = () => {
        if (!intervalId) {
            return;
        }

        window.clearInterval(intervalId);
        intervalId = null;
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
});