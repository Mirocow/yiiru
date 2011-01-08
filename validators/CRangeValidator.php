<?php
/**
 * Файл класса CRangeValidator.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Валидатор CRangeValidator проверяет, чтобы значение атрибута было в списке, определенном свойством {@link range}).
 * Вы можете инвертировать логику валидации при помощи свойства {@link not} (доступно с версии 1.1.5).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CRangeValidator.php 2799 2011-01-01 19:31:13Z qiang.xue $
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
	 * @var boolean инвертировать ли логику валидации. По умолчанию - false. Если установлено в значение true,
	 * то значение атрибут не должно находиться в списке значений, определенных свойством {@link range}.
	 * @since 1.1.5
	 **/
 	public $not=false;

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
		if(!is_array($this->range))
			throw new CException(Yii::t('yii','The "range" property must be specified with a list of values.'));
		if(!$this->not && !in_array($value,$this->range,$this->strict))
		{
			$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} is not in the list.');
			$this->addError($object,$attribute,$message);
		}
		else if($this->not && in_array($value,$this->range,$this->strict))
		{
			$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} is in the list.');
			$this->addError($object,$attribute,$message);		
		}
	}
}

