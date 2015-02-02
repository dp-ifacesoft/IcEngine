<?php
/**
 * Поле HTML-формы
 *
 * @author LiverEnemy
 */

abstract class Html_Form_Field
{
    /**
     * Поля-контрагенты (подрядчики)
     *
     * Поля, значениями которых может пользоваться данное поле.
     *
     * Структура должна совпадать со структурой $_contractorRequirements, кроме того, что
     * значениями элементов массива должны быть не строковые имена классов полей,
     * а сами экземпляры полей
     *
     * @var Html_Form_Field[]
     *
     */
    protected $_contractors = [];

    /**
     * Ассоциативный массив требований к полям-подрядчикам, без которых выбор значения невозможен
     *
     * @var string[]
     *
     * Заполнять массив нужно таким образом:
     *  <code>
     *  protected $_contractorRequirements = [
     *      'countryUrl'    => 'Country_Url',
     *  ];
     *  </code>
     */
    protected $_contractorRequirements = [];
    /**
     * Форма, которой принадлежит данное поле
     *
     * @var Html_Form
     */
    protected $_form;

    /**
     * @var Data_Transport Входной Data_Transport для извлечения данных
     */
    protected $_input;

    /**
     * Подпись к элементу ввода Html-поля
     *
     * @var string
     */
    protected $_label;

    /**
     * Атрибут name элемента ввода Html-поля
     *
     * Если не установлено в коде, то при создании объекта поля конструируется автоматически из имени поля.
     * Прикладному разработчику оставлена возможность указать это имя самостоятельно
     * на случай конфликтов имен GET-параметров или жестко прописанного имени в задании на разработку
     * (можете поверить: необходимость строго заданных имен GET-параметров уже бывала).
     *
     * @var string
     */
    protected $_name;

    /**
     * Тип поля для выбора наиболее подходящего элемента ввода в пользовательском интерфейсе
     *
     * @var string
     */
    protected $_type = 'text';

    /**
     * Значение для фильтрации
     *
     * @var string
     */
    protected $_value;


    /**
     * Защита от дурака: проверка на установленные значения $_type, $_name и $_label в коде класса поля
     *
     * Дорогие коллеги! Для правильного выбора элемента интерфейса
     * ОБЯЗАТЕЛЬНО задавайте $_type и $_label в своих классах Html-полей!
     *
     * @throws Exception в случае, если не установлены все необходимые поля класса в программном коде
     */
    public function __construct()
    {
        $type = $this->getType();
        if (empty($type))
        {
            throw new Exception(__CLASS__ . ": filter type was not set");
        }
        $label = $this->getLabel();
        if (!$label)
        {
            throw new Exception(__CLASS__ . ': $_label required for a filter was not set');
        }
        $name = $this->getName();
        if (!isset($name))
        {
            $className = get_class($this);
            $name = 'field-' . substr($className, strlen('Html_Form_Field_'));
            $this->_setName($name);
        }
    }
    /**
     * Получить поле-контрагент с определенным индексом
     *
     * @param   string              $index Индекс требуемого поля
     * @return  null|Html_Form_Field
     */
    protected function _getContractor($index)
    {
        $contractors = $this->_getContractors();
        if (empty($contractors[$index]) || !($contractors[$index] instanceof Html_Form_Field)) {
            return NULL;
        }
        return $contractors[$index];
    }

    /**
     * Получить все поля-контрагенты
     *
     * @return Html_Form_Field[]
     */
    protected function _getContractors()
    {
        return $this->_contractors;
    }

    /**
     * Получить имя класса требуемого поля-контрагента по известному индексу
     *
     * @param string $index Индекс проверяемого требования
     * @return null|string
     */
    protected function _getContractorRequirement($index)
    {
        $requirements = $this->_getContractorRequirements();
        if (empty($requirements[$index])) {
            return null;
        }
        $namePart = $requirements[$index];
        return App::htmlFormFieldManager()->getClassName($namePart);
    }

    /**
     * Получить все требования к полям-контрагентам
     *
     * @return string[]
     */
    protected function _getContractorRequirements()
    {
        return $this->_contractorRequirements;
    }

