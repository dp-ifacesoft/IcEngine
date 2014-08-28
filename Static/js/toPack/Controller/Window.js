/**
 * Контроллер окошек
 * TODO Рефакторить метод showToggle, как время будет
 *
 * @author neon
 */
var Controller_Window = {

    afterInitCallbacks: [],

    afterInitCallbacksCalled: [],

    /**
     * Имя текущего открытого окна
     * @var string
     */
    current: '',

    /**
     * Текущие проинициализированные окошки
     * @var array
     */
    windows: [],

    /**
     * Открытые окошки
     * @var array
     */
    opened: [],

    /**
     * Перекрытые окошки
     * @var array
     */
    overloaded: [],

    /**
     * Шаг изменения индекса для css
     * @var int
     */
    zIndexStep: 101,

    /**
     * Значение zIndex по умолчанию, взято из вёрстки
     * @var int
     */
    zIndexDefault: 1000000,

    /**
     * Значение zIndex тени, нужно проинициализировать
     * @var int
     */
    zIndexShadow: 0,

    /**
     * Текущее смещение между самым верхним и нижним окном
     * @var int
     */
    zIndexDelta: 0,

    /**
     * Флаг показывает, видна ли тень
     * @var boolean
     */
    isShadowShow: false,

    /**
     * Флаг указывает, произведена ли инициализация
     * @var boolean
     */
    isInited: false,

    /**
     * Добавить окно в стек открытых
     * @param className string
     * @return array
     */
    addOpened: function(className)
    {
        Controller_Window.opened.push(className);
        return Controller_Window.opened;
    },

    /**
     * Закрыть все открытые
     */
    close: function()
    {
        var className, opened = Controller_Window.opened;
        for (var i in opened) {
            className = opened[i];
            Controller_Window.showToggle({
                'className':    className
            });
        }
        //Controller_Window.shadow();
        //Controller_Window.zIndexDefault = 1100;
        //Controller_Window.zIndexDelta = 0;
    },

    /**
     * Закрыть текущее открытое окно
     */
    closeWindow: function()
    {
        Controller_Window.showWindow(Controller_Window.current, true);
    },

     /**
     * Получить окно по имени класса
     * @param className string
     * @return array|false
     */
    getWindow: function(className)
    {
        var index, windows = Controller_Window.windows, window;
        for (index in windows) {
            window = windows[index];
            if (window.className == className) {
                return window;
            }
        }
        return false;
    },

    getThis: function()
    {
        return Controller_Window;
    },

    /**
     * Получить список открытых окон
     * @return array
     */
    getOpened: function()
    {
        return Controller_Window.opened;
    },

    /**
     * Инициализация
     */
    init: function()
    {
        if (!Controller_Window.isInited) {
            this.zIndexDelta = this.zIndexStep;
            this.zIndexShadow = Controller_Overlay.getZindex();
            this.isInited = true;
            Controller_Overlay.onClose(this.closeWindow);
            Controller_Overlay.onChange({
                'object': Controller_Window,
                'field': 'isShadowShow'
            });
        }
    },

    /**
     * срабатывает на закрытие окна
     */
    onHideCallback: function() {},

    /**
     * Отцентрировать окошко
     */
    centerWindow: function(className) {
        var window = this.getWindow(className),
            $object = window.object;
        Loader.load('Helper_Window', 'ice');
        $object.css({
            'left': (screen.width - $object.width()) / 2,
            'top':  (Helper_Window.getClientHeight() - $object.height()) / 2
        });
    },

    /**
     * Обновить окно
     * @param className string
     * @return void
     */
    refreshWindow: function(className)
    {
        var window = this.getWindow(className),
            $object = window.object;
        $object.css({'z-index': window.zIndex});
        this.centerWindow(className);
    },

    removeWindow: function(className)
    {
        this.unsetWindow(className);
        $('.' + className).remove();
    },

    /**
     * Установить текущее открытое окно
     * @param className string
     */
    setCurrent: function(className)
    {
        Controller_Window.current = className;
    },

    /**
     * Обновить окно по имени класса
     * @param className string
     * @param window array
     * @return void
     */
    setWindow: function(className, window)
    {
        var index, windows = Controller_Window.windows;
        for (index in windows) {
            if (windows[index].className == className) {
                Controller_Window.windows[index] = window;
            }
        }
    },

    /**
     * Управление тенью
     */
    shadow: function()
    {
        Controller_Overlay.shadowToggle();
    },

    /**
     * Непосредственное отображение/скрытие окна
     * При открытие новых окон, поверх существующих, они будут переносится
     * под тень в порядке их открытия
     * @param {string} className
     * @param {boolean} auto означает что действие пришло из Controller_Overlay
     * @return boolean
     */
    showWindow: function(className, auto)
    {
        var $this = this.getThis(),
            i, j, window,
            windows = Controller_Window.windows,
            opened = this.getOpened();
        window = this.getWindow(className);
        if (!window) {
            return false;
        }
        if (window.object.is(':visible')) {
            var onHide = true;
            window.object.hide();
            opened = this.unsetOpened(window.className);
            var length = opened.length, offset = 0;
            if (length > 1) {
                offset = length - 1;
            }
            this.setCurrent(opened[offset]);
        } else {
            opened = this.addOpened(window.className);
            window.object.show();
            this.setCurrent(window.className);
            this.centerWindow(window.className);
            window.object.css({
                'zIndex':   this.zIndexDefault
            });
        }
        opened = this.getOpened();
        var openedWindow, openedClassName;
        for (j = opened.length - 1; j >= 0; j--) {
            openedClassName = opened[j];
            if (openedClassName == className && opened.length != 1) {
                continue;
            }
            openedWindow = this.getWindow(openedClassName);
            if (Controller_Window.current == openedClassName) {
                openedWindow.zIndex = Controller_Window.zIndexDefault;
                Controller_Window.zIndexDelta = 0;
            } else {
                openedWindow.zIndex -= Controller_Window.zIndexDelta;
            }
            Controller_Window.zIndexDelta += Controller_Window.zIndexStep;
            this.setWindow(openedWindow.className, openedWindow);
            this.refreshWindow(openedWindow.className);
        }
        if ((!auto && !Controller_Window.isShadowShow) ||
            (auto && opened.length > 0) ||
            (!auto && opened.length == 0)) {
            this.shadow();
        }
        if ($this.afterInitCallbacks.length) {
            var functionName;
            for (functionName in $this.afterInitCallbacks) {
                $this.afterInitCallbacks[functionName].call();
                $this.afterInitCallbacksCalled.push(functionName);
            }
        }
        if (onHide) {
            Controller_Window.onHideCallback();
        }
        return true;
    },

    /**
     * Показать/скрыть окно
     * Если его ещё нет проинициализировать
     * Доступны некоторые стандартные окошки, типо Error
     *
     * @param options array {className, html, type}
     */
    showToggle: function(options)
    {
        var $this = this.getThis(),
            $body = $('body'),
            type = 'type' in options ? options.type : null,
            className = 'className' in options ? options.className : null,
            html = 'html' in options ? options.html : null,
            callback = 'callback' in options ? options.callback : null,
            afterInitCallback = 'afterInitCallback' in options ?
                options['afterInitCallback'] : null;
        if (type) {
            className = className + '' + type;
        }
        if (!$('.' + className, $body).length) {
            if (type) {
                View_Render.assign({
                    'html': html
                });
                html = View_Render.fetch('Controller/Window/' +
                    type + '.tpl');
            }
            $body.prepend(html);
            if (!$('.windowContainer:first').hasClass(className)) {
                $('.windowContainer:first').addClass(className);
                $('.closeWindow', $('.' + className)).click(function() {
                    Controller_Window.showToggle({
                        'className':    className
                    });
                });
            }
            $('.close_form', $('.' + className)).click(function() {
                Controller_Window.showToggle({
                    'className':    className
                });
            });
            if (type && type == 'Confirm' && callback) {
                $('.windowConfirmYesBtn', $('.' + className)).click(function() {
                    Controller_Window.showToggle({
                        'className':    className
                    });
                    callback.call();
                });
                $('.windowConfirmNoBtn', $('.' + className)).click(function() {
                    Controller_Window.showToggle({
                        'className':    className
                    });
                });
            }
            var $object = $('.' + className);
            $object.hide();
            Controller_Window.windows.push({
                'className': className,
                'object': $object,
                'zIndex': Controller_Window.zIndexDefault
            });
        }
        var isCalled = className in $this.afterInitCallbacksCalled,
            isFunction = typeof (afterInitCallback) == 'function';
        if (!isCalled && isFunction) {
            $this.afterInitCallbacks.push(afterInitCallback);
        }
        Controller_Window.showWindow(className);
    },

    /**
     * Сбросить окно из стека открытых окон
     * @param className string
     * @return array
     */
    unsetOpened: function(className)
    {
        var index, openedClassName, opened = Controller_Window.opened,
            newOpened = [];
        for (index in opened) {
            openedClassName = opened[index];
            if (openedClassName != className) {
                newOpened.push(openedClassName);
            }
        }
        Controller_Window.opened = newOpened;
        return Controller_Window.opened;
    },

    /**
     * Сбросить окно из стека окон
     * @param className string
     */
    unsetWindow: function(className)
    {
        var index, window, windows = Controller_Window.windows;
        for (index in windows) {
            window = windows[index];
            if (window.className == className) {
                delete Controller_Window.windows[index];
            }
        }
    }
};