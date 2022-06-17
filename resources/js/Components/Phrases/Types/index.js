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
            translator.translate(res => mean.text = res.sentences[0].trans)
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

const noun = {
    gender: new SelectField('Gender', ['das', 'der', 'die', 'die(PI)'] ).setIsRequired(true),
    plural: new InputField('Plural').setIsRequired(true).setFillCallback((url, word, mainContent)=> {
        const pos = mainContent.querySelectorAll(`#${word}__1 > .definitions > .hom > .form.inflected_forms.type-infl > .type-gram `)
        if (pos.length === 0) throw new Error('No pos found')
        let plural = Array.from(pos).find(p => p.textContent.trim() === 'plural')
        if (!plural) throw new Error('No plural found')
        return plural.previousElementSibling.textContent.trim()
    }),
    genitive: new InputField('Genitive').setIsRequired(true).setFillCallback((url, word, mainContent)=> {
        const pos = mainContent.querySelectorAll(`#${word}__1 > .definitions > .hom > .gramGrp > .pos`)
        if (pos.length === 0) throw new Error('No pos found')
        let gen = Array.from(pos).map(p => p.textContent.trim().toLowerCase()).find(p => p.endsWith('noun'))
        if (!gen) throw new Error('No gen found')
        return gen.split(' ').shift()
    }),
    case: new CaseField('Case', ['AKK', 'DAT', 'GEN']).setIsMultiple(true).setFillCallback((url, word, mainContent)=> {
        const tableRows = mainContent.querySelectorAll(`#${word}__1 > .content.definitions.dictionary.biling > .short_noun_table.decl > .table > .tr`)
        if (tableRows.length === 0) throw new Error('No table found')
        if (!tableRows[0].textContent.trim().toLowerCase().startsWith('case')) throw new Error('No case found')
        let keys = {
            'accusative': 'AKK',
            'dative': 'DAT',
            'genitive': 'GEN',
        }
        let cases = []
        Array.from(tableRows).slice(1).forEach(row => {
            let caseName = row.childNodes[0].textContent.trim().toLowerCase()
            if (!keys[caseName]) return
            Array.from(row.childNodes).slice(1).forEach(c => {
                cases.push({
                    case: keys[caseName],
                    preposition: c.textContent.trim(),
                })
            })
        })
        if (cases.length === 0) throw new Error('No cases found')
        return cases
    }),
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
const verb = {
    regular: new CheckboxField('Regular').setIsRequired(true),
    reflective: new CheckboxField('Reflective').setIsRequired(true),
    past: new InputField('Past').setIsRequired(true),
    pastPerfect: new InputField('Past Perfect').setIsRequired(true),
    auxiliaryVerb: new SelectField('Auxiliary Verb', ['haben', 'sein']).setIsRequired(true),
    case: new CaseField('Case', ['AKK', 'DAT', 'GEN']).setIsMultiple(true),
    description: new TextareaField('Description').setIsMultiple(true),
    examples: new ExampleField('Examples').setHasSelection(true).setHasTranslation(true).setIsMultiple(true),
}
const adjective = {
    superiorAdjective: new InputField('Superior Adjective').setIsRequired(true),
    superlativeAdjective: new InputField('Superlative Adjective').setIsRequired(true),
    case: new CaseField('Case', ['AKK', 'DAT', 'GEN']).setIsMultiple(true),
    description: new TextareaField('Description').setIsMultiple(true),
    examples: new ExampleField('Examples').setHasSelection(true).setHasTranslation(true).setIsMultiple(true),
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
