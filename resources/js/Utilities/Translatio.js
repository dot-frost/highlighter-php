class Translator {
    _from;
    _to;
    _text;

    setFrom(from) {
        this._from = from;
        return this;
    }
    setTo(to) {
        this._to = to;
        return this;
    }
    setText(text) {
        this._text = text;
        return this;
    }

    translate(callback) {
        throw new Error('Not implemented');
    }

    openWindow({ windowTarget, features }) {
        throw new Error('Not implemented');
    }
}

class GoogleTranslator extends Translator {
    _url = 'https://translate.googleapis.com/translate_a/single';
    constructor({ text, to, from }) {
        super();
        this.setText(text)
            .setTo(to)
            .setFrom(from);
    }
    translate(callback) {
        const params = {
            client: 'gtx',
            sl: this._from,
            tl: this._to,
            hl: this._to,
            dj: 1,
            dt: ['at', 'bd', 'ex', 'ld', 'md', 'qca', 'rw', 'rm', 'ss', 't'],
            ie: 'UTF-8',
            oe: 'UTF-8',
            otf: 1,
            ssel: 0,
            tsel: 0,
            kc: 7,
            q: this._text
        }
        const query = Object.keys(params)
            .map(key => {
                if (['string' , 'number'].includes(typeof params[key])) {
                    return `${key}=${params[key]}`
                }
                if (Array.isArray(params[key])) {
                    return params[key].map(value => `${key}=${value}`).join('&');
                }
            })
            .join('&');
        const url = `${this._url}?${query}`;
        fetch(url)
            .then(res => res.json())
            .then(callback)
            .catch(error => {
                throw new Error(error);
            });
    }
    openWindow({ windowTarget = '_blank', features = 'width=500,height=500' }) {
        const url = 'https://translate.google.com/';
        const params = {
            sl: this._from,
            tl: this._to,
            text: this._text
        }
        const query = Object.keys(params)
            .map(key => `${key}=${params[key]}`)
            .join('&');
        const newUrl = `${url}?${query}`;
        window.open(newUrl, windowTarget, features);
    }
}

class CollinsTranslator extends Translator {
    constructor({ text, to, from }) {
        super();
        this.setText(text)
            .setTo(to)
            .setFrom(from);
    }
    openWindow({ windowTarget = '_blank', features = 'width=500,height=500' }) {
        const langs = {
            en: 'english',
            fr: 'french',
            de: 'german',
            it: 'italian',
            es: 'spanish',
        }
        const url = `https://www.collinsdictionary.com/dictionary/${langs[this._from]}-${langs[this._to]}/${this._text}`;
        window.open(url, windowTarget, features);
    }
    setText(text) {
        return super.setText(text.toLowerCase());
    }
}

class BAmoozTranslator extends Translator {
    constructor({ text, to, from }) {
        super();
        this.setText(text)
            .setTo(to)
            .setFrom(from);
    }
    openWindow({ windowTarget = '_blank', features = 'width=500,height=500' }) {
        const url = `https://dic.b-amooz.com/${this._from}/dictionary/w`;
        const params = {
            word: this._text
        }
        const query = Object.keys(params)
            .map(key => `${key}=${params[key]}`)
            .join('&');
        const newUrl = `${url}?${query}`;
        window.open(newUrl, windowTarget, features);
    }
}

class TranslatorFactory {
    static create(type, { text, to, from }) {
        console.log(type, text, to, from);
        switch (type) {
            case 'google':
                return new GoogleTranslator({ text:text, to:to, from:from });
            case 'collins':
                return new CollinsTranslator({ text:text, to:to, from:from });
            case 'bamooz':
                return new BAmoozTranslator({ text:text, to:to, from:from });
            default:
                throw new Error('Unknown translator type');
        }
    }
}

class Translate {
    _translator;
    constructor(type, { text, to, from }) {
        debugger
        this._translator = TranslatorFactory.create(type, { text:text, to:to, from:from });
    }
    setText(text) {
        this._translator.setText(text);
        return this;
    }
    setTo(to) {
        this._translator.setTo(to);
        return this;
    }
    setFrom(from) {
        this._translator.setFrom(from);
        return this;
    }
    translate(callback) {
        return this._translator.translate(callback);
    }
    openWindow({ windowTarget, features }) {
        return this._translator.openWindow({ windowTarget, features });
    }
}

export default Translate;
