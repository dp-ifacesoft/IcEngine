var Controller_Static_Resource = {
    /**
     * Обновить статику
     */
    recache: function ($li) {
        $div = $li.closest('.hero-unit');
        position = $li.position();
        $div.find('.result-msg').html('<img src="/Ice/Static/images/site/wait.gif" alt="" />');

        Controller.call(
            'Static_Resource/recache',
            {},
            callback
        );

        function callback($result) {
            $div.find('.result-msg').find('img').remove();
        }

    }
};