<?php

/**
 * Генератор всего
 *
 * @author markov
 */
class Controller_Create extends Controller_Abstract
{
    /**
     * Генерирует класс vo с геттерами
     * 
     * @param string $name название класса
     * @Template(null)
     */
    public function vo($name)
    {
        $nameClass = 'Vo_' . $name;
        $config = App::configManager()->get($nameClass);
        if (!$config) {
            echo 'Конфиг Vo не найден';
            return false;
        }
        $resultFields = [];
        if (isset($config['fields'])) {
            foreach ($config['fields']->__toArray() as $key => $field) {
                if (is_array($field)) {
                    $resultFields[$key] = $field;
                } else {
                    $resultFields[$field] = [];
                }
            }
        }
        $output = App::helperCodeGenerator()->fromTemplate(
            'vo', [
                'name'      => $nameClass,
                'comment'   => $config['comment'] ? $config['comment'] : null, 
                'author'   => $config['author'] ? $config['author'] : null,
                'fields'    => $resultFields,
            ]
        );
        $filename = IcEngine::root() . 'Ice/Class/' . str_replace('_', '/', $nameClass) . '.php';
        file_put_contents($filename, $output);
    }
}
