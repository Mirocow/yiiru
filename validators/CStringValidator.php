<?php
/**
 * Файл класса CStringValidator.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Валидатор CStringValidator проверяет соответствие длины строкового атрибута некоторой величине.
 *
 * Примечание: валидатор должен использоваться только для строковых атрибутов.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CStringValidator.php 2799 2011-01-01 19:31:13Z qiang.xue $
 * @package system.validators
 * @since 1.0
 */
class CStringValidator extends CValidator
{
	/**
	 * @var integer максимальная длина. По умолчанию - null, т.е. без лимита максимума длины.
	 */
	public $max;
	/**
	 * @var integer минимальная длина. По умолчанию - null, т.е. без лимита минимума длины.
	 */
	public $min;
	/**
	 * @var integer точная длина. По умолчанию - null, т.е. без точной длины.
	 */
	public $is;
	/**
	 * @var string пользовательское сообщение об ошибке, используемое, если сообщение слишком длинное
	 */
	public $tooShort;
	/**
	 * @var string пользовательское сообщение об ошибке, используемое, если сообщение слишком короткое
	 */
	public $tooLong;
	/**
	 * @var boolean может ли быть значение атрибута пустым или равным null. По умолчанию - true,
	 * т.е. пустой атрибут считается валидным
	 */
	public $allowEmpty=true;
	/**
	 * @var string кодировка строки валидируемого значения (например, 'UTF-8').
	 * Установка данного свойства требует включенного PHP расширения mbstring.
	 * Значение данного свойства будет использовано в качестве второго параметра функции mb_strlen().
	 * По умолчанию - false, т.е., для вычисления длины строки будет использоваться функция strlen()
	 * @since 1.1.1
	 */
	public $encoding=false;

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
		if($this->encoding!==false && function_exists('mb_strlen'))
			$length=mb_strlen($value,$this->encoding);
		else
			$length=strlen($value);
		if($this->min!==null && $length<$this->min)
		{
			$message=$this->tooShort!==null?$this->tooShort:Yii::t('yii','{attribute} is too short (minimum is {min} characters).');
			$this->addError($object,$attribute,$message,array('{min}'=>$this->min));
		}
		if($this->max!==null && $length>$this->max)
		{
			$message=$this->tooLong!==null?$this->tooLong:Yii::t('yii','{attribute} is too long (maximum is {max} characters).');
			$this->addError($object,$attribute,$message,array('{max}'=>$this->max));
		}
		if($this->is!==null && $length!==$this->is)
		{
			$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} is of the wrong length (should be {length} characters).');
			$this->addError($object,$attribute,$message,array('{length}'=>$this->is));
		}
	}
}

