<?php
/**
 * Файл класса CModel.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */


/**
 * CModel - это базовый класс, обеспечивающий общие функции, необходимые объектам моделей данных.
 *
 * CModel определяет базовый каркас для моделей данных, которым необходима валидация.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CModel.php 1173 2009-06-26 01:57:22Z qiang.xue $
 * @package system.base
 * @since 1.0
 */
abstract class CModel extends CComponent implements IteratorAggregate, ArrayAccess
{
	private $_errors=array();	// имя атрибута => массив ошибок
	private $_validators;  		// валидаторы
	private $_scenario='';  	// сценарий

	/**
	 * Возвращает список имен атрибутов модели.
	 * @return array список имен атрибутов.
	 * @since 1.0.1
	 */
	abstract public function attributeNames();

	/**
	 * Возвращает правила валидации атрибутов.
	 *
	 * Метод должен переопределяться для объявления правил валидации.
	 * Каждое правило в массиве со следующей структурой:
	 * <pre>
	 * array('список атрибутов', 'имя валидатора', 'on'=>'scenario name', ...параметры валидации...)
	 * </pre>
	 * где
	 * <ul>
	 * <li>список атрибутов: определяет атрибуты (разделенные запятыми) для валидации;</li>
	 * <li>имя валидатора: определяет валидатор для использования. Может быть именем метода класса модели,
	 *   именем встроенного валидатора или класса валидатора (или псевдонима его пути).
	 *   Метод валидации должен иметь следующую структуру:
	 * <pre>
	 * // $params относится к параметрам валидации, полученным в правиле
	 * function validatorName($attribute,$params)
	 * </pre>
	 *   Встроенный валидатор относится к одному из валидаторов, объявленных в свойстве {@link CValidator::builtInValidators}.
	 *   И класс валидатора - это класс, наследующий класс {@link CValidator}.</li>
	 * <li>on: определяет сценарии, для которых должно быть выполнено правило валидации.
	 *   Различные сценарии разделяются запятыми. Если данная опция не установлена, правило
	 *   будет применено ко всем сценариям. За деталями о данной опции обратитесь к свойству {@link scenario}.</li>
	 * <li>дополнительные параметры, используемые для инициализации соответствующих свойств валидатора.
	 *   За списком возможных свойств обращайтесь к API класса индивидуального валидатора.</li>
	 * </ul>
	 *
	 * Пример:
	 * <pre>
	 * array(
	 *     array('username', 'required'),
	 *     array('username', 'length', 'min'=>3, 'max'=>12),
	 *     array('password', 'compare', 'compareAttribute'=>'password2', 'on'=>'register'),
	 *     array('password', 'authenticate', 'on'=>'login'),
	 * );
	 * </pre>
	 *
	 * Примечание: чтобы наследовать правила, определенные в родительском классе, в классе-потомке
	 * необходимо объединить правила родителя с правилами потомка, используя функции такие, как array_merge().
	 *
	 * @return array правила валидации, применяемые при вызове метода {@link validate()}.
	 * @see scenario
	 */
	public function rules()
	{
		return array();
	}

	/**
	 * Возвращает список поведений, свойства которых должна перенимать модель.
	 * Возвращаемое значение должно быть массивом конфигураций поведений, индексированный по
	 * именам поведений. Каждая конфигурация поведения может быть либо строкой, определяющей класс поведения, либо
	 * массивом со следующей структурой:
	 * <pre>
	 * 'behaviorName'=>array(
	 *     'class'=>'path.to.BehaviorClass',
	 *     'property1'=>'value1',
	 *     'property2'=>'value2',
	 * )
	 * </pre>
	 *
	 * Примечание: классы поведений должны реализовывать интерфейс {@link IBehavior} или наследовать класс
	 * {@link CBehavior}. Поведения, объявленные в данном методе будут присоединены
	 * к модели при создании экземпляра модели.
	 *
	 * За деталями о поведениях обратитесь к классу {@link CComponent}.
	 * @return array конфигурации поведений (имя поведения=>конфигурация поведения)
	 * @since 1.0.2
	 */
	public function behaviors()
	{
		return array();
	}

	/**
	 * Возвращает ярлыки атрибутов.
	 * Ярлыки атрибутов в основном используются в сообщениях об ошибках валидации.
	 * По умолчанию ярлык атрибута генерируется с использованием метода {@link generateAttributeLabel}.
	 * Метод позволяет явно определять ярлыки атрибутов.
	 *
	 * Примечание: чтобы наследовать ярлыки, определенные в родительском классе, в классе-потомке
	 * необходимо объединить ярлыки родителя с ярлыками потомка, используя функции такие, как array_merge().
	 *
	 * @return array ярлыки атрибутов (имя=>ярлык)
	 * @see generateAttributeLabel
	 */
	public function attributeLabels()
	{
		return array();
	}

