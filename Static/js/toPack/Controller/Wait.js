/**
 * Контроллер для того чтобы картинку загрузки подставлять
 *
 * @author neon
 */
var Controller_Wait = {

    url: '/Ice/Static/images/site/wait.gif',
    width: 128,
    height: 15,

    getHtml: function(width, height)
    {
        if (typeof(width) == 'undefined') {
            width = this.width;
        }
        if (typeof(height) == 'undefined') {
            height = this.height;
        }
        return '<img src="' + this.url + '" width="' + width +
            '" height="' + height + '" class="notResize" />';
    },

    remove: function($container)
    {
        $('img.notResize', $container).remove();
    }

};