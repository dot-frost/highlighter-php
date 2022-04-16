<template>
    <slot/>
</template>

<script>
import store from "../Store";
export default {
    setup({ auth }) {
        const setAuth = (auth) => {
            store.auth.isLoggedIn = !!auth
            store.auth.user = auth?.user ?? {}
            store.auth.permissions = auth?.permissions ?? []
            store.auth.roles = auth?.roles ?? []
        };
        setAuth(auth)
        return {
            setAuth
        }
    },
    name: "Base",
    props: ['auth'],
    watch: {
        auth() {
            this.setAuth(this.auth)
        }
    },
}
</script>
