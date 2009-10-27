<?php
/**
 * Файл класса CUnsafeValidator.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Валидатор CUnsafeValidator помечает связанные атрибуты как небезопасные так, что они не могут быть присвоены пакетно.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CUnsafeValidator.php 1065 2009-05-26 14:47:59Z qiang.xue $
 * @package system.validators
 * @since 1.0
 */
class CUnsafeValidator extends CValidator
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