
import 'bootstrap';

document.querySelectorAll('[data-admin-theme-toggle]').forEach((toggle) => {
    const themeLabel = toggle.querySelector('[data-admin-theme-label]');
    const themeIcon = toggle.querySelector('[data-admin-theme-icon]');

    const updateThemeControl = () => {
        const isDark = document.documentElement.dataset.adminTheme === 'dark';
        themeLabel.textContent = isDark ? 'Light theme' : 'Dark theme';
        themeIcon.classList.toggle('bi-sun', isDark);
        themeIcon.classList.toggle('bi-moon-stars', !isDark);
    };

    updateThemeControl();
    toggle.addEventListener('click', () => {
        const nextTheme = document.documentElement.dataset.adminTheme === 'dark' ? 'light' : 'dark';
        document.documentElement.dataset.adminTheme = nextTheme;

        try {
            localStorage.setItem('admin-theme', nextTheme);
        } catch {
            // Keep this change active for the current page when storage is unavailable.
        }

        updateThemeControl();
    });
});

document.querySelectorAll('[data-sales-history-columns]').forEach((controls) => {
    controls.querySelectorAll('[data-sales-column-toggle]').forEach((toggle) => {
        const updateColumnVisibility = () => {
            const column = toggle.value;

            document.querySelectorAll(`[data-sales-history-column="${column}"]`).forEach((cell) => {
                cell.hidden = !toggle.checked;
            });
        };

        toggle.addEventListener('change', updateColumnVisibility);
        updateColumnVisibility();
    });
});

document.querySelectorAll('form[data-live-search]').forEach((form) => {
    const searchInput = form.querySelector('input[name="search"]');

    if (!searchInput) {
        return;
    }

    let searchTimeout;

    searchInput.addEventListener('input', () => {
        window.clearTimeout(searchTimeout);
        searchTimeout = window.setTimeout(() => form.requestSubmit(), 350);
    });
});
