// Клієнтські сценарії APEX TRACK: плавна прокрутка, екран завантаження, фото, слайдер,
// «виїжджаючі» блоки, лічильники, підказки пошуку, автофільтри, вікно підтвердження.
import Lenis from 'lenis';

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// ── Плавна прокрутка з інерцією (як на neundex.com) ───────────────────────────
let lenis = null;
if (!reduceMotion) {
    lenis = new Lenis({ lerp: 0.085, wheelMultiplier: 0.9 });
    const raf = (time) => { lenis.raf(time); requestAnimationFrame(raf); };
    requestAnimationFrame(raf);
    document.querySelectorAll('a[href^="#"]').forEach((a) => a.addEventListener('click', (e) => {
        const target = document.querySelector(a.getAttribute('href'));
        if (target) { e.preventDefault(); lenis.scrollTo(target, { offset: -70 }); }
    }));
    if (location.hash && document.querySelector(location.hash)) {
        window.addEventListener('load', () => lenis.scrollTo(location.hash, { offset: -70, immediate: true }));
    }
}

// ── Фото проявляються після завантаження ─────────────────────────────────────
const markLoaded = (img) => {
    img.classList.add('is-loaded');
    img.closest('.img-shell')?.classList.add('is-loaded');
};
document.querySelectorAll('img[data-fade]').forEach((img) => {
    if (img.complete && img.naturalWidth) markLoaded(img);
    else {
        img.addEventListener('load', () => markLoaded(img), { once: true });
        img.addEventListener('error', () => markLoaded(img), { once: true });
    }
});

// ── Екран завантаження 0→100% (лише при першому відкритті сайту в сесії) ──────
const preloader = document.querySelector('[data-preloader]');
const startPage = () => document.dispatchEvent(new Event('apex:ready'));
if (preloader && document.documentElement.classList.contains('is-preloading')) {
    const valueEl = preloader.querySelector('[data-preloader-value]');
    const barEl = preloader.querySelector('[data-preloader-bar]');
    const images = [...document.querySelectorAll('img[data-preload]')];
    let target = 0;
    let shown = 0;
    const update = () => {
        const done = images.filter((i) => i.complete).length;
        target = images.length ? Math.round((done / images.length) * 100) : 100;
    };
    images.forEach((i) => i.addEventListener('load', update, { once: true }));
    images.forEach((i) => i.addEventListener('error', update, { once: true }));
    update();
    const forceDone = setTimeout(() => { target = 100; }, 4000); // фото завелике — не тримаємо довше 4 с
    const tick = () => {
        shown += Math.max(1, (target - shown) * 0.12);
        shown = Math.min(shown, target);
        valueEl.textContent = String(Math.round(shown));
        barEl.style.width = `${shown}%`;
        if (shown >= 100) {
            clearTimeout(forceDone);
            try { sessionStorage.setItem('apex-loaded', '1'); } catch (e) { /* приватний режим */ }
            setTimeout(() => {
                preloader.classList.add('is-done');
                document.documentElement.classList.remove('is-preloading');
                startPage();
            }, 250);
            return;
        }
        requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
} else {
    requestAnimationFrame(startPage);
}

// ── Мобільне меню ─────────────────────────────────────────────────────────────
document.querySelector('[data-menu-toggle]')?.addEventListener('click', (e) => {
    const menu = document.querySelector('[data-menu]');
    const open = menu.classList.toggle('hidden') === false;
    e.currentTarget.setAttribute('aria-expanded', String(open));
});

// ── Флеш-повідомлення ────────────────────────────────────────────────────────
const flash = document.querySelector('[data-flash]');
if (flash) {
    flash.querySelector('[data-flash-close]')?.addEventListener('click', () => flash.remove());
    setTimeout(() => flash.remove(), 5000);
}

// ── Блоки, що виїжджають при прокрутці ───────────────────────────────────────
const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
        if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            revealObserver.unobserve(entry.target);
        }
    });
}, { threshold: 0.12 });

// ── Великі цифри рахують від 0 до значення (десяткові — з комою) ─────────────
const animateNumber = (el, target, decimals, duration = 1800) => {
    const format = (v) => v.toFixed(decimals).replace('.', ',');
    if (reduceMotion) { el.textContent = format(target); return; }
    const start = performance.now();
    const step = (now) => {
        const t = Math.min((now - start) / duration, 1);
        el.textContent = format(target * (1 - Math.pow(1 - t, 4)));
        if (t < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
};
const counters = [...document.querySelectorAll('[data-count-to]')];
const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        const el = entry.target;
        counterObserver.unobserve(el);
        const delay = el.closest('[data-reveal]') ? 350 : 0;
        setTimeout(() => animateNumber(el, Number(el.dataset.countTo), Number(el.dataset.decimals ?? 0)), delay);
    });
}, { threshold: 0.3 });
if (!reduceMotion) counters.forEach((el) => { el.textContent = '0'; });

