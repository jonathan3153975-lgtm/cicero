const STORAGE_KEY = 'academia-theme';

function applyTheme(theme) {
    document.documentElement.dataset.theme = theme;

    try {
        localStorage.setItem(STORAGE_KEY, theme);
    } catch (error) {
        /* armazenamento indisponível */
    }
}

export function initThemeToggle() {
    const buttons = document.querySelectorAll('[data-theme-toggle]');

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            const current = document.documentElement.dataset.theme === 'light' ? 'dark' : 'light';
            applyTheme(current);
        });
    });
}
