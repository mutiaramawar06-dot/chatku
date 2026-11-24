/**
 * Contacts Management Module (FR-06, FR-07, FR-16, FR-24)
 * Strict XSS prevention by utilizing textContent for all user-provided data.
 */

import { ApiClient, resolveImageUrl } from './api.js';
import { observeLazyImages } from './lazy.js';

export class ContactsService {
    static async getContacts() {
        const res = await ApiClient.get('/api/contacts');
        return res.data || [];
    }

    static async addContact(phone, aliasName) {
        return await ApiClient.post('/api/contacts', {
            phone: phone.trim(),
            alias_name: aliasName.trim(),
        });
    }

    static async updateContact(id, aliasName, phone = null) {
        const payload = { alias_name: aliasName.trim() };
        if (phone) {
            payload.phone = phone.trim();
        }
        return await ApiClient.put(`/api/contacts/${id}`, payload);
    }

    static async deleteContact(id) {
        return await ApiClient.delete(`/api/contacts/${id}`);
    }

    /**
     * Renders a contact item DOM node safely with textContent (FR-16) and lazy-loaded image (FR-24).
     */
    static createContactElement(contact, onSelect, onEdit, onDelete) {
        const item = document.createElement('div');
        item.className = 'contact-item';
        item.dataset.contactId = contact.id;

        // Avatar container
        const avatarBox = document.createElement('div');
        avatarBox.className = 'contact-avatar-box';

        if (contact.photo_path) {
            const img = document.createElement('img');
            img.className = 'contact-avatar-img';
            img.alt = contact.alias_name;
            const fullAvatar = resolveImageUrl(contact.photo_path);
            img.setAttribute('data-src', fullAvatar);
            img.src = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="40" height="40"%3E%3Crect width="100%25" height="100%25" fill="%23e2e8f0"/%3E%3C/svg%3E';
            avatarBox.appendChild(img);
        } else {
            const placeholder = document.createElement('div');
            placeholder.className = 'avatar-placeholder';
            placeholder.textContent = (contact.alias_name || 'U').charAt(0).toUpperCase();
            avatarBox.appendChild(placeholder);
        }

        // Info container
        const infoBox = document.createElement('div');
        infoBox.className = 'contact-info';
        infoBox.onclick = () => onSelect && onSelect(contact);

        const nameEl = document.createElement('h4');
        nameEl.className = 'contact-name';
        nameEl.textContent = contact.alias_name;

        const subEl = document.createElement('p');
        subEl.className = 'contact-sub';
        subEl.textContent = `${contact.user_phone} (${contact.user_name})`;

        infoBox.appendChild(nameEl);
        infoBox.appendChild(subEl);

        // Actions container
        const actionsBox = document.createElement('div');
        actionsBox.className = 'contact-actions';

        const chatBtn = document.createElement('button');
        chatBtn.className = 'btn-icon';
        chatBtn.title = 'Kirim Pesan';
        chatBtn.textContent = '💬';
        chatBtn.onclick = (e) => {
            e.stopPropagation();
            onSelect && onSelect(contact);
        };

        const editBtn = document.createElement('button');
        editBtn.className = 'btn-icon';
        editBtn.title = 'Edit Kontak';
        editBtn.textContent = '✏️';
        editBtn.onclick = (e) => {
            e.stopPropagation();
            onEdit && onEdit(contact);
        };

        const deleteBtn = document.createElement('button');
        deleteBtn.className = 'btn-icon text-danger';
        deleteBtn.title = 'Hapus Kontak';
        deleteBtn.textContent = '🗑️';
        deleteBtn.onclick = (e) => {
            e.stopPropagation();
            onDelete && onDelete(contact);
        };

        actionsBox.appendChild(chatBtn);
        actionsBox.appendChild(editBtn);
        actionsBox.appendChild(deleteBtn);

        item.appendChild(avatarBox);
        item.appendChild(infoBox);
        item.appendChild(actionsBox);

        return item;
    }
}
