import { describe, it, expect, beforeEach, vi } from 'vitest';
import { ApiClient } from '../js/api.js';
import { AuthService } from '../js/auth.js';
import { ContactsService } from '../js/contacts.js';
import { ChatService } from '../js/chat.js';

describe('1. Form Validation Unit Tests (FR-13, FR-14)', () => {
    describe('Name Validation (FR-14: No numbers allowed)', () => {
        it('should accept valid alphabetic names', () => {
            expect(AuthService.validateName('Budi Santoso').valid).toBe(true);
            expect(AuthService.validateName('Siti Nurhaliza').valid).toBe(true);
            expect(AuthService.validateName("O'Connor").valid).toBe(true);
        });

        it('should reject names containing numbers', () => {
            const res = AuthService.validateName('Budi123');
            expect(res.valid).toBe(false);
            expect(res.message).toContain('tidak boleh mengandung angka');
        });

        it('should reject names that are too short', () => {
            expect(AuthService.validateName('A').valid).toBe(false);
            expect(AuthService.validateName('').valid).toBe(false);
        });
    });

    describe('Phone Validation (FR-13: Digits only, optional +)', () => {
        it('should accept valid phone numbers', () => {
            expect(AuthService.validatePhone('081234567890').valid).toBe(true);
            expect(AuthService.validatePhone('+6281234567890').valid).toBe(true);
            expect(AuthService.validatePhone('08571234567').valid).toBe(true);
        });

        it('should reject phone numbers with alphabets or special symbols', () => {
            expect(AuthService.validatePhone('0812ABC5678').valid).toBe(false);
            expect(AuthService.validatePhone('0812-3456-7890').valid).toBe(false);
            expect(AuthService.validatePhone('phone123456').valid).toBe(false);
        });

        it('should reject phone numbers that are too short or too long', () => {
            expect(AuthService.validatePhone('12345').valid).toBe(false);
            expect(AuthService.validatePhone('12345678901234567890123').valid).toBe(false);
        });
    });

    describe('Password Validation', () => {
        it('should validate minimum 6 characters', () => {
            expect(AuthService.validatePassword('123456').valid).toBe(true);
            expect(AuthService.validatePassword('securePass123').valid).toBe(true);
            expect(AuthService.validatePassword('12345').valid).toBe(false);
            expect(AuthService.validatePassword('').valid).toBe(false);
        });
    });
});

describe('2. Client API CRUD with Mocked Fetch (FR-26)', () => {
    beforeEach(() => {
        localStorage.clear();
        vi.restoreAllMocks();
    });

    it('should include Bearer token in headers when user is authenticated', async () => {
        ApiClient.setToken('mock_jwt_token_12345');

        global.fetch = vi.fn().mockResolvedValue({
            ok: true,
            status: 200,
            headers: {
                get: (header) => (header.toLowerCase() === 'content-type' ? 'application/json' : null),
            },
            json: async () => ({ success: true, data: { id: '01M7TEST', name: 'Alice' } }),
        });

        const res = await ApiClient.get('/api/me');

        expect(global.fetch).toHaveBeenCalledTimes(1);
        const [url, config] = global.fetch.mock.calls[0];
        expect(config.headers['Authorization']).toBe('Bearer mock_jwt_token_12345');
        expect(res.success).toBe(true);
        expect(res.data.name).toBe('Alice');
    });

    it('should handle Contact CRUD requests (add, list, update, delete)', async () => {
        ApiClient.setToken('mock_token');

        // Mock add contact (POST)
        global.fetch = vi.fn().mockResolvedValueOnce({
            ok: true,
            status: 201,
            headers: { get: () => 'application/json' },
            json: async () => ({
                success: true,
                statusCode: 201,
                data: { id: 10, alias_name: 'Sahabat', user_phone: '081299999999' },
            }),
        });

        const addRes = await ContactsService.addContact('081299999999', 'Sahabat');
        expect(addRes.success).toBe(true);
        expect(addRes.data.id).toBe(10);

        // Mock list contacts (GET)
        global.fetch = vi.fn().mockResolvedValueOnce({
            ok: true,
            status: 200,
            headers: { get: () => 'application/json' },
            json: async () => ({
                success: true,
                data: [{ id: 10, alias_name: 'Sahabat', user_phone: '081299999999' }],
            }),
        });

        const listRes = await ContactsService.getContacts();
        expect(listRes.length).toBe(1);
        expect(listRes[0].alias_name).toBe('Sahabat');

        // Mock update contact (PUT)
        global.fetch = vi.fn().mockResolvedValueOnce({
            ok: true,
            status: 200,
            headers: { get: () => 'application/json' },
            json: async () => ({
                success: true,
                data: { id: 10, alias_name: 'Sahabat Terbaik', user_phone: '081299999999' },
            }),
        });

        const updateRes = await ContactsService.updateContact(10, 'Sahabat Terbaik');
        expect(updateRes.data.alias_name).toBe('Sahabat Terbaik');

        // Mock delete contact (DELETE)
        global.fetch = vi.fn().mockResolvedValueOnce({
            ok: true,
            status: 200,
            headers: { get: () => 'application/json' },
            json: async () => ({ success: true, message: 'Contact deleted successfully' }),
        });

        const deleteRes = await ContactsService.deleteContact(10);
        expect(deleteRes.success).toBe(true);
    });

    it('should throw error and parse error payload when API returns 404/409/422', async () => {
        global.fetch = vi.fn().mockResolvedValueOnce({
            ok: false,
            status: 404,
            headers: { get: () => 'application/json' },
            json: async () => ({
                success: false,
                message: 'Target user with phone number 089999999999 is not registered',
                errors: { phone: 'Unregistered user phone number' },
            }),
        });

        await expect(ContactsService.addContact('089999999999', 'Ghost')).rejects.toThrow(
            'Target user with phone number 089999999999 is not registered'
        );
    });
});

