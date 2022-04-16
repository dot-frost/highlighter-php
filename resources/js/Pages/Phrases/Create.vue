<template>
    <form class="container min-h-screen p-2 space-y-2" @submit.prevent="createPhrase">
        <div class="flex flex-col justify-items-stretch gap-2">
            <div class="form-control">
                <label for="text" class="label">
                    <label class="label-text" for="text">Text:</label>
                    <label class="label-text-alt">
                        <button type="button" class="btn btn-xs glass text-gray-700" id="translate-text" @click="translateText">
                            <i class="fas fa-globe-americas"></i>
                        </button>
                    </label>
                </label>
                <textarea name="text" id="text" class="textarea" rows="1" v-model="form.text"></textarea>
            </div>
            <div class="form-control">
                <label class="label-text" for="meaning-fa">Meaning Persian:</label>
                <textarea name="meaning[fa]" id="meaning-fa" class="textarea" dir="rtl" rows="1" v-model="form.meaning.fa"></textarea>
            </div>
            <div class="form-control">
                <label class="label-text" for="meaning-en">Meaning English:</label>
                <textarea name="meaning[en]" id="meaning-en" class="textarea" rows="1" v-model="form.meaning.en"></textarea>
            </div>
            <div class="form-control">
                <label class="label-text" for="exercise">Exercise:</label>
                <input type="text" name="exercise" id="exercise" class="input input-sm" v-model="form.exercise">
            </div>
        </div>
        <div class="w-full btn-group">
            <button type="button" class="btn btn-sm btn-primary" @click="()=>openTranslateText('google','en')">Google:
                EN
            </button>
            <button type="button" class="btn btn-sm btn-primary" @click="()=>openTranslateText('google','fa')">Google:
                FA
            </button>
        </div>
        <div class="w-full btn-group">
            <button type="button" class="btn btn-sm btn-success" @click="addOption">Add Option</button>
            <button type="button" class="btn btn-sm btn-info" @click="addExample">Add Example</button>
            <button type="button" class="btn btn-sm btn-warning" @click="addVoice">Add Voice</button>
        </div>
        <div class="flex flex-col items-stretch gap-1 bg-gray-400 rounded-lg p-3" id="informations">
            <div class="flex gap-2 pb-2 border-b-2 border-b-gray-700" v-for="(option , index )  in form.options"
                 :key="`option-${index}`">
                <div class="input-group flex-grow">
                    <select class="select select-sm w-1/4" required v-model="option.name">
                        <option value="type">Type</option>
                        <option value="gender">Gender</option>
                        <option value="plural">Plural</option>
                        <option value="past">Past</option>
                        <option value="participle">Participle</option>
                        <option value="case">Case</option>
                    </select>
                    <div class="divider divider-horizontal m-0"></div>
                    <input type="text" class="input input-sm w-full" placeholder="Option Value" required
                           v-model="option.value">
                </div>
                <button type="button" class="btn btn-sm btn-outline btn-error" @click="form.options.splice(index,1)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <div class="flex gap-2 pb-2 items-center border-b-2 border-b-gray-700"
                 v-for="(example , index )  in form.examples" :key="`example-${index}`">
                <div class="flex-grow space-y-1">
                    <input type="text" class="input input-sm w-full" placeholder="Example" required
                           v-model="example.text">
                    <div class="divider m-0"></div>
                    <input type="text" v-for="( mean , lang ) in example.meaning" :key="`mean-${lang}`"
                           class="input input-sm w-full" :dir="lang === 'fa' ? 'rtl' : 'ltr'" :placeholder="`Meaning ${lang.toUpperCase()}`" required
                           v-model="example.meaning[lang]">
                </div>
                <button type="button" class="btn btn-sm btn-outline btn-error" @click="form.examples.splice(index, 1)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <div class="flex gap-2 pb-2 border-b-2 border-b-gray-700" v-for="(voice , index )  in form.voices"
                 :key="`voice-${index}`">
                <div class="input-group flex-grow">
                    <select class="select select-sm w-1/4" required v-model="voice.name">
                        <option value="google">Google</option>
                        <option value="duden">Duden</option>
                        <option value="collins">Collins</option>
                    </select>
                    <div class="divider divider-horizontal m-0"></div>
                    <input type="text" class="input input-sm w-full" placeholder="Voice Link" required
                           v-model="voice.link">
                </div>
                <button type="button" class="btn btn-sm btn-outline btn-error" @click="form.voices.splice(index, 1)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        <button type="submit" class="btn btn-sm btn-outline btn-wide gap-2 mx-auto">
            Save
            <i class="fas fa-save"></i>
        </button>
    </form>
</template>

<script>
import {useForm} from "@inertiajs/inertia-vue3";

export default {
    setup({ page, book, text, languages, highlights, translated }) {
        const createFrom = useForm({
            page_id: page.id,
            highlights: highlights,
            text: text,
            meaning: {
                fa: '',
                en: ''
            },
            exercise: '',
            options: [],
            examples: [],
            voices: []
        })
        return {
            form: createFrom,
            createPhrase:()=>{
                createFrom.post(route('phrases.store'))
            },
            templates: {
                option: {
                    name: null,
                    value: null
                },
                example: {
                    text: null,
                    meaning: {
                        fa: null,
                        en: null
                    }
                },
                voice: {
                    name: null,
                    link: null
                }
            }
        }
    },
    name: "Create",
    watch: {
      form: {
        deep: true,
        handler(form) {
            window.lastCallCreate && clearTimeout(window.lastCallCreate)
            window.lastCallCreate = setTimeout(() => {
                this.createPhrase()
            }, 500)
        }
      }
    },
    props: {
        page: Object,
        book: Object,
        text: String,
        languages: Array,
        highlights: Array,
        translated: Object,
    },
    methods: {
        addOption() {
            this.form.options.push({
                ...this.templates.option
            })
        },
        addExample() {
            this.form.examples.push({
                ...this.templates.example,
                meaning: {
                    ...this.templates.example.meaning
                }
            })
        },
        addVoice() {
            this.form.voices.push({
                ...this.templates.voice
            })
        },
        openTranslateText(website, language) {
            switch (website) {
                case 'google':
                    let link = 'https://translate.google.com/#view=home&op=translate&sl=de&tl=' + language + '&text=' + this.form.text
                    window.open(link, '_blank', 'location=yes,height=570,width=520,scrollbars=yes,status=yes,left=500,top=0')
                    break
            }
        },
        translateText() {
            let translateUrl = `https://translate.googleapis.com/translate_a/single?client=gtx&sl=de&dt=t&dt=bd&dj=1&q=${this.text}&tl=%lang%`
            for ( let lang in this.languages ){
                fetch(translateUrl.replace('%lang%', lang))
                    .then(res => res.json())
                    .then(res => {
                        console.log(res)
                        this.form.meaning[lang] = res.sentences[0].trans
                    })
            }
        }
    },
    mounted() {
        this.translateText()
        for ( let lang in this.languages ){
            if (~~this.languages[lang]){
                this.openTranslateText('google', lang)
            }
        }
    }
}
</script>

<style scoped>

</style>
