<?php
/**
 * Файл класса CRegularExpressionValidator.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Валидатор CRegularExpressionValidator проверяет атрибут на соответствие определенному {@link pattern регулярному выражению}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CRegularExpressionValidator.php 1354 2009-08-20 18:15:14Z qiang.xue $
 * @package system.validators
 * @since 1.0
 */
class CRegularExpressionValidator extends CValidator
{
	/**
	 * @var string регулярное выражение, которому должен соответствовать атрибут
	 */
	public $pattern;
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
		if($this->pattern===null)
			throw new CException(Yii::t('yii','The "pattern" property must be specified with a valid regular expression.'));
		if(!preg_match($this->pattern,$value))
		{
			$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} is invalid.');
			$this->addError($object,$attribute,$message);
		}
	}
}

