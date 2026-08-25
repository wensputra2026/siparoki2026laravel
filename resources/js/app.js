import './bootstrap';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';

// Cegah browser memindahkan scroll window saat refresh (biar tidak "lompat").
if ('scrollRestoration' in history) {
    history.scrollRestoration = 'manual';
}

const appElement = document.getElementById('app');
const pages = import.meta.glob('./Pages/**/*.vue');

if (appElement && appElement.dataset.page) {
    createInertiaApp({
        title: (title) => (title ? `${title} - SIPAROKI` : 'SIPAROKI'),
        resolve: (name) => {
            const page = pages[`./Pages/${name}.vue`];

            if (!page) {
                throw new Error(`Inertia page not found: ${name}`);
            }

            return page();
        },
        setup({ el, App, props, plugin }) {
            createApp({ render: () => h(App, props) })
                .use(plugin)
                .mount(el);
        },
        progress: {
            delay: 100,
            color: '#f59e0b',
            includeCSS: true,
            showSpinner: true,
        },
    });
}
