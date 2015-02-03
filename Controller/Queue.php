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
     * 
     * @Template("null")
     */
    public function index($context)
    {
        $onGoingTasks = $context->collectionManager->create(
            'Queue'
        )
            ->addOptions(
                [
                    'name'  => '::Process_Status',
                    'id'    => Helper_Process::ONGOING
                ]
            )
            ->raw();
        $tasksCount = $this->config()->tasksCount - count($onGoingTasks);
        echo 'tasksCount: ' . $tasksCount . PHP_EOL;
        if ($tasksCount > 0) {
            $tasks = $context->collectionManager->create('Queue')
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
                        'name'  => '::Order_Desc',
                        'field' => 'priority'
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
        }
        foreach ($tasks as $task) {
            $task->update([
                'Process_Status__id'    => Helper_Process::ONGOING
            ]);
            $service = $this->getService($task['serviceName']);
            $params = unserialize($task['serializedParams']);
            echo $task['serviceName'] . '->' . $task['serviceMethod'] . PHP_EOL;
            call_user_func_array([$service, $task['serviceMethod']], $params);
            $task->update([
                'Process_Status__id'    => Helper_Process::SUCCESS
            ]);
        }
        echo 'done' . PHP_EOL;
    }
}
