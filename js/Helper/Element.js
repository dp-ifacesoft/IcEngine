/**
 * Помощник работы с элементами
 *
 * @author neon
 */
var Helper_Element = {

    /**
     * @param {jQuery} $element
     * @param {array} scheme
     * @return {array}
     */
    fromAttr: function($element, scheme)
    {
        console.log($element);
        if (typeof(scheme) == 'undefined') {
            scheme = [];
        }
        var arr;
	        arr = $.map($element.attributes, function (attribute) {
	            return attribute.name + ' = ' + attribute.value;
	        });
	        alert(arr);
        //console.log($element.attributes);
    }
}