<?php
/**
 * Файл содержит класс CTypedMap.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Класс CTypedMap представляет карту с элементами определенного типа.
 *
 * Класс CTypedMap расширяет класс {@link CMap} делая обязательным условие, при котором
 * добавляемые в список элементы имеют один и тот же определенный тип класса.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CTypedMap.php 2685 2010-11-29 11:03:47Z mdomba $
 * @package system.collections
 * @since 1.0
 */
class CTypedMap extends CMap
{
	private $_type;

	/**
	 * Конструктор.
	 * @param string $type тип класса
	 */
	public function __construct($type)
	{
		$this->_type=$type;
	}

	/**
	 * Добавляет элемент в карту.
	 * Метод переопределяет родительскую реализацию проверкой
	 * соответствия определенному типу добавляемого элемента.
	 * @param integer $index определенная позиция
	 * @param mixed $item новый элемент
	 * @throws CException вызывается, если переданный индекс превысил границы карты,
	 * карта только для чтения или элемент не соответствует ожидаемому типу
	 */
	public function add($index,$item)
	{
		if($item instanceof $this->_type)
			parent::add($index,$item);
		else
			throw new CException(Yii::t('yii','CTypedMap<{type}> can only hold objects of {type} class.',
				array('{type}'=>$this->_type)));
	}
}
