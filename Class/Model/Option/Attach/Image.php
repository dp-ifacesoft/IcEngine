<?php

/**
 * Добавляет в ['data']['images'] изображения
 *
 * @author markov
 */
class Model_Option_Attach_Image extends Model_Option
{
    /**
     * @inheritdoc
     */
    public function after()
    {
        $ids = $this->collection->column('id');
        $modelName = $this->collection->modelName();
        $images = Model_Collection_Manager::create('Component_Image')
            ->addOptions(
                array(
                    'name' => '::Table',
                    'table' => $modelName
                ),
                array(
                    'name' => '::Row',
                    'id' => $ids
                ),
                '::Active'
            )->raw();

        $imagesGroup = array();
        foreach ($images as $image) {
            $imagesGroup[$image['rowId']][] = $image;
        }
        foreach ($this->collection as $model) {
            $model['data']['images'] = array();
            if (!isset($imagesGroup[$model['id']])) {
                continue;
            }
            $model['data']['images'] = $imagesGroup[$model['id']];
        }
    }
}
