/**
 * Транспорт для Controller.call
 *
 * @author neon
 */
function Sync(){

}
/**
 * Отправка запроса
 *
 * @param url string
 * @param data array
 * @param callback function
 * @param noCache boolean
 */
Sync.query = function(url, data, callback, noCache) {
    $.ajax({
        'url':      '/Controller/sync/',
        'async':    false,
        'data':     data,
        'type':     'POST',
        'dataType': 'json'
    }).done(function(result) {
        callback.call(null, result);
    }).error(function(result) {
        console.log('error');
    });
};