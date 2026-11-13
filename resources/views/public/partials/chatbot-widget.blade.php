<style>
    @keyframes unpamDotBlinkAnim {

        0%,
        100% {
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
        width: 11px !important;
        height: 11px !important;
        border-radius: 9999px !important;
        background-color: #FACC15 !important;
        border: 2px solid #FFFFFF !important;
        animation: unpamDotBlinkAnim 1.1s ease-in-out infinite !important;
        -webkit-animation: unpamDotBlinkAnim 1.1s ease-in-out infinite !important;
    }

    /* Root widget: panel + tombol trigger bergerak bersama.
       Posisi selalu disimpan sebagai right/bottom (jangkar = tombol trigger)
       agar tombol tidak melompat saat panel disembunyikan. */
    #unpam-chatbot-root {
        position: fixed;
        right: 24px;
        bottom: 20px;
        z-index: 50;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        pointer-events: none;
    }

    #chatbot-panel,
    #chatbot-trigger-container {
        pointer-events: auto;
    }

    #chatbot-panel {
        width: 380px;
        max-width: calc(100vw - 24px);
        height: min(500px, calc(100vh - 110px));
        height: min(500px, calc(100svh - 110px));
        max-height: calc(100svh - 110px);
        margin-bottom: 10px;
        transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.2s ease;
        will-change: transform, opacity;
    }

    @media (max-width: 640px) {
        #unpam-chatbot-root {
            right: 12px;
            bottom: calc(24px + env(safe-area-inset-bottom, 0px));
        }

        #chatbot-panel {
            width: calc(100vw - 24px);
            max-width: 100%;
            height: min(420px, calc(100vh - 150px));
            height: min(420px, calc(100svh - 150px));
            max-height: calc(100svh - 150px);
            margin-bottom: 8px;
        }
    }

    .panel-hidden {
        display: none !important;
    }

    /* Tutup dari tombol trigger (bawah): menciut ke bawah */
    .panel-closed-bottom {
        opacity: 0;
        transform-origin: bottom right;
        transform: translateY(16px) scale(0.92);
        pointer-events: none;
    }

    /* Tutup dari tombol silang header (atas): menciut ke atas */
    .panel-closed-top {
        opacity: 0;
        transform-origin: top right;
        transform: translateY(-16px) scale(0.92);
        pointer-events: none;
    }

    .panel-open {
        opacity: 1;
        transform: none;
        pointer-events: auto;
    }

    /* Scrollbar halus untuk daftar tombol options */
    #chatbot-options-list::-webkit-scrollbar {
        width: 4px;
    }

    #chatbot-options-list::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 4px;
    }

    .dark #chatbot-options-list::-webkit-scrollbar-thumb {
        background-color: #475569;
    }
</style>

