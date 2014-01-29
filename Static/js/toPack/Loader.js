/**
 * Загрузчик классов js
 *
 * @author morph
 */
var Loader = {
    /**
     * Группы путей
     */
    groups: {
        'engine':   '/IcEngine/Static/js/',
        'ice':      '/Ice/Static/js/noPack/',
        'cache':    '/cache/'
    },

    /**
     * Статусы загрузки классов
     */
    statuses: {},

    /**
     * Получить путь до файла по имени класса
     */
    filename: function(className) {
        return className.replace(/_/g, '/') + '.js?' +
            Static_Js_Time_Rebuild.get();
    },

    /**
     * Загружает класс или классы
     */
    load: function(className, group, async) {
        var names = className;
        if (typeof(async) == 'undefined') {
            async = false;
        }
        if (typeof names == 'string') {
            names = [className];
        }
        var i;
        for (i in names) {
            if (window[names[i]] !== undefined) {
                continue;
            }
            Loader.require(Loader.filename(names[i]), group, async);
        }
    },

    /*
     * Возможно потоп пригодится
     *loadFromModule: function(className, moduleName)
    {
        var path = '/' + moduleName + '/Static/js/noPack/',
            fileName = className.replace('_', '/');
        Loader.require(path + fileName);
    },*/

    loadJtpl: function(path)
    {
        var filename = '/Ice/Static/jtpl/' + path + '?' +
            Static_Js_Time_Rebuild.get(),
            result = false;
        $.ajax({
            url: filename,
            success: function(text) {
                var textPrepared = text.split("\n").join("");
                View_Render.addTemplate(path, textPrepared);
                result = true;
            },
            async: false
        });
        return result;
    },

    /**
     * Загрузить файл
     */
    require: function(filename, group, async) {
        if (!group) {
            group = 'ice';
        }
        var path = Loader.groups[group];
        filename = path + filename;
        if (filename in Loader.statuses) {
            return;
        }
        $.ajax({
            url: filename,
            success: function(text) {
                Loader.statuses[filename] = true;
                $('<script></script>').attr('type', 'text/javascript').
                    html(text).appendTo($('head'));
            },
            async: async
        });
    }
};