	/**
	 * Выполняет валидацию.
	 *
	 * Метод выполняет правила валидации, объявленные в {@link rules}.
	 * Будут выполнены только правила, применяемые к текущему {@link scenario сценарию}.
	 * Правило считается применимым к сценарию, если его опция 'on' не установлена или содержит сценарий.
	 *
	 * Ошибки, возникающие при валидации, можно получить методом {@link getErrors}.
	 *
	 * @param array список валидируемых атрибутов. По умолчанию - null,
	 * т.е. проверяться должны все атрибуты, перечисленные в применяемых
	 * правилах валидации. Если данный параметр передан в виде списка атрибутов,
	 * валидироваться будут только перечисленные атрибуты.
	 * @return boolean успешна ли валидация
	 * @see beforeValidate
	 * @see afterValidate
	 */
	public function validate($attributes=null)
	{
		$this->clearErrors();
		if($this->beforeValidate())
		{
			foreach($this->getValidators() as $validator)
				$validator->validate($this,$attributes);
			$this->afterValidate();
			return !$this->hasErrors();
		}
		else
			return false;
	}

	/**
	 * Метод вызывается перед началом валидации.
	 * По умолчанию вызывается метод {@link onBeforeValidate} для вызова события.
	 * Вы можете переопределить данный метод, чтобы выполнить предварительные действия перед валидацией.
	 * Убедитесь, что родительский метод также вызывается.
	 * @return boolean должна ли выполняться валидация. По умолчанию - true.
	 */
	protected function beforeValidate()
	{
		$event=new CModelEvent($this);
		$this->onBeforeValidate($event);
		return $event->isValid;
	}

	/**
	 * Метод вызывается после валидации.
	 * По умолчанию вызывается метод {@link onAfterValidate} для вызова события.
	 * Вы можете переопределить данный метод, чтобы выполнить некоторые действия после валидации.
	 * Убедитесь, что родительский метод также вызывается.
	 */
	protected function afterValidate()
	{
		$this->onAfterValidate(new CEvent($this));
	}

	/**
	 * Данное событие вызывается перед выполнением валидации.
	 * @param CModelEvent параметр события
	 * @since 1.0.2
	 */
	public function onBeforeValidate($event)
	{
		$this->raiseEvent('onBeforeValidate',$event);
	}

	/**
	 * Данное событие вызывается после выполнения валидации.
	 * @param CModelEvent параметр события
	 * @since 1.0.2
	 */
	public function onAfterValidate($event)
	{
		$this->raiseEvent('onAfterValidate',$event);
	}

	/**
	 * Возвращает валидаторы, применимые к текущему {@link scenario сценарию}.
	 * @param string имя атрибута, валидаторы которого должны буть вовращены.
	 * Если null, будут возвращены валидаторы для ВСЕХ атрибутов модели.
	 * @return array валидаторы, применимые к текущему {@link scenario сценарию}.
	 * @since 1.0.1
	 */
	public function getValidators($attribute=null)
	{
		if($this->_validators===null)
			$this->_validators=$this->createValidators();

		$validators=array();
		$scenario=$this->getScenario();
		foreach($this->_validators as $validator)
		{
			if($validator->applyTo($scenario))
			{
				if($attribute===null || in_array($attribute,$validator->attributes,true))
					$validators[]=$validator;
			}
		}
		return $validators;
	}

	/**
	 * Создает объекты валидаторов, основанных на определении {@link rules}.
	 * В основном, метод используется внутренне.
	 * @return array объекты валидаторов, основанных на определении {@link rules}.
	 */
	public function createValidators()
	{
		$validators=array();
		foreach($this->rules() as $rule)
		{
			if(isset($rule[0],$rule[1]))  // attributes, validator name
				$validators[]=CValidator::createValidator($rule[1],$this,$rule[0],array_slice($rule,2));
			else
				throw new CException(Yii::t('yii','{class} has an invalid validation rule. The rule must specify attributes to be validated and the validator name.',
					array('{class}'=>get_class($this))));
		}
		return $validators;
	}

