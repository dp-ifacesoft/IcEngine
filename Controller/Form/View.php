<?php

/**
 * Контроллер для отрисовки формы
 *
 * @author markov
 */
class Controller_Form_View extends Controller_Abstract
{
    /**
     * Отрисовывает форму
     */
    public function index($form, $content)
    {
        $this->output->send(array(
            'form'      => $form,
            'content'   => $content
        ));
    }
}
