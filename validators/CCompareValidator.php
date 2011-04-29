<?php
/**
 * Файл класса CCompareValidator.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Валидатор CCompareValidator сравнивает значение определенного атрибута с другим значением на равенство.
 *
 * Сравниваемое значение может быть значением другого атрибута (определенного свойством
 * {@link compareAttribute}) или постоянным значением (определенным свойством
 * {@link compareValue}). Если определены оба свойства, преимущество имеет второе.
 * Если не определено ни одно из них, атрибут будет сравнен с другим атрибутом с именем
 * вида "ATTRNAME_repeat", где ATTRNAME - имя исходного атрибута.
 *
 * Сравнение может быть строгим - {@link strict}.
 *
 * Начиная с версии 1.0.8, CCompareValidator поддерживает различные операторы сравнения.
 * Ранее сравнивалось только равенство двух значений.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CCompareValidator.php 3120 2011-03-25 01:50:48Z qiang.xue $
 * @package system.validators
 * @since 1.0
 */
class CCompareValidator extends CValidator
{
	/**
	 * @var string имя атрибута, с которым сравнивается исходный атрибут
	 */
	public $compareAttribute;
	/**
	 * @var string постоянное значение, с которым сравнивается атрибут
	 */
	public $compareValue;
	/**
	 * @var boolean срогое ли сравнение (и тип и значение должны соответствовать).
	 * По умолчанию - false.
	 */
	public $strict=false;
	/**
	 * @var boolean может ли быть значение атрибута пустым или равным null. По умолчанию - true,
	 * т.е. пустой атрибут считается валидным
	 */
	public $allowEmpty=false;
	/**
	 * @var string оператор сравнения. По умолчанию - '='.
	 * Допустимы следующие операторы:
	 * <ul>
	 * <li>'=' или '==': равенство двух значений. Если свойство {@link strict} установлено в true, сравнение будет
	 * строгим (т.е. тип значения также будет проверен).</li>
	 * <li>'!=': проверка того, что два значения не равны. Если свойство {@link strict} установлено в true, сравнение будет
	 * строгим (т.е. тип значения также будет проверен).</li>
	 * <li>'>': валидируемое значение больше значения, с которым происходит сравнение.</li>
	 * <li>'>=': валидируемое значение больше или равно значения, с которым происходит сравнение.</li>
	 * <li>'<': валидируемое значение меньше значения, с которым происходит сравнение.</li>
	 * <li>'<=': валидируемое значение меньше или равно значения, с которым происходит сравнение.</li>
	 * </ul>
	 * @since 1.0.8
	 */
	public $operator='=';

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
		if($this->compareValue!==null)
			$compareTo=$compareValue=$this->compareValue;
		else
		{
			$compareAttribute=$this->compareAttribute===null ? $attribute.'_repeat' : $this->compareAttribute;
			$compareValue=$object->$compareAttribute;
			$compareTo=$object->getAttributeLabel($compareAttribute);
		}

		switch($this->operator)
		{
			case '=':
			case '==':
				if(($this->strict && $value!==$compareValue) || (!$this->strict && $value!=$compareValue))
				{
					$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} must be repeated exactly.');
					$this->addError($object,$attribute,$message,array('{compareAttribute}'=>$compareTo));
				}
				break;
			case '!=':
				if(($this->strict && $value===$compareValue) || (!$this->strict && $value==$compareValue))
				{
					$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} must not be equal to "{compareValue}".');
					$this->addError($object,$attribute,$message,array('{compareAttribute}'=>$compareTo,'{compareValue}'=>$compareValue));
				}
				break;
			case '>':
				if($value<=$compareValue)
				{
					$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} must be greater than "{compareValue}".');
					$this->addError($object,$attribute,$message,array('{compareAttribute}'=>$compareTo,'{compareValue}'=>$compareValue));
				}
				break;
			case '>=':
				if($value<$compareValue)
				{
					$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} must be greater than or equal to "{compareValue}".');
					$this->addError($object,$attribute,$message,array('{compareAttribute}'=>$compareTo,'{compareValue}'=>$compareValue));
				}
				break;
			case '<':
				if($value>=$compareValue)
				{
					$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} must be less than "{compareValue}".');
					$this->addError($object,$attribute,$message,array('{compareAttribute}'=>$compareTo,'{compareValue}'=>$compareValue));
				}
				break;
			case '<=':
				if($value>$compareValue)
				{
					$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} must be less than or equal to "{compareValue}".');
					$this->addError($object,$attribute,$message,array('{compareAttribute}'=>$compareTo,'{compareValue}'=>$compareValue));
				}
				break;
			default:
				throw new CException(Yii::t('yii','Invalid operator "{operator}".',array('{operator}'=>$this->operator)));
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
		if($this->compareValue !== null)
		{
			$compareTo=$this->compareValue;
			$compareValue=CJSON::encode($this->compareValue);
		}
		else
		{
			$compareAttribute=$this->compareAttribute === null ? $attribute . '_repeat' : $this->compareAttribute;
			$compareValue="\$('#" . (CHtml::activeId($object, $compareAttribute)) . "').val()";
			$compareTo=$object->getAttributeLabel($compareAttribute);
		}

		$message=$this->message;
		switch($this->operator)
		{
			case '=':
			case '==':
				if($message===null)
					$message=Yii::t('yii','{attribute} must be repeated exactly.');
				$condition='value!='.$compareValue;
				break;
			case '!=':
				if($message===null)
					$message=Yii::t('yii','{attribute} must not be equal to "{compareValue}".');
				$condition='value=='.$compareValue;
				break;
			case '>':
				if($message===null)
					$message=Yii::t('yii','{attribute} must be greater than "{compareValue}".');
				$condition='value<='.$compareValue;
				break;
			case '>=':
				if($message===null)
					$message=Yii::t('yii','{attribute} must be greater than or equal to "{compareValue}".');
				$condition='value<'.$compareValue;
				break;
			case '<':
				if($message===null)
					$message=Yii::t('yii','{attribute} must be less than "{compareValue}".');
				$condition='value>='.$compareValue;
				break;
			case '<=':
				if($message===null)
					$message=Yii::t('yii','{attribute} must be less than or equal to "{compareValue}".');
				$condition='value>'.$compareValue;
				break;
			default:
				throw new CException(Yii::t('yii','Invalid operator "{operator}".',array('{operator}'=>$this->operator)));
		}

		$message=strtr($message,array(
			'{attribute}'=>$object->getAttributeLabel($attribute),
			'{compareValue}'=>$compareTo,
		));

		return "
if(".($this->allowEmpty ? "$.trim(value)!='' && " : '').$condition.") {
	messages.push(".CJSON::encode($message).");
}
";
	}
}
