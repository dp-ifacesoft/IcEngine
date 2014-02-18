<?php

/**
 * тестовый контроллер
 *
 * @author markov
 */
class Controller_Form_Test extends Controller_Abstract
{
    /**
     * @Context("formBuilder", "dto")
     * @Route('/testform/')
     */
    public function test($context)
    {
        $services = array(
            array(
                'value' => 1,
                'title' => 'option1'
            ),
            array(
                'value' => 2,
                'title' => 'option2'
            ),
            array(
                'value' => 3,
                'title' => 'option3'
            ),
        );
        $form = $context->formBuilder
            ->setFormName('testForm')
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
                    'Min'   => 100,
                    'Max'   => 200,
                    'Not_null',
                    'Email'
                ))
            ->add('author', 'text')
            ->add('text', 'textarea')
            ->add('services', 'select')
                ->setSelectable($services)
                ->setValue(2)
            ->getForm();
        
        $dto = $context->dto->newInstance()
            ->setFormName('testForm')
            ->setFormAttributes(array(
                'action'    => '/test/',
                'enctype'   => 'text/plain'
            ))
            ->setElements(array(
                array(
                    'title' => 'text',
                    'attributes'    => array(
                        'type'          => 'text',
                        'placeholder'   => 'example'
                    ),
                    'validators'    => array(
                        'Min'   => 100,
                        'Max'   => 200,
                        'Not_null',
                        'Email'
                    )
                ),
                array('author' => 'text'),
                array('text' => 'textarea'),
                array(
                    'services' => 'select',
                    'selectable' => $services,
                    'value'      => 2
                )
            ));
        $form2 = $context->formBuilder->create($dto);
        $form->bindRequest(array(
            'author'    => 'Пушкин'
        ));
        $this->output->send(array(
            'form'  => $form,
            'form2' => $form2
        ));
    }
}
