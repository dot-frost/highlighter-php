<template>
    <slot :open="open"></slot>
    <div v-if="isOpen" class="modal modal-bottom sm:modal-middle" :class="{'modal-open': isOpen}">
        <div class="modal-box">
            <h3 class="font-bold text-lg">Are you sure?</h3>
            <div class="modal-action">
                <button type="button" class="btn" @click="confirm">Confirm</button>
                <button type="button" class="btn" @click="cancel">Cancel</button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "Confirm",
    props:{
        confirmAction: {
            type: Function,
            required: true
        },
    },
    data() {
        return {
            isOpen: false,
        }
    },
    methods: {
        open() {
            this.isOpen = true;
        },
        close() {
            this.isOpen = false;
        },
        confirm() {
            this.confirmAction();
            this.close();
        },
        cancel() {
            this.close();
        }
    }
}
</script>

<style scoped>

</style>
