<?php

/**
 * Сервис для работы с очередью тасков
 *
 * @author nastya
 * @Service("serviceQueue")
 */
class Service_Queue extends Service_Abstract
{
    /**
     * Добавить таск в очередь
     * @param array $data данные для таска:
     *          date $startTime время запуска таска
     *          string $serviceName имя сервиса для выполнения таска
     *          string $serviceMethod имя метода сервиса
     *          array $params параметры для выполнения таска
     * @return array созданный таск
     */
    public function addTask($data)
    {
        $modelManager = App::modelManager();
        $helperDate = App::helperDate();
        $startTime = isset($data['startTime']) ? $data['startTime'] : $helperDate->toUnix();
        $priority = isset($data['priority']) ? $data['priority'] : 0; 
        $serializeParams = serialize($data['params']);
        $task = $modelManager->create(
            'Queue', array(
                'createdAt'         => $helperDate->toUnix(),
                'startTime'         => $startTime,
                'serviceName'       => $data['serviceName'],
                'serviceMethod'     => $data['serviceMethod'],
                'serializedParams'  => $serializeParams,
                'priority'  => $priority
            )
        );
        $task->save();
        return $task;
    }
}
