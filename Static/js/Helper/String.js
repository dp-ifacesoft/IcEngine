/**
 * Помощник работы со строками
 *
 * @author neon
 */
var Helper_String = {
    /**
     * Преобразует входную строку в эквивалентную в unicode символах
     *
     * @param value {String}
     * @return String
     */
    toUnicode: function(value)
    {
        var uni = [],
            i = value.length;
        while (i--) {
            uni[i] = value.charCodeAt(i).toString(16);
        }
        return "(\\u0" + uni.join("\\u0") + ')';
    }
};