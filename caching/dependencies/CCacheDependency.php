<?php
/**
 * Файл класса CCacheDependency.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Класс CCacheDependency - это базовый класс для классов зависимостей кэша.
 *
 * Класс CCacheDependency реализует интерфейс {@link ICacheDependency}.
 * Класс-потомки должны переопределять метод {@link generateDependentData} для
 * фактической проверки зависимости.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CCacheDependency.php 2799 2011-01-01 19:31:13Z qiang.xue $
 * @package system.caching.dependencies
 * @since 1.0
 */
class CCacheDependency extends CComponent implements ICacheDependency
{
	private $_data;

	/**
	 * Выполняет зависимость, генерируя и сохраняя данные, связанные с зависимостью.
	 * Метод вызывается кэшем перед записью в него данных.
	 */
	public function evaluateDependency()
	{
		$this->_data=$this->generateDependentData();
	}

	/**
	 * @return boolean изменилась ли зависимость
	 */
	public function getHasChanged()
	{
		return $this->generateDependentData()!=$this->_data;
	}

	/**
	 * @return mixed данные, используемые для определения изменения зависимостиe.
	 * Данные доступны после вызова метода {@link evaluateDependency}.
	 */
	public function getDependentData()
	{
		return $this->_data;
	}

	/**
	 * Генерирует данные, необходимые для определения изменения зависимости.
	 * Класс-потомки должны переопределять данный метод для генерации фактических данных зависимости.
	 * @return mixed данные, необходимые для определения изменения зависимости
	 */
	protected function generateDependentData()
	{
		return null;
	}
}