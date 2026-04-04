/**
 * File d'attente hors ligne (IndexedDB) + rejeu POST/PUT vers les routes Laravel existantes.
 * Activer sur un formulaire : attribut data-offline-queue
 */

const DB_NAME = 'adventiste_offline';
const DB_VERSION = 1;
const STORE = 'queue';

function getSyncCsrfUrl() {
    const el = document.querySelector('meta[name="sync-csrf-url"]');
    const u = el?.getAttribute('content')?.trim();
    return u || '/sync/csrf';
}

function getServiceWorkerUrl() {
    const el = document.querySelector('meta[name="service-worker-url"]');
    const u = el?.getAttribute('content')?.trim();
    return u || '/sw.js';
}

function openDb() {
    return new Promise((resolve, reject) => {
        const req = indexedDB.open(DB_NAME, DB_VERSION);
        req.onupgradeneeded = () => {
            const db = req.result;
            if (!db.objectStoreNames.contains(STORE)) {
                db.createObjectStore(STORE, { keyPath: 'id' });
            }
        };
        req.onsuccess = () => resolve(req.result);
        req.onerror = () => reject(req.error);
    });
}

async function queueCount() {
    const db = await openDb();
    return new Promise((resolve, reject) => {
        const req = db.transaction(STORE, 'readonly').objectStore(STORE).count();
        req.onsuccess = () => resolve(req.result);
        req.onerror = () => reject(req.error);
    });
}

async function enqueueForm(form) {
    const fd = new FormData(form);
    const body = new URLSearchParams(fd).toString();
    const action = form.getAttribute('action') || window.location.href;
    const db = await openDb();
    const item = {
        id: crypto.randomUUID(),
        createdAt: Date.now(),
        action,
        body,
    };
    return new Promise((resolve, reject) => {
        const tx = db.transaction(STORE, 'readwrite');
        tx.objectStore(STORE).add(item);
        tx.oncomplete = () => resolve(item);
        tx.onerror = () => reject(tx.error);
    });
}

async function getAllQueued() {
    const db = await openDb();
    return new Promise((resolve, reject) => {
        const req = db.transaction(STORE, 'readonly').objectStore(STORE).getAll();
        req.onsuccess = () => resolve((req.result || []).sort((a, b) => a.createdAt - b.createdAt));
        req.onerror = () => reject(req.error);
    });
}

async function removeQueued(id) {
    const db = await openDb();
    return new Promise((resolve, reject) => {
        const tx = db.transaction(STORE, 'readwrite');
        tx.objectStore(STORE).delete(id);
        tx.oncomplete = () => resolve();
        tx.onerror = () => reject(tx.error);
    });
}

async function refreshCsrfToken() {
    const r = await fetch(getSyncCsrfUrl(), {
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    });
    if (!r.ok) {
        throw new Error('csrf');
    }
    const { token } = await r.json();
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) {
        meta.setAttribute('content', token);
    }
    if (window.axios?.defaults?.headers?.common) {
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
    }
    return token;
}

/**
 * Remplace _token dans le corps x-www-form-urlencoded.
 */
function applyCsrfToBody(body, token) {
    const params = new URLSearchParams(body);
    params.set('_token', token);
    return params.toString();
}

async function replayOne(item) {
    const token = await refreshCsrfToken();
    const body = applyCsrfToBody(item.body, token);
    const res = await fetch(item.action, {
        method: 'POST',
        credentials: 'same-origin',
        redirect: 'follow',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'text/html,application/xhtml+xml',
        },
        body,
    });
    if (res.status === 419) {
        const token2 = await refreshCsrfToken();
        const body2 = applyCsrfToBody(item.body, token2);
        const res2 = await fetch(item.action, {
            method: 'POST',
            credentials: 'same-origin',
            redirect: 'follow',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': token2,
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'text/html,application/xhtml+xml',
            },
            body: body2,
        });
        return res2;
    }
    return res;
}

let flushing = false;

