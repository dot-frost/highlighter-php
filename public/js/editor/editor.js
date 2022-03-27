const DELETE_ICON = "data:image/svg+xml,%3C%3Fxml version='1.0' encoding='utf-8'%3F%3E%3C!DOCTYPE svg PUBLIC '-//W3C//DTD SVG 1.1//EN' 'http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd'%3E%3Csvg version='1.1' id='Ebene_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' width='595.275px' height='595.275px' viewBox='200 215 230 470' xml:space='preserve'%3E%3Ccircle style='fill:%23F44336;' cx='299.76' cy='439.067' r='218.516'/%3E%3Cg%3E%3Crect x='267.162' y='307.978' transform='matrix(0.7071 -0.7071 0.7071 0.7071 -222.6202 340.6915)' style='fill:white;' width='65.545' height='262.18'/%3E%3Crect x='266.988' y='308.153' transform='matrix(0.7071 0.7071 -0.7071 0.7071 398.3889 -83.3116)' style='fill:white;' width='65.544' height='262.179'/%3E%3C/g%3E%3C/svg%3E";
const deleteImg = document.createElement('img');
deleteImg.src = DELETE_ICON;
function renderIcon(icon) {
    return function renderIcon(ctx, left, top, styleOverride, fabricObject) {
        const size = this.cornerSize;
        ctx.save();
        ctx.translate(left, top);
        ctx.rotate(fabric.util.degreesToRadians(fabricObject.angle));
        ctx.drawImage(icon, -size/2, -size/2, size, size);
        ctx.restore();
    }
}

