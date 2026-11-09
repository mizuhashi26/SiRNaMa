<style>
    @keyframes unpamDotBlinkAnim {
        0%, 100% {
            opacity: 1;
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(250, 204, 21, 0.95);
        }
        50% {
            opacity: 0.15;
            transform: scale(0.75);
            box-shadow: 0 0 0 8px rgba(250, 204, 21, 0);
        }
    }
    .unpam-blinking-dot {
        display: inline-block !important;
        width: 12px !important;
        height: 12px !important;
        border-radius: 9999px !important;
        background-color: #FACC15 !important;
        border: 2px solid #FFFFFF !important;
        animation: unpamDotBlinkAnim 1.1s ease-in-out infinite !important;
        -webkit-animation: unpamDotBlinkAnim 1.1s ease-in-out infinite !important;
    }
    #chatbot-panel {
        transform-origin: bottom right;
        transition: opacity 0.22s cubic-bezier(0.16, 1, 0.3, 1), transform 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        will-change: transform, opacity;
    }
    .panel-hidden {
        display: none !important;
    }
    .panel-state-closed-top {
        opacity: 0 !important;
        transform: translateY(-20px) scale(0.94) !important;
        pointer-events: none !important;
    }
    .panel-state-closed-bottom {
        opacity: 0 !important;
        transform: translateY(20px) scale(0.94) !important;
        pointer-events: none !important;
    }
    .panel-state-open {
        opacity: 1 !important;
        transform: translateY(0) scale(1) !important;
        pointer-events: auto !important;
    }
</style>

