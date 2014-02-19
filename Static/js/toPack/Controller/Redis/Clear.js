/**
 * Очистка кэша
 *
 * @author morph, goorus
 */
var Controller_Redis_Clear = {

    /**
     * Очистить весь кэш кроме сессий.
     *
     * @param $form jQuery
     */
    clearContent: function ($form) {
        $form.css('color', 'red');

        Controller.call(
            'Redis_Clear/clearContent',
            {},
            function () {
                $form.css('color', '');
            }
        );
    }
};