<template>
    <div class="flex gap-1 items-center p-0.5">
        <div class="form-control w-full">
            <label class="input-group input-group-sm">
                <span class="flex-grow-0 text-xs">{{ label }}</span>
                <textarea v-model="value" :placeholder="placeholder" rows="1" class="textarea textarea-bordered p-2 leading-tight min-h-[2rem] flex-grow" :dir="isRtl? 'rtl' : 'ltr'" ref="autoHeight" @input="SetHeight"/>
            </label>
        </div>
        <div v-if="$slots.tools" class="flex-grow-0 flex gap-1">
            <slot name="tools" :value="value" :updateValue="(v) => emit('update:modelValue', v)"></slot>
        </div>
    </div>
</template>

<script setup>
import {computed, onMounted, ref} from 'vue';

const props = defineProps({
    label: {
        type: String,
        required: true
    },
    isRtl: {
        type: Boolean,
        default: false
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
const autoHeight = ref(null)
function SetHeight(e) {
    let target = e.target;
    target.style.height = 'auto';
    target.style.height = target.scrollHeight + 2 + 'px'
}
onMounted(() => SetHeight({target:autoHeight.value}))
</script>
