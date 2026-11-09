/**
 * Busca de pessoas para vinculação a usuários
 */
export function initPessoaSearch() {
    const searchInput = document.getElementById('pessoa_search');
    const pessoaIdInput = document.getElementById('pessoa_id');
    const resultsContainer = document.getElementById('pessoa_search_results');
    const statusSpan = document.getElementById('pessoa_search_status');

    if (!searchInput || !pessoaIdInput || !resultsContainer) {
        return;
    }

    let debounceTimer = null;
    let lastQuery = '';

    const showResults = (pessoas) => {
        if (pessoas.length === 0) {
            resultsContainer.innerHTML = '<div class="form__suggestion-item">Nenhuma pessoa encontrada</div>';
            resultsContainer.style.display = 'block';
            return;
        }

        resultsContainer.innerHTML = pessoas.map(pessoa => `
            <div class="form__suggestion-item" data-id="${pessoa.id}" data-nome="${pessoa.nome}" data-cpf="${pessoa.cpf || ''}" data-cnpj="${pessoa.cnpj || ''}" data-email="${pessoa.email_principal || ''}">
                <strong>${pessoa.nome}</strong>
                <span class="text-muted">${pessoa.tipo === 'PF' ? 'CPF: ' + pessoa.cpf : 'CNPJ: ' + pessoa.cnpj}</span>
                ${pessoa.email_principal ? `<span class="text-muted">${pessoa.email_principal}</span>` : ''}
            </div>
        `).join('');

        resultsContainer.style.display = 'block';

        // Adicionar evento de clique nos resultados
        resultsContainer.querySelectorAll('.form__suggestion-item[data-id]').forEach(item => {
            item.addEventListener('click', () => {
                selectPessoa(item.dataset);
            });
        });
    };

    const selectPessoa = (pessoa) => {
        searchInput.value = pessoa.nome + (pessoa.cpf ? ' (CPF: ' + pessoa.cpf + ')' : (pessoa.cnpj ? ' (CNPJ: ' + pessoa.cnpj + ')' : ''));
        pessoaIdInput.value = pessoa.id;
        resultsContainer.style.display = 'none';
        resultsContainer.innerHTML = '';
        statusSpan.textContent = 'Pessoa selecionada';
        statusSpan.className = 'input-group__text text-success';
        searchInput.classList.remove('is-invalid');
        searchInput.classList.add('is-valid');
    };

    const doSearch = async (query) => {
        if (query.length < 2) {
            resultsContainer.style.display = 'none';
            return;
        }

        statusSpan.textContent = 'Buscando...';
        statusSpan.className = 'input-group__text text-muted';

        try {
            const response = await fetch(`/admin/usuarios/buscar-pessoas?q=${encodeURIComponent(query)}`);
            const data = await response.json();
            showResults(data.pessoas || []);
            statusSpan.textContent = data.pessoas?.length ? `${data.pessoas.length} resultado(s)` : 'Nenhum resultado';
            statusSpan.className = 'input-group__text ' + (data.pessoas?.length ? 'text-success' : 'text-warning');
        } catch (error) {
            console.error('Erro ao buscar pessoas:', error);
            resultsContainer.innerHTML = '<div class="form__suggestion-item form__error">Erro ao buscar</div>';
            resultsContainer.style.display = 'block';
            statusSpan.textContent = 'Erro na busca';
            statusSpan.className = 'input-group__text text-danger';
        }
    };

    searchInput.addEventListener('input', () => {
        const query = searchInput.value.trim();
        
        // Se o usuário limpou a seleção, limpar o hidden input
        if (query === '' && pessoaIdInput.value !== '') {
            pessoaIdInput.value = '';
            searchInput.classList.remove('is-valid', 'is-invalid');
            statusSpan.textContent = '';
            statusSpan.className = 'input-group__text';
        }

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            if (query !== lastQuery) {
                lastQuery = query;
                doSearch(query);
            }
        }, 300);
    });

    // Fechar resultados ao clicar fora
    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
            resultsContainer.style.display = 'none';
        }
    });

    // Navegação por teclado
    searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            resultsContainer.style.display = 'none';
        }
    });
}