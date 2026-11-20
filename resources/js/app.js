// Клієнтські сценарії APEX TRACK: меню, «виїжджаючі» блоки, слайдер, спідометр.

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// Мобільне меню
document.querySelector('[data-menu-toggle]')?.addEventListener('click', (e) => {
    const menu = document.querySelector('[data-menu]');
    const open = menu.classList.toggle('hidden') === false;
    e.currentTarget.setAttribute('aria-expanded', String(open));
});


// Флеш-повідомлення: закрити вручну або автоматично через 5 с
const flash = document.querySelector('[data-flash]');
if (flash) {
    flash.querySelector('[data-flash-close]')?.addEventListener('click', () => flash.remove());
    setTimeout(() => flash.remove(), 5000);
}

// Блоки, що виїжджають при прокрутці
const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
        if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            revealObserver.unobserve(entry.target);
        }
    });
}, { threshold: 0.15 });
document.querySelectorAll('[data-reveal]').forEach((el) => revealObserver.observe(el));

// Слайдер на головній: стрілки, індикатори, вкладки класів, автопрогортання
const slider = document.querySelector('[data-slider]');
if (slider) {
    const all = [...slider.querySelectorAll('[data-slide]')];
    const dots = slider.querySelector('[data-dots]');
    let visible = all;
    let index = 0;
    let timer;

    const render = () => {
        all.forEach((slide) => { slide.hidden = true; });
        const current = visible[index];
        if (current) {
            current.hidden = false;
            if (!reduceMotion) {
                current.animate(
                    [{ opacity: 0, transform: 'translateX(40px)' }, { opacity: 1, transform: 'none' }],
                    { duration: 600, easing: 'cubic-bezier(.2,.7,.2,1)' },
                );
            }
        }
        if (dots) {
            dots.innerHTML = visible.map((_, i) =>
                `<button type="button" aria-label="Слайд ${i + 1}" class="h-1 rounded-full transition-all duration-500 ${i === index ? 'w-10 bg-white' : 'w-6 bg-white/25 hover:bg-white/50'}" data-dot="${i}"></button>`,
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

// Великі цифри рахують від 0 до значення, коли з'являються на екрані (десяткові — з комою)
const animateNumber = (el, target, decimals, duration = 1800) => {
    const format = (v) => v.toFixed(decimals).replace('.', ',');
    if (reduceMotion) { el.textContent = format(target); return; }
    const start = performance.now();
    const tick = (now) => {
        const t = Math.min((now - start) / duration, 1);
        el.textContent = format(target * (1 - Math.pow(1 - t, 4)));
        if (t < 1) requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
};

// Лічильники: одразу ставимо 0 і запускаємо, коли число видно (і батьківський блок уже «виїхав»)
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

const startCounters = () => counters.forEach((el) => {
    if (!reduceMotion) el.textContent = '0';
    counterObserver.observe(el);
});
startCounters();

// Повернення «Назад» з кешу браузера (bfcache): анімації запускаються знову
window.addEventListener('pageshow', (e) => {
    if (!e.persisted) return;
    document.querySelectorAll('[data-reveal]').forEach((el) => { el.classList.remove('is-visible'); revealObserver.observe(el); });
    startCounters();
});

// Підказки пошуку: власний випадний список зі збігами з бази (замість історії браузера)
document.querySelectorAll('[data-suggest]').forEach((box) => {
    const input = box.querySelector('input');
    const list = box.querySelector('[role="listbox"]');
    const escape = (t) => t.replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    let items = [];
    let active = -1;
    let timer;
    let controller;

    const close = () => { list.hidden = true; input.setAttribute('aria-expanded', 'false'); active = -1; };
    const highlight = () => list.querySelectorAll('li').forEach((li, i) => {
        li.classList.toggle('bg-paper-100', i === active);
        li.setAttribute('aria-selected', String(i === active));
    });

    const render = (query) => {
        if (!items.length) {
            list.innerHTML = `<li class="px-4 py-3 text-sm text-ink-600">Немає збігів для «${escape(query)}»</li>`;
        } else {
            const safe = query.replace(/[.*+?^$(){}|[\]\\]/g, '\\$&');
            const re = new RegExp('(' + safe + ')', 'ig');
            list.innerHTML = items.map((it, i) => `
                <li role="option" id="sugg-${i}" class="cursor-pointer px-4 py-3 transition hover:bg-paper-100" data-index="${i}">
                    <p class="font-semibold">${escape(it.title).replace(re, '<mark class="bg-apex-300/60 rounded px-0.5">$1</mark>')}</p>
                    <p class="text-sm text-ink-600">${escape(it.subtitle)}</p>
                </li>`).join('');
        }
        list.hidden = false;
        input.setAttribute('aria-expanded', 'true');
    };

    input.addEventListener('input', () => {
        clearTimeout(timer);
        const query = input.value.trim();
        if (query.length < 2) { close(); return; }
        timer = setTimeout(async () => {
            controller?.abort();
            controller = new AbortController();
            try {
                const res = await fetch(`${box.dataset.suggestUrl}?q=${encodeURIComponent(query)}`, {
                    headers: { Accept: 'application/json' }, signal: controller.signal,
                });
                items = await res.json();
                active = -1;
                render(query);
            } catch (err) {
                if (err.name !== 'AbortError') close();
            }
        }, 200);
    });

    input.addEventListener('keydown', (e) => {
        if (list.hidden || !items.length) return;
        if (e.key === 'ArrowDown') { e.preventDefault(); active = (active + 1) % items.length; highlight(); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); active = (active - 1 + items.length) % items.length; highlight(); }
        else if (e.key === 'Enter' && active >= 0) { e.preventDefault(); window.location = items[active].url; }
        else if (e.key === 'Escape') close();
    });

    list.addEventListener('mousedown', (e) => {
        const li = e.target.closest('[data-index]');
        if (li) window.location = items[Number(li.dataset.index)].url;
    });
    input.addEventListener('blur', () => setTimeout(close, 150));
});

// Підтвердження дій (бронювання, скасування, видалення) у власному модальному вікні
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
document.addEventListener('submit', (e) => {
    const form = e.target.closest('form[data-confirm]');
    if (!form) return;
    e.preventDefault();
    pendingForm = form;
    confirmDialog.querySelector('[data-text]').textContent = form.dataset.confirm;
    confirmDialog.showModal();
});
