<?php
/**
 * Файл класса CInlineValidator.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Валидатор CInlineValidator представляет валидатор, определенный как метод валидируемого объекта.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CInlineValidator.php 2497 2010-09-23 13:28:52Z mdomba $
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
}
