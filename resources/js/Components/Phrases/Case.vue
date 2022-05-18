<template>
    <div class="flex gap-1 items-center p-1">
        <div class="flex-grow flex flex-row gap-1">
            <Select :options="caseOptions" v-model="value.case" label="Case" class="flex-grow"/>
            <Input v-model="value.preposition" label="Preposition" class="flex-grow"/>
        </div>
        <slot name="tools" :value="value" :updateValue="(v) => emit('update:modelValue', v)"></slot>
    </div>
</template>

<script setup>
import {computed} from "vue";
import Select from "./Select";
import Input from "./Input";

const props = defineProps({
    label: {
        type: String,
        required: true,
    },
    caseOptions: {
        type: Array,
        required: true,
    },
    modelValue: {
        type: Object,
        default: {
            case: '',
            preposition: '',
        },
        required: true,
    },
})
const emit = defineEmits(['update:modelValue']);
const value = computed({
    get() {
        return props.modelValue;
    },
    set(value) {
        this.$emit('update:modelValue', value);
    },
})

</script>

