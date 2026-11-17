/**
 * Firebase Cloud Messaging & Web Push Module (FR-29, Kriteria 6)
 */

import { ApiClient } from './api.js';

export class FcmService {
    static async requestPermissionAndRegister() {
        if (!('Notification' in window)) {
            console.warn('Browser ini tidak mendukung desktop notifications.');
            return false;
        }

        try {
            const permission = await Notification.requestPermission();
            if (permission !== 'granted') {
                return false;
            }

            // Register service worker if available
            if ('serviceWorker' in navigator) {
                const reg = await navigator.serviceWorker.ready;
                // Generate or retrieve push token (simulated or real FCM)
                let token = localStorage.getItem('chat_fcm_token');
                if (!token) {
                    token = 'fcm_' + Math.random().toString(36).substring(2) + Date.now().toString(36);
                    localStorage.setItem('chat_fcm_token', token);
                }

                // Register token to backend server
                await ApiClient.post('/api/device-token', { fcm_token: token });
                return true;
            }
        } catch (err) {
            console.warn('Gagal mendaftarkan token notifikasi:', err);
        }
        return false;
    }

    static showLocalNotification(title, options) {
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification(title, {
                icon: '/icons/icon-192.png',
                badge: '/icons/icon-192.png',
                ...options,
            });
        }
    }
}
