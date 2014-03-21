/**
 * Помощник для окна
 *
 * @author neon
 */
var Helper_Window = {

    windowBottom: function() {
		return this.windowTop() + this.windowHeight();
	},

    windowHeight: function() {
		return (document.compatMode == 'CSS1Compat') ?
			document.documentElement.clientHeight : document.body.clientHeight;
	},

	windowTop: function() {
			return self.pageYOffset ||
				(document.documentElement &&
					document.documentElement.scrollTop) ||
					(document.body && document.body.scrollTop);
	},

    /**
     * Получить высоту рабочей области браузера
     *
     * @return int;
     */
    getClientHeight: function() {
        return document.documentElement.clientHeight;
    }
}
