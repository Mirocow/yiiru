<?php
/**
 * Файл класса CUniqueValidator.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Валидатор CUniqueValidator проверяет значение атрибута на уникальность в соответствующей таблице БД.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CUniqueValidator.php 1354 2009-08-20 18:15:14Z qiang.xue $
 * @package system.validators
 * @since 1.0
 */
class CUniqueValidator extends CValidator
{
	/**
	 * @var boolean регистрозависима ли проверка. По умолчанию - true.
	 * Примечание: установка значения false предполагает, что тип атрибута - строка.
	 */
	public $caseSensitive=true;
	/**
	 * @var boolean может ли быть значение атрибута пустым или равным null. По умолчанию - true,
	 * т.е. пустой атрибут считается валидным
	 */
	public $allowEmpty=true;
	/**
	 * @var string имя ActiveRecord-класса, используемое для поиска валидируемого атрибута.
	 * По умолчанию - null, т.е. использование валидируемого в данный момент объекта.
	 * Вы можете использовать здесь псевдонимы (и пути) для ссылки на имя класса.
	 * @see attributeName
	 * @since 1.0.8
	 */
	public $className;
	/**
	 * @var string имя атрибута ActiveRecord-класса, используемое для поиска значения валидируемого атрибута.
	 * По умолчанию - null, т.е. использование имени валидируемого атрибута.
	 * @see className
	 * @since 1.0.8
	 */
	public $attributeName;
	/**
	 * @var array дополнительный критерий запроса. Будет объединен с условием,
	 * проверяющим существование значения атрибута в соответствующем столбце таблицы.
	 * Данный массив будет использован для создания экземпляра {@link CDbCriteria}.
	 * @since 1.0.8
	 */
	public $criteria=array();


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

		$className=$this->className===null?get_class($object):Yii::import($this->className);
		$attributeName=$this->attributeName===null?$attribute:$this->attributeName;
		$finder=CActiveRecord::model($className);
		$table=$finder->getTableSchema();
		if(($column=$table->getColumn($attributeName))===null)
			throw new CException(Yii::t('yii','Column "{column} does not exist in table "{table}".',
				array('{column}'=>$attributeName,'{table}'=>$table->name)));

		$columnName=$column->rawName;
		$criteria=new CDbCriteria(array(
			'condition'=>$this->caseSensitive ? "$columnName=:value" : "LOWER($columnName)=LOWER(:value)",
			'params'=>array(':value'=>$value),
		));
		if($this->criteria!==array())
			$criteria->mergeWith($this->criteria);

		if($column->isPrimaryKey || $this->className!==null)
			$exists=$finder->exists($criteria);
		else
		{
			// need to exclude the current record based on PK
			$criteria->limit=2;
			$objects=$finder->findAll($criteria);
			$n=count($objects);
			if($n===1)
				$exists=$objects[0]->getPrimaryKey()!=$object->getPrimaryKey();
			else
				$exists=$n>1;
		}

		if($exists)
		{
			$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} "{value}" has already been taken.');
			$this->addError($object,$attribute,$message,array('{value}'=>$value));
		}
	}
}

