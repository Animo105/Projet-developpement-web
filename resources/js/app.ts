import '../css/app.css';

import { createInertiaApp, Head } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { initializeTheme } from './composables/useAppearance';

import PrimeVue from 'primevue/config';
import Aura from '@primeuix/themes/aura';
import ToastService from 'primevue/toastservice';
import ConfirmationService from 'primevue/confirmationservice';
import Tooltip from 'primevue/tooltip';

import AppLayout from '@/layouts/AppLayout.vue';
import { Paginator } from 'primevue';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent<DefineComponent>(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ).then((page) => {
            page.default.layout = AppLayout
            return page
            }
        ),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .component('Head', Head)
            .component("Paginator", Paginator)
            .use(PrimeVue, {
                theme: {
                    preset: Aura,
                    options: {
                        prefix: 'p',
                        darkModeSelector: '.dark',
                    }
                }
            })
            .use(ToastService)
            .use(ConfirmationService)
            .directive('tooltip', Tooltip)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();