// Анімації стартують лише після зникнення екрана завантаження
document.addEventListener('apex:ready', () => {
    document.querySelectorAll('[data-reveal]').forEach((el) => revealObserver.observe(el));
    counters.forEach((el) => counterObserver.observe(el));
}, { once: true });

// Повернення «Назад» з кешу браузера (bfcache): анімації запускаються знову
window.addEventListener('pageshow', (e) => {
    if (!e.persisted) return;
    document.querySelectorAll('[data-reveal]').forEach((el) => { el.classList.remove('is-visible'); revealObserver.observe(el); });
    counters.forEach((el) => { if (!reduceMotion) el.textContent = '0'; counterObserver.observe(el); });
});

// ── Слайдер на головній: плавний перехід, вкладки класів, автопрогортання ─────
const slider = document.querySelector('[data-slider]');
if (slider) {
    const all = [...slider.querySelectorAll('[data-slide]')];
    const dots = slider.querySelector('[data-dots]');
    const label = slider.querySelector('[data-counter-label]');
    let visible = all;
    let index = 0;
    let timer;

    const render = () => {
        all.forEach((slide) => {
            const active = slide === visible[index];
            slide.classList.toggle('is-active', active);
            slide.classList.toggle('opacity-0', !active);
            slide.classList.toggle('pointer-events-none', !active);
            slide.toggleAttribute('aria-hidden', !active);
            if (active) slide.querySelector('img[loading="lazy"]')?.removeAttribute('loading');
        });
        if (label) label.textContent = `${String(index + 1).padStart(2, '0')} / ${String(visible.length).padStart(2, '0')}`;
        if (dots) {
            dots.innerHTML = visible.map((_, i) =>
                `<button type="button" aria-label="Слайд ${i + 1}" class="h-0.5 rounded-full transition-all duration-500 ${i === index ? 'w-10 bg-white' : 'w-5 bg-white/35 hover:bg-white/60'}" data-dot="${i}"></button>`,
            ).join('');
        }
    };
    const go = (step) => {
        if (!visible.length) return;
        index = (index + step + visible.length) % visible.length;
        render();
    };
    const restart = () => {
        clearInterval(timer);
        if (!reduceMotion && visible.length > 1) timer = setInterval(() => go(1), 7000);
    };

    slider.querySelector('[data-prev]')?.addEventListener('click', () => { go(-1); restart(); });
    slider.querySelector('[data-next]')?.addEventListener('click', () => { go(1); restart(); });
    dots?.addEventListener('click', (e) => {
        const dot = e.target.closest('[data-dot]');
        if (dot) { index = Number(dot.dataset.dot); render(); restart(); }
    });
    slider.querySelectorAll('[data-filter]').forEach((tab) => tab.addEventListener('click', () => {
        slider.querySelectorAll('[data-filter]').forEach((t) => t.setAttribute('aria-selected', String(t === tab)));
        const f = tab.dataset.filter;
        visible = f === 'all' ? all : all.filter((s) => s.dataset.category === f);
        index = 0;
        render();
        restart();
    }));

    render();
    restart();
}

// ── Фільтри гаража застосовуються одразу після зміни ─────────────────────────
document.querySelectorAll('form[data-autosubmit]').forEach((form) => {
    form.addEventListener('change', (e) => {
        if (e.target.name === 'q') return; // пошук — по Enter або з підказок
        form.requestSubmit();
    });
});

