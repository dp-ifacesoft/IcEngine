<?php
/**
 * REST контроллер
 *
 * @author LiverEnemy
 */

class Controller_Rest extends Controller_Abstract
{
    /**
     * Получить одну модель
     *
     * @Route(
     *      "/REST/v1/{$service}/get/{$action}/{$id}.xml",
     *      "name"="restGetByIdXml",
     *      "weight"=100,
     *      "params"={
     *          "viewRender"="xml"
     *      },
     *      "components"={
     *          "service"={
     *              "pattern"="([^/]+)"
     *          },
     *          "action"={
     *              "pattern"="([^/]+)"
     *          },
     *          "id"={
     *              "pattern"="([0-9]+)"
     *          }
     *      }
     * )
     *
     * @Route(
     *      "/REST/v1/{$service}/get/{$action}/{$id}.json",
     *      "name"="restGetByIdJson",
     *      "weight"=100,
     *      "params"={
     *          "viewRender"="json"
     *      },
     *      "components"={
     *          "service"={
     *              "pattern"="([^/]+)"
     *          },
     *          "action"={
     *              "pattern"="([^/]+)"
     *          },
     *          "id"={
     *              "pattern"="([0-9]+)"
     *          }
     *      }
     * )
     *
     * @Route(
     *      "/REST/v1/{$service}/get/{$action}.json",
     *      "name"="restGetJson",
     *      "weight"=100,
     *      "params"={
     *          "viewRender"="json"
     *      },
     *      "components"={
     *          "service"={
     *              "pattern"="([^/]+)"
     *          },
     *          "action"={
     *              "pattern"="([^/]+)"
     *          }
     *      }
     * )
     *
     * @Route(
     *      "/REST/v1/{$service}/get/{$action}.xml",
     *      "name"="restGetXml",
     *      "weight"=100,
     *      "params"={
     *          "viewRender"="xml"
     *      },
     *      "components"={
     *          "service"={
     *              "pattern"="([^/]+)"
     *          },
     *          "action"={
     *              "pattern"="([^/]+)"
     *          }
     *      }
     * )
     */
    public function get($service, $action, $id = null)
    {
        /** @var Service_Rest_Api_Manager $serviceRestApiManager */
        $serviceRestApiManager = $this->getService('serviceRestApiManager');
        $restApi = $serviceRestApiManager->get($service, 'Default');
        $result = $restApi
            ->setAction($action)
            ->setRequestData([
                'action'    => 'get' . ucfirst($action),
                'id'        => $id,
            ])
            ->call();
        $this->output->send($result);
    }

    /**
     * Обработка POST-запроса
     *
     * Во избежание использования данного метода прикладными разработчиками не по назначению,
     * привожу здесь перевод главы 9.5 стандарта RFC2616, описывающего протокол HTTP v1.1.
     *
     * HTTP-метод POST используется, чтобы "попросить" основной сервер (не шлюз)
     * принять содержащуюся в запросе сущность как экземпляр ресурса, идентифицированного с помощью Request-URI
     * в строке запроса.
     *
     * POST разработан, чтобы предоставить разработчикам унифицированный метод, покрывающий следующий функционал:
     *  - Добавление примечаний, комментариев к имеющимся ресурсам.
     *  - Отправка сообщения на форум, в группу новостей, список почтовой рассылки или в подходящую группу статей.
     *  - Предоставление блока данных (таких, как результат отправки формы) методу-обработчику данных.
     *  - Увеличение БД с помощью операции добавления данных (например, INSERT).
     *
     * В конечном счете, функция, выполняемая методом POST, определяется сервером
     * и обычно зависит от Request-URI. Добавленная сущность должна принадлежать ресурсу,
     * описываемому указанным URI, так же, как файл принадлежит содержащей его папке,
     * новостная статья принадлежит группе новостей, в которой эта статья размещена, или запись принадлежит своей БД.
     *
     * Действие, выполняемое методом POST, может и не иметь своим следствием ресурс,
     * идентифицируемый Request-URI.
     * В этом случае необходимо возвратить либо статус 200 (OK), либо 204 (No Content)
     * в зависимости от того, включает ли ответ сущность, описываемую результатом.
     *
     *
     * @param string $service   Название сервиса REST API, к которому стоит отправить обращение
     * @param string $action    Название целевого метода сервиса $service
     *
     * @Route(
     *      "/REST/v1/{$service}/post/{$action}.json",
     *      "name"="restPostJson",
     *      "weight"=100,
     *      "params"={
     *          "viewRender"="json"
     *      },
     *      "components"={
     *          "service"={
     *              "pattern"="([^/]+)"
     *          },
     *          "action"={
     *              "pattern"="([^/]+)"
     *          }
     *      }
     * )
     *
     * @Route(
     *      "/REST/v1/{$service}/post/{$action}.xml",
     *      "name"="restPostXml",
     *      "weight"=100,
     *      "params"={
     *          "viewRender"="xml"
     *      },
     *      "components"={
     *          "service"={
     *              "pattern"="([^/]+)"
     *          },
     *          "action"={
     *              "pattern"="([^/]+)"
     *          }
     *      }
     * )
     *
     */
    public function post($service, $action)
    {
        /** @var Service_Rest_Api_Manager $serviceRestApiManager */
        $serviceRestApiManager = $this->getService('serviceRestApiManager');
        $restApi = $serviceRestApiManager->get($service, 'Default');
        /** @var Request $requestService */
        $requestService = $this->getService('request');
        $data = $requestService->parsePhpInput();
        $result = $restApi
            ->setAction($action)
            ->setRequestData($data)
            ->call();
        $this->output->send($result);
    }
} 