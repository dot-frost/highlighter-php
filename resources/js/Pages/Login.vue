<template>
    <h3 class="my-4 text-2xl font-semibold text-gray-700">Account Login</h3>
    <form @submit.prevent="login" class="flex flex-col gap-2">
        <div class="form-control w-full">
            <label  for="email" class="label">
                <span class="label-text text-sm font-semibold text-gray-500">Email address</span>
            </label>
            <input type="email"
                   id="email"
                   autofocus
                   v-model="form.email"
                   class="input input-bordered w-full"
                   :class="{'input-error': form.errors.email}"
            >
            <label class="label" v-if="form.errors.email">
                <span class="label-text-alt text-red-600">{{ form.errors.email }}</span>
            </label>
        </div>
        <div class="form-control w-full">
            <label  for="password" class="label">
                <span class="label-text text-sm font-semibold text-gray-500">Password</span>
                <a href="#" class="label-text-alt text-sm text-blue-600 hover:underline focus:text-blue-800">Forgot Password?</a>
            </label>
            <input type="password"
                   id="password"
                   v-model="form.password"
                   class="input input-bordered w-full"
                   :class="{'input-error': form.errors.password}"
            >
            <label class="label" v-if="form.errors.password">
                <span class="label-text text-red-600">{{ form.errors.password }}</span>
            </label>
        </div>
        <div>
            <button type="submit" class="btn w-full no-animation" :class="{'loading disabled' : form.processing}">
                Log in
            </button>
        </div>
    </form>
</template>

<script>
import Auth from "../Layouts/Auth";
import { useForm } from "@inertiajs/inertia-vue3";

export default {
    setup(_, ctx) {
        const form = useForm({
            email: null,
            password: null,
        })
        return {
            form,
            login() {
                form.cancel()
                form.post(route('login'))
            }
        }
    },
    name: "Login",
    layout: Auth,
}
</script>

<style scoped>

</style>
