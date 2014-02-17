<?php

/**
 * Отображение элемента формы
 *
 * @author markov
 */
class Controller_Form_Element_View extends Controller_Abstract
{
    /**
     * Отображает элемент формы
     * 
     * @Context("loader")
     */
    public function index($form, $elementName, $context)
    {
        $element = $form->element($elementName);
        $elementType = $element->getType();
        $path = 'General/' . $elementType;
        $pathByName = $form->name . '/' . $element->name . '/' 
            . $elementType;
        $existsPathByName = $context->loader->findFile(
            $pathByName  . '.tpl', 'Form'
        );
        if ($existsPathByName) {
            $path = $pathByName;
        } else {
            $pathByType = $form->name . '/'. $elementType;
            $pathExistsByType = $context->loader->findFile(
                $pathByType  . '.tpl', 'Form'
            );
            if ($pathExistsByType) {
                $path = $pathByType;
            }
        }
        $this->task->setTemplate('Form/' . $path);
        $this->output->send(array(
            'element'   => $element
        ));
    }
}
