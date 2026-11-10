export function initDependentSelects() {
    // Rede -> Região
    document.querySelectorAll('[data-rede-select]').forEach((redeSelect) => {
        const form = redeSelect.closest('form');
        const regiaoSelect = form?.querySelector('[data-regiao-select]');

        if (!regiaoSelect) {
            return;
        }

        const filterRegions = () => {
            const redeId = redeSelect.value;
            const selected = regiaoSelect.value;
            let selectedIsValid = selected === '';

            Array.from(regiaoSelect.options).forEach((option) => {
                if (option.value === '') {
                    return;
                }

                const matches = option.dataset.redeId === redeId;
                option.hidden = !matches;
                option.disabled = !matches;

                if (matches && option.value === selected) {
                    selectedIsValid = true;
                }
            });

            if (!selectedIsValid) {
                regiaoSelect.value = '';
            }
        };

        redeSelect.addEventListener('change', filterRegions);
        filterRegions();
    });

    // Região -> Unidade
    document.querySelectorAll('[data-regiao-select]').forEach((regiaoSelect) => {
        const form = regiaoSelect.closest('form');
        const unidadeSelect = form?.querySelector('#unidade_id');
        const redeSelect = form?.querySelector('[data-rede-select]');

        if (!unidadeSelect) {
            return;
        }

        const filterUnidades = () => {
            const regiaoId = regiaoSelect.value;
            const redeId = redeSelect?.value ?? '';
            const selected = unidadeSelect.value;
            let selectedIsValid = selected === '';

            Array.from(unidadeSelect.options).forEach((option) => {
                if (option.value === '') {
                    return;
                }

                const optionRegiaoId = option.dataset.regiaoId;
                const optionRedeId = option.dataset.redeId;
                
                // Se região selecionada, filtra por região
                // Senão, se rede selecionada, filtra por rede
                // Senão, mostra todas
                let matches = false;
                if (regiaoId !== '') {
                    matches = optionRegiaoId === regiaoId;
                } else if (redeId !== '') {
                    matches = optionRedeId === redeId;
                } else {
                    matches = true;
                }

                option.hidden = !matches;
                option.disabled = !matches;

                if (matches && option.value === selected) {
                    selectedIsValid = true;
                }
            });

            if (!selectedIsValid) {
                unidadeSelect.value = '';
            }
        };

        regiaoSelect.addEventListener('change', filterUnidades);
        // Also filter when rede changes
        redeSelect?.addEventListener('change', filterUnidades);
        filterUnidades();
    });
}
