(() => {
    const page = document.querySelector('.about-page');
    const menuToggle = document.querySelector('#nav-toggle');

    if (!page) return;

    if (menuToggle) {
        document.querySelectorAll('.about-page .nav-links a').forEach((link) => {
            link.addEventListener('click', () => {
                menuToggle.checked = false;
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') menuToggle.checked = false;
        });
    }

    const revealItems = page.querySelectorAll('[data-reveal]');
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (!('IntersectionObserver' in window) || reduceMotion) {
        revealItems.forEach((item) => item.classList.add('is-visible'));
        return;
    }

    page.classList.add('about-motion-ready');

    const revealObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -30px 0px' });

    revealItems.forEach((item) => revealObserver.observe(item));
})();