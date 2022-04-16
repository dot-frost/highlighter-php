<template>
    <aside
        class="fixed inset-y-0 z-50 flex flex-col flex-shrink-0 w-64 max-h-screen overflow-hidden transition-all transform bg-white border-r shadow-lg lg:z-auto lg:static lg:shadow-none"
        :class="{'-translate-x-full lg:translate-x-0 lg:w-20': !$store.template.sidebar.isOpen}"
    >
        <!-- sidebar header -->
        <div class="flex items-center justify-between flex-shrink-0 p-2"
             :class="{'lg:justify-center': !$store.template.sidebar.isOpen}">
          <span class="p-2 text-xl font-semibold leading-8 tracking-wider uppercase whitespace-nowrap">
            Nix<span v-if="$store.template.sidebar.isOpen">
                Ford
            </span>
          </span>
            <button @click="!$store.template.sidebar.toggle()" class="p-2 rounded-md lg:hidden">
                <svg
                    class="w-6 h-6 text-gray-600"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <!-- Sidebar links -->
        <nav class="flex-1 overflow-hidden hover:overflow-y-auto">
            <ul class="p-2 overflow-hidden flex flex-col gap-2">
                <li v-for="link in links" :key="link.route">
                    <Link :href="link.route"
                          class="flex items-center px-2 py-4 rounded-md hover:bg-gray-300 gap-2"
                          :class="{'justify-center': !$store.template.sidebar.isOpen, 'bg-gray-300': $page.url ? $route().current(link.name) : false}">
                        <span><i class="fa fa-lg text-gray-600" :class="link.icon"></i></span>
                        <span :class="{ 'lg:hidden': !$store.template.sidebar.isOpen }">{{ link.title }}</span>
                    </Link>
                </li>
                <!-- Sidebar Links... -->
            </ul>
        </nav>
        <!-- Sidebar footer -->
        <div class="flex-shrink-0 p-2 border-t max-h-14 mb-2">
            <Link :href="$route('logout')" method="post" class="btn btn-outline w-full px-4 py-2 font-medium uppercase">
                <i class="fa fa-sign-out-alt fa-fw fa-lg text-gray-600"></i>
                <span :class="{'lg:hidden': !$store.template.sidebar.isOpen}"> Logout </span>
            </Link>
        </div>
    </aside>
</template>

<script>
import { Link } from '@inertiajs/inertia-vue3'
export default {
    setup() {
        return {
            menu: [
                {
                    title: 'Dashboard',
                    name: 'home',
                    icon: 'fa-home',
                    route: route('home')
                },
                {
                    title: 'Books',
                    name: 'books.*',
                    icon: 'fa-book',
                    model: 'books',
                    action: 'read',
                    route: route('books.index')
                },
                {
                    title: 'Users',
                    name: 'users.*',
                    icon: 'fa-users',
                    model: 'users',
                    action: 'read',
                    route: route('users.index')
                }
            ]
        }
    },
    name: "Sidebar",
    props: {
        isSidebarOpen: {
            type: Boolean,
            default: false
        }
    },
    components: {
        Link
    },
    computed: {
        links() {
            return this.menu.filter(link => {
                if (!link.model) return true
                if (this.$store.auth.can(link.model, link.action)) return true
                return false
            })
        }
    },
    methods: {
        logout() {
            this.$inertia.post(route('logout'))
        }
    }
}
</script>

<style scoped>

</style>
