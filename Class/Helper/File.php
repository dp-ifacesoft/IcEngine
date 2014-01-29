<?php
/**
 *
 * @desc Помощник работы с файлами
 * @author Юрий
 * @package IcEngine
 * @Service("helperFile")
 */
class Helper_File
{

    /**
     * @desc Возвращает расширение файла
     * @param $filename Имя файла
     * @return string Расширение
     */
    public function extention ($filename)
    {
        return strtolower (substr (strrchr ($filename, '.'), 1));
    }

    /**
     * @desc Удаляет переданные файлы
     * @param string|array $file Путь до файла (файлов).
     * @internal param $_ Произвольное количество путей до файлов.
     * @return integer Количество удаленных файлов
     */
    public function delete ($file)
    {
        $result = 0;
        $files = is_array ($file) ? $file : func_get_args ();

        foreach ($files as $file)
        {
            if (is_file ($file))
            {
                unlink ($file);
                $result++;
            }
        }

        return $result;
    }

    /**
     * @desc Получает список файлов или папок в определенной папке (возможно, рекурсивно)
     * @param string $dir путь к папке, в которой осуществлять поиск
     * @param boolean $sortAsc Сортировать результаты по возрастанию
     * @param boolean $recursive осуществлять ли поиск рекурсивно
     * @param boolean $relative возвращать относительные, а не абсолютные, имена найденных элементов
     * @param boolean $includingFiles включать в выборку найденные файлы
     * @param boolean $includingDirs включать в выборку найденные файлы
     * @return Array
     */
    public function scan($dir,
                                $sortAsc = true,
                                $recursive = false,
                                $relative = true,
                                $includingFiles = true,
                                $includingDirs = false)
    {
        $dir = rtrim($dir, '/');
        if (!is_dir($dir))
        {
            return NULL;
        }
        $elements = scandir($dir, !$sortAsc);
        $return = array();
        foreach ($elements as $item)
        {
            if ($item == '.' || $item == '..')
            {
                continue;
            }
            $path = $dir . '/' . $item;
            if (($includingFiles && is_file($path))
                ||
                ($includingDirs  && is_dir($path)))
            {
                $return[] = $relative ? $item : $path;
            }
            if ($recursive && is_dir($path))
            {
                $return = array_merge($return,
                    $this->scan($path, $sortAsc, $recursive, $relative, $includingFiles, $includingDirs));
            }
        }
        return $return;
    }

}