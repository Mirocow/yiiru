<?php
/**
 * Файл класса CBooleanValidator.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Валидатор CBooleanValidator проверяет значение атрибута на соответствие либо свойству {@link trueValue}
 * либо свойству {@link falseValue}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CBooleanValidator.php 1678 2010-01-07 21:02:00Z qiang.xue $
 * @package system.validators
 * @since 1.0.10
 */
class CBooleanValidator extends CValidator
{
	/**
	 * @var mixed значение, представляющее статус true. По умолчанию - '1'.
	 */
	public $trueValue='1';
	/**
	 * @var mixed значение, представляющее статус false. По умолчанию - '0'.
	 */
	public $falseValue='0';
	/**
	 * @var boolean должно ли сравнение с {@link trueValue} и {@link falseValue} быть строгим.
	 * Если да, и тип и значение атрибута должны соответствовать {@link trueValue} или {@link falseValue}.
	 * По умолчанию - false, т.е. проверяются только значения.
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
		if(!$this->strict && $value!=$this->trueValue && $value!=$this->falseValue
			|| $this->strict && $value!==$this->trueValue && $value!==$this->falseValue)
		{
			$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} must be either {true} or {false}.',
				array('{true}'=>$this->trueValue, '{false}'=>$this->falseValue));
			$this->addError($object,$attribute,$message);
		}
	}
}
