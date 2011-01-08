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
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CRequiredValidator.php 2799 2011-01-01 19:31:13Z qiang.xue $
 * @package system.validators
 * @since 1.0
 */
class CRequiredValidator extends CValidator
{
	/**
	 * @var mixed желаемое значение, котороое должен иметь атрибут.
	 * Если установлено в null, валидатор будет проверять, что значение определенного атрибута не нулевое и не пустое.
	 * Если установлено в некоторое ненулевое значение, валидатор будет проверять значение атрибута на
	 * соответствие значению этого свойства. По умолчанию - null.
	 * @since 1.0.10
	 */
	public $requiredValue;
	/**
	 * @var boolean должно ли сравнение со свойством {@link requiredValue} быть строгим.
	 * Если установлено в true, и значение и тип атрибута должны соответствовать свойству {@link requiredValue}.
	 * По умолчанию - false, т.е. проверяется только значение.
	 * Свойство используется только если свойство {@link requiredValue} не нулевое.
	 * @since 1.0.10
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
}
