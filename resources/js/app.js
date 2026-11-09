function initSidebar() {
    const sidebar = document.getElementById('app-sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');
    const toggle = document.getElementById('sidebar-toggle');

    if (!sidebar || !backdrop || !toggle) {
        return;
    }

    const setOpen = (open) => {
        sidebar.classList.toggle('-translate-x-full', !open);
        backdrop.classList.toggle('hidden', !open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        toggle.setAttribute('aria-label', open ? 'Close navigation' : 'Open navigation');
    };

    toggle.addEventListener('click', () => {
        setOpen(sidebar.classList.contains('-translate-x-full'));
    });

    backdrop.addEventListener('click', () => setOpen(false));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setOpen(false);
        }
    });

    sidebar.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            if (window.matchMedia('(max-width: 1023px)').matches) {
                setOpen(false);
            }
        });
    });
}

function initPasswordToggle() {
    const button = document.getElementById('toggle-password');
    const input = document.getElementById('password');

    if (!button || !input) {
        return;
    }

    button.addEventListener('click', () => {
        const showing = input.type === 'text';
        input.type = showing ? 'password' : 'text';
        button.textContent = showing ? 'Show' : 'Hide';
        button.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
    });
}

function boot() {
    initSidebar();
    initPasswordToggle();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}
