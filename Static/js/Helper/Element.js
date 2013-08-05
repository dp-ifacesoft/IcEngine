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
        var index, result = {}, attrName = '';
        for (index in scheme) {
            attrName = scheme[index];
            result[attrName] = $element.attr(attrName);
        }
        if (typeof(scheme) == 'undefined') {
            scheme = [];
        }
        return result;
    }
}