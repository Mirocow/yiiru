<?php
/**
 * Файл класса CUrlValidator.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Валидатор CUrlValidator проверяет, чтобы атрибут был допустимым URL-адресом протоколов http и https.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CUrlValidator.php 2914 2011-01-25 12:13:38Z keyboard.idol@gmail.com $
 * @package system.validators
 * @since 1.0
 */
class CUrlValidator extends CValidator
{
	/**
	 * @var string регулярное выражение, используемое для валидации значения атрибута.
	 * С версии 1.1.7 шаблон может содержать метку {schemes}, заменяемую регулярным выражением,
	 * представленным свойством {@see validSchemes}.
	 */
	public $pattern='/^(http|https):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)/i';
	/**
	 * @var array список URI-схем, которые должны считаться валидными. По умолчанию, схемы http и https
	 * считаются валидными
	 * @since 1.1.7
	 **/
	public $validSchemes=array('http','https');
	/**
	 * @var string URI-схема по умолчанию. Если входное значение не содержит части со схемой, то
	 * схема по умолчанию будет подставлена перед значением (изменив тем самым входное значение).
	 * По умолчанию - null, т.е., URL-адрес должен содержать часть со схемой
	 * @since 1.1.7
	 **/
	public $defaultScheme;
	/**
	 * @var boolean может ли быть значение атрибута пустым или равным null. По умолчанию - true,
	 * т.е. пустой атрибут считается валидным
	 */
	public $allowEmpty=true;

	/**
	 * Валидирует отдельный атрибут.
	 * При возникновении ошибки к объекту добавляется сообщение об ошибке.
	 * @param CModel $object валидируемый объект данных
	 * @param string $attribute имя валидируемого атрибута
	 */
	protected function validateAttribute($object,$attribute)
	{
		$value=$object->$attribute;
		if($this->allowEmpty && $this->isEmpty($value))
			return;
		if(($value=$this->validateValue($value))!==false)
			$object->$attribute=$value;
		else
		{
			$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} is not a valid URL.');
			$this->addError($object,$attribute,$message);
		}
	}

	/**
	 * Проверяет статичное значение на соответствие адресу URL.
	 * Примечание: данный метод не использует свойство {@link allowEmpty}.
	 * Метод предоставлен для того, чтобы можно было вызывать его непосредственно без прохождения механизма правил валидации модели.
	 * @param mixed $value валидируемое значение
	 * @return mixed false, если значние не является валидным URL-адресом, иначе - возможно модифицированное значение ({@see defaultScheme})
	 * @since 1.1.1
	 */
	public function validateValue($value)
	{
		if(is_string($value))
		{
			if($this->defaultScheme!==null && strpos($value,'://')===false)
				$value=$this->defaultScheme.'://'.$value;
	
			if(strpos($this->pattern,'{schemes}')!==false)
				$pattern=str_replace('{schemes}','('.implode('|',$this->validSchemes).')',$this->pattern);
			else
				$pattern=$this->pattern;
	
			if(preg_match($pattern,$value))
				return $value;
		}
		return false;
	}
}