<div id="unpam-chatbot-root" class="fixed bottom-5 right-5 z-50 flex flex-col items-end">
    <!-- Panel Chat Pop-up -->
    <section id="chatbot-panel"
             aria-label="Layanan Chat"
             class="panel-hidden panel-state-closed-bottom w-[calc(100vw-2rem)] sm:w-96 max-h-[82vh] bg-white border-2 border-unpam-navy rounded-2xl shadow-2xl flex-col overflow-hidden mb-3">
        <!-- Header Widget: Judul Singkat & Ikon Aksi Bold -->
        <div id="chatbot-header-handle" class="bg-unpam-navy text-white px-4 py-3 flex items-center justify-between gap-2 border-b-2 border-unpam-gold cursor-grab active:cursor-grabbing select-none">
            <div class="flex items-center gap-2.5">
                <!-- Bulat Berkedip Nyata -->
                <span class="unpam-blinking-dot" aria-hidden="true"></span>
                <div>
                    <!-- Judul Disederhanakan -->
                    <h2 class="font-bold text-sm text-white leading-snug">Layanan Chat</h2>
                    <p id="chatbot-status-badge" class="text-[11px] text-amber-200">Status: Memuat sesi...</p>
                </div>
            </div>

            <!-- Tombol Aksi di Header Chat (Ikon Bergaya Bold) -->
            <div class="flex items-center gap-1.5">
                <!-- Tombol Posisi Semula: Ikon Reset Bold Tanpa Teks -->
                <button type="button"
                        id="chatbot-reset-pos-panel-btn"
                        title="Kembalikan letak widget ke posisi semula (kanan-bawah)"
                        aria-label="Kembalikan posisi semula"
                        class="min-h-[36px] min-w-[36px] inline-flex items-center justify-center rounded-lg bg-slate-800 text-amber-300 hover:bg-slate-700 hover:text-white border border-amber-400/50 transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                        <path d="M3 3v5h5"/>
                    </svg>
                </button>

                <!-- Tombol Minimize / Bawah: Ikon Chevron Down Bold -->
                <button type="button"
                        id="chatbot-minimize-btn"
                        title="Kecilkan chat"
                        aria-label="Kecilkan chat"
                        class="min-h-[36px] min-w-[36px] inline-flex items-center justify-center rounded-lg bg-slate-800 text-white hover:bg-slate-700 border border-slate-600 transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m6 9 6 6 6-6"/>
                    </svg>
                </button>

                <!-- Tombol Tutup: Ikon Silang (✕) Bold -->
                <button type="button"
                        id="chatbot-close-btn"
                        title="Tutup chat"
                        aria-label="Tutup jendela chat"
                        class="min-h-[36px] min-w-[36px] inline-flex items-center justify-center rounded-lg bg-slate-800 text-white hover:bg-red-700 border border-slate-600 transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M18 6 6 18"/>
                        <path d="m6 6 12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Identitas Opsional Pengunjung saat meminta Admin -->
        <div id="chatbot-identity-bar" class="bg-slate-100 border-b border-slate-300 px-3.5 py-2 text-xs">
            <label for="chatbot-visitor-name" class="block font-semibold text-slate-800 mb-1">
                Nama / NIM (Opsional untuk Admin):
            </label>
            <input type="text"
                   id="chatbot-visitor-name"
                   placeholder="Contoh: Budi Santoso - 2110114001"
                   class="w-full min-h-[38px] px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white text-slate-900 text-xs focus:outline-unpam-gold">
        </div>

        <!-- Daftar Pesan -->
        <div id="chatbot-messages"
             role="log"
             aria-live="polite"
             class="flex-1 overflow-y-auto p-3.5 space-y-3 bg-slate-50 min-h-[240px] max-h-[340px] text-sm">
            <div id="chatbot-loading-state" class="text-slate-700 bg-white border border-slate-200 rounded-lg p-3 text-xs">
                Memuat layanan menu Chatbot UNPAM...
            </div>
        </div>

        <!-- Area Error State -->
        <div id="chatbot-error-state" class="hidden bg-red-50 border-t border-red-300 px-3 py-2 text-xs text-red-900">
            Gagal terhubung ke server chat. Silakan coba kembali.
        </div>

        <!-- Tombol Pilihan Menu & Sub-Layanan -->
        <div id="chatbot-options-container" class="bg-white border-t border-slate-200 px-3 py-2.5">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-bold text-slate-800">Pilihan Menu & Sub-Layanan:</p>
                <span class="text-[11px] text-slate-500">Pilih nomor atau opsi</span>
            </div>
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
                   class="flex-1 min-h-[44px] px-3.5 py-2 rounded-lg border border-slate-400 bg-white text-slate-900 text-sm focus:outline-unpam-gold">
            <button type="submit"
                    id="chatbot-submit-btn"
                    class="min-h-[44px] px-4 py-2 rounded-lg bg-unpam-gold text-slate-950 font-bold text-sm hover:bg-unpam-goldHover shadow-sm shrink-0">
                Kirim
            </button>
        </form>
    </section>

    <!-- Tombol Pembuka Widget (Satu Kapsul Trigger Terpadu) -->
    <div id="chatbot-trigger-container" class="relative">
        <button type="button"
                id="chatbot-trigger"
                aria-expanded="false"
                aria-controls="chatbot-panel"
                class="min-h-[48px] px-4 py-2.5 rounded-full bg-unpam-navy text-white font-bold text-xs sm:text-sm shadow-2xl border-2 border-unpam-gold hover:bg-unpam-navyDark flex items-center gap-2.5 cursor-grab active:cursor-grabbing transition-all select-none">
            <!-- Bulat Berkedip Nyata (UNPAM Gold) -->
            <span class="unpam-blinking-dot" aria-hidden="true"></span>

            <!-- Teks Simpel -->
            <span class="tracking-tight text-white font-bold">Chat Layanan</span>

            <!-- Tombol Kembalikan Posisi: Ikon Putar Kecil Menyatu di Kapsul (Aktif jika di-drag) -->
            <span id="chatbot-reset-pos-pill"
                  title="Kembalikan letak widget ke posisi semula (kanan-bawah)"
                  aria-label="Kembalikan posisi semula"
                  role="button"
                  tabindex="0"
                  class="hidden p-1 rounded-full bg-slate-800 text-amber-300 hover:text-white hover:bg-slate-700 ml-1 border border-amber-400/60 transition-colors">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                    <path d="M3 3v5h5"/>
                </svg>
            </span>

            <!-- Ikon Handle Penggeser Mini -->
            <svg class="w-3.5 h-3.5 text-unpam-gold/80 ml-0.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <circle cx="7" cy="5" r="1.5"/><circle cx="13" cy="5" r="1.5"/>
                <circle cx="7" cy="10" r="1.5"/><circle cx="13" cy="10" r="1.5"/>
                <circle cx="7" cy="15" r="1.5"/><circle cx="13" cy="15" r="1.5"/>
            </svg>
        </button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const root = document.getElementById('unpam-chatbot-root');
    const panel = document.getElementById('chatbot-panel');
    const headerHandle = document.getElementById('chatbot-header-handle');
    const triggerBtn = document.getElementById('chatbot-trigger');
    const closeBtn = document.getElementById('chatbot-close-btn');
    const minimizeBtn = document.getElementById('chatbot-minimize-btn');
    const resetPosPill = document.getElementById('chatbot-reset-pos-pill');
    const resetPosPanelBtn = document.getElementById('chatbot-reset-pos-panel-btn');
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
    let isInitialOpen = true;
    let currentOptionsJson = '';

    // --- RESET POSISI KE TEMPAT SEMULA ---
    function resetPosition(e) {
        if (e) {
            e.stopPropagation();
            e.preventDefault();
        }
        root.style.left = 'auto';
        root.style.top = 'auto';
        root.style.right = '1.25rem';
        root.style.bottom = '1.25rem';
        if (resetPosPill) {
            resetPosPill.classList.add('hidden');
            resetPosPill.classList.remove('inline-flex');
        }
    }

    if (resetPosPill) {
        resetPosPill.addEventListener('click', resetPosition);
        resetPosPill.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') resetPosition(e);
        });
    }
    if (resetPosPanelBtn) resetPosPanelBtn.addEventListener('click', resetPosition);

    // --- FITUR DRAGGABLE (Dapat digeser mouse & touch) ---
    let isPointerDown = false;
    let didDrag = false;
    let startX = 0, startY = 0;
    let initialLeft = 0, initialTop = 0;

    function onPointerDown(e) {
        if (e.target.closest('#chatbot-close-btn') ||
            e.target.closest('#chatbot-minimize-btn') ||
            e.target.closest('#chatbot-reset-pos-pill') ||
            e.target.closest('#chatbot-reset-pos-panel-btn') ||
            e.target.closest('input') ||
            e.target.closest('form') ||
            e.target.closest('#chatbot-options-container') ||
            e.target.closest('#chatbot-messages')) {
            return;
        }

        isPointerDown = true;
        didDrag = false;

        const rect = root.getBoundingClientRect();
        initialLeft = rect.left;
        initialTop = rect.top;

        startX = e.clientX || (e.touches && e.touches[0].clientX) || 0;
        startY = e.clientY || (e.touches && e.touches[0].clientY) || 0;

        document.addEventListener('mousemove', onPointerMove);
        document.addEventListener('mouseup', onPointerUp);
        document.addEventListener('touchmove', onPointerMove, { passive: false });
        document.addEventListener('touchend', onPointerUp);
    }

    function onPointerMove(e) {
        if (!isPointerDown) return;
        const clientX = e.clientX || (e.touches && e.touches[0].clientX) || 0;
        const clientY = e.clientY || (e.touches && e.touches[0].clientY) || 0;

        const deltaX = clientX - startX;
        const deltaY = clientY - startY;

        if (Math.abs(deltaX) > 4 || Math.abs(deltaY) > 4) {
            didDrag = true;
            if (e.cancelable) e.preventDefault();
        }

        if (didDrag) {
            let nextLeft = initialLeft + deltaX;
            let nextTop = initialTop + deltaY;

            const maxLeft = window.innerWidth - root.offsetWidth - 8;
            const maxTop = window.innerHeight - root.offsetHeight - 8;

            nextLeft = Math.max(8, Math.min(maxLeft, nextLeft));
            nextTop = Math.max(8, Math.min(maxTop, nextTop));

            root.style.left = nextLeft + 'px';
            root.style.top = nextTop + 'px';
            root.style.right = 'auto';
            root.style.bottom = 'auto';

            if (resetPosPill) {
                resetPosPill.classList.remove('hidden');
                resetPosPill.classList.add('inline-flex');
            }
        }
    }

    function onPointerUp() {
        if (!isPointerDown) return;
        isPointerDown = false;
        document.removeEventListener('mousemove', onPointerMove);
        document.removeEventListener('mouseup', onPointerUp);
        document.removeEventListener('touchmove', onPointerMove);
        document.removeEventListener('touchend', onPointerUp);
    }

    triggerBtn.addEventListener('mousedown', onPointerDown);
    triggerBtn.addEventListener('touchstart', onPointerDown, { passive: true });
    headerHandle.addEventListener('mousedown', onPointerDown);
    headerHandle.addEventListener('touchstart', onPointerDown, { passive: true });

    // --- ANIMASI BUKA & TUTUP DINAMIS ---
    function openChat() {
        isOpen = true;
        panel.classList.remove('panel-hidden', 'panel-state-closed-top', 'panel-state-closed-bottom');
        panel.classList.add('flex');

        // Bounding check agar panel pas di layar
        const rect = root.getBoundingClientRect();
        if (rect.top < 10) {
            root.style.top = '10px';
            root.style.bottom = 'auto';
        }
        if (rect.right > window.innerWidth - 8) {
            root.style.left = Math.max(8, window.innerWidth - root.offsetWidth - 8) + 'px';
            root.style.right = 'auto';
        }

        // Trigger reflow untuk CSS transition
        void panel.offsetWidth;
        panel.classList.add('panel-state-open');
        triggerBtn.setAttribute('aria-expanded', 'true');
        isInitialOpen = true;

        fetchSession();
        if (!pollTimer) {
            pollTimer = setInterval(fetchSession, 4000);
        }
    }

    function closeChatFromHeader() {
        if (!isOpen) return;
        isOpen = false;
        triggerBtn.setAttribute('aria-expanded', 'false');

        // Animasi menutup ke arah atas (header)
        panel.classList.remove('panel-state-open');
        panel.classList.add('panel-state-closed-top');

        setTimeout(function () {
            panel.classList.add('panel-hidden');
            panel.classList.remove('flex');
        }, 220);

        if (pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
    }

    function closeChatFromBottom() {
        if (!isOpen) return;
        isOpen = false;
        triggerBtn.setAttribute('aria-expanded', 'false');

        // Animasi menutup ke arah bawah (trigger)
        panel.classList.remove('panel-state-open');
        panel.classList.add('panel-state-closed-bottom');

        setTimeout(function () {
            panel.classList.add('panel-hidden');
            panel.classList.remove('flex');
        }, 220);

        if (pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
    }

    window.openUnpamChat = openChat;

    // Klik trigger bawah: buka atau tutup ke arah bawah
    triggerBtn.addEventListener('click', function (e) {
        if (e.target.closest('#chatbot-reset-pos-pill')) return;
        if (didDrag) {
            didDrag = false;
            return;
        }
        if (isOpen) {
            closeChatFromBottom();
        } else {
            openChat();
        }
    });

    // Klik tombol di header: tutup/minimize ke arah atas
    closeBtn.addEventListener('click', closeChatFromHeader);
    minimizeBtn.addEventListener('click', closeChatFromHeader);

    // --- RENDER PERCAKAPAN & PERSISTENCE MENU STATE ---
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

            const savedScrollTop = messagesBox.scrollTop;
            messagesBox.innerHTML = '';

            if (msgs.length === 0) {
                messagesBox.innerHTML = '<div class="text-xs text-slate-700 bg-white border border-slate-200 rounded-lg p-3">Belum ada percakapan. Pilih menu di bawah untuk memulai.</div>';
            } else {
                msgs.forEach(function (item) {
                    const wrapper = document.createElement('div');
                    const isUser = item.sender_type === 'user';
                    const isAdmin = item.sender_type === 'admin';
                    const isSystem = item.sender_type === 'system';

                    if (isSystem) {
                        wrapper.className = 'bg-slate-900 text-amber-200 border border-amber-400/50 rounded-lg p-2.5 text-xs';
                    } else if (isUser) {
                        wrapper.className = 'ml-6 bg-unpam-navy text-white rounded-xl p-3 text-xs sm:text-sm shadow-sm';
                    } else if (isAdmin) {
                        wrapper.className = 'mr-6 bg-amber-50 border border-amber-300 text-slate-900 rounded-xl p-3 text-xs sm:text-sm shadow-sm';
                    } else {
                        wrapper.className = 'mr-4 bg-white border border-slate-300 text-slate-900 rounded-xl p-3 text-xs sm:text-sm shadow-sm';
                    }

                    const header = document.createElement('div');
                    header.className = 'flex items-center justify-between gap-2 mb-1 text-[11px] ' + (isUser ? 'text-amber-200' : 'text-slate-600');
                    header.innerHTML = '<span class="font-bold">' + (item.sender_name || item.sender_type) + '</span><span>' + (item.time || '') + '</span>';

                    const body = document.createElement('div');
                    body.className = 'leading-relaxed break-words';
                    body.innerHTML = formatSafeHtml(item.message);

                    wrapper.appendChild(header);
                    wrapper.appendChild(body);
                    messagesBox.appendChild(wrapper);
                });

                if (isInitialOpen) {
                    messagesBox.scrollTop = messagesBox.scrollHeight;
                    isInitialOpen = false;
                } else {
                    messagesBox.scrollTop = savedScrollTop;
                }
            }
        }

        // Render Opsi Menu: Pertahankan state sub-layanan
        const newOptionsJson = JSON.stringify(data.options || []);
        if (newOptionsJson !== currentOptionsJson) {
            currentOptionsJson = newOptionsJson;
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
                    btn.className = 'min-h-[38px] px-3 py-1.5 rounded-lg border border-slate-300 bg-slate-100 hover:bg-unpam-gold hover:text-slate-950 hover:border-amber-500 text-slate-900 font-semibold text-xs transition-colors';
                    btn.textContent = opt.label;
                    btn.addEventListener('click', function () {
                        sendChatMessage(opt.code);
                    });
                    optionsList.appendChild(btn);
                });
            }
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

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && isOpen) {
            closeChatFromHeader();
            triggerBtn.focus();
        }
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        sendChatMessage(input.value);
    });
});
</script>
