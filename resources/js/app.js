const THEME_KEY = 'theme';
const LEGACY_THEME_KEY = 'admin_theme';

const themeManager = {
    stored() {
        return localStorage.getItem(THEME_KEY) ?? localStorage.getItem(LEGACY_THEME_KEY);
    },

    current() {
        return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
    },

    isDark() {
        return this.current() === 'dark';
    },

    apply(mode) {
        const root = document.documentElement;
        root.classList.toggle('dark', mode === 'dark');
        root.classList.toggle('light', mode === 'light');
    },

    set(mode) {
        localStorage.setItem(THEME_KEY, mode);
        // Keep the admin layout's legacy key in sync.
        localStorage.setItem(LEGACY_THEME_KEY, mode);
        this.apply(mode);
        window.dispatchEvent(new CustomEvent('theme-changed', { detail: { mode } }));
    },

    toggle() {
        const next = this.isDark() ? 'light' : 'dark';
        this.set(next);

        return next;
    },
};

window.themeManager = themeManager;

document.addEventListener('click', (event) => {
    if (event.target.closest('[data-theme-toggle]')) {
        themeManager.toggle();
    }
});

// Keep tabs in sync when the preference changes elsewhere.
window.addEventListener('storage', (event) => {
    if (event.key !== THEME_KEY && event.key !== LEGACY_THEME_KEY) {
        return;
    }

    const mode = themeManager.stored();

    if (mode === 'light' || mode === 'dark') {
        themeManager.apply(mode);
    }
});

document.addEventListener('alpine:init', () => {
    window.Alpine.store('theme', {
        dark: themeManager.isDark(),
        toggle() {
            this.dark = themeManager.toggle() === 'dark';
        },
    });
});
