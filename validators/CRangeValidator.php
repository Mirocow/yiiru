<?php
/**
 * Файл класса CRangeValidator.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Валидатор CRangeValidator проверяет, чтобы значение атрибута было в списке, определенном свойством {@link range}).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CRangeValidator.php 1678 2010-01-07 21:02:00Z qiang.xue $
 * @package system.validators
 * @since 1.0
 */
class CRangeValidator extends CValidator
{
	/**
	 * @var array список допустимых значений, среди которых должен быть атрибут
	 */
	public $range;
	/**
	 * @var boolean должна ли проверка быть строгой (и тип и значение должны соответствовать)
	 */
	public $strict=false;
	/**
	 * @var boolean может ли быть значение атрибута пустым или равным null. По умолчанию - true,
	 * т.е. пустой атрибут считается валидным
	 */
	public $allowEmpty=true;

	/**
	 * Валидирует отдельный атрибут.
	 * При возникновении ошибки к объекту добавляется сообщение об ошибке.
	 * @param CModel валидируемый объект данных
	 * @param string имя валидируемого атрибута
	 */
	protected function validateAttribute($object,$attribute)
	{
		$value=$object->$attribute;
		if($this->allowEmpty && $this->isEmpty($value))
			return;
		if(is_array($this->range) && !in_array($value,$this->range,$this->strict))
		{
			$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} is not in the list.');
			$this->addError($object,$attribute,$message);
		}
	}
}

