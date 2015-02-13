<?php
/**
 * REST контроллер
 *
 * @author LiverEnemy
 */

class Controller_Rest extends Controller_Abstract
{
    /**
     * Точка входа для REST-запросов
     *
     * Во избежание непонимания специфики HTTP-метода POST,
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
     * @Route(
     *      "/REST/v1/{$service}/{$action}.{$viewRenderName}",
     *      "name"="restIndex",
     *      "weight"=100,
     *      "components"={
     *          "service"={
     *              "pattern"="([^/]+)"
     *          },
     *          "action"={
     *              "pattern"="([^/]+)"
     *          },
     *          "viewRenderName"={
     *              "pattern"="(json|xml)"
     *          }
     *      }
     * )
     *
     * @Route(
     *      "/REST/v1/{$service}/{$action}/{$id}.{$viewRenderName}",
     *      "name"="restIndexById",
     *      "weight"=100,
     *      "components"={
     *          "service"={
     *              "pattern"="([^/]+)"
     *          },
     *          "action"={
     *              "pattern"="([^/]+)"
     *          },
     *          "id"={
     *              "pattern"="([0-9]+)"
     *          },
     *          "viewRenderName"={
     *              "pattern"="(json|xml)"
     *          }
     *      }
     * )
     *
     * @param string $service           Название сервиса Rest_Api
     * @param string $action            Название экшена Rest_Api
     * @param string $viewRenderName    Название viewRender для экшена
     */
    public function index($service, $action, $viewRenderName)
    {
        /** @var View_Render_Manager $viewRenderManager */
        $viewRenderManager = $this->getService('viewRenderManager');
        $viewRender = $viewRenderManager->byName(ucfirst($viewRenderName));
        $this->getTask()->setViewRender($viewRender);
        $input = $this->input->receiveAll();
        /** @var Request $requestService */
        $requestService = $this->getService('request');
        $data = $requestService->receiveAllFromInput($input);
        /** @var Service_Rest_Api_Manager $restApiManager */
        $restApiManager = $this->getService('serviceRestApiManager');
        $restApi = $restApiManager->get($service, 'Default');
        $result = $restApi
            ->setRequestData($data)
            ->setAction($action)
            ->call()
        ;
        $this->output->send($result);
    }
} 