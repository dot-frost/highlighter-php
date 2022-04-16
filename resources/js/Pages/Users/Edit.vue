<template>
    <div class="flex justify-between">
    <h1 class="text-2xl font-bold">
        Edit User
    </h1>
    <div>
        <Link :href="$route('users.index')" class="text-blue-500 hover:text-blue-700">
            Back
        </Link>
    </div>
</div>
    <div class="divider"></div>
    <div>
        <form @submit.prevent="updateUser">
            <div class="form-control w-full">
                <label class="label">
                    <span class="label-text">Name</span>
                </label>
                <input type="text" class="input input-bordered w-full" :class="{ 'input-error': form.errors.name }" v-model="form.name">
                <label class="label" v-if="form.errors.name">
                    <span class="label-text-alt text-red-600">{{ form.errors.name }}</span>
                </label>
            </div>
            <div class="form-control w-full">
                <label class="label">
                    <span class="label-text">Email</span>
                </label>
                <input type="email" class="input input-bordered w-full" :class="{ 'input-error': form.errors.email }" v-model="form.email">
                <label class="label" v-if="form.errors.email">
                    <span class="label-text-alt text-red-600">{{ form.errors.email }}</span>
                </label>
            </div>
            <div class="form-control w-full">
                <label class="label">
                    <span class="label-text">Password</span>
                </label>
                <input type="password" class="input input-bordered w-full" :class="{ 'input-error': form.errors.password }" v-model="form.password">
                <label class="label" v-if="form.errors.password">
                    <span class="label-text-alt text-red-600">{{ form.errors.password }}</span>
                </label>
            </div>
            <div class="form-control w-full">
                <label class="label">
                    <span class="label-text">Confirm Password</span>
                </label>
                <input type="password" class="input input-bordered w-full" :class="{ 'input-error': form.errors.password_confirmation }" v-model="form.password_confirmation">
                <label class="label" v-if="form.errors.password_confirmation">
                    <span class="label-text-alt text-red-600">{{ form.errors.password_confirmation }}</span>
                </label>
            </div>
            <button type="submit" class="btn btn-primary w-full mt-3">
                Update
            </button>
        </form>
    </div>
</template>

<script>
import Dashboard from "../../Layouts/Dashboard";
import { Link } from "@inertiajs/inertia-vue3";
import { useForm } from "@inertiajs/inertia-vue3";

export default {
    setup({ user }) {
        const editForm = useForm({
            _method: "PUT",
            name: user.name,
            email: user.email,
            password: "",
            password_confirmation: "",
        });
        return {
            form: editForm,
            updateUser() {
                editForm.post(route("users.update", user.id));
            }
        }
    },
    name: "Show",
    layout: Dashboard,
    props: {
        user: Object
    },
    components: {
        Link
    },
}
</script>

<style scoped>

</style>
