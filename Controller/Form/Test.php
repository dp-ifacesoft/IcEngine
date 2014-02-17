<?php

/**
 * тестовый контроллер
 *
 * @author markov
 */
class Controller_Form_Test extends Controller_Abstract
{
    /**
     * @Context("formBuilder")
     * @Route('/testform/')
     */
    public function test($context)
    {
        $form = $context->formBuilder
            ->setName('testForm')
            ->setFormAttributes(array(
                'action'    => '/test/',
                'enctype'   => 'text/plain'
            ))
            ->add('title', 'text')
                ->setAttributes(array(
                    'type'          => 'text',
                    'placeholder'   => 'example'
                ))
                ->setValidators(array(
                    'min'   => 100,
                    'max'   => 200,
                    'required'
                ))
            ->add('author', 'text')
                ->getForm();
        $this->output->send(array(
            'form'  => $form
        ));
    }
}
