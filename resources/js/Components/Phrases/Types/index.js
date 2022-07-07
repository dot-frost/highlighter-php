import Select from './../Select'
import Input from './../Input'
import Textarea from './../Textarea'
import Checkbox from './../Checkbox'
import Example from './../Example'
import Case from './../Case'
import Translate from "../../../Utilities/Translatio";

class Field {
    type
    component
    value
    label

    isRequired = false
    isMultiple = false

    hasSelection = false
    hasAutoFill = false
    hasTranslation = false
    _fillCallback = null
    set(key, value) {
        this[key] = value
        return this
    }

    setType = type => this.set('type', type)
    setComponent = component => this.set('component', component)
    setValue = value => this.set('value', value)
    setLabel = label => this.set('label', label)

    setIsRequired = isRequired => this.set('isRequired', isRequired)
    setIsMultiple = isMultiple => this.set('isMultiple', isMultiple)

    setHasSelection = hasSelection => this.set('hasSelection', hasSelection)
    setHasTranslation = hasTranslation => this.set('hasTranslation', hasTranslation)
    translate = () => {
        throw new Error('Not implemented')
    }
    setFillCallback = (fillCallback) => {
        this.hasAutoFill = true ;
        return this.set('_fillCallback', fillCallback)
    }
    fill = (source) => {
        if (! source instanceof HTMLDocument) throw new Error('Source must be an HTMLDocument')
        const metaOGURL = source.querySelector('meta[property="og:url"]')
        if (! metaOGURL) throw new Error('No metas found')
        const url = metaOGURL.content
        const word = url.split('/').pop()
        const mainContent = source.querySelector('#main_content')
        if (! mainContent) throw new Error('No main content found')
        return this._fillCallback(url, word, mainContent)
    }
}
class SelectField extends Field {
    options
    isMultipleSelect = false

    constructor(label, options, value = null) {
        super()
        this.setType('select')
            .setComponent(Select)
            .setLabel(label)
            .setOptions(options)
            .setValue(value)
            .setIsMultipleSelect(Array.isArray(value))
    }
    setOptions = options => this.set('options', options)
    setIsMultipleSelect = isMultipleSelect => this.set('isMultipleSelect', isMultipleSelect)

    get props() {
        return {
            label: this.label,
            options: this.options,
            isMultipleSelect: this.isMultipleSelect,
        }
    }
}
class InputField extends Field {
    constructor(label, value = null) {
        super();
        this.setType('input')
            .setComponent(Input)
            .setLabel(label)
            .setValue(value)
    }
    get props() {
        return {
            label: this.label,
            placeholder: this.label,
        }
    }
}
class TextareaField extends Field {
    constructor(label, value = null) {
        super();
        this.setType('textarea')
            .setComponent(Textarea)
            .setLabel(label)
            .setValue(value)
    }
    get props() {
        return {
            label: this.label,
            placeholder: this.label,
        }
    }
}
class CheckboxField extends Field {
    constructor(label, value = false) {
        super();
        this.setType('checkbox')
            .setComponent(Checkbox)
            .setLabel(label)
            .setValue(value)
    }
    get props() {
        return {
            label: this.label,
        }
    }
}
class ExampleField extends Field {
    constructor(label, value = null) {
        super();

        value = value || {
            text: '',
            meaning: [
                {
                    lang: 'en',
                    isRtl: false,
                    text: '',
                },
                {
                    lang: 'fa',
                    isRtl: true,
                    text: '',
                }
            ]
        }

        this.setType('example')
            .setComponent(Example)
            .setLabel(label)
            .setValue(value)
    }
    get props() {
        return {
            label: this.label,
        }
    }

    translate = (value, sourceLanguage = 'de') => {
        let translator = new Translate('google', {text:value.text})
        translator.setFrom(sourceLanguage)
        value.meaning.forEach(mean =>  {
            translator.setTo(mean.lang)
            translator.translate(res => mean.text = res.sentences.map(s=>s.trans).join())
        })
    }
}
class CaseField extends Field {
    caseOptions
    constructor(label, caseOptions ,value = null) {
        super();
        value = value || {
            case: '',
            preposition: '',
        }
        this.setType('case')
            .setComponent(Case)
            .setCaseOptions(caseOptions)
            .setLabel(label)
            .setValue(value)
    }
    setCaseOptions = caseOptions => this.set('caseOptions', caseOptions)

