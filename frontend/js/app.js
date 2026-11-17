/**
 * Main Application Orchestrator (Pure Vanilla JS ES Modules)
 */

import { ApiClient } from './api.js';
import { AuthService } from './auth.js';
import { ContactsService } from './contacts.js';
import { ChatService } from './chat.js';
import { FcmService } from './fcm.js';
import { observeLazyImages } from './lazy.js';

class App {
    constructor() {
        this.currentUser = null;
        this.activePartner = null;
        this.cachedConversations = [];
        this.cachedContacts = [];

        this.initDom();
        this.bindEvents();
        this.checkAuth();
        this.initPwa();
    }

    initDom() {
        // Screens
        this.authScreen = document.getElementById('auth-screen');
        this.mainScreen = document.getElementById('main-screen');

        // Auth Tabs & Forms
        this.tabLoginBtn = document.getElementById('tab-login-btn');
        this.tabRegisterBtn = document.getElementById('tab-register-btn');
        this.loginForm = document.getElementById('login-form');
        this.registerForm = document.getElementById('register-form');
        this.authAlert = document.getElementById('auth-alert');

        // App Sidebar Tabs
        this.tabChatsBtn = document.getElementById('nav-chats-btn');
        this.tabContactsBtn = document.getElementById('nav-contacts-btn');
        this.tabProfileBtn = document.getElementById('nav-profile-btn');
        this.panelChats = document.getElementById('panel-chats');
        this.panelContacts = document.getElementById('panel-contacts');
        this.panelProfile = document.getElementById('panel-profile');

        // Containers
        this.conversationsList = document.getElementById('conversations-list');
        this.contactsList = document.getElementById('contacts-list');
        this.messagesContainer = document.getElementById('messages-container');

        // Chat Header & Input
        this.chatArea = document.getElementById('chat-area');
        this.emptyChatPlaceholder = document.getElementById('empty-chat-placeholder');
        this.activeChatView = document.getElementById('active-chat-view');
        this.chatPartnerName = document.getElementById('chat-partner-name');
        this.chatPartnerPhone = document.getElementById('chat-partner-phone');
        this.chatPartnerAvatar = document.getElementById('chat-partner-avatar');
        this.chatForm = document.getElementById('chat-form');
        this.messageInput = document.getElementById('message-input');
        this.imageInput = document.getElementById('image-input');
        this.imagePreviewBox = document.getElementById('image-preview-box');
        this.imagePreviewImg = document.getElementById('image-preview-img');
        this.removeImageBtn = document.getElementById('remove-image-btn');
        this.chatBackBtn = document.getElementById('chat-back-btn');

        // Modals
        this.contactModal = document.getElementById('contact-modal');
        this.contactModalTitle = document.getElementById('contact-modal-title');
        this.contactForm = document.getElementById('contact-form');
        this.contactModalId = document.getElementById('contact-modal-id');
        this.contactModalPhone = document.getElementById('contact-modal-phone');
        this.contactModalAlias = document.getElementById('contact-modal-alias');
        this.contactModalAlert = document.getElementById('contact-modal-alert');
        this.closeContactModalBtn = document.getElementById('close-contact-modal-btn');
        this.openAddContactBtn = document.getElementById('open-add-contact-btn');

        // Profile Panel Items
        this.profileAvatar = document.getElementById('profile-avatar');
        this.profileName = document.getElementById('profile-name');
        this.profilePhone = document.getElementById('profile-phone');
        this.profileId = document.getElementById('profile-id');
        this.logoutBtn = document.getElementById('logout-btn');
        this.deleteAccountBtn = document.getElementById('delete-account-btn');
        this.enableNotificationBtn = document.getElementById('enable-notification-btn');

        // Toast Container
        this.toastBox = document.getElementById('toast-box');
    }

