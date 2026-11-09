export function initRichTextEditors() {
    document.querySelectorAll('[data-rich-text]').forEach((wrapper) => {
        const editor = wrapper.querySelector('[data-rich-editor]');
        const input = wrapper.querySelector('[data-rich-input]');
        const toolbar = wrapper.querySelector('.rich-text__toolbar');
        const form = wrapper.closest('form');

        if (!editor || !input) {
            return;
        }

        wrapper.classList.add('is-enhanced');
        editor.innerHTML = input.value.trim() !== '' ? input.value : '';

        const sync = () => {
            input.value = editor.innerHTML.trim();
        };

        toolbar?.querySelectorAll('[data-command]').forEach((button) => {
            button.addEventListener('click', () => {
                const command = button.dataset.command;
                const value = button.dataset.value ?? null;

                document.execCommand(command, false, command === 'formatBlock' ? value : null);
                editor.focus();
                sync();
            });
        });

        editor.addEventListener('input', sync);
        form?.addEventListener('submit', sync);
    });
}
