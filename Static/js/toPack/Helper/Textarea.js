/**
 * Хелпер для работы с текстовым полем
 * 
 * @author markov
 */
var Helper_Textarea = {
    
    /**
     * Оборачивает тегами
     */
    wrapTag: function($textarea, openTag, closeTag) {
        var value = $textarea.val();
        var start = $textarea[0].selectionStart;
        var end = $textarea[0].selectionEnd;
        if (end-start == 0) {
            return;
        }
        var result = value.substr(0, start) + openTag + 
            value.substring(start, end) + closeTag + 
            value.substring(end, value.length);
        $textarea.val(result);
    }
}