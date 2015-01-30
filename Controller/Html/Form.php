<?php
/**
 * Контроллер Html-формы
 *
 * @author LiverEnemy
 */

class Controller_Html_Form extends Controller_Abstract
{
    /**
     * @param Html_Form $form
     */
    public function index(Html_Form $form)
    {
        $this->output->send([
            'form' => $form,
        ]);
    }
}