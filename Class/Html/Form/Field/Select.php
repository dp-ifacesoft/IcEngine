<?php
/**
 * Поле формы, имеющее несколько значений для выбора
 *
 * @author LiverEnemy
 */

abstract class Html_Form_Field_Select extends Html_Form_Field
{
    /**
     * @inheritdoc
     */
    protected $_type = 'select';

    /**
     * Опшены для выбора
     *
     * Каждый опшен - это Value_Object с данными ['value' => ..., 'text' => ...],
     * из которых элемент 'value' будет отправлен из формы на сервер,
     * а элемент 'text' будет виден на форме пользователю
     *
     * @var Html_Form_Field_Select_Option[]
     */
    protected $_options = [];

    /**
     * @var Html_Form_Field_Select_Option|NULL
     */
    protected $_selected;

    /**
     * Добавить новый опшен для выбора значения
     *
     * @param Html_Form_Field_Select_Option $option Ассоциативный массив данных, которыми требуется заполнить опшен
     * @return $this
     */
    protected function _addOption(Html_Form_Field_Select_Option $option)
    {
        $this->_options[] = $option;
        return $this;
    }

    /**
     * Очистить список доступных опшенов для выбора
     *
     * @return $this
     */
    protected function _clearOptions()
    {
        $this->_options = [];
        return $this;
    }


    /**
     * Получить опшен с заданным значением
     *
     * @param string $value Интересующее значение
     * @return Html_Form_Field_Select_Option|NULL
     */
    protected function _getOptionByValue($value)
    {
        $options = $this->getOptions();
        foreach ($options as $option) {
            if ($option->getValue() == $value) {
                return $option;
            }
        }
        return NULL;
    }

    /**
     * Initialize an _options field
     *
     * @return mixed
     */
    abstract protected function _initOptions();

    /**
     * Сделать выбранным определенный опшен
     *
     * @param Html_Form_Field_Select_Option $option Выбираемый опшен
     * @return $this
     */
    protected function _setSelected(Html_Form_Field_Select_Option $option)
    {
        $this->_selected = $option;
        return $this;
    }

    /**
     * Получить опшены для выбора
     *
     * @return Html_Form_Field_Select_Option[]
     */
    public function getOptions()
    {
        if (empty($this->_options)) {
            $this->_initOptions();
        }
        return $this->_options;
    }

    /**
     * Получить выбранный опшен
     *
     * @return Html_Form_Field_Select_Option|NULL
     */
    public function getSelected()
    {
        return $this->_selected;
    }

    /**
     * Проверить, есть ли интересующий опшен в данном списке выбора
     *
     * @param Html_Form_Field_Select_Option $option Проверяемый опшен
     * @return bool
     */
    public function hasOption(Html_Form_Field_Select_Option $option)
    {
        $options = $this->getOptions();
        foreach ($options as $item) {
            if ($option == $item) {
                return true;
            }
        }
        return false;
    }

    /**
     * Проверить, есть ли опшен с интересующим нас value
     *
     * @param string $value Интересующее нас значение
     * @return bool
     */
    public function hasOptionValue($value)
    {
        return (bool) $this->_getOptionByValue($value);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->_initOptions();
        return parent::init();
    }

    /**
     * Выбрать опшен
     *
     * @param Html_Form_Field_Select_Option $option     Опшен для выбора
     * @param bool                          $autoAdd    Добавить опшен в поле в случае его отсутствия
     * @return $this
     * @throws Exception В случае, если выбираемого опшена нет и отключено автодобавление
     */
    public function select(Html_Form_Field_Select_Option $option, $autoAdd = false)
    {
        if (!$this->hasOption($option)) {
            if (!$autoAdd) {
                throw new Exception(__METHOD__ . ': There\'s no such option to select. Try to set autoAdd to true');
            }
            $this->_addOption($option);
        }
        $selected = $this->getSelected();
        if ($selected) {
            $selected->unSelect();
        }
        $option->select();
        $this->_setSelected($option);
        return parent::setValue($option->getValue());
    }

    /**
     * Задать доступные для выбора опшены
     *
     * @param array $options Массив массивов данных для создания опшенов
     * @return $this
     */
    public function setOptions(array $options = [])
    {
        $this->_clearOptions();
        $optionManager = App::htmlFormFieldSelectOptionManager();
        foreach ($options as $optionData) {
            /** @var Html_Form_Field_Select_Option $option */
            $option = $optionManager->get('');
            $option->setData($optionData);
            $this->_addOption($option);
        }
        return $this;
    }

    /**
     * Задать выбранное значение
     *
     * @param string $value Устанавливаемое значение
     * @return $this
     */
    public function setValue($value)
    {
        $option = $this->_getOptionByValue($value);
        if ($option) {
            return $this->select($option);
        }
        return $this;
    }
}