async function flushQueue() {
    if (!navigator.onLine || flushing) {
        return;
    }
    flushing = true;
    try {
        const items = await getAllQueued();
        for (const item of items) {
            try {
                const res = await replayOne(item);
                if (res.status === 401) {
                    window.dispatchEvent(
                        new CustomEvent('adventiste-offline-sync-error', {
                            detail: {
                                message:
                                    'Session expirée. Reconnectez-vous, puis utilisez « Synchroniser maintenant ».',
                            },
                        }),
                    );
                    break;
                }
                if (res.ok || (res.redirected && res.url)) {
                    await removeQueued(item.id);
                    continue;
                }
                if (res.status === 422) {
                    await removeQueued(item.id);
                    window.dispatchEvent(
                        new CustomEvent('adventiste-offline-sync-error', {
                            detail: {
                                message:
                                    'Une saisie en attente a été rejetée par le serveur (validation). Vérifiez les données.',
                            },
                        }),
                    );
                    continue;
                }
                break;
            } catch {
                break;
            }
        }
    } finally {
        flushing = false;
        window.dispatchEvent(new CustomEvent('adventiste-offline-queue-changed'));
    }
}

function updateOfflineBar() {
    const bar = document.getElementById('adventiste-offline-bar');
    if (!bar) {
        return;
    }
    const msg = document.getElementById('adventiste-offline-bar-msg');
    const btn = document.getElementById('adventiste-offline-sync-btn');
    queueCount()
        .then((n) => {
            const offline = !navigator.onLine;
            const show = offline || n > 0;
            bar.classList.toggle('hidden', !show);
            if (msg) {
                if (offline && n > 0) {
                    msg.textContent = `Hors ligne — ${n} envoi(s) en attente.`;
                } else if (offline) {
                    msg.textContent = 'Hors ligne — connexion indisponible.';
                } else if (n > 0) {
                    msg.textContent = `${n} envoi(s) en attente de synchronisation.`;
                }
            }
            if (btn) {
                btn.classList.toggle('hidden', n === 0 || !navigator.onLine);
            }
        })
        .catch(() => {});
}

function showToast(message) {
    let el = document.getElementById('adventiste-offline-toast');
    if (!el) {
        el = document.createElement('div');
        el.id = 'adventiste-offline-toast';
        el.className =
            'fixed bottom-6 left-1/2 z-[2147483001] max-w-md -translate-x-1/2 rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 shadow-lg dark:border-emerald-700 dark:bg-emerald-950 dark:text-emerald-100';
        document.body.appendChild(el);
    }
    el.textContent = message;
    el.classList.remove('hidden');
    clearTimeout(showToast._t);
    showToast._t = setTimeout(() => el.classList.add('hidden'), 5000);
}

function registerServiceWorker() {
    if (!('serviceWorker' in navigator)) {
        return;
    }
    window.addEventListener('load', () => {
        const url = getServiceWorkerUrl();
        navigator.serviceWorker.register(url, { type: 'classic' }).catch(() => {});
    });
}

function initOfflineFormCapture() {
    document.addEventListener(
        'submit',
        (ev) => {
            const form = ev.target;
            if (!(form instanceof HTMLFormElement)) {
                return;
            }
            if (!form.hasAttribute('data-offline-queue')) {
                return;
            }
            if (form.method.toUpperCase() !== 'POST') {
                return;
            }
            if (form.enctype === 'multipart/form-data') {
                if (!navigator.onLine) {
                    ev.preventDefault();
                    ev.stopPropagation();
                    window.alert(
                        'Les formulaires avec fichiers joints nécessitent une connexion. Réessayez en ligne.',
                    );
                }
                return;
            }
            if (navigator.onLine) {
                return;
            }
            ev.preventDefault();
            ev.stopPropagation();
            enqueueForm(form)
                .then(() => {
                    updateOfflineBar();
                    showToast('Enregistré hors ligne. Sera envoyé automatiquement à la reconnexion.');
                })
                .catch(() => {
                    window.alert("Impossible d'enregistrer hors ligne sur cet appareil.");
                });
        },
        true,
    );
}

export function initOfflineSync() {
    registerServiceWorker();
    initOfflineFormCapture();
    window.addEventListener('online', () => {
        updateOfflineBar();
        flushQueue();
    });
    window.addEventListener('offline', updateOfflineBar);
    window.addEventListener('adventiste-offline-queue-changed', updateOfflineBar);
    window.addEventListener('adventiste-offline-sync-error', (e) => {
        showToast(e.detail?.message || 'Erreur de synchronisation.');
    });
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            updateOfflineBar();
            flushQueue();
        }
    });
    document.addEventListener('DOMContentLoaded', () => {
        updateOfflineBar();
        flushQueue();
        const btn = document.getElementById('adventiste-offline-sync-btn');
        if (btn) {
            btn.addEventListener('click', () => flushQueue());
        }
    });
}