    get props() {
        return {
            label: this.label,
            caseOptions: this.caseOptions,
        }
    }
}
function getCase(caseKey, url, word, mainContent) {
    const tableRows = mainContent.querySelectorAll(`#${word}__1 > .content.definitions.dictionary.biling > .short_noun_table.decl > .table > .tr`)
    if (tableRows.length === 0) throw new Error('No table found')
    if (!tableRows[0].textContent.trim().toLowerCase().startsWith('case')) throw new Error('No case found')
    let cases = {}
    Array.from(tableRows).slice(1).forEach(row => {
        let caseName = row.childNodes[0].textContent.trim().toLowerCase()
        Array.from(row.childNodes).slice(1).forEach(c => {
            if (!cases[caseName]) cases[caseName] = []
            cases[caseName].push(c.textContent.trim().split().slice(1).join(' '))
        })
    })
    if (cases.length === 0) throw new Error('No cases found')
    return cases
}
function getNameWord(word, mainContent) {
    return mainContent.querySelector(`#${word}__1`).dataset.typeBlock.split('').map((l,i) => i === 0 ? l.toUpperCase(): l.toLowerCase()).join('')
}
const noun = {
    gender: new InputField('Gender').setIsRequired(true).setFillCallback((url, word, mainContent)=> {
        const pos = mainContent.querySelectorAll(`#${word}__1 > .definitions > .hom > .gramGrp > .pos`)
        if (pos.length === 0) throw new Error('No pos found')
        let gen = Array.from(pos).map(p => p.textContent.trim().toLowerCase()).find(p => p.endsWith('noun'))
        if (!gen) throw new Error('No gen found')
        return gen.split(' ').shift()
    }),
    plural: new InputField('Plural').setIsRequired(true).setFillCallback((url, word, mainContent)=> {
        const pos = mainContent.querySelectorAll(`#${word}__1 > .definitions > .hom > .form.inflected_forms.type-infl > .type-gram `)
        if (pos.length === 0) throw new Error('No pos found')
        let plural = Array.from(pos).find(p => p.textContent.trim() === 'plural')
        if (!plural) throw new Error('No plural found')
        return plural.previousElementSibling.textContent.trim()
    }),
    genitive: new InputField('Genitive').setIsRequired(true).setFillCallback((url, word, mainContent)=> {
        const pos = mainContent.querySelectorAll(`#${word}__1 > .definitions > .hom > .form.inflected_forms.type-infl > .type-gram `)
        if (pos.length === 0) throw new Error('No pos found')
        let plural = Array.from(pos).find(p => p.textContent.trim() === 'genitive')
        if (!plural) throw new Error('No genitive found')
        return plural.previousElementSibling.textContent.trim()
    }),
    case: new CaseField('Case', ['AKK', 'DAT', 'GEN']).setIsMultiple(true),
    description: new TextareaField('Description').setIsMultiple(true),
    examples: new ExampleField('Examples').setHasSelection(true).setHasTranslation(true).setIsMultiple(true).setFillCallback((url, word, mainContent)=> {
        const examples = mainContent.querySelectorAll('.res_cell_center .he .assets > .cB.cB-e > .listExBlock > .type-example')
        if (examples.length === 0) throw new Error('No examples found')
        return Array.from(examples).slice(0,4).map(e => {
            let text = e.querySelector('.quote').textContent.trim()
            return {
                text,
                meaning: [
                    { lang: 'en', isRtl: false, text: '' },
                    { lang: 'fa', isRtl: true, text: '' },
                ],
            }
        })
    }),
}
const getVerbConjugation = async (url, word, mainContent) => {
    let link = mainContent.querySelectorAll(`#${word}__1 > .definitions > .hom > .verbtable`)
    if (link.length === 0) throw new Error('No verb table found')
    if ( window.verbTable === undefined) {
        window.verbTable = await fetch(link[0].href, { mode: 'cors', }).then(res => res.text())
    }
    return (new DOMParser).parseFromString( window.verbTable, 'text/html')
}
const verb = {
    regular: new SelectField('Regular', ['Regular', 'Irregular']).setIsRequired(true).setFillCallback(async (url, word, mainContent)=> {
        let verbTable = await getVerbConjugation(url, word, mainContent)
        word = mainContent.querySelector(`#${word}__1`).dataset.typeBlock
        let conjugations = verbTable.querySelectorAll('.short_verb_table > .conjugation ')
        if (!conjugations) throw new Error('No conjugations found')
        let preterite = Array.from(conjugations).find(c => c.children[0].textContent.toLowerCase().trim() === 'preterite')
        if (!preterite) throw new Error('No preterite found')
        let ich = Array.from(preterite.querySelectorAll('span.infl')).find(c => c.firstChild.textContent.toLowerCase().trim() === 'ich')
        if (!ich) throw new Error('No ich found')
        ich.removeChild(ich.firstChild)
        let past = ich.textContent.toLowerCase().trim()
        let presentPerfect = Array.from(conjugations).find(c => c.children[0].textContent.toLowerCase().trim() === 'present perfect')
        if (!presentPerfect) throw new Error('No present perfect found')
        let wir = Array.from(presentPerfect.querySelectorAll('span.infl')).find(c => c.firstChild.textContent.toLowerCase().trim() === 'wir')
        if (!wir) throw new Error('No wir found')
        wir.removeChild(wir.firstChild)
        presentPerfect = wir.textContent.toLowerCase().trim()
        let newVerb = [
            word.slice(0, -2) + 'te',
            'ge'+word.slice(0, -2) + 't',
        ]
        console.log(newVerb)
        let pastWords = past.split(' ')
        let presentPerfectWords = presentPerfect.split(' ')
        if ( pastWords.includes(newVerb[0]) && presentPerfectWords.includes(newVerb[1])) {
            return 'Regular'
        }
        return 'Irregular'
    }),
    reflective: new SelectField('Reflective', ['Reflective', 'Non-Reflective']).setIsRequired(true),
    past: new InputField('Past').setIsRequired(true).setFillCallback(async (url, word, mainContent)=> {
        let verbTable = await getVerbConjugation(url, word, mainContent)
        let conjugations = verbTable.querySelectorAll('.short_verb_table > .conjugation ')
        if (!conjugations) throw new Error('No conjugations found')
        let preterite = Array.from(conjugations).find(c => c.children[0].textContent.toLowerCase().trim() === 'preterite')
        if (!preterite) throw new Error('No preterite found')
        let ich = Array.from(preterite.querySelectorAll('span.infl')).find(c => c.firstChild.textContent.toLowerCase().trim() === 'ich')
        if (!ich) throw new Error('No ich found')
        ich.removeChild(ich.firstChild)
        return ich.textContent.toLowerCase().trim()
    }),
    presentPerfect: new InputField('Present Perfect').setIsRequired(true).setFillCallback(async (url, word, mainContent)=> {
        let verbTable = await getVerbConjugation(url, word, mainContent)
        let conjugations = verbTable.querySelectorAll('.short_verb_table > .conjugation ')
        if (!conjugations) throw new Error('No conjugations found')
        let presentPerfect = Array.from(conjugations).find(c => c.children[0].textContent.toLowerCase().trim() === 'present perfect')
        if (!presentPerfect) throw new Error('No present perfect found')
        let wir = Array.from(presentPerfect.querySelectorAll('span.infl')).find(c => c.firstChild.textContent.toLowerCase().trim() === 'wir')
        if (!wir) throw new Error('No wir found')
        wir.removeChild(wir.firstChild)
        return wir.textContent.toLowerCase().trim()
    }),
    auxiliaryVerb: new SelectField('Auxiliary Verb', ['haben', 'sein']).setIsRequired(true).setFillCallback(async (url, word, mainContent)=> {
        let verbTable = await getVerbConjugation(url, word, mainContent)
        let conjugations = verbTable.querySelectorAll('.short_verb_table > .conjugation ')
        if (!conjugations) throw new Error('No conjugations found')
        let presentPerfect = Array.from(conjugations).find(c => c.children[0].textContent.toLowerCase().trim() === 'present perfect')
        if (!presentPerfect) throw new Error('No present perfect found')
        let wir = Array.from(presentPerfect.querySelectorAll('span.infl')).find(c => c.firstChild.textContent.toLowerCase().trim() === 'wir')
        if (!wir) throw new Error('No wir found')
        wir.removeChild(wir.firstChild)
        return wir.textContent.toLowerCase().trim().search('haben') !== -1 ? 'haben' : 'sein'
    }),
    imperative: new InputField('Imperative').setIsRequired(true).setFillCallback(async (url, word, mainContent)=> {

        let verbTable = await getVerbConjugation(url, word, mainContent)
        let heads = verbTable.querySelectorAll('.short_verb_table > h2 ')
        if (!heads) throw new Error('No heads found')
        let imperativeHead = Array.from(heads).find(c => c.textContent.toLowerCase().trim() === 'imperative')
        if (!imperativeHead) throw new Error('No imperative found')
        let conjugation = imperativeHead.nextElementSibling
        if (!conjugation) throw new Error('No conjugation found')
        let data = Array.from(conjugation.children).reduce((acc, cur)=> {
            if (cur.textContent.includes(' (du)')) {
                acc.push(cur.querySelector('b').textContent.toLowerCase().trim())
            }
            return acc
        }, [])
        return data.join(' , ')
    }),
    case: new CaseField('Case', ['AKK', 'DAT', 'GEN']).setIsMultiple(true),
    description: new TextareaField('Description').setIsMultiple(true),
    examples: new ExampleField('Examples').setHasSelection(true).setHasTranslation(true).setIsMultiple(true).setFillCallback((url, word, mainContent)=> {
        const examples = mainContent.querySelectorAll('.res_cell_center .he .assets > .cB.cB-e > .listExBlock > .type-example')
        if (examples.length === 0) throw new Error('No examples found')
        return Array.from(examples).slice(0,4).map(e => {
            let text = e.querySelector('.quote').textContent.trim()
            return {
                text,
                meaning: [
                    { lang: 'en', isRtl: false, text: '' },
                    { lang: 'fa', isRtl: true, text: '' },
                ],
            }
        })
    }),
}
const adjective = {
    superiorAdjective: new InputField('Superior Adjective').setIsRequired(true).setFillCallback((url, word, mainContent)=> {
        let blocks = mainContent.querySelectorAll('.cB.cB-def.dictionary.biling[data-type-block]')
        if (blocks.length === 0) throw new Error('No blocks found')
        let block = Array.from(blocks).find(b => {
            let type = b.querySelector('.definitions > .hom > .gramGrp.hi.rend-sc > .pos')
            if (!type) return false
            return type.textContent.toLowerCase().trim() === 'adjective'
        })
        if (!block) throw new Error('No block found')
        let typeGrams = block.querySelectorAll('.inflected_forms.type-infl > .lbl.type-gram')
        if (typeGrams.length === 0) throw new Error('No type grams found')
        let typeGram = Array.from(typeGrams).find(g => g.textContent.toLowerCase().trim() === 'comparative')
        if (!typeGram) throw new Error('No type gram found')
        return typeGram.nextElementSibling.textContent.toLowerCase().trim()
    }),
    superlativeAdjective: new InputField('Superlative Adjective').setIsRequired(true).setFillCallback((url, word, mainContent)=> {
        let blocks = mainContent.querySelectorAll('.cB.cB-def.dictionary.biling[data-type-block]')
        if (blocks.length === 0) throw new Error('No blocks found')
        let block = Array.from(blocks).find(b => {
            let type = b.querySelector('.definitions > .hom > .gramGrp.hi.rend-sc > .pos')
            if (!type) return false
            return type.textContent.toLowerCase().trim() === 'adjective'
        })
        if (!block) throw new Error('No block found')
        let typeGrams = block.querySelectorAll('.inflected_forms.type-infl > .lbl.type-gram')
        if (typeGrams.length === 0) throw new Error('No type grams found')
        let typeGram = Array.from(typeGrams).find(g => g.textContent.toLowerCase().trim() === 'superlative')
        if (!typeGram) throw new Error('No type gram found')
        return typeGram.nextElementSibling.textContent.toLowerCase().trim()
    }),
    case: new CaseField('Case', ['AKK', 'DAT', 'GEN']).setIsMultiple(true),
    description: new TextareaField('Description').setIsMultiple(true),
    examples: new ExampleField('Examples').setHasSelection(true).setHasTranslation(true).setIsMultiple(true).setFillCallback((url, word, mainContent)=> {
        const examples = mainContent.querySelectorAll('.res_cell_center .he .assets > .cB.cB-e > .listExBlock > .type-example')
        if (examples.length === 0) throw new Error('No examples found')
        return Array.from(examples).slice(0,4).map(e => {
            let text = e.querySelector('.quote').textContent.trim()
            return {
                text,
                meaning: [
                    { lang: 'en', isRtl: false, text: '' },
                    { lang: 'fa', isRtl: true, text: '' },
                ],
            }
        })
    }),
}
const other = {
    description: new TextareaField('Description').setIsMultiple(true),
    examples: new ExampleField('Examples').setHasSelection(true).setHasTranslation(true).setIsMultiple(true),
}

const types = {
    noun,
    verb,
    adjective,
    other
}
export default types