<div id="unpam-chatbot-root">
    <!-- Panel Chat Pop-up -->
    <section id="chatbot-panel" aria-label="Layanan Chatbot Mahasiswa"
        class="panel-hidden panel-closed-bottom bg-white dark:bg-slate-900 border-2 border-unpam-navy dark:border-slate-700 rounded-2xl shadow-2xl flex flex-col overflow-hidden text-slate-900 dark:text-slate-100">
        <!-- Header Widget: Handle Draggable, Judul & Ikon Aksi -->
        <div id="chatbot-header-handle"
            title="Tahan dan geser untuk memindahkan. Klik dua kali untuk kembali ke posisi awal."
            class="bg-unpam-navy text-white px-3.5 py-2.5 flex items-center justify-between gap-2 border-b-2 border-unpam-gold cursor-move active:cursor-grabbing select-none shrink-0">
            <div class="flex items-center gap-2.5 pointer-events-none">
                <!-- Bulat Berkedip Nyata -->
                <span class="unpam-blinking-dot" aria-hidden="true"></span>
                <div>
                    <!-- Judul Ringkas -->
                    <h2 class="font-bold text-sm text-white leading-snug">SiRNaMa Chat</h2>
                    <p id="chatbot-status-badge" class="text-[11px] text-amber-200 leading-none">Status: Memuat sesi...
                    </p>
                </div>
            </div>

            <!-- Tombol Aksi di Header Chat (Ikon Bold) -->
            <div class="flex items-center gap-1.5 pointer-events-auto">
                <!-- Tombol Posisi Semula: Ikon Reset Putar Bold -->
                <button type="button" id="chatbot-reset-pos-panel-btn" title="Kembalikan ke posisi awal (kanan bawah)"
                    aria-label="Kembalikan ke posisi awal"
                    class="min-h-[34px] min-w-[34px] inline-flex items-center justify-center rounded-lg bg-slate-800 text-amber-300 hover:bg-slate-700 hover:text-white border border-amber-400/50 transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
                        <path d="M3 3v5h5" />
                    </svg>
                </button>

                <!-- Tombol Scroll to Bottom (Ikon Panah Bawah: Scroll ke pesan akhir) -->
                <button type="button" id="chatbot-scroll-bottom-header-btn" title="Scroll ke pesan paling akhir"
                    aria-label="Ke pesan paling akhir"
                    class="min-h-[34px] min-w-[34px] inline-flex items-center justify-center rounded-lg bg-slate-800 text-white hover:bg-slate-700 border border-slate-600 transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 5v14" />
                        <path d="m19 12-7 7-7-7" />
                    </svg>
                </button>

                <!-- Tombol Tutup: Ikon Silang (✕) Bold (Menciut ke Atas) -->
                <button type="button" id="chatbot-close-btn" title="Tutup jendela chat" aria-label="Tutup jendela chat"
                    class="min-h-[34px] min-w-[34px] inline-flex items-center justify-center rounded-lg bg-slate-800 text-white hover:bg-red-700 border border-slate-600 transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M18 6 6 18" />
                        <path d="m6 6 12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Identitas Opsional Pengunjung saat meminta Admin -->
        <div id="chatbot-identity-bar"
            class="bg-slate-100 dark:bg-slate-800 border-b border-slate-300 dark:border-slate-700 px-3.5 py-1.5 text-xs shrink-0">
            <label for="chatbot-visitor-name"
                class="block font-semibold text-slate-800 dark:text-slate-200 mb-0.5 text-[11px]">
                Nama / NIM (Opsional untuk Admin):
            </label>
            <input type="text" id="chatbot-visitor-name" placeholder="Contoh: Budi Santoso - 2110114001"
                class="w-full min-h-[34px] px-2.5 py-1 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:outline-unpam-gold">
        </div>

        <!-- Daftar Pesan (Fleksibel Menyesuaikan Tinggi Viewport & Auto Scroll) -->
        <div id="chatbot-messages" role="log" aria-live="polite"
            class="flex-1 min-h-0 overflow-y-auto p-3.5 space-y-3 bg-slate-50 dark:bg-slate-900 text-sm">
            <div id="chatbot-loading-state"
                class="text-xs text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-3">
                Memuat layanan menu Chatbot SiRNaMa {{ $brandAppName }}...
            </div>
        </div>

        <!-- Area Error State -->
        <div id="chatbot-error-state"
            class="hidden bg-red-50 dark:bg-red-950/60 border-t border-red-300 dark:border-red-800 px-3 py-1.5 text-xs text-red-900 dark:text-red-200 shrink-0">
            Gagal terhubung ke server chat. Silakan coba kembali.
        </div>

        <!-- Tombol Pilihan Menu & Sub-Layanan -->
        <div id="chatbot-options-container"
            class="bg-slate-50 dark:bg-slate-850 dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700 px-3 py-2 shrink-0">
            <div class="flex items-center justify-between mb-1.5">
                <p class="text-[11px] font-bold text-slate-700 dark:text-slate-300 tracking-tight">Pilihan Menu &amp;
                    Sub-Layanan:</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Pilih opsi</span>
            </div>
            <!-- Container Opsi dengan max-height dan overflow-y auto agar tidak meluap keluar kartu -->
            <div id="chatbot-options-list" class="flex flex-wrap gap-1.5 max-h-24 sm:max-h-28 overflow-y-auto pr-0.5">
            </div>
        </div>

        <!-- Form Kirim Pesan / Kode Menu (Selalu Terkunci di Bagian Bawah Jendela) -->
        <form id="chatbot-form"
            class="bg-slate-100 dark:bg-slate-800 border-t border-slate-300 dark:border-slate-700 p-2.5 flex items-center gap-2 shrink-0">
            <label for="chatbot-input" class="sr-only">Ketik angka menu atau pesan untuk Admin</label>
            <input type="text" id="chatbot-input" name="message" required placeholder="Ketik 1, 2, 3 atau pesan Anda..."
                class="flex-1 min-h-[40px] px-3 py-1.5 rounded-lg border border-slate-400 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 text-sm focus:outline-unpam-gold">
            <button type="submit" id="chatbot-submit-btn"
                class="min-h-[40px] px-4 py-1.5 rounded-lg bg-unpam-gold text-slate-950 font-bold text-sm hover:bg-unpam-goldHover shadow-sm shrink-0">
                Kirim
            </button>
        </form>
    </section>

    <!-- Tombol Pembuka Widget (Kapsul Trigger Terpadu di Kanan Bawah) -->
    <div id="chatbot-trigger-container" class="relative">
        <button type="button" id="chatbot-trigger" aria-expanded="false" aria-controls="chatbot-panel"
            class="min-h-[46px] px-4 py-2.5 rounded-full bg-unpam-navy text-white font-bold text-xs sm:text-sm shadow-2xl border-2 border-unpam-gold hover:bg-slate-800 flex items-center gap-2.5 transition-colors select-none cursor-grab active:cursor-grabbing touch-none focus-visible:outline-unpam-gold"
            title="Klik untuk membuka chat, tahan dan geser untuk memindahkan">
            <!-- Bulat Berkedip Nyata (UNPAM Gold) -->
            <span class="unpam-blinking-dot" aria-hidden="true"></span>
            <!-- Teks Simpel -->
            <span class="tracking-tight text-white font-bold">Chat SiRNaMa</span>

            <!-- Tombol Kembalikan Posisi di Kapsul Trigger (Muncul saat widget digeser) -->
            <span id="chatbot-reset-pos-pill" title="Kembalikan ke posisi awal (kanan bawah)"
                aria-label="Kembalikan ke posisi awal" role="button" tabindex="0"
                class="hidden p-1 rounded-full bg-slate-800 text-amber-300 hover:text-white hover:bg-slate-700 ml-0.5 border border-amber-400/60 transition-colors">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
                    <path d="M3 3v5h5" />
                </svg>
            </span>

            <!-- Ikon Handle Penggeser Mini -->
            <svg class="w-3.5 h-3.5 text-unpam-gold/80 ml-0.5" fill="currentColor" viewBox="0 0 20 20"
                aria-hidden="true">
                <circle cx="7" cy="5" r="1.5" />
                <circle cx="13" cy="5" r="1.5" />
                <circle cx="7" cy="10" r="1.5" />
                <circle cx="13" cy="10" r="1.5" />
                <circle cx="7" cy="15" r="1.5" />
                <circle cx="13" cy="15" r="1.5" />
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
        const scrollBottomHeaderBtn = document.getElementById('chatbot-scroll-bottom-header-btn');
        const resetPosPanelBtn = document.getElementById('chatbot-reset-pos-panel-btn');
        const resetPosPill = document.getElementById('chatbot-reset-pos-pill');
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
        let currentOptionsJson = '';
        let isCustomDragged = false;

        // --- PENYESUAIAN VISUAL VIEWPORT MOBILE & AVOID OVERLAPPING FOOTER ---
        // Mencegah tombol/panel chat tertutup toolbar bawah browser HP dan jangan menimpa footer saat scroll mentok bawah
        function syncMobileVisualViewport() {
            const vv = window.visualViewport;
            const viewportH = vv ? vv.height : window.innerHeight;
            const isMobile = window.innerWidth <= 640;

            if (isMobile) {
                const availPanelH = Math.max(280, Math.min(420, Math.floor(viewportH - 135)));
                panel.style.height = availPanelH + 'px';
                panel.style.maxHeight = availPanelH + 'px';
            } else {
                panel.style.height = '';
                panel.style.maxHeight = '';
            }

            if (!isCustomDragged) {
                let baseBottom = isMobile ? 24 : 20;
                if (vv && isMobile) {
                    const occludedBottom = Math.max(0, Math.round(window.innerHeight - (vv.height + vv.offsetTop)));
                    baseBottom += occludedBottom;
                }

                // Cek apakah footer utama halaman publik terlihat di viewport bawah
                const footerEl = document.getElementById('public-main-footer') || document.querySelector('body > footer');
                if (footerEl) {
                    const footerRect = footerEl.getBoundingClientRect();
                    const windowHeight = window.innerHeight;
                    // Jika bagian atas footer sudah masuk ke layar bawah
                    if (footerRect.top < windowHeight) {
                        const overlap = windowHeight - footerRect.top;
                        baseBottom += Math.max(0, overlap + 12);
                    }
                }

                root.style.bottom = baseBottom + 'px';
            }
        }

        syncMobileVisualViewport();
        window.addEventListener('scroll', syncMobileVisualViewport, { passive: true });
        window.addEventListener('resize', syncMobileVisualViewport);
        if (window.visualViewport) {
            window.visualViewport.addEventListener('resize', syncMobileVisualViewport);
            window.visualViewport.addEventListener('scroll', syncMobileVisualViewport);
        }

        // --- DRAG / MOVE WIDGET (via header panel atau tombol trigger) ---
        // Posisi root disimpan sebagai right/bottom agar tombol trigger tetap diam
        // saat panel muncul/hilang (tidak melompat ke atas).
        const EDGE = 8;
        let isPointerDown = false;
        let didDrag = false;
        let startX = 0, startY = 0;
        let startRight = 0, startBottom = 0;

        function pointerXY(e) {
            const p = (e.touches && e.touches[0]) || (e.changedTouches && e.changedTouches[0]) || e;
            return { x: p.clientX || 0, y: p.clientY || 0 };
        }

        function setRootPos(right, bottom) {
            const vv = window.visualViewport;
            const viewW = vv ? vv.width : window.innerWidth;
            const viewH = vv ? vv.height : window.innerHeight;
            const maxRight = Math.max(EDGE, viewW - root.offsetWidth - EDGE);
            const maxBottom = Math.max(EDGE, viewH - root.offsetHeight - EDGE);
            root.style.right = Math.max(EDGE, Math.min(maxRight, right)) + 'px';
            root.style.bottom = Math.max(EDGE, Math.min(maxBottom, bottom)) + 'px';
        }

        function clampRootIntoView() {
            syncMobileVisualViewport();
            const rect = root.getBoundingClientRect();
            setRootPos(window.innerWidth - rect.right, window.innerHeight - rect.bottom);
        }

        function resetRootPosition(e) {
            if (e) {
                e.stopPropagation();
                e.preventDefault();
            }
            isCustomDragged = false;
            root.style.right = '';
            root.style.bottom = '';
            if (resetPosPill) {
                resetPosPill.classList.add('hidden');
                resetPosPill.classList.remove('inline-flex');
            }
            syncMobileVisualViewport();
        }

        if (resetPosPanelBtn) {
            resetPosPanelBtn.addEventListener('click', resetRootPosition);
        }
        if (resetPosPill) {
            resetPosPill.addEventListener('click', resetRootPosition);
            resetPosPill.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') resetRootPosition(e);
            });
        }

        function onPointerDown(e) {
            if (e.type === 'mousedown' && e.button !== 0) return;
            // Tombol aksi di header (reset/scroll/close) atau pill reset tidak memicu drag
            if (e.target.closest('button') && e.currentTarget === headerHandle) return;
            if (e.target.closest('#chatbot-reset-pos-pill')) return;

            const rect = root.getBoundingClientRect();
            const pt = pointerXY(e);
            startX = pt.x;
            startY = pt.y;
            startRight = window.innerWidth - rect.right;
            startBottom = window.innerHeight - rect.bottom;
            isPointerDown = true;
            didDrag = false;

            document.addEventListener('mousemove', onPointerMove);
            document.addEventListener('mouseup', onPointerUp);
            document.addEventListener('touchmove', onPointerMove, { passive: false });
            document.addEventListener('touchend', onPointerUp);
        }

        function onPointerMove(e) {
            if (!isPointerDown) return;
            const pt = pointerXY(e);
            const dx = pt.x - startX;
            const dy = pt.y - startY;

            if (!didDrag && (Math.abs(dx) > 4 || Math.abs(dy) > 4)) {
                didDrag = true;
                isCustomDragged = true;
                if (resetPosPill) {
                    resetPosPill.classList.remove('hidden');
                    resetPosPill.classList.add('inline-flex');
                }
            }
            if (!didDrag) return;
            if (e.cancelable) e.preventDefault();

            setRootPos(startRight - dx, startBottom - dy);
        }

        function onPointerUp() {
            isPointerDown = false;
            document.removeEventListener('mousemove', onPointerMove);
            document.removeEventListener('mouseup', onPointerUp);
            document.removeEventListener('touchmove', onPointerMove);
            document.removeEventListener('touchend', onPointerUp);
        }

        headerHandle.addEventListener('mousedown', onPointerDown);
        headerHandle.addEventListener('touchstart', onPointerDown, { passive: true });
        triggerBtn.addEventListener('mousedown', onPointerDown);
        triggerBtn.addEventListener('touchstart', onPointerDown, { passive: true });

        // Klik dua kali header: kembali ke posisi awal (kanan bawah)
        headerHandle.addEventListener('dblclick', function (e) {
            if (e.target.closest('button')) return;
            resetRootPosition(e);
        });

        window.addEventListener('resize', function () {
            syncMobileVisualViewport();
            if (isCustomDragged) clampRootIntoView();
        });

        // --- AUTO SCROLL HELPER ---
        function scrollToBottom(smooth = true, targetElement = null) {
            if (!messagesBox) return;

            // Jika ada target elemen (misal pesan user terbaru), posisikan agar pesan itu tetap terlihat di layar atas chat
            if (targetElement && typeof targetElement.scrollIntoView === 'function') {
                targetElement.scrollIntoView({ behavior: smooth ? 'smooth' : 'auto', block: 'start' });
                return;
            }

            const doScroll = function (useSmooth) {
                if (!messagesBox) return;
                if (useSmooth && typeof messagesBox.scrollTo === 'function') {
                    messagesBox.scrollTo({
                        top: messagesBox.scrollHeight,
                        behavior: 'smooth'
                    });
                } else {
                    messagesBox.scrollTop = messagesBox.scrollHeight;
                }
            };
            doScroll(false);
            setTimeout(function () { doScroll(smooth); }, 60);
        }

        // --- TOMBOL SCROLL TO BOTTOM HEADER ---
        if (scrollBottomHeaderBtn) {
            scrollBottomHeaderBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                scrollToBottom(true);
            });
        }

        // --- LOGIKA BUKA & TUTUP WIDGET CHAT ---
        let hideTimer = null;

        function openChat() {
            if (isOpen) return;
            isOpen = true;
            if (hideTimer) {
                clearTimeout(hideTimer);
                hideTimer = null;
            }

            panel.classList.remove('panel-hidden', 'panel-closed-top', 'panel-closed-bottom');
            syncMobileVisualViewport();
            if (isCustomDragged) clampRootIntoView();

            void panel.offsetWidth;
            panel.classList.remove('panel-closed-bottom');
            panel.classList.add('panel-open');
            triggerBtn.setAttribute('aria-expanded', 'true');

            scrollToBottom(false);
            fetchSession();
            if (!pollTimer) {
                pollTimer = setInterval(fetchSession, 4000);
            }
        }

        function closeChat(direction) {
            if (!isOpen) return;
            isOpen = false;
            triggerBtn.setAttribute('aria-expanded', 'false');

            panel.classList.remove('panel-open', 'panel-closed-top', 'panel-closed-bottom');
            panel.classList.add(direction === 'top' ? 'panel-closed-top' : 'panel-closed-bottom');

            hideTimer = setTimeout(function () {
                if (!isOpen) panel.classList.add('panel-hidden');
                hideTimer = null;
            }, 230);

            if (pollTimer) {
                clearInterval(pollTimer);
                pollTimer = null;
            }
        }

        window.openUnpamChat = openChat;

        triggerBtn.addEventListener('click', function (e) {
            if (e.target.closest('#chatbot-reset-pos-pill')) return;
            if (didDrag) {
                didDrag = false;
                return;
            }
            if (isOpen) {
                closeChat('bottom');
            } else {
                openChat();
            }
        });

        closeBtn.addEventListener('click', function () {
            closeChat('top');
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && isOpen) {
                closeChat('top');
                triggerBtn.focus();
            }
        });

        // --- RENDER PERCAKAPAN & AUTO-SCROLL RAMAH PENGGUNA ---
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
            const isContentUpdated = msgs.length !== lastRenderedCount || data.status !== lastStatus;

            if (isContentUpdated) {
                lastRenderedCount = msgs.length;
                lastStatus = data.status;

                messagesBox.innerHTML = '';

                if (msgs.length === 0) {
                    messagesBox.innerHTML = '<div class="text-xs text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-3">Belum ada percakapan. Silakan pilih salah satu menu di bawah untuk memulai layanan.</div>';
                } else {
                    msgs.forEach(function (item) {
                        const wrapper = document.createElement('div');
                        const isUser = item.sender_type === 'user';
                        const isAdmin = item.sender_type === 'admin';
                        const isSystem = item.sender_type === 'system';

                        if (isSystem) {
                            wrapper.className = 'bg-slate-900 text-amber-200 border border-amber-400/50 rounded-lg p-2.5 text-xs';
                        } else if (isUser) {
                            wrapper.className = 'ml-6 bg-unpam-navy text-white rounded-xl p-2.5 text-xs sm:text-sm shadow-sm';
                        } else if (isAdmin) {
                            wrapper.className = 'mr-6 bg-amber-50 dark:bg-slate-800 border border-amber-300 dark:border-amber-500/50 text-slate-900 dark:text-slate-100 rounded-xl p-2.5 text-xs sm:text-sm shadow-sm';
                        } else {
                            wrapper.className = 'mr-4 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl p-2.5 text-xs sm:text-sm shadow-sm';
                        }

                        const header = document.createElement('div');
                        header.className = 'flex items-center justify-between gap-2 mb-1 text-[11px] ' + (isUser ? 'text-amber-200' : 'text-slate-600 dark:text-slate-400');
                        header.innerHTML = '<span class="font-bold">' + (item.sender_name || item.sender_type) + '</span><span>' + (item.time || '') + '</span>';

                        const body = document.createElement('div');
                        body.className = 'leading-relaxed break-words';
                        body.innerHTML = formatSafeHtml(item.message);

                        wrapper.appendChild(header);
                        wrapper.appendChild(body);
                        messagesBox.appendChild(wrapper);
                    });
                }

                // Fokus tepat pada balasan bot terbaru yang muncul setelah pengguna kirim pesan
                const allBotMsgs = messagesBox.querySelectorAll('.mr-4, .mr-6, .bg-slate-900');
                const lastBotMsg = allBotMsgs.length > 0 ? allBotMsgs[allBotMsgs.length - 1] : null;

                if (lastBotMsg) {
                    // Scroll agar kepala pesan balasan bot langsung berada tepat di atas layar chat
                    lastBotMsg.scrollIntoView({ behavior: 'smooth', block: 'start' });
                } else {
                    scrollToBottom(true);
                }
            }

            // Render Opsi Menu & Sub-Layanan
            const newOptionsJson = JSON.stringify(data.options || []);
            if (newOptionsJson !== currentOptionsJson) {
                currentOptionsJson = newOptionsJson;
                optionsList.innerHTML = '';
                const options = Array.isArray(data.options) ? data.options : [];

                if (options.length === 0) {
                    const note = document.createElement('p');
                    note.className = 'text-xs text-slate-600 dark:text-slate-400';
                    note.textContent = 'Admin sedang aktif melayani percakapan ini. Silakan ketik pesan Anda pada kolom di bawah.';
                    optionsList.appendChild(note);
                } else {
                    options.forEach(function (opt) {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'min-h-[32px] px-2.5 py-1.5 rounded-md text-[11px] sm:text-xs font-semibold border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 hover:bg-unpam-gold hover:text-slate-950 dark:hover:bg-unpam-gold dark:hover:text-slate-950 hover:border-amber-400 text-slate-800 dark:text-slate-100 transition-colors shrink-0 shadow-xs';
                        btn.textContent = opt.label;
                        btn.addEventListener('click', function (e) {
                            e.stopPropagation();
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

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            sendChatMessage(input.value);
        });
    });
</script>