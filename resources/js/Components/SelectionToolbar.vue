<template>
    <div v-if="selection.isSelected" class="absolute p-1 flex justify-between glass rounded-md gap-2 bg-black transform -translate-x-1/2 -translate-y-full text-white hover:text-gray-900" :style="selection.position">
        <div v-if="false" class="btn-group items-center">
            <div class="dropdown">
                <label tabindex="0" class="btn btn-xs m-1">{{ selection.translator }}</label>
                <ul tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box text-gray-900">
                    <li>
                        <label class="label">
                            <input type="radio" name="translator" value="google" v-model="selection.translator">
                            Google
                        </label>
                    </li>
                    <li>
                        <label class="label">
                            <input type="radio" name="translator" value="collins" v-model="selection.translator">
                            Collins
                        </label>
                    </li>
                    <li>
                        <label class="label">
                            <input type="radio" name="translator" value="bamooz" v-model="selection.translator">
                            B-Amooz
                        </label>
                    </li>
                </ul>
            </div>
            <label class="swap swap-flip">
                <input type="checkbox" v-model="selection.lang" true-value="en" false-value="fa" style="display: block" >
                <span class="swap-on">EN</span>
                <span class="swap-off">FA</span>
            </label>
            <button type="button" class="btn btn-xs p-1 btn-success" @click="()=>openTranslateText(selection.text, selection.translator, selection.lang)">
                <i class="fa fa-language"></i>
            </button>
        </div>
        <div class="flex items-center gap-1">
            <div class="btn-group">
                <button type="button" class="btn btn-xs p-1 btn-success" @click="()=>openTranslateText(selection.text, 'google', 'en')">
                    G-<i class="fa fa-language"></i>-EN
                </button>
                <button type="button" class="btn btn-xs p-1 btn-success" @click="()=>openTranslateText(selection.text, 'google', 'fa')">
                    G-<i class="fa fa-language"></i>-FA
                </button>
            </div>
            <button type="button" class="btn btn-xs p-1 btn-success" @click="()=>openTranslateText(selection.text, 'collins', 'en')">
                C-<i class="fa fa-language"></i>-EN
            </button>
            <button type="button" class="btn btn-xs p-1 btn-success" @click="()=>openTranslateText(selection.text, 'bamooz', 'fa')">
                B-<i class="fa fa-language"></i>-FA
            </button>
        </div>
    </div>
</template>

<script>
import {reactive, ref} from 'vue';
import Translate from "../Utilities/Translatio";
const position = ref({
    top: 0,
    left: 0,
});

const selection = reactive({
    isSelected: false,
    element: null,
    position,
    text: '',
    lang: 'fa',
    translator: 'google',
});

export const selectionSetText = (event) => {
    selection.text = window.getSelection().toString()
    selection.isSelected = true
    const target = event.target
    selection.target = target
    selection.position.top = (target.offsetTop) + 'px'
    selection.position.left = (target.offsetLeft + target.offsetWidth / 2) + 'px'
}
export default {
    setup() {
        return {
            selection,
            openTranslateText(text, website, language) {
                new Translate(website, {text, to: language, from: 'de'}).openWindow({})
            },
        }
    },
    name: "SelectionToolbar",
    mounted() {
        document.onselectionchange = (e) => {
            const selection = window.getSelection()
            const range = selection.getRangeAt(0)
            const isSelected = selection.toString().length > 0
            const elements = [
                range.startContainer,
                ... (range.startContainer.childNodes || [])
            ]
            this.selection.isSelected = false
            elements.forEach(element => {
                if (this.selection.isSelected) return
                this.selection.isSelected = isSelected && element === this.selection.target
            })
        }
    },
}
</script>

<style scoped>

</style>
