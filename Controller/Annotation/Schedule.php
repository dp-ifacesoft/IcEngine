<?php

/**
 * Контроллер для аннотаций типа "Schedule"
 * 
 * @author morph
 */
class Controller_Annotation_Schedule extends Controller_Abstract
{
    /**
     * Обновить аннотации
     * 
     * @Template(null)
     * @Validator("Not_Null"={"data"})
     * @Context("helperAnnotationSchedule", "helperArray", "unitOfWork", "collectionManager", "queryBuilder", "dds")
     */
    public function update($data, $context)
    {
        $schedules = array();
        $schedulesForSave = array();
        $context->unitOfWork->setAutoFlush(500);
        $schedulesExists = $context->collectionManager->create('Schedule')
            ->raw();
        foreach ($data as $controllerAction => $annotationData) {
            $subData = $annotationData['Schedule'];
            $scheduleData = $subData['data'][0];
            $params = isset($scheduleData['params'])
                ? $scheduleData['params'] : array();
            if (strpos($controllerAction, 'Controller_') !== false) {
                $controllerAction = str_replace(
                    'Controller_', '', $controllerAction
                );
            } else {
                $tmp = explode('/', $controllerAction);
                $name = $context->helperAnnotationSchedule->getName($tmp[0]);
                if (!$name) {
                    continue;
                }    
                $params['name'] = $name;
                $params['method'] = $tmp[1];
                $controllerAction = 'Service/run';
            }
            $interval = reset($scheduleData);
            $priority = isset($scheduleData['priority'])
                ? $scheduleData['priority'] : 0;
            $deltaSec = $context->helperAnnotationSchedule->delta(array(
                'interval'  => $interval
            ));
            $schedules[] = array(
                'controllerAction'  => $controllerAction,
                'deltaSec'  => $deltaSec,
                'interval'  => $interval,
                'priority'  => $priority,
                'params'    => $params,
                'paramsJson' => json_encode($params, JSON_UNESCAPED_UNICODE),
                'exectTime' => isset($scheduleData['exectTime']) ? 
                    '0000-00-00 ' . $scheduleData['exectTime'] . ':00' : null,
                'hasExectTime'  => (int) isset($scheduleData['exectTime'])
            );
        }
        foreach ($schedules as $schedule) {
            $scheduleDataForSave = array(
                'controllerAction'  => $schedule['controllerAction'],
                'deltaSec'          => $schedule['deltaSec'],
                'interval'          => $schedule['interval'],
                'priority'          => $schedule['priority'],
                'paramsJson'        => $schedule['paramsJson'],
                'exectTime'         => $schedule['exectTime'],
                'hasExectTime'      => $schedule['hasExectTime']
            );
            $schedulesExistsFound = $context->helperArray->filter(
                $schedulesExists, array(
                    'controllerAction'  => $schedule['controllerAction']
                )
            );
            foreach ($schedulesExistsFound as $item) {
                if (!$context->helperAnnotationSchedule
                        ->exists($item, $schedule)
                ) {
                    continue;
                }
                $scheduleDataForSave = array_merge($scheduleDataForSave, 
                    array(
                        'deltaSec'  => $item['deltaSec'],
                        'lastTs'    => $item['lastTs'],
                        'lastDate'  => $item['lastDate'],
                        'inProcess' => $item['inProcess']
                    )
                );
            }
            $schedulesForSave[] = $scheduleDataForSave;
        }
        $scheduleQueryTruncate = $context->queryBuilder
            ->truncateTable('Schedule');
        $context->dds->execute($scheduleQueryTruncate);
        foreach ($schedulesForSave as $schedule) {
            $scheduleQueryInsert = $context->queryBuilder
                ->insert('Schedule')
                ->values($schedule);
            $context->unitOfWork->push($scheduleQueryInsert);
        } 
        $context->unitOfWork->flush();
    }
}