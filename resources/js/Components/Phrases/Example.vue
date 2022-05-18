<template>
    <div class="flex gap-1 items-center p-1">
        <div class="flex-grow flex flex-col gap-1">
            <Textarea v-model="value.text" :placeholder="label" :label="label" @select="selectionSetText"/>
            <div class="flex flex-col gap-1">
                <template  v-for="mean in value.meaning" :key="`mean-${mean.lang}`">
                    <Textarea v-model="mean.text" :is-rtl="mean.isRtl" :placeholder="`Mean ${mean.lang}`" :label="`Mean ${mean.lang}`"/>
                </template>
            </div>
        </div>
        <div v-if="$slots.tools" class="flex-grow-0 flex flex-col gap-1">
            <slot name="tools" :value="value" :updateValue="(v) => emit('update:modelValue', v)"></slot>
        </div>
    </div>
</template>

<script setup>
import { selectionSetText } from '../SelectionToolbar'
import Textarea from "./Textarea";
import {computed} from "vue";

const props = defineProps({
    label: {
        type: String,
        required: true,
    },
    modelValue: {
        type: Object,
        default: {
            text: "",
            meaning: [
                {
                    lang: null,
                    isRtl: false,
                    text: "",
                }
            ],
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

