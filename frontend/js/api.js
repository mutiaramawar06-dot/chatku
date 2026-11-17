/**
 * API Client Module (Native ES Module)
 * Handles client-server communications via fetch with Bearer token authentication (FR-04, FR-17).
 */

const API_BASE_URL = window.location.port === '8000' 
    ? '' 
    : 'http://localhost:8000';

export class ApiClient {
    static getToken() {
        return localStorage.getItem('chat_token');
    }

    static setToken(token) {
        if (token) {
            localStorage.setItem('chat_token', token);
        } else {
            localStorage.removeItem('chat_token');
        }
    }

    static getUser() {
        const raw = localStorage.getItem('chat_user');
        try {
            return raw ? JSON.parse(raw) : null;
        } catch {
            return null;
        }
    }

    static setUser(user) {
        if (user) {
            localStorage.setItem('chat_user', JSON.stringify(user));
        } else {
            localStorage.removeItem('chat_user');
        }
    }

    static clearAuth() {
        localStorage.removeItem('chat_token');
        localStorage.removeItem('chat_user');
    }

    static async request(path, options = {}) {
        const url = `${API_BASE_URL}${path}`;
        const headers = options.headers || {};
        const token = this.getToken();

        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        // If body is not FormData, default to JSON
        let body = options.body;
        if (body && !(body instanceof FormData) && typeof body === 'object') {
            headers['Content-Type'] = 'application/json';
            body = JSON.stringify(body);
        }

        const config = {
            method: options.method || 'GET',
            headers,
            body,
        };

        try {
            const response = await fetch(url, config);
            let responseData = null;
            const contentType = response.headers.get('content-type') || '';
            if (contentType.includes('application/json')) {
                responseData = await response.json();
            } else {
                responseData = { message: await response.text() };
            }

            if (!response.ok) {
                // If 401 Unauthorized, automatically clear token and notify
                if (response.status === 401 && path !== '/api/auth/login') {
                    this.clearAuth();
                    window.dispatchEvent(new CustomEvent('auth:expired'));
                }
                const error = new Error(responseData?.message || `HTTP error ${response.status}`);
                error.status = response.status;
                error.data = responseData;
                throw error;
            }

            return responseData;
        } catch (error) {
            throw error;
        }
    }

    static get(path) {
        return this.request(path, { method: 'GET' });
    }

    static post(path, body) {
        return this.request(path, { method: 'POST', body });
    }

    static put(path, body) {
        return this.request(path, { method: 'PUT', body });
    }

    static delete(path) {
        return this.request(path, { method: 'DELETE' });
    }
}
