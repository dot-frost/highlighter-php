<template>
    <form class="container min-h-screen p-2 space-y-2 relative" @submit.prevent="updatePhrase">
        <div class="flex flex-col justify-items-stretch gap-2">
            <div class="form-control">
                <label for="text" class="label">
                    <label class="label-text" for="text">Text:</label>
                    <label class="label-text-alt gap-1 flex flex-row-reverse">
                        <button type="button" class="btn btn-xs glass text-gray-700" id="translate-text" @click="translateText">
                            <i class="fas fa-globe-americas"></i>
                        </button>
                        <Voice :voices="form.voice">
                            <template v-slot="{ isPlaying, activeVoice, play }">
                                <button type="button" class="btn btn-xs glass text-gray-700 gap-1"
                                        :class="{'bg-red-400 hover:bg-red-400': form.voice.collins === false, 'bg-green-400 hover:bg-green-400': activeVoice === 'collins' && isPlaying}"
                                        @click="() => playAudio('collins', play)">
                                    Collins
                                    <i class="fas fa-volume-high"></i>
                                </button>
                                <button type="button" class="btn btn-xs glass text-gray-700 gap-1"
                                        :class="{'bg-red-400 hover:bg-red-400': form.voice.google === false, 'bg-green-400 hover:bg-green-400': activeVoice === 'google' && isPlaying}"
                                        @click="() => playAudio('google', play)">
                                    Google
                                    <i class="fas fa-volume-high"></i>
                                </button>
                            </template>
                        </Voice>
                    </label>
                </label>
                <textarea name="text" id="text" class="textarea h-full" rows="auto " v-model="form.text" @select="selectionSetText"></textarea>
            </div>
            <div class="form-control">
                <label class="label-text" for="meaning-fa">Meaning Persian:</label>
                <textarea name="meaning[fa]" id="meaning-fa" class="textarea" dir="rtl" rows="1" v-model="form.meaning.fa"></textarea>
            </div>
            <div class="form-control">
                <label class="label-text" for="meaning-en">Meaning English:</label>
                <textarea name="meaning[en]" id="meaning-en" class="textarea" rows="1" v-model="form.meaning.en"></textarea>
            </div>
        </div>
        <div class="flex flex-col gap-2">
            <Information v-model:data="form.phraseData" v-model:phrase-type="form.phraseType"/>
        </div>
        <div class="flex justify-evenly gap-1">
            <button type="submit" class="btn btn-sm btn-outline gap-2 w-1/2">
                Save
                <i class="fas fa-save"></i>
            </button>
            <Link :href="$route('phrases.destroy', phrase.id)" :data="{'_method': 'DELETE'}" method="post" type="submit" class="btn btn-sm btn-outline gap-2 w-1/2 btn-error">
                Delete
                <i class="fas fa-trash"></i>
            </Link>
        </div>
        <SelectionToolbar/>
    </form>
</template>

<script>
import { useForm } from "@inertiajs/inertia-vue3";
import { Link } from "@inertiajs/inertia-vue3";
import SelectionToolbar , { selectionSetText } from "../../Components/SelectionToolbar";
import Translate from "../../Utilities/Translatio";
import Information from "../../Components/Phrases/Information";
import Voice from "../../Components/Phrases/Voice";

export default {
    setup({ phrase }) {
        const editForm = useForm({
            _method: "put",
            text: phrase.phrase,
            meaning: phrase.information.meaning,
            exercise: phrase.information.exercise,
            voice: phrase.information.voice || {google: "", collins: ""},
            phraseType: phrase.information.type,
            phraseData: phrase.information.data,
        })
        return {
            form: editForm,
            updatePhrase:()=>{
                editForm.post(route('phrases.update', phrase.id), {
                    preserveState: true,
                    preserveScroll: true,
                    onBefore: (form) => {
                        window.oldData = JSON.stringify(form.data);
                    },
                })
            },
            selectionSetText
        }
    },
    name: "Edit",
    props: {
        phrase: Object,
    },
    components: {
        Voice,
        Information,
        Link,
        SelectionToolbar
    },
    watch: {
        form: {
            deep: true,
            handler(form) {
                if (window.oldData === JSON.stringify(form.data())) {
                    return;
                }
                window.lastCallUpdate && clearTimeout(window.lastCallUpdate)
                window.lastCallUpdate = setTimeout(() => {
                    this.updatePhrase()
                }, 1000)
            }
        }
    },
    methods: {
        openTranslateText(text, website, language) {
            new Translate(website, {text, to: language, from: 'de'}).openWindow()
        },
        translateText() {
            for ( let lang in this.form.meaning ){
                new Translate('google', {text: this.form.text, to: lang, from: 'auto'})
                    .translate(res => {
                        this.form.meaning[lang] = res.sentences[0].trans
                    })
            }
        },
        getAudio(text, source, set){
            axios.get(route('getVoice'), {
                params: {
                    text,
                    clients: [source],
                }
            }).then(res => {
                set(res.data.voices[source])
            })
        },
        playAudio(source, play){
            this.getAudio(this.form.text, source, (url) => {
                if (! url.endsWith('mp3')) return this.form.voice[source] = false
                this.form.voice[source] = url
                play(source)
            })
        },
    },
}
</script>
