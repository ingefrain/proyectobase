(() => {
    const shell = document.querySelector('.admin-shell');
    const toggle = document.querySelector('[data-menu-toggle]');
    const close = document.querySelector('[data-drawer-close]');

    if (!shell || !toggle || !close) return;

    const setOpen = (open) => {
        shell.classList.toggle('drawer-open', open);
        toggle.setAttribute('aria-expanded', String(open));
        if (open) close.focus();
        else toggle.focus();
    };

    toggle.addEventListener('click', () => setOpen(!shell.classList.contains('drawer-open')));
    close.addEventListener('click', () => setOpen(false));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && shell.classList.contains('drawer-open')) setOpen(false);
    });
})();
