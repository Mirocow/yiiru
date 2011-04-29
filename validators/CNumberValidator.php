<?php
/**
 * Файл класса CNumberValidator.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Валидатор CNumberValidator проверяет, что значение атрибута является числом.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CNumberValidator.php 3190 2011-04-16 23:40:21Z qiang.xue $
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
	 * @var integer|float верхняя граница числа. По умолчанию - null, т.е. без верхней границы.
	 */
	public $max;
	/**
	 * @var integer|float нижняя граница числа. По умолчанию - null, т.е. без нижней границы.
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
	 * @var string the regular expression for matching integers.
	 * @since 1.1.7
	 */
	public $integerPattern='/^\s*[+-]?\d+\s*$/';
	/**
	 * @var string the regular expression for matching numbers.
	 * @since 1.1.7
	 */
	public $numberPattern='/^\s*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/';


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
		if($this->integerOnly)
		{
			if(!preg_match($this->integerPattern,"$value"))
			{
				$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} must be an integer.');
				$this->addError($object,$attribute,$message);
			}
		}
		else
		{
			if(!preg_match($this->numberPattern,"$value"))
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
		$label=$object->getAttributeLabel($attribute);

		if(($message=$this->message)===null)
			$message=$this->integerOnly ? Yii::t('yii','{attribute} must be an integer.') : Yii::t('yii','{attribute} must be a number.');
		$message=strtr($message, array(
			'{attribute}'=>$label,
		));

		if(($tooBig=$this->tooBig)===null)
			$tooBig=Yii::t('yii','{attribute} is too big (maximum is {max}).');
		$tooBig=strtr($tooBig, array(
			'{attribute}'=>$label,
			'{max}'=>$this->max,
		));

		if(($tooSmall=$this->tooSmall)===null)
			$tooSmall=Yii::t('yii','{attribute} is too small (minimum is {min}).');
		$tooSmall=strtr($tooSmall, array(
			'{attribute}'=>$label,
			'{min}'=>$this->min,
		));

		$pattern=$this->integerOnly ? $this->integerPattern : $this->numberPattern;
		$js="
if(!value.match($pattern)) {
	messages.push(".CJSON::encode($message).");
}
";
		if($this->min!==null)
		{
			$js.="
if(value<{$this->min}) {
	messages.push(".CJSON::encode($tooSmall).");
}
";
		}
		if($this->max!==null)
		{
			$js.="
if(value>{$this->max}) {
	messages.push(".CJSON::encode($tooBig).");
}
";
		}

		if($this->allowEmpty)
		{
			$js="
if($.trim(value)!='') {
	$js
}
";
		}

		return $js;
	}
}
