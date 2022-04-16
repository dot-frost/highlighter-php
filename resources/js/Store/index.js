import { reactive } from 'vue'
const sidebar = reactive({
    isOpen: false,
    toggle(){
        this.isOpen = !this.isOpen
    },
    activeRoute: '',
})
const auth = reactive({
    isLoggedIn: false,
    user: {},
    permissions: [],
    roles: [],
    can(model, action, id = null){
        if(this.permissions.includes('*')) return true
        if(this.permissions.includes(`${model}`)) return true
        if(this.permissions.includes(`${model}.*`)) return true
        if(this.permissions.includes(`${model}.${action}`)) return true
        if(this.permissions.includes(`${model}.${action}.*`)) return true
        if(id && this.permissions.includes(`${model}.${action}.${id}`)) return true
        return false
    }
})
export default reactive({
    template: {
        theme: 'light',
        sidebar
    },
    auth
})
