/**
 * Инициализация визивига
 * 
 * @author markov 
 */
var Form_Element_Redactor = {
    /**
     * Инициализация
     */
    init: function(data) {
        var $container = $('#' + data.id);
        Loader.load('Controller_Redactor');
        Loader.load('includes_redactor_redactor');
        Controller_Redactor.init($container, data.template);
    }
}