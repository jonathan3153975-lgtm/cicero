/**
 * Pessoas Index - AJAX Search + Mobile Sticky Search Behavior
 * 
 * Features:
 * - AJAX search without page reload
 * - Debounced search on input
 * - Enter key triggers search
 * - Clear button resets search
 * - Mobile sticky search bar
 */
export function initPessoasSearch() {
    const searchCard = document.querySelector('.card--search');
    const resultsCard = document.querySelector('.card--results');
    const pessoasGrid = document.getElementById('pessoas-grid');
    const searchInput = document.getElementById('busca');
    const btnBuscar = document.getElementById('btn-buscar');
    const btnLimpar = document.getElementById('btn-limpar');
    const resultsContainer = document.getElementById('pessoas-results-card');
    
    if (!searchCard || !resultsCard || !resultsContainer || !searchInput) {
        return;
    }

    let isSticky = false;
    let searchCardHeight = 0;
    let resultsCardTop = 0;
    let debounceTimer = null;
    let abortController = null;

    // ==========================================================================
    // Sticky Search Behavior (Mobile)
    // ==========================================================================
    const updateMeasurements = () => {
        searchCardHeight = searchCard.offsetHeight;
        const resultsCardRect = resultsCard.getBoundingClientRect();
        resultsCardTop = resultsCardRect.top + window.scrollY;
    };

    const handleStickyScroll = () => {
        if (window.innerWidth >= 768) {
            if (isSticky) {
                searchCard.classList.remove('is-sticky');
                resultsCard.classList.remove('has-sticky-search');
                document.body.style.paddingTop = '';
                isSticky = false;
            }
            return;
        }

        const scrollY = window.scrollY;
        const shouldBeSticky = scrollY >= resultsCardTop - searchCardHeight;

        if (shouldBeSticky && !isSticky) {
            searchCard.classList.add('is-sticky');
            resultsCard.classList.add('has-sticky-search');
            document.body.style.paddingTop = searchCardHeight + 'px';
            isSticky = true;
        } else if (!shouldBeSticky && isSticky) {
            searchCard.classList.remove('is-sticky');
            resultsCard.classList.remove('has-sticky-search');
            document.body.style.paddingTop = '';
            isSticky = false;
        }
    };

    const handleResize = () => {
        updateMeasurements();
        handleStickyScroll();
    };

    // Initial measurements
    updateMeasurements();
    
    // Listen for scroll and resize
    window.addEventListener('scroll', handleStickyScroll, { passive: true });
    window.addEventListener('resize', handleResize);

    // ==========================================================================
    // AJAX Search
    // ==========================================================================
    const performSearch = async (searchTerm) => {
        // Cancel previous request
        if (abortController) {
            abortController.abort();
        }
        abortController = new AbortController();

        // Show loading state
        resultsContainer.classList.add('is-loading');
        btnBuscar.disabled = true;
        btnBuscar.innerHTML = '<svg class="spinner" viewBox="0 0 24 24" aria-hidden="true" width="18" height="18"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="30 100" stroke-linecap="round"><animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/></svg>';

        try {
            const url = new URL('/admin/pessoas', window.location.origin);
            url.searchParams.set('busca', searchTerm);
            
            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                },
                signal: abortController.signal,
            });

            if (!response.ok) {
                throw new Error('Erro na busca');
            }

            const html = await response.text();
            
            // Update results container
            resultsContainer.innerHTML = html;
            
            // Update URL without reload
            const newUrl = searchTerm ? `?busca=${encodeURIComponent(searchTerm)}` : window.location.pathname;
            window.history.replaceState({}, '', newUrl);
            
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error('Erro na busca:', error);
                resultsContainer.innerHTML = `
                    <div class="pessoas-empty">
                        <svg viewBox="0 0 24 24" aria-hidden="true" class="pessoas-empty__icon"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <h3>Erro ao buscar</h3>
                        <p>Tente novamente em alguns instantes.</p>
                    </div>
                `;
            }
        } finally {
            resultsContainer.classList.remove('is-loading');
            btnBuscar.disabled = false;
            btnBuscar.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true" width="18" height="18"><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg><span class="btn__text">Buscar</span>';
        }
    };

    const debouncedSearch = (searchTerm) => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            performSearch(searchTerm);
        }, 300);
    };

    // Search button click
    btnBuscar?.addEventListener('click', () => {
        const term = searchInput.value.trim().toLowerCase(); // Case-insensitive
        performSearch(term);
    });

    // Clear button click
    btnLimpar?.addEventListener('click', () => {
        searchInput.value = '';
        performSearch('');
        searchInput.focus();
    });

    // Enter key in search input
    searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const term = searchInput.value.trim().toLowerCase(); // Case-insensitive
            performSearch(term);
        }
    });

    // Debounced search on input (optional - comment out if you want only button/enter)
    // searchInput.addEventListener('input', (e) => {
    //     const term = e.target.value.trim().toLowerCase();
    //     debouncedSearch(term);
    // });

    // Cleanup function (for SPA navigation if needed)
    window.cleanupPessoasSearch = () => {
        window.removeEventListener('scroll', handleStickyScroll);
        window.removeEventListener('resize', handleResize);
        clearTimeout(debounceTimer);
        if (abortController) {
            abortController.abort();
        }
        searchCard.classList.remove('is-sticky');
        resultsCard.classList.remove('has-sticky-search');
        document.body.style.paddingTop = '';
        isSticky = false;
    };
}