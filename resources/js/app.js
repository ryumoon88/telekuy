import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import PrimeVue from 'primevue/config';
import Aura from '@primevue/themes/aura';
import { definePreset } from '@primevue/themes';
import { Ripple } from 'primevue';
import ConfirmationService from 'primevue/confirmationservice';
import Default from './Layouts/Default.vue';
import ToastService from 'primevue/toastservice';
import Tooltip from 'primevue/tooltip';


const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

const TelekuyPreset = definePreset(Aura, {
    semantic: {
        primary: {
            50: '{orange.50}',
            100: '{orange.100}',
            200: '{orange.200}',
            300: '{orange.300}',
            400: '{orange.400}',
            500: '{orange.500}',
            600: '{orange.600}',
            700: '{orange.700}',
            800: '{orange.800}',
            900: '{orange.900}',
            950: '{orange.950}'
        },
        colorScheme: {
            dark: {
                surface: {
                    // 0: '{blue.950}',
                    // 50: '{blue.900}',
                    // 100: '{blue.800}',
                    // 200: '{blue.700}',
                    // 300: '{blue.600}',
                    // 400: '{blue.500}',
                    // 500: '{blue.400}',
                    // 600: '{blue.300}',
                    // 700: '{blue.200}',
                    // 800: '{blue.100}',
                    // 900: '{blue.50}',
                    // 950: '#ffffff'
                    0: '#ffffff',
                    50: '{blue.50}',
                    100: '{blue.100}',
                    200: '{blue.200}',
                    300: '{blue.300}',
                    400: '{blue.400}',
                    500: '{blue.500}',
                    600: '{blue.600}',
                    700: '{blue.700}',
                    800: '{blue.800}',
                    900: '{blue.900}',
                    950: '{blue.950}'

                },
                contentBackground: '{blue.950}',
                overlay: {
                    popoverBackground: '{blue.950}'
                }
            },
        },
    },
    components: {
        menubar: {
            background: 'transparent',
            border: {
                color: 'none',
                radius: 'none'
            },
            item: {
                // color: '#FAA11A',
                // active: {
                //     color: '#FAA11A'
                // }
            },
            overlay: {
                background: '#04163D'
            }
        },
        button: {

        }
    }
});

createInertiaApp({
    title: (title) => title ? `${title} - ${appName}` : appName,
    resolve: (name) => {
        return resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ).then((page) => {
            page.default.layout = page.default.layout || Default;
            return page;
        })
        // console.log(page)
        // page.default.layout = page.default.layout || Default
        // return page;
    },
    setup({ el, App, props, plugin }) {
        el.classList.add('dark')
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(ConfirmationService)
            .use(ToastService)
            .use(PrimeVue, {
                theme: {
                    preset: TelekuyPreset,
                    options: {
                        darkModeSelector: '.dark',
                        cssLayer: {
                            name: 'primevue', //any name you want. will be referenced on app.css
                            order: 'tailwind-base, primevue, tailwind-utilities'
                        }
                    }
                },
                ripple: true
            })
            .directive('ripple', Ripple)
            .directive('tooltip', Tooltip)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

window.formatCurrency = (num, fraction = 2) => {
    let formatter = new Intl.NumberFormat("id-ID", {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: fraction
    });
    return formatter.format(num);
}

window.csrf_token = () => {
    console.log(document.querySelector('meta[name="csrf-token"]').getAttribute('content'))
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
}
