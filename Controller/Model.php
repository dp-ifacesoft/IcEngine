<?php

/**
 * Контроллер создания модели
 *
 * @author morph
 */
class Controller_Model extends Controller_Abstract
{
	/**
	 * Создание модели
	 * 
     * @Template(null)
     * @Validator("User_Cli")
     * @Context("helperModelCreate", "helperModelScheme")
	 */
	public function create($name, $withoutTable, $extends, $context)
	{
        $dto = $context->helperModelScheme->createDto($name);
        $dto->setWithoutTable($withoutTable);
        if ($extends) {
            $dto->setExtends($extends);
        }
		$context->helperModelCreate->create($dto);
	}
}