export default class Highlight {
    #events = {
        add: [],
        destroy: [],
        change: [],
        select: [],
    };

    #size = null
    #position = null
    #color = null
    #opacity = null
    #rect = null

    constructor({ top, left, width, height, fill, opacity}, canvas) {
        this.canvas = canvas;
        this.#position = {
            x: left,
            y: top
        };
        this.#size = {
            width: width,
            height: height
        };
        this.#color = fill
        this.#opacity = opacity
        let canvasRatio = Highlight.getCanvasRatio(this.canvas);
        this.createRect(
            Highlight.convertPercentagePositionToRectPosition(this.position, canvasRatio),
            Highlight.convertPercentageSizeToRectSize(this.size, canvasRatio),
            this.#color,
            this.#opacity
        );
        this.#initRectEvents()
        this.#rect.highlight = this
    }

    static convertRectPositionToPercentagePosition(rectPosition, canvasRatio) {
        return {
            x: rectPosition.x / canvasRatio.x,
            y: rectPosition.y / canvasRatio.y
        }
    }

    static convertRectSizeToPercentageSize(rectSize, canvasRatio) {
        return {
            width: rectSize.width / canvasRatio.x,
            height: rectSize.height / canvasRatio.y
        }
    }

    static convertPercentagePositionToRectPosition(percentagePosition, canvasRatio) {
        return {
            x: Math.round(percentagePosition.x * canvasRatio.x),
            y: Math.round(percentagePosition.y * canvasRatio.y)
        }
    }

    static convertPercentageSizeToRectSize(percentageSize, canvasRatio) {
        return {
            width: Math.round(percentageSize.width * canvasRatio.x),
            height: Math.round(percentageSize.height * canvasRatio.y)
        }
    }
    static getCanvasRatio(canvas) {
        let xPixelRatioCanvas = canvas.width / 10000;
        let yPixelRatioCanvas = canvas.height / 10000;
        return {
            x: xPixelRatioCanvas,
            y: yPixelRatioCanvas,
        }
    }

    updateRect() {
        let { x, y } = Highlight.convertPercentagePositionToRectPosition(this.#position, Highlight.getCanvasRatio(this.canvas))
        let { width, height } = Highlight.convertPercentageSizeToRectSize(this.#size, Highlight.getCanvasRatio(this.canvas))

        this.#rect.set({
            left: x,
            top: y,
            width: width,
            height: height,
        })
        this.#rect.setCoords();
    }

    createRect(position, size, color, opacity) {
        this.#rect = new fabric.Rect({
            left: position.x,
            top: position.y,
            width: size.width,
            height: size.height,
            fill: color,
            opacity: opacity,
            hasControls: true,
            lockMovementX: true,
            lockMovementY: true,
            hoverCursor: 'pointer',
            padding: 2,
            borderColor: '#000000',
        })

        this.#rect.setControlsVisibility({
            bl: false,
            br: false,
            mb: false,
            ml: false,
            mr: false,
            mt: false,
            tl: false,
            tr: false,
            mtr: false,
        })
        if (typeof this.#rect.controls.deleteControl === 'undefined'){
            this.#rect.controls.deleteControl = new fabric.Control({
                cursorStyle: 'pointer',
                mouseUpHandler: (e, control) => {
                    control.target.highlight.destroy()
                },
                render: renderIcon(deleteImg),
                cornerSize: 24
            });
        }
    }

    #initRectEvents() {
        this.rect.on('object:moving', (e) => {
            this.top = e.target.top
            this.left = e.target.left
        })
        this.rect.on('object:scaling', (e) => {
            this.width = e.target.width
            this.height = e.target.height
        })
    }

    #set(key, value) {
        this.#rect.set(key, value)
        this.#rect.setCoords()
    }
    #fire(event, ...args) {
        this.#events[event].forEach(fn => fn(...args))
    }
    on( event, fn ) {
        if (typeof this.#events[event] === 'undefined') {
            this.#events[event] = []
        }
        this.#events[event].push(fn)
    }

    destroy() {
        this.#fire('destroy', this)
    }

    setOpts(opts) {
        Object.entries(opts).forEach(([key, value]) => {
            if (typeof this[key] !== 'undefined') {
                this[key] = value
            }
        })
    }

    set color(color) {
        this.#color = color
        this.#set('fill', this.#color)
        this.#fire('change', this)
    }
    set opacity(opacity) {
        this.#opacity = opacity
        this.#set('opacity', this.#opacity)
        this.#fire('change', this)
    }

    get rect() {
        return this.#rect
    }
    get position() {
        return Object.assign({}, this.#position)
    }
    get top() {
        return this.#position.y
    }
    get left() {
        return this.#position.x
    }
    get size() {
        return Object.assign({}, this.#size)
    }
    get width() {
        return this.#size.width
    }
    get height() {
        return this.#size.height
    }
    get color() {
        return this.#color
    }
    get opacity() {
        return this.#opacity
    }

    toJson() {
        return {
            position: {
                x: this.left,
                y: this.top,
            },
            size: {
                width: this.width,
                height: this.height,
            },
            color: this.color,
            opacity: this.opacity,
        }
    }
}


class HighlightManager {
    constructor(canvas) {
        this.canvas = canvas
        this.highlights = []
        this.canvas.on('mouse:down', (e) => {
            if (e.target) {
                if (e.target.highlight) {
                    e.target.highlight.destroy()
                }
            }
        })
    }

    addHighlight(position, size, color, opacity) {
        let highlight = new Highlight(this.canvas, position, size, color, opacity)
        this.highlights.push(highlight)
        this.canvas.add(highlight.rect)
        return highlight
    }

    removeHighlight(highlight) {
        this.highlights = this.highlights.filter(h => h !== highlight)
        this.canvas.remove(highlight.rect)
    }

    getHighlight(position) {
        return this.highlights.find(h => h.rect.containsPoint(position))
    }

    getHighlights() {
        return this.highlights
    }

    getHighlightsJson() {
        return this.highlights.map(h => h.toJson())
    }

    setHighlightsJson(highlights) {
        this.highlights = []
        highlights.forEach(h => {
            this.addHighlight(h.position, h.size, h.color, h.opacity)
        })
    }

    clear() {
        this.highlights.forEach(h => h.destroy())
        this.highlights = []
    }
}

class Editor0 {
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
    on(event, callback) {
        this.#event[event].push(callback)
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
            }, this.canvas)
        })
        this.#highlights.forEach(h => {
            h.on('destroy', (h)=>this.removeHighlight(h))
            this.canvas.add(h.rect);
        })
    }
    addHighlight(highlight) {
        this.#highlights.push(highlight)
        highlight.on('destroy', ()=> {
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
    #initSelecting(){
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

                this.addHighlight(new Highlight( {
                    top: position.y,
                    left: position.x,
                    width: size.width,
                    height: size.height,
                    fill: this.#draw.highlight.fill,
                    opacity: this.#draw.highlight.opacity,
                } , this.canvas))
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
        let ratio = this.image.height/ this.image.width ;
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
        this.#selected.opacity = opacity ;
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





const Editor = class {

}
