import { initThemeToggle } from './components/theme-toggle.js';
import { initPasswordToggles } from './components/password-toggle.js';
import { initSidebar } from './modules/nav.js';
import { initHealthCheck } from './modules/health-check.js';

function boot() {
    initThemeToggle();
    initPasswordToggles();
    initSidebar();
    initHealthCheck();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}
