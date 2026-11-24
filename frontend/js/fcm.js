/**
 * Firebase Cloud Messaging & Web Notification Module (FR-29, Kriteria 6)
 */

import { ApiClient } from './api.js';

export class FcmService {
    static async requestPermissionAndRegister() {
        if (!('Notification' in window)) {
            console.warn('Browser ini tidak mendukung notifikasi.');
            return false;
        }

        try {
            const permission = await Notification.requestPermission();
            if (permission !== 'granted') {
                return false;
            }

            // Generate or retrieve push token (simulated FCM token)
            let token = localStorage.getItem('chat_fcm_token');
            if (!token) {
                token = 'fcm_' + Math.random().toString(36).substring(2) + Date.now().toString(36);
                localStorage.setItem('chat_fcm_token', token);
            }

            // Register token to backend server
            await ApiClient.post('/api/device-token', { fcm_token: token });
            this.showLocalNotification('Simple Chat', {
                body: 'Notifikasi aktif! Anda akan menerima pemberitahuan saat ada pesan atau foto baru masuk.',
            });
            return true;
        } catch (err) {
            console.warn('Gagal mendaftarkan notifikasi:', err);
            return false;
        }
    }

    static playNotificationSound() {
        try {
            const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();

            osc.type = 'sine';
            osc.frequency.setValueAtTime(587.33, audioCtx.currentTime); // D5
            osc.frequency.setValueAtTime(880, audioCtx.currentTime + 0.1); // A5

            gain.gain.setValueAtTime(0.15, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.35);

            osc.connect(gain);
            gain.connect(audioCtx.destination);

            osc.start();
            osc.stop(audioCtx.currentTime + 0.35);
        } catch {
            // AudioContext might be muted by browser policy until interaction
        }
    }

    static showLocalNotification(title, options = {}) {
        if ('Notification' in window && Notification.permission === 'granted') {
            try {
                new Notification(title, {
                    icon: '/icons/icon-192.png',
                    badge: '/icons/icon-192.png',
                    ...options,
                });
            } catch (err) {
                console.warn('Notification error:', err);
            }
        }
    }
}
