/**
 * Обновление аннотаций
 *
 * @author dp
 */
var Controller_Annotation = {

    /**
     * Обновление аннотаций
     *
     * @param $form jQuery
     */
    update: function ($form) {
        $form.css('color', 'red');
        Controller.call(
            'Annotation/update',
            {},
            function () {
                $form.css('color', '');
            }
        );
    }
};