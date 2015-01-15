/**
 * Контроллер для отображения тени
 *
 * @author neon
 */
var Controller_Overlay = {

	/**
	 * Название класса тени
     * @var string
	 */
	shadowClassName: 'shadowOverlayContainer',

	/**
	 * Массив callback'ов
     * @var array
	 */
	closeCallback: [],

    /**
     * Массив callback'ов Показать/скрыть тень
     * @var array
     */
    changeCallback: [],

    /**
     * Индекс zIndex тени, взято из вёрстки
     * @var int
     */
    zIndexDefault: 1000,

    /**
     * Добавление своих событий, Показать/скрыть тень
     * @param callback {}
     */
    onChange: function(callback)
    {
        this.changeCallback.push(callback);
    },

	/**
	 * Добавление своих событий, на закрытие (callback)
     * @params callback function
	 */
	onClose: function(callback)
	{
		this.closeCallback.push(callback);
	},

	/**
	 * Инициализация переключения тени
	 */
	shadowToggle: function()
	{
		var index, $this = this;
		if (!$('.' + this.shadowClassName).length) {
			$('body').prepend('<div class="' + this.shadowClassName + '"></div>');
			$('.' + this.shadowClassName).hide();
			$('.' + this.shadowClassName).click(function() {
				for (index in $this.closeCallback) {
					$this.closeCallback[index].call(null, true);
				}
                $this.shadowShow();
			});
		}
		this.shadowShow();
	},

	/**
	 * Непосредственное отображение/скрытие
	 */
	shadowShow: function()
	{
		var $container = $('.' + Controller_Overlay.shadowClassName),
            action = 'show', actionBool = true, index;
		if ($container.is(':visible')) {
			action = 'hide';
            actionBool = false;
		}
        $container[action]();
        var tmp, callback;
        for (index in Controller_Overlay.changeCallback) {
            callback = Controller_Overlay.changeCallback[index];
            callback.object[callback.field] = actionBool;
        }
	},

    /**
     * Получить zIndex тени
     * @return int
     */
    getZindex: function()
    {
        return this.zIndexDefault;
    }
}