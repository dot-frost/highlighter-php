<template>
    <div class="fixed z-10 top-0 left-0 right-0 bg-white border-b-4 border-b-gray-700">
        <div id="toolbar" class="w-full flex justify-center items-stretch p-2 gap-2">
            <label class="absolute left-2 top-1/2 transform -translate-y-1/2">
                Book: Test
            </label>
            <div id="page-tools" class="flex justify-between gap-2">
                <div class="btn-group">
                    <Link class="btn btn-sm btn-outline btn-secondary" :href="$route('books.pages.index', book.id)">
                        <i class="fas fa-book"></i>
                    </Link>
                    <Link role="button" class="btn btn-sm btn-outline btn-primary" v-if="previous"
                          :href="$route('books.pages.show', [book.id, previous.id])">
                        <i class="fa-solid fa-arrow-left"></i>
                    </Link>
                    <Link role="button" class="btn btn-sm btn-outline btn-primary" v-if="next"
                          :href="$route('books.pages.show', [book.id, next.id])">
                        <i class="fa-solid fa-arrow-right"></i>
                    </Link>
                </div>
                <div class="dropdown">
                    <label tabindex="0" class="btn btn-xs m-1">
                        <i class="fa" :class="{
                                    'fa-circle': page.status === 'INITIAL',
                                    'fa-clock': page.status === 'PENDING',
                                    'fa-check': page.status === 'DONE',
                                    'fa-check-double': page.status === 'APPROVED',
                                }"></i>
                    </label>
                    <div tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box gap-1" v-if="hasRole('Super-Admin') || (can('pages.update', page.id) &&  page.status !== 'APPROVED')">
                        <button v-if="hasRole('Super-Admin') || (can('pages.update', page.id) &&  page.status !== 'APPROVED')" class="btn btn-xs" @click="()=>setStatus(page,'INITIAL')">
                            <i class="fa fa-circle"></i>
                        </button>
                        <button v-if="hasRole('Super-Admin') || (can('pages.update', page.id) &&  page.status !== 'APPROVED')" class="btn btn-xs" @click="()=>setStatus(page,'PENDING')">
                            <i class="fa fa-clock"></i>
                        </button>
                        <button v-if="hasRole('Super-Admin') || (can('pages.update', page.id) &&  page.status !== 'APPROVED')" class="btn btn-xs" @click="()=>setStatus(page,'DONE')">
                            <i class="fa fa-check"></i>
                        </button>
                        <button v-if="hasRole('Super-Admin')" class="btn btn-xs" @click="()=>setStatus(page,'APPROVED')">
                            <i class="fa fa-check-double"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="divider divider-horizontal m-0"></div>
            <div id="tools">
                <div class="btn-group" role="group" aria-label="Basic example">
                    <button type="button" class="btn btn-sm btn-outline btn-primary btn-active" id="highlight-tool">
                        <i class="fas fa-highlighter"></i>
                    </button>
                </div>
            </div>
            <div class="divider divider-horizontal m-0"></div>
            <div id="tool-options" class="flex justify-start items-center gap-1">
                <div class="flex items-stretch option highlight-tool">
                    <button type="button" class="btn btn-sm btn-outline btn-primary"
                            :class="{ 'btn-active': tools.highlight.timer}" id="highlight-tool-timer"
                            @click="tools.highlight.timer = true">
                        <i class="fas fa-clock"></i>
                    </button>
                </div>
                <div class="flex items-stretch option highlight-tool">
                    <input type="color" v-model="tools.highlight.color" ref="highlight_tool_color"
                           id="highlight-tool-color">
                </div>
                <div class="flex items-stretch option highlight-tool">
                    <input type="range" class="range range-xs" id="highlight-tool-opacity" min="0" max="100"
                           v-model="tools.highlight.opacity">
                </div>
                <div class="option highlight-tool">
                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-sm btn-outline">
                            <i class="fas fa-language"></i>
                        </label>
                        <ul tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-52">
                            <li v-for="lang in tools.highlight.languages">
                                <label class="swap swap-flip">
                                    <input type="checkbox" v-model="lang.checked"/>
                                    <div class="swap-on">
                                        {{ lang.name }}
                                    </div>
                                    <div class="swap-off">
                                        <del>{{ lang.name }}</del>
                                    </div>
                                </label>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <section id="editor-container" class="mt-14 min-h-[calc(100vh-3.5rem)]">
        <canvas id="canvas" ref="canvas" class="w-full h-full"></canvas>
    </section>
</template>

<script>
import {Link} from "@inertiajs/inertia-vue3";
import Editor from "../../editor/Editor";
import NProgress from 'nprogress'

