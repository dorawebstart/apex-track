// Клієнтські сценарії APEX TRACK: меню, «виїжджаючі» блоки, слайдер, спідометр.

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// Мобільне меню
document.querySelector('[data-menu-toggle]')?.addEventListener('click', (e) => {
    const menu = document.querySelector('[data-menu]');
    const open = menu.classList.toggle('hidden') === false;
    e.currentTarget.setAttribute('aria-expanded', String(open));
});

// Шапка темнішає після прокрутки
const header = document.querySelector('[data-header]');
window.addEventListener('scroll', () => {
    header?.classList.toggle('bg-night-950/85', window.scrollY > 40);
    header?.classList.toggle('backdrop-blur-lg', window.scrollY > 40);
}, { passive: true });

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

// Плавна анімація числа від 0 до target
const animateNumber = (el, target, decimals = 0, duration = 1600) => {
    if (reduceMotion) { el.textContent = target.toFixed(decimals); return; }
    const start = performance.now();
    const tick = (now) => {
        const t = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - t, 3);
        el.textContent = (target * eased).toFixed(decimals);
        if (t < 1) requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
};

// Спідометр: стрілка від −90° до +90°, дуга заповнюється, лічильник рахує до максимальної швидкості
const gaugeObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        const gauge = entry.target;
        const speed = Number(gauge.dataset.speed);
        const ratio = Math.min(speed / Number(gauge.dataset.max), 1);
        const needle = gauge.querySelector('[data-needle]');
        const arc = gauge.querySelector('[data-arc]');
        const easing = 'cubic-bezier(.25,.9,.3,1)';

        needle.style.transition = reduceMotion ? 'none' : `transform 1.6s ${easing}`;
        arc.style.transition = reduceMotion ? 'none' : `stroke-dashoffset 1.6s ${easing}`;
        requestAnimationFrame(() => {
            needle.style.transform = `rotate(${-90 + ratio * 180}deg)`;
            arc.style.strokeDashoffset = String(100 - ratio * 100);
        });
        animateNumber(gauge.querySelector('[data-counter]'), speed);
        gauge.closest('.card')?.querySelectorAll('[data-count-to]').forEach((el) =>
            animateNumber(el, Number(el.dataset.countTo), Number(el.dataset.decimals ?? 0)));
        gaugeObserver.unobserve(gauge);
    });
}, { threshold: 0.4 });
document.querySelectorAll('[data-speedometer]').forEach((g) => gaugeObserver.observe(g));
