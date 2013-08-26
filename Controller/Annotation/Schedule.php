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
     * @Context("helperAnnotationSchedule", "helperArray")
     */
    public function update($data, $context)
    {
        $schedules = array();
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
            $schedules[$controllerAction][] = array(
                'interval'  => $interval,
                'priority'  => $priority,
                'params'    => $params
            );
        }
        $dds = $this->getService('dds');
        $queryBuilder = $this->getService('query');
        $serviceSchedule = array();
        if (isset($schedules['Service/run'])) {
            $serviceSchedule = $schedules['Service/run'];
            unset($schedules['Service/run']);
        }
        $scheduleNames = array_keys($schedules);
        $scheduleQuery = $queryBuilder
            ->select('id', 'controllerAction', 'priority', 'paramsJson', 
                'deltaSec')
            ->from('Schedule');
        $existsSchedules = $dds->execute($scheduleQuery)->getResult()
            ->asTable();
        $existsScheduleNames = array_unique(
            $context->helperArray->column($existsSchedules, 'controllerAction')
        );
        $existsServiceSchedule = array();
        $serviceIndex = array_search('Service/run', $existsScheduleNames);
        if ($serviceIndex !== false) {
            unset($existsScheduleNames[$serviceIndex]);
            foreach ($existsSchedules as $schedule) {
                if ($schedule['controllerAction'] != 'Service/run') {
                    continue;
                }
                $schedule['params'] = json_decode(
                    urldecode($schedule['paramsJson']), true
                );
                unset($schedule['paramsJson']);
                $existsServiceSchedule[] = $schedule;
            }
        }
        $unitOfWork = $this->getService('unitOfWork');
        if ($serviceSchedule) {
            $indexedServiceSchedule = array();
            $indexedExistsServiceSchedule = array();
            foreach ($serviceSchedule as $schedule) {
                $key = $schedule['params']['name'] . '/'. 
                    $schedule['params']['method'];
                $indexedServiceSchedule[$key] = $schedule;
            }
            if ($existsServiceSchedule) {
                foreach ($existsServiceSchedule as $schedule) {
                    $key = $schedule['params']['name'] . '/'. 
                        $schedule['params']['method'];
                    $indexedExistsServiceSchedule[$key] = $schedule; 
                }
            }
            $serviceScheduleNames = array_keys($indexedServiceSchedule);
            $existsServiceScheduleNames = array_keys(
                $indexedExistsServiceSchedule
            );
            sort($serviceScheduleNames);
            sort($existsServiceScheduleNames);
            $renaimedServiceScheduleNames = array_intersect(
                $serviceScheduleNames, $existsServiceScheduleNames
            );
            $addedServiceScheduleNames = array_diff(
                $serviceScheduleNames, $existsServiceScheduleNames
            );
            $deletedServiceScheduleNames = array_diff(
                $serviceScheduleNames, $existsServiceScheduleNames
            );
            if ($renaimedServiceScheduleNames) {
                foreach ($renaimedServiceScheduleNames as $schedule) {
                    $scheduleData = $indexedServiceSchedule[$schedule];
                    $deltaSec = $context->helperAnnotationSchedule->delta(
                        $scheduleData
                    );
                    $paramsJson = urlencode(
                        json_encode($scheduleData['params'])
                    );
                    $priority = $scheduleData['priority'];
                    $oldScheduleData = $indexedExistsServiceSchedule[$schedule];
                    $oldParamsJson = urlencode(
                        json_encode($oldScheduleData['params'])
                    );
                    if ($deltaSec == $oldScheduleData['deltaSec'] &&
                        $paramsJson == $oldParamsJson &&
                        $priority == $oldScheduleData['priority']) {
                        continue;
                    }
                    $query = $queryBuilder
                        ->update('Schedule')
                        ->set('deltaSec', $deltaSec)
                        ->set('priority', $priority)
                        ->set('paramsJson', $paramsJson);
                    $unitOfWork->push($query);
                }
                $unitOfWork->flush();
            }
            if ($deletedServiceScheduleNames) {
                $deleteIds = array();
                foreach ($deletedServiceScheduleNames as $schedule) {
                    if (!isset($indexedExistsServiceSchedule[$schedule])) {
                        continue;
                    }
                    $id = $indexedExistsServiceSchedule[$schedule]['id'];
                    $deleteIds[] = $id;
                }
                if ($deleteIds) {
                    $query = $queryBuilder
                        ->delete()
                        ->from('Schedule')
                        ->where('id', $deleteIds);
                    $dds->execute($query);
                }
            }
            if ($addedServiceScheduleNames) {
                foreach ($addedServiceScheduleNames as $schedule) {
                    $scheduleData = $indexedServiceSchedule[$schedule];
                    $deltaSec = $context->helperAnnotationSchedule->delta(
                        $scheduleData
                    );
                    $paramsJson = urlencode(
                        json_encode($scheduleData['params'])
                    );
                    $query = $queryBuilder
                        ->insert('Schedule')
                        ->values(array(
                            'controllerAction'  => 'Service/run',
                            'deltaSec'          => $deltaSec,
                            'lastTs'            => time(),
                            'paramsJson'        => $paramsJson,
                            'priority'          => $scheduleData['priority']
                        ));
                    $unitOfWork->push($query);
                }
                $unitOfWork->flush();
            }
        }
        sort($existsScheduleNames);
        sort($scheduleNames);
        $addedScheduleNames = array_diff($scheduleNames, $existsScheduleNames);
        $deletedScheduleNames = array_diff(
            $existsScheduleNames, $scheduleNames
        );
        if ($deletedScheduleNames) {
            $deleteQuery = $queryBuilder
                ->delete()
                ->from('Schedule')
                ->where('controllerAction', $deletedScheduleNames);
            $dds->execute($deleteQuery);
        }
        if ($addedScheduleNames) {
            foreach ($addedScheduleNames as $scheduleName) {
                if (!isset($schedules[$scheduleName])) {
                    continue;
                }
                $scheduleData = reset($schedules[$scheduleName]);
                $deltaSec = $context->helperAnnotationSchedule->delta(
                    $scheduleData
                );
                $paramsJson = urlencode(json_encode($scheduleData['params']));
                $insertQuery = $queryBuilder
                    ->insert('Schedule')
                    ->values(array(
                        'controllerAction'  => $scheduleName,
                        'deltaSec'          => $deltaSec,
                        'lastTs'            => time(),
                        'paramsJson'        => $paramsJson,
                        'priority'          => $scheduleData['priority']
                    ));
                $unitOfWork->push($insertQuery);
            }
            $unitOfWork->flush();
        }
    }
}