export default {
    name: "Show",
    props: {
        page: Object,
        book: Object,
        next: Object,
        previous: Object,
    },
    components: {
        Link
    },
    data() {
        return {
            tools: {
                highlight: {
                    timer: false,
                    color: "#00f5f0",
                    opacity: 50,
                    languages: [
                        {
                            name: "Persian",
                            code: "fa",
                            checked: false,
                        },
                        {
                            name: "English",
                            code: "en",
                            checked: false,
                        },
                    ],
                }
            },
        }
    },
    watch: {
        'tools.highlight.color': function (val) {
            window.editor.selectedSetColor(val)
        },
        'tools.highlight.opacity': function (val) {
            window.editor.selectedSetOpacity(val / 100);
        },
    },
    methods: {
        can(model, permission, id = null) {
            return this.$store.auth.can(model, permission, id);
        },
        hasRole(role) {
            return this.$store.auth.roles.some(r => r === role);
        },
        createEditor(image) {
            window.highlights = JSON.parse(JSON.stringify(this.page.highlights || []))
            window.editor = new Editor({
                canvas: this.$refs.canvas,
                image,
                getColor: () => this.tools.highlight.color,
                getOpacity: () => this.tools.highlight.opacity / 100,
                highlights: window.highlights,
            });
            window.editor.on('addHighlight', ()=>{
                if (window.lastCall) {
                    clearTimeout(window.lastCall);
                }
                this.saveHighlights()
            });
            window.editor.on(['removeHighlight', 'updateHighlight'], ()=>{
                if (window.lastCall) {
                    clearTimeout(window.lastCall);
                }
                window.lastCall = setTimeout(this.saveHighlights, 500);
            });

            window.editor.on(['select', 'selectUpdate'], this.updateHighlightTool);

            window.addEventListener('keydown', function (e) {
                if (['-', '+'].includes(e.key)) {
                    e.preventDefault();
                    let width = document.body.scrollWidth * 0.01;
                    let height = document.body.scrollHeight * 0.01;
                    let pos = {
                        x: window.mousePosition.x / width,
                        y: window.mousePosition.y / height,
                    }
                    window.editor.zoom = window.editor.zoom + parseFloat(e.key + 0.05);
                    window.dispatchEvent(new Event('resize'));
                    setTimeout(() => {
                        let width = document.body.scrollWidth * 0.01;
                        let height = document.body.scrollHeight * 0.01;
                        let scrollX = (pos.x * width) - window.innerWidth / 2;
                        let scrollY = (pos.y * height) - window.innerHeight / 2;
                        window.scroll({
                            left: scrollX,
                            top: scrollY,
                            behavior: 'instant'
                        });
                    }, 0);
                }
                if (e.key === 'Delete' && window.editor.selected) {
                    window.editor.selected.destroy();
                }
            });
            window.addEventListener('mousemove', function (e) {
                window.mousePosition = {
                    x: e.offsetX,
                    y: e.offsetY,
                }
            });
        },
        updateHighlightTool(highlight){
            this.tools.highlight.color = highlight.color;
            this.tools.highlight.opacity = highlight.opacity * 100;
            if (highlight.data.phrase_id){
                if (!window.phraseWindow || window.phraseWindow.closed){
                    window.phraseWindow = window.open('', '_phrase', 'status=0,title=0,width=500,height=500');
                }
                $('<a>').attr('target', '_phrase').attr('href', route('phrases.edit', highlight.data.phrase_id)).text('highlight.phrase')[0].click()
            }
        },
        saveHighlights(highlight) {
            let highlights = editor.highlightsToJson()

            window.newHighlight = window.highlights.length < highlights.length;
            window.highlights = highlights;

            if (window.newHighlight) {
                const latestHighlight = editor.lastHighlightGetImageData()
                const canvas = document.createElement('canvas');
                const image = new Image();
                image.src = latestHighlight;
                const sendHighlights = this.sendHighlights
                image.onload = function () {
                    canvas.width = image.width;
                    canvas.height = image.height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(image, 0, 0);
                    canvas.toBlob((blob) => {
                        sendHighlights(highlights, blob);
                    })
                }
            } else {
                this.sendHighlights(highlights);
            }
        },
        sendHighlights(highlights, image = null) {
            const formData = new FormData();
            image && formData.append('image', image);
            formData.append('highlights', JSON.stringify(highlights));
            formData.append('_method', 'PUT');

            fetch(route('books.pages.update', [this.book.id, this.page.id]), {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            }).then(response => response.json()).then(json => {
                if (json.success && window.newHighlight) {
                    if (typeof window.words === 'undefined') {
                        window.words = {
                            text: '',
                            highlights: []
                        };
                    }
                    window.words.text += json.text.replace(/\n/g, '') + ' ';
                    window.words.highlights.push(highlights[highlights.length - 1]);
                    if (!this.tools.highlight.timer) {
                        let form = $('<form>')
                        form.hide()
                        form.attr('action', route('books.pages.last-text', [this.book.id, this.page.id])).attr('method', 'POST').attr('target', '_LastText')
                        form.append($('<input>').attr('type', 'text').attr('name', '_token').val($('meta[name="csrf-token"]').attr('content')))
                        form.append($('<input>').attr('type', 'text').attr('name', 'text').val(window.words.text))
                        form.append($('<input>').attr('type', 'text').attr('name', 'languages[fa]').val(~~this.tools.highlight.languages.find(l=>l.code === 'fa').checked))
                        form.append($('<input>').attr('type', 'text').attr('name', 'languages[en]').val(~~this.tools.highlight.languages.find(l=>l.code === 'en').checked))
                        form.append($('<textarea>').attr('name', 'highlights').val(JSON.stringify(window.words.highlights)))
                        form.appendTo('body')

                        let map = window.open('', '_LastText', 'status=0,title=0,width=500,height=500');
                        window.mapp = map;
                        if (map) {
                            form.submit()
                            form.remove()
                            window.words.text = ''
                            window.words.highlights = []
                        }
                    }
                    this.tools.highlight.timer = false
                }
            })
        },
        setStatus(page, status) {
            NProgress.start()
            axios.post(route('books.pages.status',[this.book.id,page.id]), {status}).then(() => {
                page.status = status;
            }).catch(() => {
                NProgress.set(0)
            }).finally(() => {
                NProgress.done();
            })
        }
    },
    mounted() {
        let image = new Image();
        image.src = this.page.imageUrl;
        image.onload = () => {
            console.log(image);
            this.createEditor(image);
        }
        $(this.$refs.highlight_tool_color).spectrum({
            color: this.tools.highlight.color,
            showAlpha: false,
            showInput: true,
            showPalette: true,
            showSelectionPalette: true,
            showButtons: false,
            move: (color) => {
                this.tools.highlight.color = color.toHexString();
            }
        });
    }
}
</script>

<style scoped>

</style>