// ── Пошук як на awwwards: при фокусі — усі заїзди, при введенні — збіги ───────
document.querySelectorAll('[data-suggest]').forEach((box) => {
    const input = box.querySelector('input');
    const list = box.querySelector('[role="listbox"]');
    const escape = (t) => t.replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const searchIcon = '<svg class="size-4 shrink-0 text-ink-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5" stroke-linecap="round"/></svg>';
    let options = [];
    let active = -1;
    let timer;
    let controller;

    const close = () => { list.hidden = true; input.setAttribute('aria-expanded', 'false'); active = -1; };
    const highlight = () => list.querySelectorAll('[data-index]').forEach((el, i) => {
        el.classList.toggle('bg-paper-200', i === active);
        el.setAttribute('aria-selected', String(i === active));
    });
    const choose = (i) => {
        const opt = options[i];
        if (!opt) return;
        if (opt.url) window.location = opt.url;
        else input.form.requestSubmit();
    };

    const render = (query, items) => {
        const safe = query.replace(/[.*+?^$(){}|[\]\\]/g, '\\$&');
        const mark = (t) => (query ? escape(t).replace(new RegExp('(' + safe + ')', 'ig'), '<mark class="rounded bg-apex-300 px-0.5 text-ink-900">$1</mark>') : escape(t));
        options = query ? [{ query }, ...items] : items;
        const heading = query ? 'Збіги' : 'Найближчі заїзди';
        list.innerHTML = options.map((opt, i) => (opt.url
            ? `<a href="${escape(opt.url)}" role="option" data-index="${i}" class="flex flex-col rounded-2xl px-4 py-3 transition hover:bg-paper-200">
                   <span class="font-semibold underline decoration-ink-900/20 underline-offset-4">${mark(opt.title)}</span>
                   <span class="mt-0.5 text-sm text-ink-600">${escape(opt.subtitle)}</span>
               </a>`
            : `<button type="button" role="option" data-index="${i}" class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 text-left transition hover:bg-paper-200">
                   ${searchIcon}<span>Шукати «<mark class="rounded bg-apex-300 px-0.5">${escape(opt.query)}</mark>» у гаражі</span>
               </button>`)
            + (i === 0 && query ? `<p class="eyebrow px-4 pb-1 pt-4 text-ink-600">${heading}</p>` : ''),
        ).join('');
        if (!query) list.insertAdjacentHTML('afterbegin', `<p class="eyebrow px-4 pb-1 pt-2 text-ink-600">${heading}</p>`);
        if (query && !items.length) list.insertAdjacentHTML('beforeend', '<p class="px-4 py-3 text-sm text-ink-600">Немає заїздів із такою назвою.</p>');
        list.hidden = false;
        input.setAttribute('aria-expanded', 'true');
        list.querySelectorAll('[data-index]').forEach((el) => el.addEventListener('mousedown', (e) => {
            e.preventDefault();
            choose(Number(el.dataset.index));
        }));
    };

    const load = () => {
        clearTimeout(timer);
        const query = input.value.trim();
        timer = setTimeout(async () => {
            controller?.abort();
            controller = new AbortController();
            try {
                const res = await fetch(`${box.dataset.suggestUrl}?q=${encodeURIComponent(query)}`, {
                    headers: { Accept: 'application/json' }, signal: controller.signal,
                });
                active = -1;
                render(query, await res.json());
            } catch (err) {
                if (err.name !== 'AbortError') close();
            }
        }, query ? 160 : 0);
    };

    input.addEventListener('focus', load);
    input.addEventListener('input', load);
    input.addEventListener('keydown', (e) => {
        if (list.hidden || !options.length) return;
        if (e.key === 'ArrowDown') { e.preventDefault(); active = (active + 1) % options.length; highlight(); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); active = (active - 1 + options.length) % options.length; highlight(); }
        else if (e.key === 'Enter' && active >= 0) { e.preventDefault(); choose(active); }
        else if (e.key === 'Escape') close();
    });
    input.addEventListener('blur', () => setTimeout(close, 120));
});

// ── Підтвердження дій у власному модальному вікні ────────────────────────────
const confirmDialog = document.createElement('dialog');
confirmDialog.className = 'm-auto w-[min(92vw,28rem)] rounded-3xl bg-paper-50 p-8 text-ink-900 shadow-2xl backdrop:bg-night-950/70 backdrop:backdrop-blur-sm';
confirmDialog.innerHTML = `
    <p class="eyebrow text-apex-700">Підтвердження</p>
    <p class="mt-4 text-lg leading-relaxed" data-text></p>
    <div class="mt-8 flex justify-end gap-3">
        <button type="button" class="btn border border-ink-900/20 hover:border-ink-900" data-cancel>Ні</button>
        <button type="button" class="btn-dark" data-ok>Так, підтверджую</button>
    </div>`;
document.body.append(confirmDialog);
let pendingForm = null;
confirmDialog.querySelector('[data-cancel]').addEventListener('click', () => confirmDialog.close());
confirmDialog.querySelector('[data-ok]').addEventListener('click', () => {
    confirmDialog.close();
    pendingForm?.submit();
});
confirmDialog.addEventListener('close', () => lenis?.start());
document.addEventListener('submit', (e) => {
    const form = e.target.closest('form[data-confirm]');
    if (!form) return;
    e.preventDefault();
    pendingForm = form;
    confirmDialog.querySelector('[data-text]').textContent = form.dataset.confirm;
    lenis?.stop();
    confirmDialog.showModal();
});
