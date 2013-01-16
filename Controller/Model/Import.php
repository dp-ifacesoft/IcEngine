<?php

/**
 * Инпорт схемы модели из СУБД. Создание файлов модели и схемы
 * 
 * @author morph
 */
class Controller_Model_Import extends Controller_Abstract
{
    /**
     * Начать инпорт модели
     * 
     * @Context("modelScheme", "helperCodeGenerator", "helperDate")
     */
    public function run($name, $to, $author, $package, $copyright, $context)
    {
        $this->task->setTemplate(null);
        $user = $context->user->getCurrent();
        if ($user->key() >= 0) {
            $signal = new \Event_Signal(array(
                'message'   => 'Should run only from console.',
                'method'    => __METHOD__
            ), 'RuntimeError');
            return $signal->notify();
        }
        if (!$to) {
            $to = $context->modelScheme->tableToModel($name);
        }
        $parts = explode('_', $to);
        $last = array_pop($parts);
        $dirName = IcEngine::root() . 'Ice/Model/' . implode('/', $parts) . '/';
        if (!is_dir($dirName)) {
            mkdir($dirName, 0644, true);
        }
        $filename = str_replace('//', '/', $dirName . $last . '.php');
        if (file_exists($filename)) {
            $signal = new \Event_Signal(array(
                'message'   => 'Model file is already exists.',
                'method'    => __METHOD__
            ), 'RuntimeError');
            return $signal->notify();
        }
        $dbQuery = $context->queryBuilder
            ->show('TABLE STATUS')
            ->where('Name', $name);
        $data = $context->dds->execute($dbQuery)->getResult()->asRow();
        if (!$data) {
            $signal = new \Event_Signal(array(
                'message'   => 'Table "' . $name . '" not found in scheme.',
                'method'    => __METHOD__
            ), 'RuntimeError');
            return $signal->notify();
        }
        $schemeQuery = $context->queryBuilder
            ->show('FULL COLUMNS')
            ->from('`' . $name . '`');
        $scheme = $context->dds->execute($schemeQuery)->getResult()->asTable();
        $fields = array();
        foreach ($scheme as $field) {
            $fieldName = $field['Field'];
            $tmp = explode(' ', $field['Type']);
            $size = 0;
            $type = $tmp[0];
            $brPos = strpos($tmp[0], '(');
            if ($brPos > 0) {
                $endBrPos = strpos($tmp[0], ')');
                $type = substr($tmp[0], 0, $brPos);
                $size = substr($tmp[0], $brPos + 1, $endBrPos - $brPos - 1);
            }
            $unsigned = false;
            if (isset($tmp[1])) {
                $unsigned = true;
            }
            $notNull = $field['Null'] == 'NO';
            $autoIncrement = $field['Extra'] == 'auto_increment';
            $output = array();
            if ($size) {
                $output[] = 'Size=' . $size;
            }
            if ($unsigned) {
                $output[] = 'Unsigned';
            }
            if ($notNull) {
                $output[] = 'Not_Null';
            }
            if ($autoIncrement) {
                $output[] = 'Auto_Increment';
            }
            $fields[$fieldName] = array(
                'type'          => ucfirst($type),
                'size'          => $size,
                'field'         => $fieldName,
                'unsigned'      => $unsigned,
                'notNull'       => $notNull,
                'autoIncrement' => $autoIncrement,
                'comment'       => $field['Comment'],
                'output'        => $output ? implode(', ', $output) : null,
                'indexes'       => array()
            );
        }
        $indexQuery = $context->queryBuilder
            ->show('INDEXES')
            ->from('`' . $name . '`');
        $indexScheme = $context->dds->execute($indexQuery)->getResult()
            ->asTable();
        foreach ($indexScheme as $index) {
            $indexName = $index['Key_name'];
            $type = 'Key';
            if ($indexName == 'PRIMARY') {
                $type = 'Primary';
            } elseif (!$index['Non_unique']) {
                $type = 'Unique';
            }
            $fields[$index['Column_name']]['indexes'][$type][] = $indexName;
        }
        $output = $context->helperCodeGenerator->fromTemplate(
            'model',
            array(
                'author'        => $author,
                'comment'       => $data['Comment'],
                'date'          => $context->helperDate->toUnix(),
                'properties'    => $fields,
                'table'         => $name,
                'name'          => $to,
                'extends'       => 'Model',
                'package'       => $package,
                'category'      => 'Model',
                'copyright'     => $copyright,
                'notCommentedProperties' => true
            )
        );
        echo $output . PHP_EOL;
        file_put_contents($filename, $output);
    }
}