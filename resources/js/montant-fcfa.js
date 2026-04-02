/**
 * Saisie montants type « 10 000.00 » (affichage à la perte de focus).
 */
function stripMontantFcfa(str) {
    if (str == null || typeof str !== 'string') {
        return '';
    }
    let s = str.replace(/\s/g, '').replace(/fcfa/gi, '').replace(/[^\d.,-]/g, '');
    s = s.replace(',', '.');
    if (s === '' || s === '-' || s === '.') {
        return '';
    }
    const n = parseFloat(s);
    return Number.isFinite(n) ? String(n) : '';
}

function formatMontantFcfa(str) {
    const raw = stripMontantFcfa(str);
    if (raw === '') {
        return '';
    }
    const n = parseFloat(raw);
    if (!Number.isFinite(n)) {
        return '';
    }
    const [intPart, dec = '00'] = n.toFixed(2).split('.');
    const intFmt = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    return `${intFmt}.${dec}`;
}

function bindMontantsFcfa(root = document) {
    root.addEventListener(
        'focusin',
        (e) => {
            const el = e.target;
            if (!(el instanceof HTMLInputElement) || !el.classList.contains('js-montant-fcfa')) {
                return;
            }
            const raw = stripMontantFcfa(el.value);
            el.value = raw === '' ? '' : raw;
        },
        true
    );
    root.addEventListener(
        'focusout',
        (e) => {
            const el = e.target;
            if (!(el instanceof HTMLInputElement) || !el.classList.contains('js-montant-fcfa')) {
                return;
            }
            const formatted = formatMontantFcfa(el.value);
            el.value = formatted;
        },
        true
    );
}

function initMontantsFcfa() {
    if (typeof document === 'undefined') {
        return;
    }
    if (window.__adventisteMontantFcfaBound) {
        return;
    }
    window.__adventisteMontantFcfaBound = true;
    bindMontantsFcfa(document);

    document.querySelectorAll('input.js-montant-fcfa').forEach((el) => {
        if (!(el instanceof HTMLInputElement) || el.value.trim() === '') {
            return;
        }
        el.value = formatMontantFcfa(el.value);
    });
}

if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMontantsFcfa, { once: true });
    } else {
        initMontantsFcfa();
    }
}
