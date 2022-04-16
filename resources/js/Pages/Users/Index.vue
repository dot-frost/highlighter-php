<template>
    <div class="flex justify-between">
        <h2 class="font-bold text-2xl">Users</h2>
        <div class="btn-group">
            <Link as="button" type="button" class="btn btn-primary"
                  :href="$route('users.create')"
            >
                <i class="fas fa-plus"></i>
                Create
            </Link>
        </div>
    </div>
    <div class="overflow-x-auto w-full">
        <table class="table w-full">
            <!-- head -->
            <thead>
            <tr>
                <th>
                    <label>
                        <input type="checkbox" class="checkbox z-0">
                    </label>
                </th>
                <th>Name</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="user in users" :key="`user-${user.id}`">
                <th>
                    <label>
                        <input type="checkbox" class="checkbox">
                    </label>
                </th>
                <td>
                    <div class="flex items-center space-x-3">
                        <div>
                            <div class="font-bold">{{ user.name }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge badge-ghost badge-sm">{{ user.email }}</span>
                </td>
                <th>
                    <div class="btn-group">
                        <Link :href="$route('users.edit', user.id)"
                              as="button" type="button"
                              class="btn btn-sm btn-outline-primary"
                        >
                            <i class="fas fa-pencil-alt"></i>
                        </Link>
                        <Link :href="$route('users.destroy', user.id)"
                              method="delete" as="button" type="button"
                              class="btn btn-sm btn-outline-primary"
                        >
                            <i class="fas fa-trash"></i>
                        </link>
                    </div>
                </th>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
import Dashboard from "../../Layouts/Dashboard";
import { Link } from "@inertiajs/inertia-vue3"
export default {
    name: "Index",
    layout: Dashboard,
    props: {
        users: Array
    },
    components: {
        Link
    }
}
</script>
