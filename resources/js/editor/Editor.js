import Highlight from "./Highlight.js";

class Editor {
    zoom = 1

    constructor({canvas, image, getColor, getOpacity, highlights = []}) {
        this.canvas = new fabric.Canvas(canvas, {
            selection: false,
        });
        this.image = image

        this.getColor = getColor
        this.getOpacity = getOpacity
        this.updateCanvasSize();
        this.setBackground()
        this.initHighlights(highlights)
        window.onresize = () => {
            this.updateCanvasSize();
            this.setBackground()
            this.#highlights.forEach(highlight => highlight.updateRect())
            this.canvas.renderAll()
        }
        this.#initSelecting()
        this.#initDrawing()
    }

    #event = {
        addHighlight: [],
        removeHighlight: [],
        updateHighlight: [],
        drawStart: [],
        onDraw: [],
        drawEnd: [],
        select: [],
        selectUpdate: [],
        selectClear: [],
    }

    on(events, callback) {
        if (typeof events === 'string') {
            events = [events]
        }
        events.forEach(event => {
            if (!this.#event[event]) {
                throw new Error(`Unknown event: ${event}`)
            }
            this.#event[event].push(callback)
        })
    }

    #fire(event, ...args) {
        this.#event[event].forEach(callback => callback(...args))
    }

    #highlights = []

    initHighlights(highlights) {
        this.#highlights = highlights.map(h => {
            return new Highlight({
                top: h.position.y,
                left: h.position.x,
                width: h.size.width,
                height: h.size.height,
                fill: h.color,
                opacity: h.opacity,
                data: h.data,
            }, this.canvas)
        })
        this.#highlights.forEach(h => {
            h.on('destroy', (h) => this.removeHighlight(h))
            this.canvas.add(h.rect);
        })
    }

    addHighlight(highlight) {
        this.#highlights.push(highlight)
        highlight.on('destroy', () => {
            this.removeHighlight(highlight)
        })
        this.canvas.add(highlight.rect);
        this.#fire('addHighlight', highlight)
    }

    removeHighlight(highlight) {
        this.canvas.remove(highlight.rect);
        this.#highlights.splice(this.#highlights.indexOf(highlight), 1);
        this.#fire('removeHighlight', highlight)
    }

    #selected = null

    #initSelecting() {
        this.canvas.on('selection:created', (e) => {
            this.#selected = e.selected[0].highlight;
            this.#fire('select', this.#selected)
        });
        this.canvas.on('selection:updated', (e) => {
            this.#fire('selectUpdate', e.selected[0].highlight, this.#selected)
            this.#selected = e.selected[0].highlight;
        });
        this.canvas.on('selection:cleared', (e) => {
            this.#fire('selectClear', this.#selected)
            this.#selected = null;
        });
    }

    #draw = {
        active: false,
        startPoint: null,
        highlight: null,
    }

    #initDrawing() {
        this.canvas.on('mouse:down', (e) => {
            if (this.#selected || e.pointer.x < 0 || e.pointer.y < 0) return;
            this.#draw.startPoint = {x: ~~e.pointer.x, y: ~~e.pointer.y};
            this.#draw.highlight = new fabric.Rect({
                left: this.#draw.startPoint.x,
                top: this.#draw.startPoint.y,
                width: 0,
                height: 0,
                fill: this.getColor(),
                opacity: this.getOpacity()
            })
            this.canvas.add(this.#draw.highlight);
            this.#fire('drawStart', this.#draw)
        });

        this.canvas.on('mouse:move', (e) => {
            if (!this.#draw.highlight) return;
            this.#draw.highlight.set(this.calculateRectangle(e.pointer));
            this.canvas.renderAll();
            this.#fire('onDraw', this.#draw)
        })

        this.canvas.on('mouse:up', (e) => {
            if (!this.#draw.highlight) return;
            this.#draw.highlight.set(this.calculateRectangle(e.pointer));
            if (this.#draw.highlight.width > 5 && this.#draw.highlight.height > 5) {
                let canvasRatio = Highlight.getCanvasRatio(this.canvas);
                let position = Highlight.convertRectPositionToPercentagePosition({
                    x: this.#draw.highlight.left,
                    y: this.#draw.highlight.top,
                }, canvasRatio);
                let size = Highlight.convertRectSizeToPercentageSize({
                    width: this.#draw.highlight.width,
                    height: this.#draw.highlight.height,
                }, canvasRatio);

                this.addHighlight(new Highlight({
                    top: position.y,
                    left: position.x,
                    width: size.width,
                    height: size.height,
                    fill: this.#draw.highlight.fill,
                    opacity: this.#draw.highlight.opacity,
                }, this.canvas))
            }
            this.canvas.remove(this.#draw.highlight);
            this.canvas.renderAll();
            this.#draw.startPoint = null;
            this.#draw.highlight = null;
            this.#fire('drawEnd', this.#draw)
        });
    }

    calculateRectangle(pointer) {
        let x = ~~pointer.x;
        let y = ~~pointer.y;

        if (x < 0) x = 0;
        if (y < 0) y = 0;

        if (x > this.canvas.width) x = this.canvas.width;
        if (y > this.canvas.height) y = this.canvas.height;

        let width = Math.abs(x - this.#draw.startPoint.x);
        let height = Math.abs(y - this.#draw.startPoint.y);
        let left = Math.min(this.#draw.startPoint.x, x);
        let top = Math.min(this.#draw.startPoint.y, y);

        return {
            left,
            top,
            width,
            height
        }
    }

    setBackground(image = null) {
        if (image) this.image = image;
        this.canvas.setBackgroundImage(new fabric.Image(this.image), this.canvas.renderAll.bind(this.canvas), {
            scaleX: this.canvas.width / this.image.width,
            scaleY: this.canvas.height / this.image.height
        });
    }

    updateCanvasSize() {
        let ratio = this.image.height / this.image.width;
        let width = document.body.clientWidth * this.zoom;
        let height = width * ratio
        this.canvas.setWidth(width);
        this.canvas.setHeight(height);
    }

    selectedSetColor(color) {
        if (!this.#selected) return;
        this.#selected.color = color;
        this.canvas.renderAll();
        this.#fire('updateHighlight', this.#selected)
    }

    selectedSetOpacity(opacity) {
        if (!this.#selected) return;
        this.#selected.opacity = opacity;
        this.canvas.renderAll();
        this.#fire('updateHighlight', this.#selected)
    }

    highlightsToJson() {
        return this.#highlights.map(h => h.toJson())
    }

    lastHighlightGetImageData() {
        if (!this.#highlights.length) return false;
        let lastHighlight = this.#highlights[this.#highlights.length - 1];
        let canvasRatio = Highlight.getCanvasRatio(this.image);
        let position = Highlight.convertPercentagePositionToRectPosition({
            x: lastHighlight.left,
            y: lastHighlight.top,
        }, canvasRatio);
        let size = Highlight.convertPercentageSizeToRectSize({
            width: lastHighlight.width,
            height: lastHighlight.height,
        }, canvasRatio);

        return (new fabric.Image(this.image)).toDataURL({
            left: position.x,
            top: position.y,
            width: size.width,
            height: size.height
        });
    }

    get selected() {
        return this.#selected;
    }
}
export default Editor;
/*
function highlightsUpdate() {
    let highlights = editor.highlightsToJson()

    window.newHighlight = window.highlights.length < highlights.length;
    window.highlights = highlights;

    if (window.newHighlight) {
        const latestHighlight = editor.lastHighlightGetImageData()
        const canvas = document.createElement('canvas');
        const image = new Image();
        image.src = latestHighlight;
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
        sendHighlights(highlights);
    }
}

function sendHighlights(highlights, image = null) {

}


*/

