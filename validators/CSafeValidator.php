<?php
/**
 * Файл класса CSafeValidator.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Валидатор CSafeValidator помечает связанные атрибуты как безопасные так, что они могут быть присвоены пакетно.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CSafeValidator.php 1065 2009-05-26 14:47:59Z qiang.xue $
 * @package system.validators
 * @since 1.0
 */
class CSafeValidator extends CValidator
{
	/**
	 * Валидирует отдельный атрибут.
	 * При возникновении ошибки к объекту добавляется сообщение об ошибке.
	 * @param CModel валидируемый объект данных
	 * @param string имя валидируемого атрибута
	 */
	protected function validateAttribute($object,$attribute)
	{
	}
}

