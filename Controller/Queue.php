<?php

/**
 * Контроллер для запуска на выполнение тасков из очереди
 *
 * @author nastya
 */
class Controller_Queue extends Controller_Abstract
{
    /**
     * tasksCount количество одновременно выполняемых тасков
     */
    protected $config = [
        'tasksCount'    => 5
    ];
    
    /**
     * Запустить таски
     * @Template("null")
     * @Schedule("P1M")
     */
    public function index($context)
    {
        $tasksCount = $this->config['tasksCount'];
        $tasks = $context->collectionManager->create(
            'Queue'
        )
            ->addOptions(
                [
                    'name'      => '::Process_Status',
                    'id'        => Helper_Process::NONE
                ],
                [
                    'name'      => '::Expired_Time',
                    'field'     => 'startTime'
                ],
                [
                    'name'      => '::Order_Asc',
                    'field'     => 'createdAt'
                ],
                [
                    'name'      => '::Limit',
                    'count'     => $tasksCount
                ]
            );
        foreach ($tasks as $task) {
            $task->update(
                array(
                    'Process_Status__id'    => Helper_Process::ONGOING
                ));
            $service = $this->getService($task['serviceName']);
            $params = unserialize($task['serializedParams']);
            print_r($task['serviceName'] . '->' . $task['serviceMethod'] . '()'
                . PHP_EOL);
            $service->$task['serviceMethod']($params, $task['id']);
        }
        print_r('done' . PHP_EOL);
    }
}
