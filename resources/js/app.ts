import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h, ref } from 'vue';
import { Toaster } from 'vue-sonner';
import 'vue-sonner/style.css';
import '../css/app.css';
import { initializeTheme } from '@/composables/useAppearance';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// ── Persistent, theme-aware Toaster ───────────────────────────────────────────
// Mounted directly on <body> so it is NEVER torn down by Inertia page navigation.
// A MutationObserver keeps the `isDark` ref in sync with the app's dark class,
// so the toast always matches the active light/dark theme.
const isDark = ref(document.documentElement.classList.contains('dark'));
new MutationObserver(() => {
    isDark.value = document.documentElement.classList.contains('dark');
}).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

const toasterEl = document.createElement('div');
toasterEl.id = 'toaster-root';
document.body.appendChild(toasterEl);

createApp({
    render: () => h(Toaster, {
        richColors: true,
        theme: isDark.value ? 'dark' : 'light',
        position: 'top-right',
        expand: false,
        visibleToasts: 5,
        offset: '24px',
        toastOptions: {
            style: {
                borderRadius: '12px',
                fontSize: '14px',
                fontWeight: '500',
                padding: '12px 16px',
                boxShadow: '0 6px 24px rgba(0,0,0,0.12), 0 2px 6px rgba(0,0,0,0.06)',
            },
        },
    }),
}).mount(toasterEl);
// ─────────────────────────────────────────────────────────────────────────────

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();
