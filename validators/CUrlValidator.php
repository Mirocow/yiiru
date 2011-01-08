<?php
/**
 * Файл класса CUrlValidator.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Валидатор CUrlValidator проверяет, чтобы атрибут был допустимым URL-адресом протоколов http и https.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CUrlValidator.php 2799 2011-01-01 19:31:13Z qiang.xue $
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
	 * @param CModel $object валидируемый объект данных
	 * @param string $attribute имя валидируемого атрибута
	 */
	protected function validateAttribute($object,$attribute)
	{
		$value=$object->$attribute;
		if($this->allowEmpty && $this->isEmpty($value))
			return;
		if(!$this->validateValue($value))
		{
			$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} is not a valid URL.');
			$this->addError($object,$attribute,$message);
		}
	}

	/**
	 * Проверяет статичное значение на соответствие адресу URL.
	 * Примечание: данный метод не использует свойство {@link allowEmpty}.
	 * Метод предоставлен для того, чтобы можно было вызывать его непосредственно без прохождения механизма правил валидации модели.
	 * @param mixed $value валидируемое значение
	 * @return boolean является ли значение верным адресом URL
	 * @since 1.1.1
	 */
	public function validateValue($value)
	{
		return is_string($value) && preg_match($this->pattern,$value);
	}
}

