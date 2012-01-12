<?php
/**
 * Файл класса CRequiredValidator.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Валидатор CRequiredValidator проверяет, что значение определенного атрибута не нулевое и не пустое.
 *
 * When using the {@link message} property to define a custom error message, the message
 * may contain additional placeholders that will be replaced with the actual content. In addition
 * to the "{attribute}" placeholder, recognized by all validators (see {@link CValidator}),
 * CRequiredValidator allows for the following placeholders to be specified:
 * <ul>
 * <li>{value}: replaced with the desired value {@link requiredValue}.</li>
 * </ul>
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CRequiredValidator.php 3515 2011-12-28 12:29:24Z mdomba $
 * @package system.validators
 * @since 1.0
 */
class CRequiredValidator extends CValidator
{
	/**
	 * @var mixed желаемое значение, котороое должен иметь атрибут.
	 * Если установлено в null, валидатор будет проверять, что значение определенного атрибута не нулевое и не пустое.
	 * Если установлено в некоторое ненулевое значение, валидатор будет проверять значение атрибута на
	 * соответствие значению этого свойства. По умолчанию - null
	 */
	public $requiredValue;
	/**
	 * @var boolean должно ли сравнение со свойством {@link requiredValue} быть строгим.
	 * Если установлено в true, и значение и тип атрибута должны соответствовать свойству {@link requiredValue}.
	 * По умолчанию - false, т.е. проверяется только значение.
	 * Свойство используется только если свойство {@link requiredValue} не нулевое
	 */
	public $strict=false;
	/**
	 * Валидирует отдельный атрибут.
	 * При возникновении ошибки к объекту добавляется сообщение об ошибке.
	 * @param CModel $object валидируемый объект данных
	 * @param string $attribute имя валидируемого атрибута
	 */
	protected function validateAttribute($object,$attribute)
	{
		$value=$object->$attribute;
		if($this->requiredValue!==null)
		{
			if(!$this->strict && $value!=$this->requiredValue || $this->strict && $value!==$this->requiredValue)
			{
				$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} must be {value}.',
					array('{value}'=>$this->requiredValue));
				$this->addError($object,$attribute,$message);
			}
		}
		else if($this->isEmpty($value,true))
		{
			$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} cannot be blank.');
			$this->addError($object,$attribute,$message);
		}
	}

	/**
	 * Returns the JavaScript needed for performing client-side validation.
	 * @param CModel $object the data object being validated
	 * @param string $attribute the name of the attribute to be validated.
	 * @return string the client-side validation script.
	 * @see CActiveForm::enableClientValidation
	 * @since 1.1.7
	 */
	public function clientValidateAttribute($object,$attribute)
	{
		$message=$this->message;
		if($this->requiredValue!==null)
		{
			if($message===null)
				$message=Yii::t('yii','{attribute} must be {value}.');
			$message=strtr($message, array(
				'{value}'=>$this->requiredValue,
				'{attribute}'=>$object->getAttributeLabel($attribute),
			));
			return "
if(value!=" . CJSON::encode($this->requiredValue) . ") {
	messages.push(".CJSON::encode($message).");
}
";
		}
		else
		{
			if($message===null)
				$message=Yii::t('yii','{attribute} cannot be blank.');
			$message=strtr($message, array(
				'{attribute}'=>$object->getAttributeLabel($attribute),
			));
			return "
if($.trim(value)=='') {
	messages.push(".CJSON::encode($message).");
}
";
		}
	}
}
