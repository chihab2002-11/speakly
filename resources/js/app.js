import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
const reverbAppKey = import.meta.env.VITE_REVERB_APP_KEY;

if (reverbAppKey) {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: reverbAppKey,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
        },
    });
}

function notificationValue(notification, keys, fallback = '') {
    for (const key of keys) {
        if (notification?.[key] !== undefined && notification[key] !== null && notification[key] !== '') {
            return notification[key];
        }

        if (notification?.data?.[key] !== undefined && notification.data[key] !== null && notification.data[key] !== '') {
            return notification.data[key];
        }
    }

    return fallback;
}

function normalizeNotification(notification) {
    const message = notificationValue(notification, ['message', 'body', 'text'], '');
    const url = notificationValue(notification, ['url', 'action_url'], '#');

    return {
        id: notificationValue(notification, ['id', 'notification_id'], ''),
        title: notificationValue(notification, ['title'], notificationValue(notification, ['type'], 'Notification')),
        message,
        body: notificationValue(notification, ['body'], message),
        text: notificationValue(notification, ['text'], message),
        type: notificationValue(notification, ['type'], 'notification'),
        url,
        actionUrl: notificationValue(notification, ['action_url'], url),
        createdAt: notificationValue(notification, ['created_at'], new Date().toISOString()),
    };
}

function unreadCountFromBadge(badge) {
    const raw = (badge?.textContent || '0').trim();

    if (raw === '9+' || raw === '99+') {
        return Number.parseInt(raw, 10);
    }

    return Number.parseInt(raw, 10) || 0;
}

function formatUnreadCount(count, compactLimit = 9) {
    if (count > compactLimit) {
        return `${compactLimit}+`;
    }

    return String(count);
}

function setUnreadCount(count) {
    document.querySelectorAll('[data-live-notification-count]').forEach((badge) => {
        const compactLimit = (badge.textContent || '').trim() === '99+' ? 99 : 9;

        badge.textContent = formatUnreadCount(count, compactLimit);
        badge.classList.toggle('hidden', count <= 0);
    });
}

function incrementUnreadCount() {
    document.querySelectorAll('[data-live-notification-count]').forEach((badge) => {
        const compactLimit = badge.textContent.trim() === '99+' ? 99 : 9;
        const nextCount = unreadCountFromBadge(badge) + 1;

        badge.textContent = formatUnreadCount(nextCount, compactLimit);
        badge.classList.remove('hidden');
    });
}

