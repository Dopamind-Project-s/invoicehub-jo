document.addEventListener('DOMContentLoaded', () => {
    const root = document.documentElement;
    const savedTheme = localStorage.getItem('invoicehub-theme') || 'light';
    root.dataset.theme = savedTheme;

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const next = root.dataset.theme === 'dark' ? 'light' : 'dark';
            root.dataset.theme = next;
            localStorage.setItem('invoicehub-theme', next);
        });
    });

    document.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            document.getElementById('appSidebar')?.classList.toggle('is-open');
        });
    });
});