	/**
	 * Возвращает значение, показывающее, требуется ли атрибут.
	 * Определяется проверкой, ассоциирован ли атрибут с правилом валидации
	 * {@link CRequiredValidator} в текущем {@link scenario сценарии}.
	 * @param string имя атрибута
	 * @return boolean требуется ли атрибут
	 * @since 1.0.2
	 */
	public function isAttributeRequired($attribute)
	{
		foreach($this->getValidators($attribute) as $validator)
		{
			if($validator instanceof CRequiredValidator)
				return true;
		}
		return false;
	}

	/**
	 * Возвращает значение, показывающее, безопасен ли атрибут для массового присваивания.
	 * @param string имя атрибута
	 * @return boolean безопасен ли атрибут для массового присваивания
	 * @since 1.1
	 */
	public function isAttributeSafe($attribute)
	{
		$attributes=$this->getSafeAttributeNames();
		return in_array($attribute,$attributes);
	}

	/**
	 * Возвращает текст ярлыка для определенного атрибута.
	 * @param string имя атрибута
	 * @return string ярлык атрибута
	 * @see generateAttributeLabel
	 * @see attributeLabels
	 */
	public function getAttributeLabel($attribute)
	{
		$labels=$this->attributeLabels();
		if(isset($labels[$attribute]))
			return $labels[$attribute];
		else
			return $this->generateAttributeLabel($attribute);
	}

	/**
	 * Возвращает значение, показывающее, есть ли ошибки валидации.
	 * @param string имя атрибута. Для проверки всех атрибутов, используйте значение null
	 * @return boolean есть ли ошибки
	 */
	public function hasErrors($attribute=null)
	{
		if($attribute===null)
			return $this->_errors!==array();
		else
			return isset($this->_errors[$attribute]);
	}

	/**
	 * Возвращает ошибки для всех атрибутов или определенного атрибута.
	 * @param string имя атрибута. Для получения ошибок всех атрибутов, используйте значение null
	 * @return array ошибки для всех атрибутов или определенного атрибута. Если ошибок нет, возвращается пустой массив
	 */
	public function getErrors($attribute=null)
	{
		if($attribute===null)
			return $this->_errors;
		else
			return isset($this->_errors[$attribute]) ? $this->_errors[$attribute] : array();
	}

	/**
	 * Возвращает первую ошибку определенного атрибута.
	 * @param string имя атрибута
	 * @return string сообщение об ошибке. Null, если ошибок нет
	 * @since 1.0.2
	 */
	public function getError($attribute)
	{
		return isset($this->_errors[$attribute]) ? reset($this->_errors[$attribute]) : null;
	}

	/**
	 * Добавляет новую ошибку к определенному атрибуту.
	 * @param string имя атрибута
	 * @param string новое сообщение об ошибке
	 */
	public function addError($attribute,$error)
	{
		$this->_errors[$attribute][]=$error;
	}

	/**
	 * Добавляет список ошибок.
	 * @param array список ошибок. Ключи массива должны быть именами атрибутов.
	 * Значения массива должны быть сообщениями об ошибках. Если атрибут имеет несколько ошибок,
	 * эти ошибки должны передаваться в виде массива.
	 * Вы можете использовать результат выполнения метода {@link getErrors} в качестве значения данного параметра
	 * @since 1.0.5
	 */
	public function addErrors($errors)
	{
		foreach($errors as $attribute=>$error)
		{
			if(is_array($error))
			{
				foreach($error as $e)
					$this->_errors[$attribute][]=$e;
			}
			else
				$this->_errors[$attribute][]=$error;
		}
	}

	/**
	 * Удаляет ошибки для всех атрибутов или одного атрибута.
	 * @param string имя атрибута. Для удаления ошибок всех атрибутов, используйте значение null
	 */
	public function clearErrors($attribute=null)
	{
		if($attribute===null)
			$this->_errors=array();
		else
			unset($this->_errors[$attribute]);
	}

