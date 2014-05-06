/**
 * Регистрирует вызовы объектов и вызывает их в дальнейшем
 *
 * @author morph
 */
var Call_List = {
    /**
     * Лист объектов
     */
    list: [],

    /**
     * Регистрация объекта
     */
    append: function (objectName, methodName, params){
        Call_List.list.push({
            objectName: objectName,
            methodName: methodName,
            params: params
        });
    },

    /**
     * Вызывает все зарегистрированные объекты
     */
    load: function() {
        var j, params, current, object;
        for (var i = 0, count = Call_List.list.length; i < count; i++) {
            current = Call_List.list[i];
            if (!(current.objectName in window)) {
                Loader.load(current.objectName);
                if (!(current.objectName in window)) {
                    console.log(current.objectName + ' undefined');
                    continue;
                }
            }
            object = window[current.objectName];
            params = [];
            for (j in current.params) {
                params.push(current.params[j]);
            }
            object[current.methodName].apply(object, params);
        }
    }
};
