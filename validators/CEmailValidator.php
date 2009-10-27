<?php
/**
 * Файл класса CEmailValidator.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Валидатор CEmailValidator проверяет, что значение атрибута - правильный адрес email.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CEmailValidator.php 1354 2009-08-20 18:15:14Z qiang.xue $
 * @package system.validators
 * @since 1.0
 */
class CEmailValidator extends CValidator
{
	/**
	 * @var string регулярное выражение, используемое для проверки значения атрибута.
	 */
	public $pattern='/^\\w+([-+.]\\w+)*@\\w+([-.]\\w+)*\\.\\w+([-.]\\w+)*$/';
	/**
	 * @var string регулярное выражение, используемое для проверки адресов email с именем.
	 * Свойство используется только если свойство {@link allowName} установлено в true.
	 * @since 1.0.5
	 * @see allowName
	 */
	public $fullPattern='/^[^@]*<\\w+([-+.]\\w+)*@\\w+([-.]\\w+)*\\.\\w+([-.]\\w+)*>$/';
	/**
	 * @var boolean допустимо ли имя в адресе email (например, "Qiang Xue <qiang.xue@gmail.com>"). По умолчанию - false.
	 * @since 1.0.5
	 * @see fullPattern
	 */
	public $allowName=false;
	/**
	 * @var boolean проверять ли запись MX для адреса email.
	 * По умолчанию - false. Для включения необходимо убедиться, что функция 'checkdnsrr'
	 * существует в вашей инсталляции PHP.
	 */
	public $checkMX=false;
	/**
	 * @var boolean проверять ли порт 25 для адреса email.
	 * По умолчанию - false.
	 * @since 1.0.4
	 */
	public $checkPort=false;
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
		$valid=preg_match($this->pattern,$value) || $this->allowName && preg_match($this->fullPattern,$value);
		if($valid)
			$domain=rtrim(substr($value,strpos($value,'@')+1),'>');
		if($valid && $this->checkMX && function_exists('checkdnsrr'))
			$valid=checkdnsrr($domain,'MX');
		if($valid && $this->checkPort && function_exists('fsockopen'))
			$valid=fsockopen($domain,25)!==false;
		if(!$valid)
		{
			$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} is not a valid email address.');
			$this->addError($object,$attribute,$message);
		}
	}
}
