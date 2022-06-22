<template>
    <div class="p-1 bg-gray-200 rounded-md flex flex-col gap-1">
        <div class="btn-group justify-evenly w-full">
            <Confirm v-for="t in allTypes" :key="`type-${t}`" :confirm-action="()=> setType(t)" v-slot="{ open }">
                <button type="button"
                        class="btn btn-default flex-grow"
                        :class="{'btn-active': t.toLowerCase() === phraseType}"
                        v-text="t"
                        @click="()=>phraseType ? open() : setType(t)"
                />
            </Confirm>
        </div>
        <div v-if="phraseType" class="rounded bg-gray-600 text-white p-1 flex gap-1 flex-wrap">
            <template v-for="(field , key) in getFields() " :key="`button-${key}`">
                <button type="button" class="btn btn-xs" @click="()=>addField(key)">{{ field.props.label }}</button>
            </template>
        </div>
        <div v-if="phraseType" class="flex flex-col gap-2">
            <template v-for="(fields, key) in data" :key="key">
                <div class="flex flex-col gap-1 border border-gray-400 rounded-lg">
                    <div v-if="Array.isArray(fields)">
                        <template v-for="(field, index) in fields" :key="key + index">
                            <Component :is="getComponent(key)" v-bind="getComponentProps(key)" v-model="data[key][index]" >
                                <template v-slot:tools="{ value, updateValue }">
                                    <Confirm v-if="!getField(key).isRequired" :confirm-action="() => deleteField(key, index)" v-slot="{ open }">
                                        <button type="button" class="btn btn-xs" @click="open">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </Confirm>
                                    <button v-if="getField(key).hasAutoFill" type="button" class="btn btn-xs" @click="() => fill(getField(key).fill, (vals)=> vals.forEach(val => data[key].push(val)))">
                                        <i class="fas fa-magic"></i>
                                    </button>
                                    <button v-if="getField(key).hasTranslation" type="button" class="btn btn-xs" @click="() => getField(key).translate(value)">
                                        <i class="fas fa-language"></i>
                                    </button>
                                </template>
                            </Component>
                            <div v-if="index < (Array.isArray(fields) ? fields : [fields]).length - 1" class="divider divider-primary m-1"></div>
                        </template>
                    </div>
                    <template v-else>
                        <Component :is="getComponent(key)" v-bind="getComponentProps(key)" v-model="data[key]" >
                            <template v-slot:tools="{ value, updateValue }">
                                <Confirm v-if="!getField(key).isRequired" :confirm-action="() => deleteField(key)" v-slot="{ open }">
                                    <button type="button" class="btn btn-xs" @click="open">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </Confirm>
                                <button v-if="getField(key).hasAutoFill" type="button" class="btn btn-xs" @click="() => fill(getField(key).fill, (val)=> data[key] = val)">
                                    <i class="fas fa-magic"></i>
                                </button>
                                <button v-if="getField(key).hasTranslation" type="button" class="btn btn-xs" @click="() => getField(key).translate(value)">
                                    <i class="fas fa-language"></i>
                                </button>
                            </template>
                        </Component>
                    </template>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
import types from "./Types";
import Confirm from "../Confirm";

export default {
    name: 'Information',
    components: {Confirm},
    props: {
        phrase: {
            type: String,
            required: true
        },
        phraseType : {
            type: String,
            default: null,
        },
        data: {
            type: Object,
        },
    },
    watch: {
        data: function (newData) {
            this.$emit('update:data', newData)
        },
        phraseType: function (newPhraseType) {
            this.$emit('update:phraseType', newPhraseType)
        }
    },
    data() {
        return {
            source: null,
        }
    },
    computed: {
        allTypes: () => Object.keys(types).map(t => t[0].toUpperCase() + t.slice(1)),
    },
    methods: {
        fill(collect, set) {
            if (this.source) {
                let res = collect(this.source)
                return (res instanceof Promise) ? res.then(set) : set(res)
            }
            let url = 'https://www.collinsdictionary.com/dictionary/german-english/' + this.phrase
            fetch(url, {mode: 'cors'})
                .then(response => response.text())
                .then(text => {
                    this.source = (new DOMParser).parseFromString(text, 'text/html');
                    this.fill(collect, set);
                })
        },
        setType(type) {
            type = type.toLowerCase()
            this.$emit('update:phraseType', type);
            let data = {}
            for (let key in types[type]) {
                if (types[type][key].isRequired) {
                    let value = JSON.parse(JSON.stringify(types[type][key].value))
                    if (types[type][key].isMultiple) {
                        value = [value]
                    }
                    data[key] = value
                }
            }

            this.$emit('update:data', data);
        },
        getComponent(key) {
            return types[this.phraseType][key].component
        },
        getComponentProps(key) {
            return types[this.phraseType][key].props
        },
        getFields() {
            let fields = {}
            for (let key in types[this.phraseType]) {
                if (typeof this.data[key] === 'undefined' || types[this.phraseType][key].isMultiple) {
                    fields[key] = types[this.phraseType][key]
                }
            }
            return fields
        },
        addField(key) {
            let value = types[this.phraseType][key].value

            if (typeof value === 'object') {
                value = JSON.parse(JSON.stringify(value))
            }

            if (types[this.phraseType][key].isMultiple) {
                if (! this.data[key]) {
                    this.data[key] = []
                }
                this.data[key].push(value)
            }else {
                this.data[key] = value
            }
        },
        deleteField(key, index = -1) {
            if (types[this.phraseType][key].isMultiple) {
                this.data[key].splice(index, 1)
                if (this.data[key].length === 0) delete this.data[key]
            } else {
                delete this.data[key]
            }
        },
        getField(key) {
            return types[this.phraseType][key]
        },
    },
    beforeCreate() {
        if (this.phraseType) {
            for (let key in this.data){
                if (!(key in types[this.phraseType])) {
                    delete this.data[key]
                }
            }
        }
    }
}
</script>
