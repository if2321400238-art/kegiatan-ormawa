import './bootstrap';

import Alpine from 'alpinejs';
import mahasiswaSearch from './mahasiswa-search';
import anggotaSearch from './anggota-search';

window.Alpine = Alpine;

Alpine.data('mahasiswaSearch', mahasiswaSearch);
Alpine.data('anggotaSearch', anggotaSearch);

Alpine.data('notificationPopup', (notifications = []) => ({
    openNotif: false,
    notifications,

    toggle() {
        this.openNotif = !this.openNotif;
    },

    close() {
        this.openNotif = false;
    },

    normalizeUrl(link) {
        if (!link) {
            return null;
        }

        try {
            const url = new URL(link, window.location.origin);
            return url.origin + url.pathname + url.search;
        } catch (error) {
            return null;
        }
    },

    currentPageUrl() {
        return window.location.origin + window.location.pathname + window.location.search;
    },

    async markAsRead(notificationId) {
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!token || !notificationId) {
            return;
        }

        try {
            await fetch(`/notifikasi/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                },
                credentials: 'same-origin',
                body: JSON.stringify({}),
            });
        } catch (error) {
            console.error('Failed to mark notification as read', error);
        }
    },

    async autoMarkCurrentPage() {
        const currentUrl = this.currentPageUrl();

        for (const notif of this.notifications || []) {
            if (!notif || notif.dibaca || !notif.link) {
                continue;
            }

            const notifUrl = this.normalizeUrl(notif.link);
            if (notifUrl && notifUrl === currentUrl) {
                await this.markAsRead(notif.id);
            }
        }
    },
}));

Alpine.start();
