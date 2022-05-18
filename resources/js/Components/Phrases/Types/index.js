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
    hasTranslation = false

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

    translate(){
        throw new Error('Not implemented')
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

    translate(value, sourceLanguage = 'de') {
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
    plural: new InputField('Plural').setIsRequired(true),
    genitive: new InputField('Genitive').setIsRequired(true),
    case: new CaseField('Case', ['AKK', 'DAT', 'GEN']).setIsMultiple(true),
    description: new TextareaField('Description').setIsMultiple(true),
    examples: new ExampleField('Examples').setHasSelection(true).setHasTranslation(true).setIsMultiple(true),
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
