/**
 * Authentication & Account Management Module (FR-01, FR-02, FR-03, FR-13, FR-14)
 */

import { ApiClient } from './api.js';

export class AuthService {
    static validateName(name) {
        const trimmed = (name || '').trim();
        if (trimmed.length < 2) {
            return { valid: false, message: 'Nama harus memiliki minimal 2 karakter' };
        }
        if (/[0-9]/.test(trimmed)) {
            return { valid: false, message: 'Nama tidak boleh mengandung angka (FR-14)' };
        }
        return { valid: true, message: '' };
    }

    static validatePhone(phone) {
        const trimmed = (phone || '').trim();
        if (!/^\+?[0-9]{8,20}$/.test(trimmed)) {
            return { valid: false, message: 'Nomor HP hanya boleh berisi digit angka (boleh diawali +) dengan panjang 8-20 digit (FR-13)' };
        }
        return { valid: true, message: '' };
    }

    static validatePassword(password) {
        if (!password || password.length < 6) {
            return { valid: false, message: 'Password harus memiliki minimal 6 karakter' };
        }
        return { valid: true, message: '' };
    }

    static async register(formData) {
        const res = await ApiClient.post('/api/auth/register', formData);
        return res;
    }

    static async login(phone, password) {
        const res = await ApiClient.post('/api/auth/login', { phone, password });
        if (res.success && res.data?.token) {
            ApiClient.setToken(res.data.token);
            ApiClient.setUser(res.data.user);
        }
        return res;
    }

    static async getProfile() {
        const res = await ApiClient.get('/api/me');
        if (res.success && res.data) {
            ApiClient.setUser(res.data);
        }
        return res;
    }

    static async deleteAccount() {
        const res = await ApiClient.delete('/api/account');
        ApiClient.clearAuth();
        return res;
    }

    static logout() {
        ApiClient.clearAuth();
    }

    static isAuthenticated() {
        return Boolean(ApiClient.getToken());
    }

    static getCurrentUser() {
        return ApiClient.getUser();
    }
}
