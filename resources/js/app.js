import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { createPinia } from 'pinia';

const pinia = createPinia();

createInertiaApp({
    title: (title) => title ? `${title} - Dukcapil Dompu` : 'Dukcapil Dompu',
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        app.config.errorHandler = (err, instance, info) => {
            console.error('Antigravity Vue Safe Handler:', err, info);
        };
        app.use(plugin)
           .use(ZiggyVue)
           .use(pinia)
           .mount(el);

        // Smoothly dismiss the initial first-paint brand preloader
        const preloader = document.getElementById('initial-page-preloader');
        if (preloader) {
            preloader.style.opacity = '0';
            setTimeout(() => {
                if (preloader && preloader.parentNode) {
                    preloader.remove();
                }
            }, 350);
        }
    },
    progress: {
        color: 'var(--primary-500, #0e91eb)',
    },
});
