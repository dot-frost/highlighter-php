<template>
    <div class="flex flex-col justify-start items-center max-h-full min-h-full py-2 gap-4">
        <div class="modal" :class="{'modal-open': isOpenModalSetPermission}">
            <div class="modal-box overflow-visible  w-fit max-w-5xl relative">
                <label class="btn btn-sm btn-circle absolute -right-2 -top-2" @click="isOpenModalSetPermission = false">✕</label>
                <div class="modal-content max-w-full max-h-full overflow-auto">
                    <table class="table table-compact">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>
                                    View
                                    <label class="swap">
                                        <input type="checkbox" value="view" ref="permission-view"
                                               :indeterminate="!(users.every(u => u.permissions.includes('view')) || users.every(u => !u.permissions.includes('view')))"
                                               :checked="users.every(u => u.permissions.includes('view'))"
                                               @change="()=> togglePermissionForAll('view', $refs['permission-view'].checked)"
                                        >
                                        <i class="swap-on fa fa-lock-open"></i>
                                        <i class="swap-off fa fa-lock"></i>
                                        <i class="swap-indeterminate fa fa-minus"></i>
                                    </label>
                                </th>
                                <th>
                                    Edit
                                    <label class="swap">
                                        <input type="checkbox" value="edit" ref="permission-edit"
                                               :indeterminate="!(users.every(u => u.permissions.includes('edit')) || users.every(u => !u.permissions.includes('edit')))"
                                               :checked="users.every(u => u.permissions.includes('edit'))"
                                               @change="()=> togglePermissionForAll('edit', $refs['permission-edit'].checked)"
                                        >
                                        <i class="swap-on fa fa-lock-open"></i>
                                        <i class="swap-off fa fa-lock"></i>
                                        <i class="swap-indeterminate fa fa-minus"></i>
                                    </label>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(user, index) in users" :key="user.id">
                                <td>{{ user.name }}</td>
                                <td>{{ user.email }}</td>
                                <td>
                                    <label class="swap">
                                        <input type="checkbox" value="view" v-model="user.permissions"/>
                                        <i class="swap-on fa fa-lock-open"></i>
                                        <i class="swap-off fa fa-lock"></i>
                                    </label>
                                </td>
                                <td>
                                    <label class="swap">
                                        <input type="checkbox" value="edit" v-model="user.permissions"/>
                                        <i class="swap-on fa fa-lock-open"></i>
                                        <i class="swap-off fa fa-lock"></i>
                                    </label>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="flex justify-between w-full">
            <h1 class="text-4xl font-bold">
                {{ book.title }} Pages
            </h1>
            <div class="flex justify-evenly gap-2">
                <button v-if="hasRole('Super-Admin')" class="btn btn-primary" :disabled="checkedPages.length === 0" @click="isOpenModalSetPermission = true">
                    <i class="fa fa-lock"></i>
                </button>
                <form v-if="can('pages', 'read', checkedPages[0] ?? null)" :action="$route('phrases.extract')" method="post" target="_blank">
                    <input type="hidden" name="_token" :value="$csrf_token">
                    <input v-for="page in checkedPages" :key="`checked_page_${page}`" type="hidden" name="pages[]" :value="page">
                    <button type="submit" class="btn btn-primary" :disabled="checkedPages.length === 0">
                        <i class="fa fa-print"></i>
                    </button>
                </form>
                <Link v-if="can('pages', 'create')" class="btn btn-success" >
                    <i class="fas fa-plus"></i>
                    Add Page
                </Link>
            </div>
        </div>
        <div class="flex-grow max-h-full overflow-auto">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 xl:grid-cols-6 gap-2 pb-16">
                <div class="card bg-base-100 shadow-xl image-full card-bordered border-gray-300 bg-base-100 shadow-xl" v-for="page in book.pages" :key="`page-${page.id}`">
                        <figure><img :src="page.imageThumbnail300Url"  :alt="page.number"/></figure>
                        <div class="card-body justify-between items-center">
                            <div class="card-actions flex justify-between items-center w-full">
                                <div class="dropdown">
                                    <label tabindex="0" class="btn btn-xs m-1">
                                        <i class="fa" :class="{
                                    'fa-circle': page.status === 'INITIAL',
                                    'fa-clock': page.status === 'PENDING',
                                    'fa-check': page.status === 'DONE',
                                    'fa-check-double': page.status === 'APPROVED',
                                }"></i>
                                    </label>
                                    <div tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box gap-1" v-if="hasRole('Super-Admin') || (can('pages.update', page.id) &&  page.status !== 'APPROVED')">
                                        <button v-if="hasRole('Super-Admin') || (can('pages.update', page.id) &&  page.status !== 'APPROVED')" class="btn btn-xs" @click="()=>setStatus(page,'INITIAL')">
                                            <i class="fa fa-circle"></i>
                                        </button>
                                        <button v-if="hasRole('Super-Admin') || (can('pages.update', page.id) &&  page.status !== 'APPROVED')" class="btn btn-xs" @click="()=>setStatus(page,'PENDING')">
                                            <i class="fa fa-clock"></i>
                                        </button>
                                        <button v-if="hasRole('Super-Admin') || (can('pages.update', page.id) &&  page.status !== 'APPROVED')" class="btn btn-xs" @click="()=>setStatus(page,'DONE')">
                                            <i class="fa fa-check"></i>
                                        </button>
                                        <button v-if="hasRole('Super-Admin')" class="btn btn-xs" @click="()=>setStatus(page,'APPROVED')">
                                            <i class="fa fa-check-double"></i>
                                        </button>
                                    </div>
                                </div>
                                <input type="checkbox" class="checkbox checkbox-sm checkbox-accent" :value="page.id" v-model="checkedPages">
                            </div>
                            <h2 class="card-title">{{ page.number }}</h2>
                            <div class="card-actions justify-center flex-nowrap justify-evenly">
                                <Link v-if="can('pages.read', page.id)" :href="$route('books.pages.show', [book.id, page.id])" class="btn btn-primary">
                                    <i class="fas fa-edit"></i>
                                </Link>
                                <Link  v-if="can('pages.destroy', page.id)" as="button" :href="$route('books.pages.destroy', [book.id, page.id])" method="delete" class="btn btn-error">
                                    <i class="fas fa-trash-alt"></i>
                                </Link>
                            </div>
                        </div>
            </div>
            </div>
        </div>
    </div>
