/**
 * перезагрузка элементов
 * @author Apostle
 */
var Ajax_Reloader = {
    templateName: '',
    activeSelectors: '',
    configMap: null,
    prefixes: [],
    
    /**
     * Перезагрузить элемент
     * @param {string} templateName имя конфига в config_ajax_reloader
     * @param {mixed} params параметры для вызываемого контроллера
     * @param {boolean} checkSelectors (optional|true) проверять селекторы
     * @param {boolean} getAttributes (optional|true) брать данные из атрибутов
     * @param {boolean} cutPrefixes (optional|true) отрезать префиксы атрибутов типа "data-*"
     * @returns {undefined}
     */
    reload: function(templateName, params, checkSelectors, getAttributes, cutPrefixes) {
        var configMap, self = this; 
        this.templateName = templateName;
        Loader.load('config_ajax_reloader', 'iceRoot');
        var prefixes = config_ajax_reloader.getPrefixes();
        configMap = config_ajax_reloader.config(params)[Ajax_Reloader.templateName];
        if (checkSelectors !== false) {
            configMap = this.getValidMap(configMap);
        };
        if (getAttributes !== false) {
            if (cutPrefixes !== false) {
               cutPrefixes = true;
            }
            configMap = this.appendAttributeData(configMap, prefixes, cutPrefixes);
        }
        function callback(result){
            console.log('in reload.callback');
            console.log(result);
            if(result.data.success){
                console.log(result.data.reloadedHtml);
                for(var selector in result.data.reloadedHtml) {
                    if(result.data.reloadedHtml[selector] == null) {
                        continue;
                    }
                    console.log(':in selector:');
                    console.log(selector);
                    var html = result.data.reloadedHtml[selector];
                    $(selector).outerHTML = html;
                    //$('<div>').append($(selector).clone()).html();
//                    $(selector).each(function () {
//                        var content = $(this);
//                        angular.element(document).injector().invoke(function($compile) {
//                            var scope = angular.element(content).scope();
//                            $compile(content)(scope);
//                        });
//                    });
//Loader.load('Controller_Component_Comment_Form_Extended', 'iceNoConflict');
//console.log(typeof Component_Comment_Form_Extended);
//Component_Comment_Form_Extended.$scope.updateUser();
var $scope = angular.element($('ng-form[ng-controller=Component_Comment_Form_Extended]')).scope();
var user = {id: '4555', login: '79236331112', name: 'apostle'};
setCookie ('user2', user, 30, '/', '.me');
$scope.init(function(){
    
});

//if(typeof angular == 'undefined') {
//    console.log('нет ангуляра');
//}
//
////Component_Comment_Form_Extended
//
//angular.element('Component_Comment_Form_Extended')
//        .injector().get('$compile')(
//            $(selector).html(
//                $compile(
//                    html
//                )(scope)
//             )
//        );

                    
                    var scripts = self.getScripts(html);
                    self.runScripts(scripts);
                }
            }
        }
        console.log(configMap);
        Controller.call(
            'Ajax_Reloader/reload', 
            {map:configMap},
            callback,
            true
        );
    },
    
    /**
     * Получить все скрипты из текста
     * @param {string} html html
     * @returns {array}
     */
    getScripts: function(html) {
        console.log('in scripts');
        var $div = document.createElement('DIV');
        $div.innerHTML = html;
        var $scripts = $div.getElementsByTagName('SCRIPT');
        var scripts = [];
        scripts = Array.prototype.slice.call($scripts);
        return scripts;
    },
    
    runScripts: function(scripts) {
        for (var script in scripts) {
            eval(scripts[script].innerHTML);
        }
    },
    /**
     * Проверяем на существование селекторы и если селектора нету, то и 
     * нет смысла лишний раз выполнять контроллер
     * @param {Object|Array} configMap - конфиг config_ajax_loader
     * @returns {Ajax_Loader.getValidMap.newMap|Array}
     */
    getValidMap: function(configMap) {
        for(var selector in configMap) {
            if($(selector).length == 0) {
                delete configMap[selector];
            }
        }
        return configMap;
    },
    
    /**
     * прикрутить атрибуты элемента с префиксами указанными в конфиге
     * @param {Oject|Array} configMap - конфиг config_ajax_loader 
     * @param {Boolean} cutPrefixes отрезать ли префиксы
     * @returns {Oject|Array}
     */   
    appendAttributeData: function(configMap, prefixes, cutPrefixes) {
        var el = null, data = {};
        console.log('append');
        console.log('preffixes:' + prefixes);
        for(var selector in configMap) {
            console.log(selector);
        }
        for(var selector in configMap) {
            el = $(selector).get(0);
            data = {};
            for (var att, i = 0, atts = el.attributes, n = atts.length; i < n; i++){
                var needed = false, cuttedNodeName = '';
                att = atts[i];
                for (var prefix in prefixes) {
                    var re = new RegExp('^'+prefixes[prefix]);
                    if (att.nodeName.match(re) != null && att.nodeName.match(re) != "undefined") {
                        if (cutPrefixes === true) {
                            var nodeName = att.nodeName.match(new RegExp('^(?:'+prefixes[prefix] + ')-?(\\w+)', 'i'));
                            if (nodeName != null && nodeName[1] != "undefined" && nodeName[1] != null){
                                needed = true;
                                cuttedNodeName = nodeName[1];
                                break;
                            };
                        }
                    }
                }
                if (needed === true && cuttedNodeName != '') {
                    data[cuttedNodeName] = att.nodeValue;
                }
            }
            console.log(data);
            if (configMap[selector][1] != "undefined" && configMap[selector][1] != null) {
                configMap[selector][1] = jQuery.extend(configMap[selector][1], data);
            } else if(data.length != "undefined" && data != null && data.length > 0){
                configMap[selector][1] = data;
            }
            data = {};
            el = null;
        }
        return configMap;
    }
};

