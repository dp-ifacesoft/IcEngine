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
    public function index($form, $content, $context)
    {
        if (!trim($content)) {
            foreach ($form as $element) {
                $content .= $context->controllerManager->html(
                    'Form_Element_View/index', array(
                        'form'          => $form,
                        'elementName'   => $element->name
                    )
                );
            }
        }
        $this->output->send(array(
            'form'      => $form,
            'content'   => $content
        ));
    }
}
