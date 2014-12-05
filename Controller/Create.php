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
        $fields = isset($config['fields']) ? $config['fields'] : [];
        $output = App::helperCodeGenerator()->fromTemplate(
            'vo', [
                'name'      => $nameClass,
                'fields'    => $fields
            ]
        );
        $filename = IcEngine::root() . 'Ice/Class/' . str_replace('_', '/', $nameClass) . '.php';
        file_put_contents($filename, $output);
    }
}
