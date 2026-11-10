import { initThemeToggle } from './components/theme-toggle.js';
import { initPasswordToggles } from './components/password-toggle.js';
import { initInputMasks } from './components/input-masks.js';
import { initRichTextEditors } from './components/rich-text.js';
import { initLoadingOverlay } from './components/loading-overlay.js';
import { initDependentSelects } from './components/dependent-selects.js';
import { initPessoaSearch } from './components/pessoa-search.js';
import { initPessoasSearch } from './components/pessoas-search.js';
import { initSidebar } from './modules/nav.js';
import { initHealthCheck } from './modules/health-check.js';

function boot() {
    initThemeToggle();
    initPasswordToggles();
    initInputMasks();
    initRichTextEditors();
    initLoadingOverlay();
    initDependentSelects();
    initPessoaSearch();
    initPessoasSearch();
    initSidebar();
    initHealthCheck();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}