    /**
     * Получить ассоциативный массив всех полей-контрагентов, требуемых данному полю
     *
     * @return Html_Form_Field[]
     */
    protected function _getRequiredContractors()
    {
        $contractors = $this->_getContractors();
        $required = [];
        foreach ($contractors as $index => $contractor) {
            if (!$this->_requires($index)) {
                continue;
            }
            $required[] = $contractor;
        }
        return $required;
    }

    /**
     * Проверить, требуется ли данному полю другое поле с указанным индексом
     *
     * @param string $index Индекс проверяемого подрядчика
     * @return bool
     */
    protected function _requires($index)
    {
        return (bool) $this->_getContractorRequirement($index);
    }

    /**
     * Задать поле-контрагент по индексу
     *
     * @param string            $index      Индекс поля-контрагента
     * @param Html_Form_Field   $contractor Экземпляр поля-контрагента
     * @return                  $this
     * @throws Exception        В случае предоставления обязательного $contractor не того типа,
     *                          который указан в $this->_contractorRequirements[$index]
     */
    protected function _setContractor($index, Html_Form_Field $contractor)
    {
        $requirement = $this->_getContractorRequirement($index);
        if ($requirement && !($contractor instanceof $requirement)) {
            throw new Exception(__METHOD__ . ' requires a contractor to be an instance of ' . $requirement);
        }
        $this->_contractors[$index] = $contractor;
        return $this;
    }

    /**
     * Установить атрибут name поля ввода в форме
     *
     * @param string $name
     * @return $this
     */
    protected function _setName($name)
    {
        $this->_name = $name;
        return $this;
    }

    /**
     * Получить форму, к которой относится данное поле
     *
     * @return Html_Form
     * @throws Exception при отсутствии установленной Html_Form
     */
    public function getForm()
    {
        if (empty($this->_form)) {
            throw new Exception(__METHOD__ . ' requires ' . __CLASS__ . ' to have an Html_Form owner');
        }
        return $this->_form;
    }

    /**
     * Получить установленные ранее входные данные
     *
     * @return Data_Transport
     */
    public function getInput()
    {
        $form = $this->getForm();
        return $form->getInput();
    }

    /**
     * Получить подпись к элементу ввода поля на форме
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->_label;
    }

    /**
     * Получить тип фильтра для выбора элемента ввода на форме
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Получить значение для фильтрации
     *
     * @return string
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * Получить имя GET-параметра с требуемыми данными для фильтрации
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Проверить, указано ли значение в данном поле
     *
     * @return bool
     */
    public function hasValue()
    {
        return !empty($this->_value);
    }

    public function init()
    {
        if (!$this->isReady()) {
            return $this;
        }
        $name = $this->getName();
        $input = $this->getInput();
        $serviceDataTransport = App::serviceDataTransport();
        $value = $serviceDataTransport->receiveFromHierarchical($input, $name);
        $this->setValue($value);
        return $this;
    }

    /**
     * Готово ли данное поле к работе
     *
     * Необходымым и достаточным условием готовности к работе является готовность к работе и наличие выбранных значений
     * у всех полей-контрагентов (contractors).
     *
     * @return bool
     */
    public function isReady()
    {
        $contractorsRequired = $this->_getRequiredContractors();
        foreach ($contractorsRequired as $index => $contractor) {
            if (!$contractor->isReady() || !$contractor->hasValue()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Задать поля-контрагенты
     *
     * @param array $contractors Ассиоциативный массив полей, являющихся подрядчиками для данного поля
     * @return $this
     * @throws Exception
     */
    public function setContractors(array $contractors = [])
    {
        foreach ($contractors as $index => $contractor) {
            $this->_setContractor($index, $contractor);
        }
        return $this;
    }

    /**
     * Установить форму-владельца поля ввода
     *
     * @param Html_Form $form Форма, на которой расположено данное поле
     * @return $this
     */
    public function setForm(Html_Form $form)
    {
        $this->_form = $form;
        return $this;
    }

    /**
     * Установить входные данные для фильтрации
     *
     * @param   Data_Transport  $input
     * @return  $this
     */
    public function setInput(Data_Transport $input)
    {
        $this->_input = $input;
        return $this;
    }

    /**
     * Установить значение для фильтрации
     *
     * @param string $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->_value = $value;
        return $this;
    }
}