	/**
	 * Генерирует дружественный ярлык атрибута.
	 * Это делается заменой подчеркиваний или дефисов пробелами и
	 * изменением первой буквы каждого слова на заглавную.
	 * Например, 'department_name' или 'DepartmentName' станет 'Department Name'.
	 * @param string имя атрибута
	 * @return string ярлык атрибута
	 */
	public function generateAttributeLabel($name)
	{
		return ucwords(trim(strtolower(str_replace(array('-','_'),' ',preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $name)))));
	}

	/**
	 * Возвращает значения всех атрибутов.
	 * @param array список атрибутов, значения которых необходимо возвратить.
	 * По умолчанию - null, т.е. будут возвращены все атрибуты, перечисленные в свойстве {@link attributeNames}.
	 * Если это массив, будут возвращены только атрибуты этого массива.
	 * @return array attribute values (name=>value).
	 */
	public function getAttributes($names=null)
	{
		$values=array();
		foreach($this->attributeNames() as $name)
			$values[$name]=$this->$name;

		if(is_array($names))
		{
			$values2=array();
			foreach($names as $name)
				$values2[$name]=isset($values[$name]) ? $values[$name] : null;
			return $values2;
		}
		else
			return $values;
	}

	/**
	 * Массово устанавливает значения атрибутов.
	 * @param array устанавливаемые значения атрибутов (имя=>значение).
	 * @param boolean должна ли привязка проводиться только для безопасных атрибутов.
	 * Безопасный атрибут - это атрибут, ассоциированный с правилом валидации в текущем {@link scenario сценарии}.
	 * @see getSafeAttributeNames
	 * @see attributeNames
	 */
	public function setAttributes($values,$safeOnly=true)
	{
		if(!is_array($values))
			return;
		$attributes=array_flip($safeOnly ? $this->getSafeAttributeNames() : $this->attributeNames());
		foreach($values as $name=>$value)
		{
			if(isset($attributes[$name]))
				$this->$name=$value;
		}
	}

	/**
	 * Возвращает сценарий, в котором используется данная модель.
	 *
	 * Сценарий влияет на то, как выполняется валидация и какие атрибуты
	 * могут быть массово присвоены.
	 *
	 * Правило валидации будет выполнено при вызове метода {@link validate()},
	 * если его опция 'on' не установлена или содержит значение текущего сценария.
	 *
	 * Атрибут может быть массово присвоен, если он ассоциирован с
	 * правилом валидации для текущего сценария. Примечание: исключение составляет
	 * валидатор {@link CUnsafeValidator unsafe}, помечающий ассоциированные атрибуты
	 * небезопасными и невозможным идля массового присвоения.
	 *
	 * @return string сценарий, в котором используется данная модель
	 * @since 1.0.4
	 */
	public function getScenario()
	{
		return $this->_scenario;
	}

	/**
	 * @param string сценарий, в котором используется данная модель
	 * @see getScenario
	 * @since 1.0.4
	 */
	public function setScenario($value)
	{
		$this->_scenario=$value;
	}

	/**
	 * Возвращает имена атрибутов, безопасных для массового присваивания.
	 * Безопасный атрибут - это атрибут, ассоциированный с правилом валидации в текущем {@link scenario сценарии}.
	 * @return array имена безопасных атрибутов
	 * @since 1.0.2
	 */
	public function getSafeAttributeNames()
	{
		$attributes=array();
		$unsafe=array();
		foreach($this->getValidators() as $validator)
		{
			if($validator instanceof CUnsafeValidator)
			{
				foreach($validator->attributes as $name)
					$unsafe[]=$name;
			}
			else
			{
				foreach($validator->attributes as $name)
					$attributes[$name]=true;
			}
		}

		foreach($unsafe as $name)
			unset($attributes[$name]);
		return array_keys($attributes);
	}

	/**
	 * Возвращает итератор для обхода атрибутов модели.
	 * Метод требуется интерфейсом IteratorAggregate.
	 * @return CMapIterator итератор для обхода атрибутов модели.
	 */
	public function getIterator()
	{
		$attributes=$this->getAttributes();
		return new CMapIterator($attributes);
	}

	/**
	 * Показывает, есть ли свойство с определенным именем.
	 * Метод требуется интерфейсом ArrayAccess.
	 * @param mixed имя проверяемого свойства
	 * @return boolean
	 * @since 1.0.2
	 */
	public function offsetExists($offset)
	{
		return property_exists($this,$offset);
	}

	/**
	 * Возвращает значение свойства по имени.
	 * Метод требуется интерфейсом ArrayAccess.
	 * @param integer имя свойства
	 * @return mixed значение именованного свойства; null, если свойства с данным именем нет
	 * @since 1.0.2
	 */
	public function offsetGet($offset)
	{
		return $this->$offset;
	}

	/**
	 * Устанавливает значение свойства по имени.
	 * Метод требуется интерфейсом ArrayAccess.
	 * @param integer имя устанавливаемого свойства
	 * @param mixed значение свойства
	 * @since 1.0.2
	 */
	public function offsetSet($offset,$item)
	{
		$this->$offset=$item;
	}

	/**
	 * Удаляет именованое свойство.
	 * Метод требуется интерфейсом ArrayAccess.
	 * @param mixed имя свойства
	 * @since 1.0.2
	 */
	public function offsetUnset($offset)
	{
		unset($this->$offset);
	}
}