function escapeHtml(value) {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };

    return String(value ?? '').replace(/[&<>"']/g, (character) => map[character]);
}

function readRouteFor(container, notificationId) {
    const template = container.dataset.liveNotificationReadRouteTemplate || '';

    if (!template || !notificationId) {
        return '';
    }

    return template.replace('__ID__', encodeURIComponent(notificationId));
}

function notificationCardHtml(notification, readRoute) {
    const openLink = notification.url && notification.url !== '#'
        ? `<a href="${escapeHtml(notification.url)}" class="text-xs font-semibold transition-colors hover:underline" style="color: var(--lumina-primary, #047857);">View Details</a>`
        : '';
    const readForm = readRoute
        ? `<form method="POST" action="${escapeHtml(readRoute)}" class="ml-auto">
                <input type="hidden" name="_token" value="${escapeHtml(csrfToken)}">
                <button type="submit" class="text-xs font-semibold transition-colors hover:underline" style="color: var(--lumina-text-muted, #64748B);">Mark as Read</button>
            </form>`
        : '';

    return `
        <div class="flex items-start gap-4 border-b p-6 transition-colors hover:bg-gray-50" style="border-color: var(--lumina-border, #E2E8F0);" data-live-notification-item data-live-notification-id="${escapeHtml(notification.id)}">
            <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100">
                <svg class="h-6 w-6 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </div>
            <div class="flex-1">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <h3 class="text-base font-bold" style="color: var(--lumina-text-primary, #0F172A);">${escapeHtml(notification.title)}</h3>
                        <p class="mt-1 text-sm" style="color: var(--lumina-text-secondary, #475569);">${escapeHtml(notification.message || 'You have a new notification.')}</p>
                    </div>
                    <div class="flex-shrink-0"><span class="flex h-2 w-2 rounded-full bg-red-500"></span></div>
                </div>
                <div class="mt-3 flex items-center gap-4">
                    <span class="text-xs" style="color: var(--lumina-text-muted, #64748B);">Just now</span>
                    ${openLink}
                    ${readForm}
                </div>
            </div>
        </div>
    `;
}

function compactNotificationCardHtml(notification) {
    const openLink = notification.url && notification.url !== '#'
        ? `<a href="${escapeHtml(notification.url)}" class="text-xs font-medium text-blue-600 hover:underline dark:text-blue-400">Open</a>`
        : '';

    return `
        <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700" data-live-notification-item data-live-notification-id="${escapeHtml(notification.id)}">
            <p class="text-sm font-semibold text-zinc-900 dark:text-white">${escapeHtml(notification.title)}</p>
            <p class="mt-1 text-xs text-zinc-600 dark:text-zinc-400">${escapeHtml(notification.message)}</p>
            <div class="mt-2 flex items-center justify-between">
                <span class="text-[11px] text-zinc-400 dark:text-zinc-500">Just now</span>
                ${openLink}
            </div>
        </div>
    `;
}

function prependNotification(notification) {
    document.querySelectorAll('[data-live-notification-empty]').forEach((emptyState) => {
        emptyState.classList.add('hidden');
    });

    document.querySelectorAll('[data-live-notification-list]').forEach((container) => {
        const readRoute = readRouteFor(container, notification.id);
        const html = container.dataset.liveNotificationReadRouteTemplate
            ? notificationCardHtml(notification, readRoute)
            : compactNotificationCardHtml(notification);

        container.insertAdjacentHTML('afterbegin', html);
    });
}

function showNotificationToast(notification) {
    const toast = document.getElementById('live-notification-toast');

    if (!toast) {
        return;
    }

    const title = toast.querySelector('[data-live-toast-title]');
    const message = toast.querySelector('[data-live-toast-message]');

    if (title) {
        title.textContent = notification.title;
    }

    if (message) {
        message.textContent = notification.message;
    }

    toast.classList.remove('hidden');

    window.clearTimeout(toast.dataset.liveHideTimer);
    toast.dataset.liveHideTimer = window.setTimeout(() => {
        toast.classList.add('hidden');
    }, 5000);
}

const knownNotificationIds = new Set();
let notificationPollInFlight = false;
let notificationInitialPollComplete = false;
const notificationPollingStartedAt = Date.now();

function rememberRenderedNotifications() {
    document.querySelectorAll('[data-live-notification-id]').forEach((item) => {
        if (item.dataset.liveNotificationId) {
            knownNotificationIds.add(item.dataset.liveNotificationId);
        }
    });
}

function rememberNotification(notification) {
    if (notification.id) {
        knownNotificationIds.add(String(notification.id));
    }
}

function handleIncomingNotification(incomingNotification, options = {}) {
    const notification = normalizeNotification(incomingNotification);
    const notificationId = String(notification.id || '');

    if (notificationId && knownNotificationIds.has(notificationId)) {
        return;
    }

    rememberNotification(notification);

    if (typeof options.unreadCount === 'number') {
        setUnreadCount(options.unreadCount);
    } else {
        incrementUnreadCount();
    }

    prependNotification(notification);

    if (options.showToast !== false) {
        showNotificationToast(notification);
    }
}

async function pollNotifications() {
    const userId = window.AuthUser?.id;

    if (!userId || notificationPollInFlight || document.hidden) {
        return;
    }

    notificationPollInFlight = true;

    try {
        const response = await fetch('/notifications/live', {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            return;
        }

        const data = await response.json();
        const notifications = Array.isArray(data.notifications) ? data.notifications : [];
        const hasRenderedNotificationState = knownNotificationIds.size > 0;
        const newNotifications = notifications
            .slice()
            .reverse()
            .filter((notification) => {
                if (!notification?.id || knownNotificationIds.has(String(notification.id))) {
                    return false;
                }

                if (notificationInitialPollComplete || hasRenderedNotificationState) {
                    return true;
                }

                const createdAt = Date.parse(notification.created_at || '');

                return Number.isNaN(createdAt) || createdAt >= notificationPollingStartedAt - 1000;
            });

        setUnreadCount(Number(data.unread_count || 0));

        newNotifications.forEach((notification) => {
            handleIncomingNotification(notification, {
                unreadCount: Number(data.unread_count || 0),
                showToast: true,
            });
        });

        if (!notificationInitialPollComplete) {
            notifications.forEach((notification) => {
                if (notification?.id) {
                    knownNotificationIds.add(String(notification.id));
                }
            });
            notificationInitialPollComplete = true;
        }
    } finally {
        notificationPollInFlight = false;
    }
}

function startLiveNotifications() {
    const userId = window.AuthUser?.id;

    if (!userId) {
        return;
    }

    rememberRenderedNotifications();

    if (window.Echo) {
        window.Echo.private(`App.Models.User.${userId}`)
            .notification((incomingNotification) => {
                handleIncomingNotification(incomingNotification);
            });
    }

    pollNotifications();
    window.setInterval(pollNotifications, 3000);
}

document.addEventListener('DOMContentLoaded', startLiveNotifications);
