<?php

/**
 * Хелпер для миграций
 *
 * @Service("helperMigration")
 * @author morph, neon
 */
class Helper_Migration extends Helper_Abstract
{
    /**
     * Хелпер по генерации кода
     * 
     * @Inject("helperCodeGenerator")
     * @var Helper_Code_Generator
     */
    protected $helperCodeGenerator;
    
    /**
     * Хелпер по работе с датами
     * 
     * @Inject("helperDate")
     * @var Helper_Date
     */
    protected $helperDate;
    
    /**
     * Хелпер для получение последовательности
     * 
     * @var Helper_Migration_Sequence
     * @Inject("helperMigrationSequence")
     */
    protected $helperMigrationSequence;
    
    /**
     * Входящий транспорт
     * 
     * @var Data_Transport
     * @Service(
     *      "helperMigrationInput", 
     *      args={"cliInput"},
     *      isStatic=true,
     *      source={
     *          name="dataTransportManager",
     *          method="get"
     *      }
     * )
     */
    protected $input;
    
    /**
     * Создает новую миграцию
     * 
     * @param string $name Название миграции
     * @param string $category Категория миграции
     * @param array $params Аргументы
     */
	public function create($name, $category, $params = array())
    {
        $author = $this->input['author'];
        $comment = $this->input['comment'];
        $createdAt = $this->helperDate->toUnix();
        $sequence = $this->helperMigrationSequence->next();
        $params = array_merge($params, array(
            'name'      => $name,
            'comment'   => $comment,
            'category'  => $category,
            'author'    => $author,
            'createdAt' => $createdAt,
            'sequence'  => $sequence
        ));
        $output = $this->helperCodeGenerator->fromTemplate(
            'migration', $params
        );
        $this->writeMigration($name, $output);
    }
    
    /**
     * Получить имя миграции
     * 
     * @param string $name
     * @return string
     */
    public function getName($name)
    {
        $unique = substr($this->getService('helperUnique')->hash(), 3, 6);
        $migrationName = $name . date('Ymd') . $unique; 
        return $migrationName;
    }
    
    /**
     * Изменить хелпер по генерации кода
     * 
     * @param Helper_Code_Generator $helperCodeGenerator
     */
    public function setHelperCodeGenerator($helperCodeGenerator)
    {
        $this->helperCodeGenerator = $helperCodeGenerator;
    }
    
    /**
     * Изменить хелпер по работе с датами
     * 
     * @param Helper_Date $helperDate
     */
    public function setHelperDate($helperDate)
    {
        $this->helperDate = $helperDate;
    }
    
    /**
     * Изменить хелпер последовательностей
     * 
     * @param Helper_Migration_Sequence $helperMigrationSequence
     */
    public function setHelperMigrationSequence($helperMigrationSequence)
    {
        $this->helperMigrationSequence = $helperMigrationSequence;
    }
    
    /**
     * Изменить входящий транспорт
     * 
     * @param Data_Transport $input
     */
    public function setInput($input)
    {
        $this->input = $input;
    }
    
    /**
     * Сохранить миграцию
     * 
     * @param string $name
     * @param string $output
     */
    protected function writeMigration($name, $output)
    {
        $filename = IcEngine::root() . 'Ice/Model/Migration/' . $name . '.php';
        file_put_contents($filename, $output);
    }
}