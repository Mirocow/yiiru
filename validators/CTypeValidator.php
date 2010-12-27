<?php
/**
 * Файл класса CTypeValidator.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Валидатор CTypeValidator проверяет соответствие типа атрибута типу, определенному свойством {@link type}.
 *
 * Поддерживаются следующие типы данных:
 * <ul>
 * <li><b>integer</b> 32-х битные целочисленные знаковые данные.</li>
 * <li><b>float</b> Числа с плавающей точкой двойной точности.</li>
 * <li><b>string</b> Строковые данные.</li>
 * <li><b>array</b> Массив. </li>
 * <li><b>date</b> Дата.</li>
 * <li><b>time</b> Время (доступен с версии 1.0.5).</li>
 * <li><b>datetime</b> Дата и время (доступен с версии 1.0.5).</li>
 * </ul>
 *
 * Для типа <b>date</b> свойство {@link dateFormat} будет использоваться для определения
 * того, как разбирать строку даты. Если переданное значение даты не соответствует данному формату,
 * атрибут будет считаться неправильным.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CTypeValidator.php 2606 2010-11-02 18:37:10Z qiang.xue $
 * @package system.validators
 * @since 1.0
 */
class CTypeValidator extends CValidator
{
	/**
	 * @var string тип данных, которому должен соответствовать атрибут. По умолчанию - 'string'.
	 * Допустимые значения - 'string', 'integer', 'float', 'array', 'date', 'time' и 'datetime'.
	 * Примечание: типы 'time' и 'datetime' доступны с версии 1.0.5.
	 */
	public $type='string';
	/**
	 * @var string шаблон формата, которому должна соответствовать дата. По умолчанию - 'MM/dd/yyyy'.
	 * За деталями определения формата даты обратитесь к компоненту {@link CDateTimeParser}.
	 * Свойство используется только если свойство {@link type} имеет значение 'date'.
	 */
	public $dateFormat='MM/dd/yyyy';
	/**
	 * @var string шаблон формата, которому должно соответствовать время. По умолчанию - 'hh:mm'.
	 * За деталями определения формата даты обратитесь к компоненту {@link CDateTimeParser}.
	 * Свойство используется только если свойство {@link type} имеет значение 'time'.
	 * @since 1.0.5
	 */
	public $timeFormat='hh:mm';
	/**
	 * @var string шаблон формата, которому должны соответствовать дата и время. По умолчанию - 'MM/dd/yyyy hh:mm'.
	 * За деталями определения формата даты обратитесь к компоненту {@link CDateTimeParser}.
	 * Свойство используется только если свойство {@link type} имеет значение 'datetime'.
	 * @since 1.0.5
	 */
	public $datetimeFormat='MM/dd/yyyy hh:mm';
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

		if($this->type==='integer')
			$valid=preg_match('/^[-+]?[0-9]+$/',trim($value));
		else if($this->type==='float')
			$valid=preg_match('/^[-+]?([0-9]*\.)?[0-9]+([eE][-+]?[0-9]+)?$/',trim($value));
		else if($this->type==='date')
			$valid=CDateTimeParser::parse($value,$this->dateFormat,array('month'=>1,'day'=>1,'hour'=>0,'minute'=>0,'second'=>0))!==false;
	    else if($this->type==='time')
			$valid=CDateTimeParser::parse($value,$this->timeFormat)!==false;
	    else if($this->type==='datetime')
			$valid=CDateTimeParser::parse($value,$this->datetimeFormat, array('month'=>1,'day'=>1,'hour'=>0,'minute'=>0,'second'=>0))!==false;
		else if($this->type==='array')
			$valid=is_array($value);
		else
			return;

		if(!$valid)
		{
			$message=$this->message!==null?$this->message : Yii::t('yii','{attribute} must be {type}.');
			$this->addError($object,$attribute,$message,array('{type}'=>$this->type));
		}
	}
}

