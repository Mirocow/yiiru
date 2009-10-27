<?php
/**
 * Файл класса CUrlValidator.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Валидатор CUrlValidator проверяет, чтобы атрибут был допустимым URL-адресом протоколов http и https.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CUrlValidator.php 1354 2009-08-20 18:15:14Z qiang.xue $
 * @package system.validators
 * @since 1.0
 */
class CUrlValidator extends CValidator
{
	/**
	 * @var string регулярное выражение, используемое для валидации значения атрибута.
	 */
	public $pattern='/^(http|https):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)/i';
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
		if(!is_string($value) || !preg_match($this->pattern,$value))
		{
			$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} is not a valid URL.');
			$this->addError($object,$attribute,$message);
		}
	}
}