</template>

<script>
import Dashboard from "../../Layouts/Dashboard";
import { Link } from "@inertiajs/inertia-vue3";
import NProgress from 'nprogress'
import {reactive} from "vue";

export default {
    setup({ users }) {
        users.forEach(user => {
            user.permissions || (user.permissions = [])
            user.permissions = user.permissions.map(p => {
                return typeof p === 'string' ? p : p.name
            })
        });
        users = reactive(users);

        return {
            users: users,
            setPermissionForSelectedPages: reactive(JSON.parse(JSON.stringify(users))),
        }
    },
    watch: {
        users: {
            handler(users) {
                if (window.lastSetPermission) clearTimeout(window.lastSetPermission);
                window.lastSetPermission = setTimeout(()=>{
                    NProgress.start();
                    this.checkedPages.forEach(pageId => {
                        axios.post(route('permissions.page.set.users', pageId), { users }).then(()=>{
                            NProgress.done();
                        });
                    });
                }, 500);
            },
            deep: true
        }
    },
    name: "Index",
    layout: Dashboard,
    props: {
        book: Object,
        users: Array,
    },
    components: {
        Link
    },
    data() {
        return {
            checkedPages: [],
            isOpenModalSetPermission: false,
        }
    },
    methods:{
        can(model, permission, id = null) {
            return this.$store.auth.can(model, permission, id);
        },
        hasRole(role) {
            return this.$store.auth.roles.some(r => r === role);
        },
        setStatus(page, status) {
            NProgress.start()
            axios.post(route('books.pages.status',[this.book.id,page.id]), {status}).then(() => {
                page.status = status;
            }).catch(() => {
                NProgress.set(0)
            }).finally(() => {
                NProgress.done();
            })
        },
        togglePermissionForAll(permission, checked) {
            this.users.forEach(user => {
                if (checked) {
                    user.permissions.push(permission);
                } else {
                    user.permissions = user.permissions.filter(p => p !== permission);
                }
            });
        },
    }
}
</script>

<style scoped>

</style>
