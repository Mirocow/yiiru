<?php
/**
 * Файл класса CNumberValidator.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Валидатор CNumberValidator проверяет, что значение атрибута является числом.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CNumberValidator.php 1678 2010-01-07 21:02:00Z qiang.xue $
 * @package system.validators
 * @since 1.0
 */
class CNumberValidator extends CValidator
{
	/**
	 * @var boolean только ли целочисленное может быть значение атрибута. По умолчанию - false.
	 */
	public $integerOnly=false;
	/**
	 * @var boolean может ли быть значение атрибута пустым или равным null. По умолчанию - true,
	 * т.е. пустой атрибут считается валидным
	 */
	public $allowEmpty=true;
	/**
	 * @var integer|double верхняя граница числа. По умолчанию - null, т.е. без верхней границы.
	 */
	public $max;
	/**
	 * @var integer|double нижняя граница числа. По умолчанию - null, т.е. без нижней границы.
	 */
	public $min;
	/**
	 * @var string пользовательское сообщение об ошибке, если значение слишком большое.
	 */
	public $tooBig;
	/**
	 * @var string пользовательское сообщение об ошибке, если значение слишком маленькое.
	 */
	public $tooSmall;


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
		if($this->integerOnly)
		{
			if(!preg_match('/^\s*[+-]?\d+\s*$/',"$value"))
			{
				$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} must be an integer.');
				$this->addError($object,$attribute,$message);
			}
		}
		else
		{
			if(!preg_match('/^\s*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/',"$value"))
			{
				$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} must be a number.');
				$this->addError($object,$attribute,$message);
			}
		}
		if($this->min!==null && $value<$this->min)
		{
			$message=$this->tooSmall!==null?$this->tooSmall:Yii::t('yii','{attribute} is too small (minimum is {min}).');
			$this->addError($object,$attribute,$message,array('{min}'=>$this->min));
		}
		if($this->max!==null && $value>$this->max)
		{
			$message=$this->tooBig!==null?$this->tooBig:Yii::t('yii','{attribute} is too big (maximum is {max}).');
			$this->addError($object,$attribute,$message,array('{max}'=>$this->max));
		}
	}
}
