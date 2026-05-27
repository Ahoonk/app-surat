import '../css/app.css'

import { createInertiaApp, router } from '@inertiajs/vue3'
import { createApp, h } from 'vue'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import AOS from 'aos'
import 'aos/dist/aos.css'

const aosOptions = {
    once: true,
    duration: 900,
    easing: 'ease-out-cubic',
    offset: 120,
    mirror: false,
}

const refreshAos = () => {
    requestAnimationFrame(() => {
        AOS.refreshHard()
    })
}

createInertiaApp({
    title: (title) => (title ? `${title} | ${import.meta.env.VITE_APP_NAME || 'PT ASKARYA'}` : (import.meta.env.VITE_APP_NAME || 'PT ASKARYA')),
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el)

        AOS.init(aosOptions)
        refreshAos()
    },
    progress: {
        color: '#38bdf8',
    },
})

router.on('navigate', refreshAos)
