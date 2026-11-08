export function initPasswordToggles() {
    const buttons = document.querySelectorAll('[data-password-toggle]');

    buttons.forEach((button) => {
        const input = document.getElementById(button.dataset.passwordToggle);

        if (!input) {
            return;
        }

        button.addEventListener('click', () => {
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            button.textContent = isPassword ? 'Ocultar' : 'Mostrar';
            button.setAttribute('aria-label', isPassword ? 'Ocultar senha' : 'Mostrar senha');
        });
    });
}
