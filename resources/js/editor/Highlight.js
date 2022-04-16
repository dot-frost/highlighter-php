class Highlight {
    #events = {
        add: [],
        destroy: [],
        change: [],
        select: [],
    };
    data = null;
    #size = null
    #position = null
    #color = null
    #opacity = null
    #rect = null

    constructor({top, left, width, height, fill, opacity, data}, canvas) {
        this.data = data || {};
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
        let {
            x,
            y
        } = Highlight.convertPercentagePositionToRectPosition(this.#position, Highlight.getCanvasRatio(this.canvas))
        let {
            width,
            height
        } = Highlight.convertPercentageSizeToRectSize(this.#size, Highlight.getCanvasRatio(this.canvas))

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

    on(event, fn) {
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
        console.log(this.data)
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
            data: this.data,
        }
    }
}

export default Highlight
