<div id="unpam-chatbot-root" class="fixed bottom-4 right-4 z-40 flex flex-col items-end">
    <!-- Panel Chat -->
    <section id="chatbot-panel"
             aria-label="Chatbot Layanan Mahasiswa UNPAM"
             class="hidden w-[calc(100vw-2rem)] sm:w-96 max-h-[82vh] bg-white border-2 border-slate-800 rounded-lg shadow-xl flex-col overflow-hidden mb-3">
        <!-- Header Widget -->
        <div class="bg-unpam-navy text-white px-4 py-3 flex items-center justify-between gap-2">
            <div>
                <h2 class="font-bold text-sm text-white leading-snug">Layanan Chat Mahasiswa</h2>
                <p id="chatbot-status-badge" class="text-xs text-slate-200">Status: Memuat sesi...</p>
            </div>
            <button type="button"
                    id="chatbot-close-btn"
                    aria-label="Tutup jendela chat"
                    class="min-h-[44px] min-w-[44px] inline-flex items-center justify-center rounded text-xs font-semibold bg-slate-800 text-white hover:bg-slate-700 px-2.5">
                Tutup
            </button>
        </div>

        <!-- Identitas Opsional Pengunjung saat meminta Admin -->
        <div id="chatbot-identity-bar" class="bg-slate-100 border-b border-slate-300 px-3 py-2 text-xs">
            <label for="chatbot-visitor-name" class="block font-semibold text-slate-800 mb-1">
                Nama / NIM Anda (Opsional untuk identifikasi Admin):
            </label>
            <input type="text"
                   id="chatbot-visitor-name"
                   placeholder="Contoh: Budi Santoso - 2110114001"
                   class="w-full min-h-[38px] px-2.5 py-1.5 rounded border border-slate-300 bg-white text-slate-900 text-xs">
        </div>

        <!-- Daftar Pesan (Empty, Loading, Error, & Active States) -->
        <div id="chatbot-messages"
             role="log"
             aria-live="polite"
             class="flex-1 overflow-y-auto p-3.5 space-y-3 bg-slate-50 min-h-[240px] max-h-[340px] text-sm">
            <div id="chatbot-loading-state" class="text-slate-700 bg-white border border-slate-200 rounded p-3 text-xs">
                Memuat layanan menu Chatbot UNPAM...
            </div>
        </div>

        <!-- Area Error State -->
        <div id="chatbot-error-state" class="hidden bg-red-50 border-t border-red-300 px-3 py-2 text-xs text-red-900">
            Gagal terhubung ke server chat. Silakan coba menekan tombol menu kembali.
        </div>

        <!-- Tombol Pilihan Menu Cepat -->
        <div id="chatbot-options-container" class="bg-white border-t border-slate-200 px-3 py-2.5">
            <p class="text-xs font-semibold text-slate-700 mb-2">Pilih Menu Layanan:</p>
            <div id="chatbot-options-list" class="flex flex-wrap gap-1.5"></div>
        </div>

        <!-- Form Kirim Pesan / Kode Menu -->
        <form id="chatbot-form" class="bg-slate-100 border-t border-slate-300 p-2.5 flex items-center gap-2">
            <label for="chatbot-input" class="sr-only">Ketik angka menu atau pesan untuk Admin</label>
            <input type="text"
                   id="chatbot-input"
                   name="message"
                   required
                   placeholder="Ketik 1, 2, 3 atau pesan Anda..."
                   class="flex-1 min-h-[44px] px-3 py-2 rounded border border-slate-400 bg-white text-slate-900 text-sm">
            <button type="submit"
                    id="chatbot-submit-btn"
                    class="min-h-[44px] px-4 py-2 rounded bg-unpam-navy text-white font-semibold text-sm hover:bg-unpam-navyDark shrink-0">
                Kirim
            </button>
        </form>
    </section>

    <!-- Tombol Pembuka Widget -->
    <button type="button"
            id="chatbot-trigger"
            aria-expanded="false"
            aria-controls="chatbot-panel"
            class="min-h-[48px] px-4 py-2.5 rounded-lg bg-unpam-navy text-white font-bold text-sm shadow-lg border-2 border-unpam-gold hover:bg-unpam-navyDark flex items-center gap-2.5">
        <span class="inline-block w-2.5 h-2.5 rounded-full bg-unpam-gold" aria-hidden="true"></span>
        <span>Chat Layanan Mahasiswa</span>
    </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const panel = document.getElementById('chatbot-panel');
    const triggerBtn = document.getElementById('chatbot-trigger');
    const closeBtn = document.getElementById('chatbot-close-btn');
    const messagesBox = document.getElementById('chatbot-messages');
    const optionsList = document.getElementById('chatbot-options-list');
    const statusBadge = document.getElementById('chatbot-status-badge');
    const errorBox = document.getElementById('chatbot-error-state');
    const form = document.getElementById('chatbot-form');
    const input = document.getElementById('chatbot-input');
    const submitBtn = document.getElementById('chatbot-submit-btn');
    const visitorNameInput = document.getElementById('chatbot-visitor-name');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    let isOpen = false;
    let pollTimer = null;
    let lastRenderedCount = 0;
    let lastStatus = '';

    function formatSafeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML
            .replace(/\*(.*?)\*/g, '<strong>$1</strong>')
            .replace(/_(.*?)_/g, '<em>$1</em>')
            .replace(/\n/g, '<br>');
    }

    function renderConversation(data) {
        errorBox.classList.add('hidden');
        statusBadge.textContent = 'Status: ' + (data.status_label || data.status);

        if (data.user_name && !visitorNameInput.value && !data.user_name.startsWith('Mahasiswa Web #')) {
            visitorNameInput.value = data.user_name;
        }

        const msgs = Array.isArray(data.messages) ? data.messages : [];
        if (msgs.length !== lastRenderedCount || data.status !== lastStatus) {
            lastRenderedCount = msgs.length;
            lastStatus = data.status;
            messagesBox.innerHTML = '';

            if (msgs.length === 0) {
                messagesBox.innerHTML = '<div class="text-xs text-slate-700 bg-white border border-slate-200 rounded p-3">Belum ada percakapan. Pilih tombol menu di bawah untuk memulai.</div>';
            } else {
                msgs.forEach(function (item) {
                    const wrapper = document.createElement('div');
                    const isUser = item.sender_type === 'user';
                    const isAdmin = item.sender_type === 'admin';
                    const isSystem = item.sender_type === 'system';

                    if (isSystem) {
                        wrapper.className = 'bg-amber-50 border border-amber-300 text-slate-900 rounded p-2.5 text-xs';
                    } else if (isUser) {
                        wrapper.className = 'ml-6 bg-unpam-navy text-white rounded-lg p-3 text-xs sm:text-sm';
                    } else if (isAdmin) {
                        wrapper.className = 'mr-6 bg-emerald-50 border border-emerald-300 text-slate-900 rounded-lg p-3 text-xs sm:text-sm';
                    } else {
                        wrapper.className = 'mr-4 bg-white border border-slate-300 text-slate-900 rounded-lg p-3 text-xs sm:text-sm';
                    }

                    const header = document.createElement('div');
                    header.className = 'flex items-center justify-between gap-2 mb-1 text-[11px] ' + (isUser ? 'text-slate-200' : 'text-slate-600');
                    header.innerHTML = '<span class="font-bold">' + (item.sender_name || item.sender_type) + '</span><span>' + (item.time || '') + '</span>';

                    const body = document.createElement('div');
                    body.className = 'leading-relaxed break-words';
                    body.innerHTML = formatSafeHtml(item.message);

                    wrapper.appendChild(header);
                    wrapper.appendChild(body);
                    messagesBox.appendChild(wrapper);
                });
                messagesBox.scrollTop = messagesBox.scrollHeight;
            }
        }

        optionsList.innerHTML = '';
        const options = Array.isArray(data.options) ? data.options : [];
        if (options.length === 0) {
            const note = document.createElement('p');
            note.className = 'text-xs text-slate-600';
            note.textContent = 'Admin sedang aktif melayani percakapan ini. Silakan ketik pesan Anda di bawah.';
            optionsList.appendChild(note);
        } else {
            options.forEach(function (opt) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'min-h-[40px] px-3 py-1.5 rounded border border-slate-300 bg-slate-100 hover:bg-unpam-gold hover:text-slate-950 hover:border-amber-500 text-slate-900 font-semibold text-xs transition-colors';
                btn.textContent = opt.label;
                btn.addEventListener('click', function () {
                    sendChatMessage(opt.code);
                });
                optionsList.appendChild(btn);
            });
        }
    }

    async function fetchSession() {
        try {
            const response = await fetch('{{ route("webchat.session") }}', {
                headers: { 'Accept': 'application/json' }
            });
            if (!response.ok) throw new Error('Gagal memuat sesi');
            const data = await response.json();
            renderConversation(data);
        } catch (err) {
            errorBox.classList.remove('hidden');
        }
    }

    async function sendChatMessage(messageText) {
        if (!messageText || !messageText.trim()) return;
        submitBtn.disabled = true;
        errorBox.classList.add('hidden');

        try {
            const response = await fetch('{{ route("webchat.message") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    message: messageText.trim(),
                    visitor_name: visitorNameInput.value.trim()
                })
            });

            if (!response.ok) throw new Error('Gagal mengirim pesan');
            const data = await response.json();
            input.value = '';
            renderConversation(data);
        } catch (err) {
            errorBox.classList.remove('hidden');
        } finally {
            submitBtn.disabled = false;
            input.focus();
        }
    }

    function openChat() {
        isOpen = true;
        panel.classList.remove('hidden');
        panel.classList.add('flex');
        triggerBtn.setAttribute('aria-expanded', 'true');
        fetchSession();
        if (!pollTimer) {
            pollTimer = setInterval(fetchSession, 4000);
        }
    }

    function closeChat() {
        isOpen = false;
        panel.classList.add('hidden');
        panel.classList.remove('flex');
        triggerBtn.setAttribute('aria-expanded', 'false');
        if (pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
    }

    window.openUnpamChat = openChat;

    triggerBtn.addEventListener('click', function () {
        if (isOpen) {
            closeChat();
        } else {
            openChat();
        }
    });

    closeBtn.addEventListener('click', closeChat);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && isOpen) {
            closeChat();
            triggerBtn.focus();
        }
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        sendChatMessage(input.value);
    });
});
</script>

