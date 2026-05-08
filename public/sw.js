// File: public/sw.js
// Service Worker — Offline Capabilities (Tiêu chí 27)

const CACHE_NAME    = 'noteapp-v1';
const DB_NAME       = 'noteapp-offline';
const DB_VERSION    = 1;

// ===== FILES CẦN CACHE KHI CÀI ĐẶT =====
const STATIC_ASSETS = [
    '/',
    '/notes',
    '/assets/css/style.css',
    '/assets/js/main.js',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
];

// ===== INSTALL: Cache static files =====
self.addEventListener('install', event => {
    console.log('[SW] Installing...');

    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('[SW] Caching static assets');
                // Dùng addAll với try-catch từng file
                return Promise.allSettled(
                    STATIC_ASSETS.map(url =>
                        cache.add(url).catch(err =>
                            console.warn('[SW] Failed to cache:', url, err)
                        )
                    )
                );
            })
            .then(() => self.skipWaiting())
    );
});

// ===== ACTIVATE: Xóa cache cũ =====
self.addEventListener('activate', event => {
    console.log('[SW] Activating...');

    event.waitUntil(
        caches.keys()
            .then(keys => Promise.all(
                keys
                    .filter(key => key !== CACHE_NAME)
                    .map(key => {
                        console.log('[SW] Deleting old cache:', key);
                        return caches.delete(key);
                    })
            ))
            .then(() => self.clients.claim())
    );
});

// ===== FETCH: Xử lý requests =====
self.addEventListener('fetch', event => {
    const { request } = event;
    const url         = new URL(request.url);

    // Bỏ qua chrome-extension và non-http
    if (!request.url.startsWith('http')) return;

    // ===== API requests (POST) → Network only =====
    if (request.method === 'POST') {
        event.respondWith(
            fetch(request).catch(() => {
                return new Response(
                    JSON.stringify({
                        status  : 'error',
                        message : 'Không có kết nối mạng!'
                    }),
                    {
                        headers: { 'Content-Type': 'application/json' }
                    }
                );
            })
        );
        return;
    }

    // ===== Static assets → Cache First =====
    if (
        url.pathname.startsWith('/assets/') ||
        url.hostname.includes('jsdelivr') ||
        url.hostname.includes('cdnjs')
    ) {
        event.respondWith(
            caches.match(request)
                .then(cached => cached || fetch(request)
                    .then(response => {
                        // Cache lại file mới
                        const clone = response.clone();
                        caches.open(CACHE_NAME)
                            .then(cache => cache.put(request, clone));
                        return response;
                    })
                )
                .catch(() => new Response('Offline', { status: 503 }))
        );
        return;
    }

    // ===== API GET → Network First, fallback Cache =====
    if (url.pathname.startsWith('/notes') ||
        url.pathname.startsWith('/labels') ||
        url.pathname.startsWith('/shared')
    ) {
        event.respondWith(
            fetch(request)
                .then(response => {
                    // Cache response mới nhất
                    const clone = response.clone();
                    caches.open(CACHE_NAME)
                        .then(cache => cache.put(request, clone));
                    return response;
                })
                .catch(() => {
                    // Offline → trả về cache
                    return caches.match(request)
                        .then(cached => {
                            if (cached) return cached;

                            // Không có cache → trả về offline page
                            return caches.match('/')
                                .then(home => home ||
                                    new Response(
                                        offlinePage(),
                                        {
                                            headers: {
                                                'Content-Type': 'text/html; charset=utf-8'
                                            }
                                        }
                                    )
                                );
                        });
                })
        );
        return;
    }

    // ===== Default → Network First =====
    event.respondWith(
        fetch(request)
            .then(response => {
                const clone = response.clone();
                caches.open(CACHE_NAME)
                    .then(cache => cache.put(request, clone));
                return response;
            })
            .catch(() =>
                caches.match(request)
                    .then(cached => cached ||
                        new Response(
                            offlinePage(),
                            {
                                headers: {
                                    'Content-Type': 'text/html; charset=utf-8'
                                }
                            }
                        )
                    )
            )
    );
});

// ===== OFFLINE PAGE =====
function offlinePage() {
    return `
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>NoteApp - Offline</title>
        <style>
            * { box-sizing: border-box; margin: 0; padding: 0; }
            body {
                font-family: 'Segoe UI', sans-serif;
                background: linear-gradient(135deg, #667eea, #764ba2);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                text-align: center;
                color: white;
                padding: 20px;
            }
            .container { max-width: 400px; }
            .icon { font-size: 5rem; margin-bottom: 20px; }
            h1 { font-size: 1.8rem; margin-bottom: 12px; }
            p  { opacity: 0.85; margin-bottom: 24px; line-height: 1.6; }
            button {
                background: white;
                color: #667eea;
                border: none;
                padding: 12px 28px;
                border-radius: 8px;
                font-size: 1rem;
                font-weight: 600;
                cursor: pointer;
                transition: opacity 0.2s;
            }
            button:hover { opacity: 0.9; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="icon">📵</div>
            <h1>Bạn đang offline</h1>
            <p>
                Không có kết nối mạng.<br>
                Một số nội dung đã được lưu có thể xem được.
            </p>
            <button onclick="location.reload()">
                🔄 Thử lại
            </button>
        </div>
    </body>
    </html>`;
}