    bindEvents() {
        // Global Auth Expired listener
        window.addEventListener('auth:expired', () => {
            this.showToast('Sesi telah berakhir. Silakan login kembali.', 'error');
            this.showAuthScreen('login');
        });

        // Auth Tabs
        this.tabLoginBtn.onclick = () => this.switchAuthTab('login');
        this.tabRegisterBtn.onclick = () => this.switchAuthTab('register');

        // Login Form Submit
        this.loginForm.onsubmit = async (e) => {
            e.preventDefault();
            await this.handleLogin();
        };

        // Register Form Submit
        this.registerForm.onsubmit = async (e) => {
            e.preventDefault();
            await this.handleRegister();
        };

        // Sidebar Navigation
        this.tabChatsBtn.onclick = () => this.switchSidebarTab('chats');
        this.tabContactsBtn.onclick = () => this.switchSidebarTab('contacts');
        this.tabProfileBtn.onclick = () => this.switchSidebarTab('profile');

        // Message Form Submit
        this.chatForm.onsubmit = async (e) => {
            e.preventDefault();
            await this.handleSendMessage();
        };

        // Image Attachment Input
        this.imageInput.onchange = (e) => this.handleImageSelect(e);
        this.removeImageBtn.onclick = () => this.clearSelectedImage();

        // Mobile Chat Back Button
        this.chatBackBtn.onclick = () => {
            this.chatArea.classList.remove('mobile-active');
            ChatService.setActivePartner(null);
            this.activePartner = null;
        };

        // Contacts Modal
        this.openAddContactBtn.onclick = () => this.openContactModal();
        this.closeContactModalBtn.onclick = () => this.closeContactModal();
        this.contactForm.onsubmit = async (e) => {
            e.preventDefault();
            await this.handleSaveContact();
        };

        // Profile Actions
        this.logoutBtn.onclick = () => this.handleLogout();
        this.deleteAccountBtn.onclick = () => this.handleDeleteAccount();
        if (this.enableNotificationBtn) {
            this.enableNotificationBtn.onclick = async () => {
                const ok = await FcmService.requestPermissionAndRegister();
                if (ok) {
                    this.showToast('Notifikasi remote berhasil diaktifkan!', 'success');
                } else {
                    this.showToast('Izin notifikasi ditolak atau tidak didukung.', 'error');
                }
            };
        }
    }

    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        this.toastBox.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('fade-out');
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }

    async checkAuth() {
        if (AuthService.isAuthenticated()) {
            try {
                const res = await AuthService.getProfile();
                this.currentUser = res.data;
                this.showMainScreen();
            } catch (err) {
                this.showAuthScreen('login');
            }
        } else {
            this.showAuthScreen('login');
        }
    }

    showAuthScreen(tab = 'login') {
        ChatService.stopPolling();
        this.authScreen.classList.remove('hidden');
        this.mainScreen.classList.add('hidden');
        this.switchAuthTab(tab);
    }

    showMainScreen() {
        this.authScreen.classList.add('hidden');
        this.mainScreen.classList.remove('hidden');
        this.renderProfile();
        this.loadConversations();
        this.loadContacts();

        // Start periodic polling every 3 seconds (FR-11)
        ChatService.startPolling(
            (convs) => this.updateConversationsList(convs),
            (history) => this.updateActiveChatMessages(history)
        );
    }

    switchAuthTab(tab) {
        this.authAlert.className = 'alert hidden';
        if (tab === 'login') {
            this.tabLoginBtn.classList.add('active');
            this.tabRegisterBtn.classList.remove('active');
            this.loginForm.classList.remove('hidden');
            this.registerForm.classList.add('hidden');
        } else {
            this.tabRegisterBtn.classList.add('active');
            this.tabLoginBtn.classList.remove('active');
            this.registerForm.classList.remove('hidden');
            this.loginForm.classList.add('hidden');
        }
    }

    switchSidebarTab(tab) {
        this.tabChatsBtn.classList.toggle('active', tab === 'chats');
        this.tabContactsBtn.classList.toggle('active', tab === 'contacts');
        this.tabProfileBtn.classList.toggle('active', tab === 'profile');

        this.panelChats.classList.toggle('hidden', tab !== 'chats');
        this.panelContacts.classList.toggle('hidden', tab !== 'contacts');
        this.panelProfile.classList.toggle('hidden', tab !== 'profile');

        if (tab === 'contacts') {
            this.loadContacts();
        } else if (tab === 'chats') {
            this.loadConversations();
        }
    }

    // --- Authentication Handlers ---
    async handleLogin() {
        const phone = document.getElementById('login-phone').value;
        const password = document.getElementById('login-password').value;

        const valPhone = AuthService.validatePhone(phone);
        if (!valPhone.valid) {
            this.showAuthError(valPhone.message);
            return;
        }

        try {
            const res = await AuthService.login(phone, password);
            this.currentUser = res.data.user;
            this.showToast('Login berhasil!', 'success');
            this.loginForm.reset();
            this.showMainScreen();
        } catch (err) {
            this.showAuthError(err.message || 'Gagal login. Periksa nomor HP dan password.');
        }
    }

    async handleRegister() {
        const name = document.getElementById('reg-name').value;
        const phone = document.getElementById('reg-phone').value;
        const password = document.getElementById('reg-password').value;
        const photoFile = document.getElementById('reg-photo').files[0];

        const valName = AuthService.validateName(name);
        if (!valName.valid) {
            this.showAuthError(valName.message);
            return;
        }

        const valPhone = AuthService.validatePhone(phone);
        if (!valPhone.valid) {
            this.showAuthError(valPhone.message);
            return;
        }

        const valPwd = AuthService.validatePassword(password);
        if (!valPwd.valid) {
            this.showAuthError(valPwd.message);
            return;
        }

        const formData = new FormData();
        formData.append('name', name);
        formData.append('phone', phone);
        formData.append('password', password);
        if (photoFile) {
            formData.append('photo', photoFile);
        }

        try {
            await AuthService.register(formData);
            this.showToast('Registrasi berhasil! Silakan login.', 'success');
            this.registerForm.reset();
            this.switchAuthTab('login');
        } catch (err) {
            this.showAuthError(err.message || 'Gagal registrasi.');
        }
    }

    showAuthError(msg) {
        this.authAlert.textContent = msg;
        this.authAlert.className = 'alert alert-error';
    }

    handleLogout() {
        if (confirm('Apakah Anda yakin ingin logout?')) {
            AuthService.logout();
            this.currentUser = null;
            this.activePartner = null;
            this.showAuthScreen('login');
        }
    }

    async handleDeleteAccount() {
        if (confirm('PERINGATAN: Apakah Anda yakin ingin menghapus akun ini secara permanen? Semua pesan dan kontak akan dihapus.')) {
            try {
                await AuthService.deleteAccount();
                this.showToast('Akun berhasil dihapus.', 'info');
                this.showAuthScreen('login');
            } catch (err) {
                this.showToast(err.message || 'Gagal menghapus akun', 'error');
            }
        }
    }

    // --- Profile Panel ---
    renderProfile() {
        if (!this.currentUser) return;
        this.profileName.textContent = this.currentUser.name;
        this.profilePhone.textContent = this.currentUser.phone;
        this.profileId.textContent = this.currentUser.id;

        if (this.currentUser.photo_path) {
            this.profileAvatar.src = this.currentUser.photo_path;
        } else {
            this.profileAvatar.src = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="60" height="60"%3E%3Ccircle cx="30" cy="30" r="30" fill="%234f46e5"/%3E%3Ctext x="50%25" y="55%25" text-anchor="middle" fill="white" font-size="24"%3E' + (this.currentUser.name || 'U').charAt(0).toUpperCase() + '%3C/text%3E%3C/svg%3E';
        }
    }

    // --- Conversations List & Eager Loading (FR-10, FR-23) ---
    async loadConversations() {
        try {
            const convs = await ChatService.getConversations();
            this.updateConversationsList(convs);
        } catch (err) {
            console.error('Failed to load conversations:', err);
        }
    }

    updateConversationsList(convs) {
        this.cachedConversations = convs;
        this.conversationsList.innerHTML = '';

        if (convs.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'empty-state';
            empty.textContent = 'Belum ada percakapan. Mulai kirim pesan dari daftar kontak!';
            this.conversationsList.appendChild(empty);
            return;
        }

        const activeId = ChatService.getActivePartner();
        convs.forEach(c => {
            const el = ChatService.createConversationElement(
                c,
                c.user_id === activeId,
                (selected) => this.openChatWithUser(selected.user_id, selected.display_name, selected.phone, selected.photo_path)
            );
            this.conversationsList.appendChild(el);
        });

        observeLazyImages(this.conversationsList);
    }

    // --- Active Chat View (FR-08, FR-09, FR-11) ---
    async openChatWithUser(userId, displayName, phone, photoPath) {
        this.activePartner = { id: userId, name: displayName, phone, photo_path: photoPath };
        ChatService.setActivePartner(userId);

        // Mobile responsive switch
        this.chatArea.classList.add('mobile-active');
        this.emptyChatPlaceholder.classList.add('hidden');
        this.activeChatView.classList.remove('hidden');

        // Header info
        this.chatPartnerName.textContent = displayName;
        this.chatPartnerPhone.textContent = phone;
        if (photoPath) {
            this.chatPartnerAvatar.src = photoPath;
        } else {
            this.chatPartnerAvatar.src = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="40" height="40"%3E%3Ccircle cx="20" cy="20" r="20" fill="%236366f1"/%3E%3Ctext x="50%25" y="55%25" text-anchor="middle" fill="white" font-size="16"%3E' + (displayName || 'U').charAt(0).toUpperCase() + '%3C/text%3E%3C/svg%3E';
        }

        // Highlight active conversation in sidebar
        const convItems = this.conversationsList.querySelectorAll('.conv-item');
        convItems.forEach(el => el.classList.toggle('active', el.dataset.userId === userId));

        // Load messages history
        try {
            const history = await ChatService.getMessageHistory(userId);
            this.renderMessages(history.messages || []);
        } catch (err) {
            this.showToast('Gagal memuat riwayat pesan: ' + err.message, 'error');
        }
    }

    renderMessages(messages) {
        this.messagesContainer.innerHTML = '';
        if (messages.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'empty-state';
            empty.textContent = 'Belum ada pesan. Sapa lawan bicara Anda sekarang!';
            this.messagesContainer.appendChild(empty);
            return;
        }

        messages.forEach(m => {
            const bubble = ChatService.createMessageBubble(m, this.currentUser.id);
            this.messagesContainer.appendChild(bubble);
        });

        observeLazyImages(this.messagesContainer);
        this.scrollChatToBottom();
    }

    updateActiveChatMessages(history) {
        if (!this.activePartner || history.partner?.id !== this.activePartner.id) return;
        const currentCount = this.messagesContainer.querySelectorAll('.message-wrap').length;
        const newCount = (history.messages || []).length;

        // If new messages arrived, re-render and scroll
        if (newCount !== currentCount) {
            this.renderMessages(history.messages || []);
        }
    }

    scrollChatToBottom() {
        this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
    }

    handleImageSelect(e) {
        const file = e.target.files[0];
        if (!file) return;

        if (file.size > 2 * 1024 * 1024) {
            this.showToast('Ukuran gambar tidak boleh melebihi 2MB', 'error');
            this.clearSelectedImage();
            return;
        }

        const reader = new FileReader();
        reader.onload = (event) => {
            this.imagePreviewImg.src = event.target.result;
            this.imagePreviewBox.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }

    clearSelectedImage() {
        this.imageInput.value = '';
        this.imagePreviewImg.src = '';
        this.imagePreviewBox.classList.add('hidden');
    }

    async handleSendMessage() {
        if (!this.activePartner) return;

        const body = this.messageInput.value.trim();
        const imageFile = this.imageInput.files[0];

        if (!body && !imageFile) {
            return;
        }

        try {
            await ChatService.sendMessage(this.activePartner.id, body, imageFile);
            this.messageInput.value = '';
            this.clearSelectedImage();

            // Refresh chat immediately
            const history = await ChatService.getMessageHistory(this.activePartner.id);
            this.renderMessages(history.messages || []);
            this.loadConversations();
        } catch (err) {
            this.showToast(err.message || 'Gagal mengirim pesan', 'error');
        }
    }

    // --- Contacts Panel & Modal ---
    async loadContacts() {
        try {
            const contacts = await ContactsService.getContacts();
            this.cachedContacts = contacts;
            this.renderContactsList(contacts);
        } catch (err) {
            console.error('Failed to load contacts:', err);
        }
    }

    renderContactsList(contacts) {
        this.contactsList.innerHTML = '';
        if (contacts.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'empty-state';
            empty.textContent = 'Belum ada kontak. Tambahkan nomor HP teman Anda!';
            this.contactsList.appendChild(empty);
            return;
        }

        contacts.forEach(c => {
            const el = ContactsService.createContactElement(
                c,
                (selected) => this.openChatWithUser(selected.contact_user_id, selected.alias_name, selected.user_phone, selected.photo_path),
                (editTarget) => this.openContactModal(editTarget),
                (delTarget) => this.handleDeleteContact(delTarget)
            );
            this.contactsList.appendChild(el);
        });

        observeLazyImages(this.contactsList);
    }

    openContactModal(contact = null) {
        this.contactModalAlert.className = 'alert hidden';
        if (contact) {
            this.contactModalTitle.textContent = 'Ubah Kontak';
            this.contactModalId.value = contact.id;
            this.contactModalPhone.value = contact.user_phone;
            this.contactModalAlias.value = contact.alias_name;
        } else {
            this.contactModalTitle.textContent = 'Tambah Kontak';
            this.contactModalId.value = '';
            this.contactForm.reset();
        }
        this.contactModal.classList.remove('hidden');
    }

    closeContactModal() {
        this.contactModal.classList.add('hidden');
    }

    async handleSaveContact() {
        const id = this.contactModalId.value;
        const phone = this.contactModalPhone.value.trim();
        const alias = this.contactModalAlias.value.trim();

        const valPhone = AuthService.validatePhone(phone);
        if (!valPhone.valid) {
            this.showContactModalError(valPhone.message);
            return;
        }

        if (alias) {
            const valAlias = AuthService.validateName(alias);
            if (!valAlias.valid) {
                this.showContactModalError(valAlias.message);
                return;
            }
        }

        try {
            if (id) {
                await ContactsService.updateContact(id, alias, phone);
                this.showToast('Kontak berhasil diperbarui', 'success');
            } else {
                await ContactsService.addContact(phone, alias);
                this.showToast('Kontak berhasil ditambahkan', 'success');
            }
            this.closeContactModal();
            this.loadContacts();
            this.loadConversations();
        } catch (err) {
            this.showContactModalError(err.message || 'Gagal menyimpan kontak');
        }
    }

    showContactModalError(msg) {
        this.contactModalAlert.textContent = msg;
        this.contactModalAlert.className = 'alert alert-error';
    }

    async handleDeleteContact(contact) {
        if (confirm(`Hapus kontak "${contact.alias_name}"?`)) {
            try {
                await ContactsService.deleteContact(contact.id);
                this.showToast('Kontak berhasil dihapus', 'info');
                this.loadContacts();
            } catch (err) {
                this.showToast(err.message || 'Gagal menghapus kontak', 'error');
            }
        }
    }

    // --- Progressive Web App (PWA) Initialization (FR-28) ---
    initPwa() {
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => {
                        console.log('PWA ServiceWorker registered successfully:', reg.scope);
                    })
                    .catch(err => {
                        console.warn('PWA ServiceWorker registration failed:', err);
                    });
            });
        }
    }
}

// Instantiate on DOMContentLoaded
document.addEventListener('DOMContentLoaded', () => {
    window.app = new App();
});
