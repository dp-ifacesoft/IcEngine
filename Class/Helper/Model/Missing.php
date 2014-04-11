<?php

/**
 * Хелпер для поиска моделей, для которых есть схемы, но нет самих моделей
 * 
 * @author morph
 * @Service("helperModelMissing")
 */
class Helper_Model_Missing extends Helper_Abstract
{
    /**
     * Получить список схем моделей
     * 
     * @return array
     */
    public function getClassNames()
    {
        $path = IcEngine::root() . 'Ice/Config/Model/Mapper/';
		$command = 'find ' . $path . '*';
		ob_start();
		system ($command);
		$content = ob_get_contents();
		ob_end_clean();
		if (!$content) {
			return array();
		}
		$files = explode(PHP_EOL, $content);
		if (!$files) {
			return array();
		}
        $result = array();
        foreach (array_slice($files, 2) as $file) {
            if (!is_file($file)) {
				continue;
			}
			$className = substr(
                str_replace('/', '_', str_replace($path, '', $file)),
                0, -4
            );
			if ($className == 'Scheme') {
				continue;
			}
			$result[] = $className;
        }
        return $result;
    }
    
    /**
     * Получить пропущенные модели
     * 
     * @return array
     */
    public function getMissings()
    {
        $missing = array();
		$classNames = $this->getClassNames();
        foreach ($classNames as $className) {
            $filename = IcEngine::root() . 'Ice/Model/' .
                str_replace('/', '_', $className) . '.php';
            if (!file_exists($filename)) {
                $missing[] = $className;
            }
        }
        return $missing;
    }
}