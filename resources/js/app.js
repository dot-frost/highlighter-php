require('./bootstrap')
require('./vendor')
import {createApp, h} from 'vue'
import { createInertiaApp } from '@inertiajs/inertia-vue3'
import {InertiaProgress} from '@inertiajs/progress'
import * as Inertia from '@inertiajs/inertia'

import Base from "./Layouts/Base";
import Store from './Store'
import Dashboard from "./Layouts/Dashboard";
import Auth from "./Layouts/Auth";
const layouts = {
    Base,
    Dashboard,
    Auth
}
InertiaProgress.init({
    showSpinner: true,
})
createInertiaApp({
    resolve: (name, x ,y) => {
        const page = require(`./Pages/${name}`).default
        if (page.layout &&  typeof page.layout.find !== 'function') {
            page.layout = [ Base, layouts[page.layout.name] ]
        }else if (!page.layout) {
            page.layout = [ Base ]
        }
        return page
    },
    setup({el, App, props, plugin}) {
        createApp({render: () => h(App, props)})
            .use(plugin)
            .use({
                install(Vue) {
                    Vue.config.globalProperties.$store = Store
                }
            })
            .use({
                install(Vue) {
                    Vue.config.globalProperties.$route = route
                }
            })
            .use({
                install(Vue) {
                    Vue.config.globalProperties.$csrf_token = $('meta[name="csrf-token"]').attr('content')
                }
            })
            .mount(el)
    },
})
