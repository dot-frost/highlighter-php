<template>
    <div class="flex h-screen overflow-y-hidden bg-white">
        <!-- Sidebar backdrop -->
        <div
            v-show="$store.template.sidebar.isOpen"
            class="fixed inset-0 z-50 bg-black bg-opacity-20 lg:hidden backdrop-blur-md"
        ></div>
        <!-- Sidebar -->
        <Transition enter-active-class="transition transform duration-300"
                    enter-from-class="-translate-x-full opacity-30  ease-in"
                    enter-to-class="translate-x-0 opacity-100 ease-out"
                    leave-active-class="transition transform duration-300"
                    leave-from-class="translate-x-0 opacity-100 ease-out"
                    leave-to-class="-translate-x-full opacity-0 ease-in">
           <Sidebar/>
        </Transition>

        <div class="flex flex-col flex-1 h-full overflow-hidden">
            <!-- Navbar -->
            <header class="flex-shrink-0 border-b">
                <div class="flex items-center justify-between p-2">
                    <!-- Navbar left -->
                    <div class="flex items-center space-x-3">
                        <span class="p-2 text-xl font-semibold tracking-wider uppercase lg:hidden">Nix Ford</span>
                        <!-- Toggle sidebar button -->
                        <button @click="$store.template.sidebar.toggle()" class="p-2 rounded-md focus:outline-none focus:ring">
                            <i class="fa-solid fa-angles-right w-4 h-4 text-gray-600" :class="{'transform transition-transform -rotate-180': $store.template.sidebar.isOpen}"></i>
                        </button>
                    </div>

                    <!-- Navbar right -->
                    <div class="relative flex items-center space-x-3">
                    </div>
                </div>
            </header>
            <!-- Main content -->
            <main class="p-5 max-h-full overflow-auto">
               <slot/>
            </main>
        </div>
    </div>
</template>

<script>
import Sidebar from "../Components/Sidebar";
export default {
    components: {Sidebar},
    computed: {
        store() {
            return this.$store;
        }
    },
    name: "Dashboard",

}
</script>

<style scoped>

</style>
