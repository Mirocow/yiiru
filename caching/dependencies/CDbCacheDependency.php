<?php
/**
 * Файл класса CDbCacheDependency.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Компонент CDbCacheDependency представляет собой зависимость, основанную на результате SQL запроса.
 *
 * Если результат запроса (скалярный) изменился, зависимость рассматривается как изменненная.
 * Для определения SQL выражения установите свойство {@link sql}.
 * Свойство {@link connectionID} определяет идентификатор компонента приложения {@link CDbConnection}.
 * Это соединение БД, используемое для выполнения запроса.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CDbCacheDependency.php 1295 2009-08-06 20:00:34Z qiang.xue $
 * @package system.caching.dependencies
 * @since 1.0
 */
class CDbCacheDependency extends CCacheDependency
{
	/**
	 * @var string идентификатор компонента приложения {@link CDbConnection}. По умолчанию - 'db'.
	 */
	public $connectionID='db';
	/**
	 * @var string SQL выражение, результат которого используется для
	 * проверки изменения зависимости.
	 * Примечание: SQL запрос должен возвращать единственное значение.
	 */
	public $sql;

	private $_db;

	/**
	 * Конструктор.
	 * @param string SQL выражение, результат которого используется для проверки изменения зависимости.
	 */
	public function __construct($sql=null)
	{
		$this->sql=$sql;
	}

	/**
	 * Магический метод PHP.
	 * Метод гарантирует, что экземпляр базы данных установлен в null, потому что он содержит обработчик ресурса
	 */
	public function __sleep()
	{
		$this->_db=null;
		return array_keys((array)$this);
	}

	/**
	 * Генерирует данные, необходимые для определения изменения зависимости.
	 * Метод возвращает результат запроса.
	 * @return mixed данные, необходимые для определения изменения зависимости
	 */
	protected function generateDependentData()
	{
		if($this->sql!==null)
			return $this->getDbConnection()->createCommand($this->sql)->queryScalar();
		else
			throw new CException(Yii::t('yii','CDbCacheDependency.sql cannot be empty.'));
	}

	/**
	 * @return CDbConnection экземпляр соединения БД
	 * @throws CException вызывается, если {@link connectionID} не указывает на действительный компонент приложения
	 */
	protected function getDbConnection()
	{
		if($this->_db!==null)
			return $this->_db;
		else
		{
			if(($this->_db=Yii::app()->getComponent($this->connectionID)) instanceof CDbConnection)
				return $this->_db;
			else
				throw new CException(Yii::t('yii','CDbHttpSession.connectionID "{id}" is invalid. Please make sure it refers to the ID of a CDbConnection application component.',
					array('{id}'=>$this->connectionID)));
		}
	}
}
