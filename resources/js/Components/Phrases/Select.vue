<template>
    <div class="flex gap-1 items-center p-1">
        <div class="flex-grow form-control w-full">
            <label class="input-group input-group-sm">
                <span class="flex-grow-0 text-xs">{{ label }}</span>
                <select class="select select-bordered select-sm flex-grow" v-model="value">
                    <option v-for="option in options" :value="option" :key="'option-' + option">{{ option }}</option>
                </select>
            </label>
        </div>
        <div v-if="$slots.tools" class="flex-grow-0 flex gap-1">
            <slot name="tools" :value="value" :updateValue="(v) => emit('update:modelValue', v)"></slot>
        </div>
    </div>
</template>


<script setup>
import { computed } from 'vue';

const props = defineProps({
    label: {
        type: String,
        required: true
    },
    options: {
        type: Array,
        required: true
    },
    modelValue: {
        type: String,
        required: true
    }
})
const emit = defineEmits(['update:modelValue'])
const value = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
})
</script>
