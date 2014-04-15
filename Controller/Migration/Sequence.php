<?php

/**
 * Генератор последовательностей для миграций
 * 
 * @author morph
 */
class Controller_Migration_Sequence extends Controller_Abstract
{
    /**
     * @inheritdoc
     */
    protected $config = array(
        'path'  => 'Ice/Var/Migration/sequence'
    );
    
    /**
     * Получить следующий член последовательности
     * 
     * @Context("helperMigrationSequence")
     * @ViewRender("Echo")
     * @Route("/migration/seq/next/")
     * @Validator("Request_Method"={"get"})
     */
    public function next($context)
    {
        $path = $this->config()->path;
        $sequence = $context->helperMigrationSequence->processSequence($path);
        $this->output->send(array(
            'content'   => $sequence
        ));
    }
    
    /**
     * Проверка статуса 
     * 
     * @Route("/migration/seq/status/")
     * @ViewRender("Echo")
     * @Validator("Request_Method"={"get"})
     */
    public function status()
    {
        $this->output->send(array(
            'content'   => 'ok'
        ));
    }
    
    /**
     * Изменить последовательность
     * 
     * @Context("helperMigrationSequence")
     * @Template(null)
     * @ViewRender("Echo")
     * @Route("/migration/seq/sync/")
     * @Validator("Request_Method"={"get"})
     */
    public function sync($value, $context)
    {
        $path = $this->config()->path;
        $context->helperMigrationSequence->processSequence($path, $value);
    }
}