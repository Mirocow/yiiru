<?php
/**
 * Файл класса CInlineValidator.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Валидатор CInlineValidator представляет валидатор, определенный как метод валидируемого объекта.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CInlineValidator.php 3517 2011-12-28 23:22:21Z mdomba $
 * @package system.validators
 * @since 1.0
 */
class CInlineValidator extends CValidator
{
	/**
	 * @var string имя метода валидации в active record-классе
	 */
	public $method;
	/**
	 * @var array дополнительные параметры, передаваемые в метод валидации
	 */
	public $params;
	/**
	 * @var string the name of the method that returns the client validation code (See {@link clientValidateAttribute}).
	 */
	public $clientValidate;

	/**
	 * Валидирует отдельный атрибут.
	 * При возникновении ошибки к объекту добавляется сообщение об ошибке.
	 * @param CModel $object валидируемый объект данных
	 * @param string $attribute имя валидируемого атрибута
	 */
	protected function validateAttribute($object,$attribute)
	{
		$method=$this->method;
		$object->$method($attribute,$this->params);
	}

	/**
	 * Returns the JavaScript code needed to perform client-side validation by calling the {@link clientValidate} method.
	 * In the client validation code, these variables are predefined:
	 * <ul>
	 * <li>value: the current input value associated with this attribute.</li>
	 * <li>messages: an array that may be appended with new error messages for the attribute.</li>
	 * <li>attribute: a data structure keeping all client-side options for the attribute</li>
	 * </ul>
	 * <b>Example</b>:
	 *
	 * If {@link clientValidate} is set to "clientValidate123", clientValidate123() is the name of
	 * the method that returns the client validation code and can look like:
	 * <pre>
	 * <?php
	 *   public function clientValidate123($attribute)
	 *   {
	 *      $js = "if(value != '123') { messages.push('Value should be 123'); }";
	 *      return $js;
	 *   }
	 * ?>
	 * </pre>
	 * @param CModel $object the data object being validated
	 * @param string $attribute the name of the attribute to be validated.
	 * @return string the client-side validation script.
	 * @see CActiveForm::enableClientValidation
	 * @since 1.1.9
	 */
	public function clientValidateAttribute($object,$attribute)
	{
		if($this->clientValidate!==null)
		{
			$method=$this->clientValidate;
			return $object->$method($attribute);
		}
	}
}