describe('3. Contact List Rendering & XSS Protection (FR-16, FR-24)', () => {
    it('should safely render contact element with textContent to prevent XSS injection', () => {
        const maliciousContact = {
            id: 42,
            alias_name: '<script>alert("XSS")</script>',
            user_name: '<img src=x onerror=alert(1)>',
            user_phone: '081234567890',
            photo_path: '/uploads/profiles/test.jpg',
        };

        const onSelect = vi.fn();
        const onEdit = vi.fn();
        const onDelete = vi.fn();

        const element = ContactsService.createContactElement(
            maliciousContact,
            onSelect,
            onEdit,
            onDelete
        );

        // Verify element structure
        expect(element.className).toContain('contact-item');
        expect(element.dataset.contactId).toBe('42');

        // Check textContent sanitization (must not contain unescaped HTML elements)
        const nameEl = element.querySelector('.contact-name');
        expect(nameEl.textContent).toBe('<script>alert("XSS")</script>');
        expect(element.querySelectorAll('script').length).toBe(0);

        const subEl = element.querySelector('.contact-sub');
        expect(subEl.textContent).toBe('081234567890 (<img src=x onerror=alert(1)>)');
        expect(element.querySelectorAll('img[onerror]').length).toBe(0);

        // Check lazy loading data-src attribute (FR-24)
        const imgEl = element.querySelector('img.contact-avatar-img');
        expect(imgEl).not.toBeNull();
        expect(imgEl.getAttribute('data-src')).toBe('/uploads/profiles/test.jpg');

        // Check action button clicks
        const deleteBtn = element.querySelector('.contact-actions .text-danger');
        deleteBtn.click();
        expect(onDelete).toHaveBeenCalledWith(maliciousContact);
    });

    it('should safely render message bubbles with textContent and outgoing/incoming classes', () => {
        const msg = {
            id: 101,
            sender_id: 'USER_A',
            receiver_id: 'USER_B',
            body: 'Hello <b>World</b> & <script>',
            image_path: '/uploads/messages/photo.png',
            created_at: '2026-11-17 14:30:00',
            read_at: '2026-11-17 14:31:00',
        };

        const bubbleWrap = ChatService.createMessageBubble(msg, 'USER_A');

        expect(bubbleWrap.className).toContain('outgoing');
        const textEl = bubbleWrap.querySelector('.message-text');
        expect(textEl.textContent).toBe('Hello <b>World</b> & <script>');
        expect(bubbleWrap.querySelectorAll('b').length).toBe(0);

        const imgEl = bubbleWrap.querySelector('img.message-img');
        expect(imgEl.getAttribute('data-src')).toBe('/uploads/messages/photo.png');

        const statusEl = bubbleWrap.querySelector('.message-status');
        expect(statusEl.textContent).toBe('✓✓');
    });
});
