<template>
    <div class="flex gap-1 items-center p-1">
        <div class="flex-grow form-control">
            <label class="input-group input-group-sm">
                <span class="flex-grow-0 text-xs">{{ label }}</span>
                <input type="text" v-model="value" :placeholder="placeholder" class="input input-bordered input-sm flex-grow w-full" />
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
    placeholder: {
        type: String,
        required: false
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

<script>

</script>
