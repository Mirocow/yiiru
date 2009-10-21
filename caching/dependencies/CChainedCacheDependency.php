<?php
/**
 * Файл класса CChainedCacheDependency.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Компонент CChainedCacheDependency представляет список зависимостей кэша.
 *
 * Если любая из зависимостей сообщает об изменении, CChainedCacheDependency
 * возвратит значение true при проверке.
 *
 * Для добавления зависимостей в цепочку CChainedCacheDependency используйте
 * свойство {@link getDependencies Dependencies}, возвращающее экземпляр класса
 * {@link CTypedList} и может быть использовано как массив
 * (за подробностями обратитесь к классу {@link CList}).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CChainedCacheDependency.php 1423 2009-09-28 01:54:38Z qiang.xue $
 * @package system.caching.dependencies
 * @since 1.0
 */
class CChainedCacheDependency extends CComponent implements ICacheDependency
{
	private $_dependencies=null;

	/**
	 * @return CTypedList список объектов зависимости
	 */
	public function getDependencies()
	{
		if($this->_dependencies===null)
			$this->_dependencies=new CTypedList('ICacheDependency');
		return $this->_dependencies;
	}

	/**
	 * @param array список объектов зависимости, добавляемых к данной цепочке.
	 * @since 1.0.10
	 */
	public function setDependencies($values)
	{
		$dependencies=$this->getDependencies();
		foreach($values as $value)
			$dependencies->add($value);
	}

	/**
	 * Выполняет зависимость, генерируя и сохраняя данные, связанные с зависимостью.
	 */
	public function evaluateDependency()
	{
		if($this->_dependencies!==null)
		{
			foreach($this->_dependencies as $dependency)
				$dependency->evaluateDependency();
		}
	}

	/**
	 * Выполняет фактическую проверку зависимости.
	 * Метод возвращает значение true, если любой из объектов зависимости сообщил об изменении зависимости.
	 * @return boolean изменилась ли зависимость
	 */
	public function getHasChanged()
	{
		if($this->_dependencies!==null)
		{
			foreach($this->_dependencies as $dependency)
				if($dependency->getHasChanged())
					return true;
		}
		return false;
	}
}
