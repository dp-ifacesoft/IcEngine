var Helper_Autocomplete = {

    /**
     * Прошлое значение input
     */
    _lastInputValue: '',
    _scrollTop: 0,
    callback: function () {},
    items: [],
    // Список подсказок
    hints: [],
    // селект
    $selector: null,
    // главный контейнер (генерируется после инициализации)
    $mainContainer: null,
    // input для ввода
    $input: null,
    // контейнер подсказок
    $hintsContainer: null,

    /**
     * Создание нового автокомплитера
     */
    create: function(params) {
        var object = {};
        $.extend(true, object, this);
        object.init(params);
        return object;
    },

    /**
     * Инициализация
     */
    init: function(params) {
        if (params.$selector) {
            this.$selector = params.$selector;
            this.$selector.hide();
        }
        this.$input = params.$input;
        this.mainContainerInit();
        this.formInputInit();
        this.hintsContainerInit();
        if (this.$selector) {
            this.itemsAddFromSelect();
        }
        if (params.callback) {
            this.callback = params.callback;
        }
        if (params.itemsJson) {
            var jsonParsed = params.itemsJson;
            this.itemsAdd(jsonParsed);
        }
        this.setDefault(this.$input.val());
    },

    /**
     * Загружает элементы
     */
    itemsAdd: function(items)
    {
        this.items = this.items.concat(items);
    },

    /**
     * Добавляет из селектора элементы
     */
    itemsAddFromSelect: function() {
        var $this = this;
        this.$selector.find('option').each(function() {
           $this.items.push({
               value: $(this).val(),
               title: $(this).html()
           });
        });
    },

    /**
     * Создается главный контейнер
     */
    mainContainerInit: function() {
        this.$mainContainer = $('<div class="main_autocomplete">');
        this.$input.after(this.$mainContainer);
        this.$mainContainer.append(this.$input);
    },

    /**
     * Инициализация формы ввода значений
     */
    formInputInit: function() {
        this.$input.attr('autocomplete', 'off');
        var $this = this;
        this.$input.bind('keyup', function(e) {
           if ($this.$input.val() != $this.$input.attr('data-title')) {
               $this.$input.removeAttr('data-title');
               $this.$input.removeAttr('data-value');
           }
           if ($this.formInputChanged($this.$input.val())) {
               $this.hintsUpdate($this.$input.val());
               $this.hintsView();
           }
           if (e.keyCode == 40) {
               $this.nextHintActive();
           }
           if (e.keyCode == 38) {
               $this.prevHintActive();
           }
           if (e.keyCode == 13) {
              $this.hintSelected();
           }
        });
    },

    /**
     * Устанавливает значение по умолчанию
     */
    setDefault: function(title) {
        for (var i in this.items) {
            if (this.items[i].title == title) {
                this.$input.attr('data-value', this.items[i].value);
                this.$input.attr('data-title', this.items[i].title);
                break;
            }
        }
    },

    /**
     * Проверяет изменилась ли строка поиска на необходимое количество символов
     */
    formInputChanged: function(value) {
        var result = Math.abs(value.length - this._lastInputValue.length) > 0;
        if (result) {
            this._lastInputValue = value;
        }
        return result;
    },

    /**
     * Пересчитать подсказки
     */
    hintsUpdate: function(searchValue) {
        this.hints = [];
        if (!searchValue.length) {
            return;
        }
        var expr = new RegExp('^' + searchValue, 'i');
        for (var i in this.items) {
            if (expr.test(this.items[i].title)) {
                this.hints.push(this.items[i]);
            }
        }
        this.hints.sort();
    },

    /**
     *  Вывести подсказки
     */
    hintsView: function() {
        var $this = this;
        this.$hintsContainer.empty();
        for(var i in this.hints) {
            var $hint = $(
                '<div class="hint" data-value="'+this.hints[i]['value']+
                    '">'+this.hints[i]['title']+'</div>'
            );
            $hint.bind('click', function() {
                $this.hintActive($(this));
                $this.hintSelected();
            });
            $hint.bind('mouseover', function() {
                $this.hintActive($(this));
            });
            this.$hintsContainer.append($hint);
        }
        if (this.$hintsContainer.is(':empty')) {
            this.$hintsContainer.hide();
            return;
        }
        this.$hintsContainer.show();
    },

    /**
     * Инициализация контейнера с подсказками
     */
    hintsContainerInit: function() {
        this.$hintsContainer = $('<div class="hints_container">');
        this.$mainContainer.append(this.$hintsContainer);
    },

    /**
     * Выбор пункта окончательный
     */
    hintSelected: function() {
        var $hintActive = this.$hintsContainer.find('.hint.selected').first();
        this.$input.val($hintActive.html());
        var value = $hintActive.attr('data-value');
        this.$input.attr('data-value', value);
        var title = $hintActive.html();
        this.$input.attr('data-title', title);
        this.$hintsContainer.hide();
        this.callback.call(null, {
            value:    value,
            title:    title
        });
    },
    /**
     * Подсветка пункта
     */
    hintActive: function($hint) {
        this.$hintsContainer.find('.hint').removeClass('selected');
        $hint.addClass('selected');
        if ((this.$hintsContainer.height() - $hint.height()) <=
            ($hint.offset().top - this.$hintsContainer.offset().top)
        ) {
            this._scrollTop += $hint.height();
            this.$hintsContainer.scrollTop(this._scrollTop);
        }
        if (($hint.offset().top - this.$hintsContainer.offset().top) <= 0) {
            this._scrollTop -= $hint.height();
            this.$hintsContainer.scrollTop(this._scrollTop);
        }
    },

    /**
     *  Устанавливает следующий элемент активным
     */
    nextHintActive: function() {
        var $currentHint = this.$hintsContainer.find('.hint.selected');
        if (!$currentHint.length) {
            this.$hintsContainer.find('.hint').first().addClass('selected');
            return;
        }
        if (!$currentHint.next('.hint').length) {
            return;
        }
        this.hintActive($currentHint.next('.hint'));
    },

    /**
     *  Устанавливает предыдущий элемент активным
     */
    prevHintActive: function() {
        var $currentHint = this.$hintsContainer.find('.hint.selected');
        if (!$currentHint.length) {
            this.$hintsContainer.find('.hint').first().addClass('selected');
            return;
        }
        if (!$currentHint.prev('.hint').length) {
            this.$hintsContainer.find('.hint').removeClass('selected');
            return;
        }
        this.hintActive($currentHint.prev('.hint'));
    }
}

