/**
 * Контроллер загрузки содержимого контроллеров для контента вкладок
 *
 * @author neon
 */
var Helper_Tab_Loader = {
    /**
     * Загрузка содержимого
     * Использую для ROLL методов, где нет js'а в выхлопе
     *
     * @param {jQuery} $container
     * @param {function} loadedCallback
     */
    load: function($container, loadedCallback)
    {
        var isLoaded = $container.attr('isLoaded');
        if (isLoaded) {
            return;
        }
        var controller = $container.attr('controller');
        if (!controller) {
            return;
        }
        var params = $container.attr('params'),
            json = {},
            target = $container.attr('target'),
            $target = $container;
        if (params) {
            json = JSON.parse(params);
        }
        if (target) {
            $target = $(target);
        }
        $target.html(Controller_Wait.getHtml());
        function callback(result) {
            $container.attr({'isLoaded': true});
            $target.html(result.html);
            if (typeof (loadedCallback) == 'function') {
                loadedCallback.call(null, $container, result);
            }
        }
        Controller.call(
            controller,
            json,
            callback,
            true
        );
    }
};