/**
 * Необходимо связать вкладки и контейнеры через аттрибут rel
 *  $tab вкладки
 *  $tabContent контейнеры с блоками относящимися к табам
 *  active название класса активной вкладки
 *  callback функция, на клик по вкладке
 */
var Helper_Tab = {
	init: function(options) {
        var $tabs = '$tab' in options ? options['$tab'] : null,
            $container = '$tabContent' in options ? options['$tabContent'] :
                null,
            selectedClassName = 'active' in options ? options['active'] :
                'selected',
            callback = 'callback' in options ? options['callback'] : null;
		selectedClassName = selectedClassName ? selectedClassName : 'selected';
		var th = this;
		callback = (typeof callback == 'function') ?
            callback : function($container) {};
		var onHashChange = function() {
			if ($tabs.length) {
                var anchor = location.hash.replace('#', '');
                var isSelected = false;
				$tabs.each(function() {
					if ($(this).attr('rel') == anchor) {
						$tabs.removeClass(selectedClassName);
						$(this).addClass(selectedClassName);
                        $container.each(function() {
                           if ($(this).attr('rel') ==  anchor) {
                                callback($(this));
                           }
                        });
						th.render($tabs, $container, selectedClassName);
                        isSelected = true;
					}
				});
                if (!isSelected) {
                    var rel = $tabs.filter('.' +selectedClassName).attr('rel');
                    rel = rel ? rel : $tabs.first().attr('rel');
                    $tabs.removeClass(selectedClassName);
                    $tabs.filter('[rel='+rel+']').addClass(selectedClassName);
                    $container.each(function() {
                       if ($(this).attr('rel') ==  rel) {
                            callback($(this));
                       }
                    });
                    th.render($tabs, $container, selectedClassName, false);
                }
			}
		};
        onHashChange();
        $(window).bind('hashchange', onHashChange);
		$tabs.bind('click', function() {
			$tabs.removeClass(selectedClassName);
			$(this).addClass(selectedClassName);
			var rel = $(this).attr('rel');
			$container.each(function() {
				if ($(this).attr('rel') == rel) {
					$(this).show();
					location.hash = rel;
				} else {
					$(this).hide();
				}
			});
		});
	},
    // удаляет вкладки, если они пустые
    removeEmpty: function($tabs, $container) {
        $container.each(function() {
           if (!$.trim($(this).html())) {
              var rel = $(this).attr('rel');
              $tabs.each(function() {
                  if ($(this).attr('rel') == rel) {
                      $(this).remove();
                  }
              });
              $(this).remove();
           }
        });
    },
	render: function($tabs, $container, selectedClassName, writeHash) {
        var activeTabName = null;
        writeHash = writeHash !== undefined  ? writeHash : true;
		$tabs.each(function() {
			if ($(this).hasClass(selectedClassName)) {
				activeTabName = $(this).attr('rel');
                if (writeHash) {
                    location.hash = $(this).attr('rel');
                }
			}
		});
        if (!activeTabName) {
            activeTabName = $tabs.first().attr('rel');
            $tabs.first().addClass(selectedClassName);
            if (writeHash) {
                location.hash = activeTabName;
            }
        }
		$container.hide();
		if (activeTabName) {
			$container.each(function() {
				if ($(this).attr('rel') == activeTabName) {
					$(this).show();
				}
			});
		}
	}
};