/**
 * Messaging & Conversations Module (FR-08, FR-09, FR-10, FR-11, FR-12, FR-16, FR-23, FR-24)
 * Handles conversation rendering, polling updates, and secure XSS-safe message bubble generation.
 */

import { ApiClient, resolveImageUrl } from './api.js';
import { observeLazyImages } from './lazy.js';

export class ChatService {
    static pollingInterval = null;
    static activePartnerId = null;

    static async getConversations() {
        const res = await ApiClient.get('/api/conversations');
        return res.data || [];
    }

    static async getMessageHistory(partnerId) {
        const res = await ApiClient.get(`/api/messages/${partnerId}`);
        return res.data || { partner: null, messages: [] };
    }

    static async sendMessage(receiverId, body, imageFile = null) {
        if (imageFile) {
            const formData = new FormData();
            formData.append('receiver_id', receiverId);
            if (body) {
                formData.append('body', body);
            }
            formData.append('image', imageFile);
            return await ApiClient.post('/api/messages', formData);
        }

        return await ApiClient.post('/api/messages', {
            receiver_id: receiverId,
            body: body || '',
        });
    }

    /**
     * Start background polling for conversation and active chat updates (FR-11).
     */
    static startPolling(onConversationsUpdate, onActiveChatUpdate, intervalMs = 2500) {
        this.stopPolling();
        this.pollingInterval = setInterval(async () => {
            try {
                if (onConversationsUpdate) {
                    const convs = await this.getConversations();
                    onConversationsUpdate(convs);
                }

                if (this.activePartnerId && onActiveChatUpdate) {
                    const history = await this.getMessageHistory(this.activePartnerId);
                    onActiveChatUpdate(history);
                }
            } catch (err) {
                // Ignore transient polling drops
            }
        }, intervalMs);
    }

    static stopPolling() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
            this.pollingInterval = null;
        }
    }

    static setActivePartner(partnerId) {
        this.activePartnerId = partnerId;
    }

    static getActivePartner() {
        return this.activePartnerId;
    }

    /**
     * Renders a conversation list item element safely.
     */
    static createConversationElement(conv, isActive, onSelect) {
        const item = document.createElement('div');
        item.className = `conv-item ${isActive ? 'active' : ''}`;
        item.dataset.userId = conv.user_id;
        item.onclick = () => onSelect && onSelect(conv);

        // Avatar
        const avatarBox = document.createElement('div');
        avatarBox.className = 'conv-avatar-box';

        if (conv.photo_path) {
            const img = document.createElement('img');
            img.className = 'conv-avatar-img';
            img.alt = conv.display_name;
            const avatarUrl = resolveImageUrl(conv.photo_path);
            img.setAttribute('data-src', avatarUrl);
            img.src = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="44" height="44"%3E%3Crect width="100%25" height="100%25" fill="%23e2e8f0"/%3E%3C/svg%3E';
            avatarBox.appendChild(img);
        } else {
            const placeholder = document.createElement('div');
            placeholder.className = 'avatar-placeholder';
            placeholder.textContent = (conv.display_name || 'U').charAt(0).toUpperCase();
            avatarBox.appendChild(placeholder);
        }

        // Details
        const detailsBox = document.createElement('div');
        detailsBox.className = 'conv-details';

        const topRow = document.createElement('div');
        topRow.className = 'conv-top-row';

        const nameEl = document.createElement('h4');
        nameEl.className = 'conv-name';
        nameEl.textContent = conv.display_name;

        const timeEl = document.createElement('span');
        timeEl.className = 'conv-time';
        timeEl.textContent = conv.last_message ? this.formatTime(conv.last_message.created_at) : '';

        topRow.appendChild(nameEl);
        topRow.appendChild(timeEl);

        const bottomRow = document.createElement('div');
        bottomRow.className = 'conv-bottom-row';

        const snippetEl = document.createElement('p');
        snippetEl.className = 'conv-snippet';
        if (conv.last_message) {
            const prefix = conv.last_message.is_outgoing ? 'Anda: ' : '';
            const content = conv.last_message.body || (conv.last_message.image_path ? '📷 [Foto]' : '');
            snippetEl.textContent = prefix + content;
        } else {
            snippetEl.textContent = 'Mulai percakapan...';
        }

        bottomRow.appendChild(snippetEl);

        if (conv.unread_count > 0) {
            const badge = document.createElement('span');
            badge.className = 'unread-badge';
            badge.textContent = conv.unread_count > 99 ? '99+' : conv.unread_count;
            bottomRow.appendChild(badge);
        }

        detailsBox.appendChild(topRow);
        detailsBox.appendChild(bottomRow);

        item.appendChild(avatarBox);
        item.appendChild(detailsBox);

        return item;
    }

    /**
     * Renders a message bubble DOM node safely with textContent (FR-16) and lazy-loaded image (FR-24).
     */
    static createMessageBubble(msg, currentUserId) {
        const isOutgoing = msg.sender_id === currentUserId;
        const bubbleWrap = document.createElement('div');
        bubbleWrap.className = `message-wrap ${isOutgoing ? 'outgoing' : 'incoming'}`;
        bubbleWrap.dataset.msgId = msg.id;

        const bubble = document.createElement('div');
        bubble.className = `message-bubble ${isOutgoing ? 'bubble-out' : 'bubble-in'}`;

        // Attached image if any
        if (msg.image_path) {
            const imgBox = document.createElement('div');
            imgBox.className = 'message-image-box';

            const fullImageUrl = resolveImageUrl(msg.image_path);
            const img = document.createElement('img');
            img.className = 'message-img';
            img.alt = 'Lampiran Gambar';
            img.setAttribute('data-src', fullImageUrl);
            img.src = fullImageUrl;
            img.onclick = () => window.open(fullImageUrl, '_blank');

            imgBox.appendChild(img);
            bubble.appendChild(imgBox);
        }

        // Message text
        if (msg.body) {
            const textEl = document.createElement('p');
            textEl.className = 'message-text';
            textEl.textContent = msg.body;
            bubble.appendChild(textEl);
        }

        // Meta info (time and read status)
        const metaBox = document.createElement('div');
        metaBox.className = 'message-meta';

        const timeSpan = document.createElement('span');
        timeSpan.className = 'message-time';
        timeSpan.textContent = this.formatTime(msg.created_at);
        metaBox.appendChild(timeSpan);

        if (isOutgoing) {
            const checkSpan = document.createElement('span');
            checkSpan.className = `message-status ${msg.read_at ? 'read' : 'sent'}`;
            checkSpan.textContent = msg.read_at ? '✓✓' : '✓';
            metaBox.appendChild(checkSpan);
        }

        bubble.appendChild(metaBox);
        bubbleWrap.appendChild(bubble);

        return bubbleWrap;
    }

    static formatTime(dateStr) {
        if (!dateStr) return '';
        try {
            const d = new Date(dateStr.replace(' ', 'T'));
            if (isNaN(d.getTime())) return '';
            const now = new Date();
            const isToday = d.toDateString() === now.toDateString();

            const hours = String(d.getHours()).padStart(2, '0');
            const mins = String(d.getMinutes()).padStart(2, '0');
            const timeStr = `${hours}:${mins}`;

            if (isToday) return timeStr;

            const day = String(d.getDate()).padStart(2, '0');
            const month = String(d.getMonth() + 1).padStart(2, '0');
            return `${day}/${month} ${timeStr}`;
        } catch {
            return '';
        }
    }
}
