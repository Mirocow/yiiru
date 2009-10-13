<?php
/**
 * Файл содержит класс, реализующий функции маппирования (Map).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Класс CMap реализует коллекцию, имеющую пары ключ-значение.
 *
 * Вы можете иметь доступ, добавлять и удалять элементы по ключу, используя методы
 * {@link itemAt}, {@link add} и {@link remove}.
 * Для получения количества элементов в карте, используйте метод {@link getCount}.
 * Объект класса CMap также может использоваться как обычный массив:
 * <pre>
 * $map[$key]=$value; // добавление пары ключ-значение
 * unset($map[$key]); // удаление значения с определенным ключом
 * if(isset($map[$key])) // проверка наличия ключа в карте
 * foreach($map as $key=>$value) // обход элементов карты
 * $n=count($map);  // получение числа элементов карты
 * </pre>
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CMap.php 1173 2009-06-26 01:57:22Z qiang.xue $
 * @package system.collections
 * @since 1.0
 */
class CMap extends CComponent implements IteratorAggregate,ArrayAccess,Countable
{
	/**
	 * @var array внутреннее хранилище данных
	 */
	private $_d=array();
	/**
	 * @var boolean только для чтения ли данный список
	 */
	private $_r=false;

	/**
	 * Конструктор.
	 * Инициализирует список массивом или итерируемым объектом.
	 * @param array начальные данные. По умолчанию null - без инициализации.
	 * @param boolean только для чтения ли данный список
	 * @throws CException вызывается, если данные не нулевые и не являются ни массивом ни итератором.
	 */
	public function __construct($data=null,$readOnly=false)
	{
		if($data!==null)
			$this->copyFrom($data);
		$this->setReadOnly($readOnly);
	}

	/**
	 * @return boolean только для чтения ли данная карта или нет. По умолчанию false.
	 */
	public function getReadOnly()
	{
		return $this->_r;
	}

	/**
	 * @param boolean только для чтения ли данный список или нет
	 */
	protected function setReadOnly($value)
	{
		$this->_r=$value;
	}

	/**
	 * Возвращает итератор для обхода элементов списка.
	 * Метод требуется интерфейсом IteratorAggregate.
	 * @return CMapIterator итератор для обхода элементов списка.
	 */
	public function getIterator()
	{
		return new CMapIterator($this->_d);
	}

	/**
	 * Возвращает количество элементов карты.
	 * Метод требуется интерфейсом Countable.
	 * @return integer количество элементов карты.
	 */
	public function count()
	{
		return $this->getCount();
	}

	/**
	 * @return integer количество элементов карты.
	 */
	public function getCount()
	{
		return count($this->_d);
	}

	/**
	 * @return array список ключей
	 */
	public function getKeys()
	{
		return array_keys($this->_d);
	}

	/**
	 * Возвращает элемент по определенному ключу.
	 * Метод в точности такой же как метод {@link offsetGet}.
	 * @param mixed ключ
	 * @return mixed элемент; null, если элемент не найден
	 */
	public function itemAt($key)
	{
		if(isset($this->_d[$key]))
			return $this->_d[$key];
		else
			return null;
	}

	/**
	 * Добавляет элемент в карту.
	 * Примечание: если определенный ключ уже существует, старое значение будет перезаписано.
	 * @param mixed ключ
	 * @param mixed значение
	 * @throws CException вызывается, если карта только для чтения
	 */
	public function add($key,$value)
	{
		if(!$this->_r)
		{
			if($key===null)
				$this->_d[]=$value;
			else
				$this->_d[$key]=$value;
		}
		else
			throw new CException(Yii::t('yii','The map is read only.'));
	}

	/**
	 * Удаляет элемент из карты по его ключу.
	 * @param mixed ключ удаляемого элемента
	 * @return mixed удаленное значение; null, если такого ключа не существует.
	 * @throws CException вызывается, если карта только для чтения
	 */
	public function remove($key)
	{
		if(!$this->_r)
		{
			if(isset($this->_d[$key]))
			{
				$value=$this->_d[$key];
				unset($this->_d[$key]);
				return $value;
			}
			else
			{
				// it is possible the value is null, which is not detected by isset
				unset($this->_d[$key]);
				return null;
			}
		}
		else
			throw new CException(Yii::t('yii','The map is read only.'));
	}

	/**
	 * Очищает карту
	 */
	public function clear()
	{
		foreach(array_keys($this->_d) as $key)
			$this->remove($key);
	}

	/**
	 * @param mixed ключ
	 * @return boolean содержит ли карта элемент с определенным ключом
	 */
	public function contains($key)
	{
		return isset($this->_d[$key]) || array_key_exists($key,$this->_d);
	}

	/**
	 * @return array список элементов массива
	 */
	public function toArray()
	{
		return $this->_d;
	}

	/**
	 * Копирует итерируемые данные в карту.
	 * Примечание: существующие данные сначала будут очищены.
	 * @param mixed копируемые данные; должны быть массивом или Traversable-объектом
	 * @throws CException вызывается, если данные не являются ни массивом ни итератором.
	 */
	public function copyFrom($data)
	{
		if(is_array($data) || $data instanceof Traversable)
		{
			if($this->getCount()>0)
				$this->clear();
			if($data instanceof CMap)
				$data=$data->_d;
			foreach($data as $key=>$value)
				$this->add($key,$value);
		}
		else if($data!==null)
			throw new CException(Yii::t('yii','Map data must be an array or an object implementing Traversable.'));
	}

	/**
	 * Сливает итерируемые данные в карту.
	 *
	 * Существующие данные в карте будут перезаписаны, если их ключи совпадают с ключами добавляемых данных.
	 * Если происходит рекурсивное слияние, выполняется следующий алгоритм:
	 * <ul>
	 * <li>данные карты сохраняются в переменную $a, а добавляемые данные - в переменную $b;</li>
	 * <li>если и $a и $b имеют индексированный массив при одинаковых строковых ключах, массивы сливаются по этому алгоритму;</li>
	 * <li>все целочисленно-индексированные элементы в $b добавляются в $a и переиндексируются соответственно;</li>
	 * <li>все строково-индексированные элементы в $b переписывают элементы в $a с теми же индексами;</li>
	 * </ul>
	 *
	 * @param mixed данные для слияния; должны быть массивом или Traversable-объектом
	 * @param boolean должно ли слияние быть рекурсивным.
	 *
	 * @throws CException вызывается, если данные не являются ни массивом ни итератором.
	 */
	public function mergeWith($data,$recursive=true)
	{
		if(is_array($data) || $data instanceof Traversable)
		{
			if($data instanceof CMap)
				$data=$data->_d;
			if($recursive)
			{
				if($data instanceof Traversable)
				{
					$d=array();
					foreach($data as $key=>$value)
						$d[$key]=$value;
					$this->_d=self::mergeArray($this->_d,$d);
				}
				else
					$this->_d=self::mergeArray($this->_d,$data);
			}
			else
			{
				foreach($data as $key=>$value)
					$this->add($key,$value);
			}
		}
		else if($data!==null)
			throw new CException(Yii::t('yii','Map data must be an array or an object implementing Traversable.'));
	}

	/**
	 * Сливает рекурсивно два массива в один.
	 * @param array массив, в который происходит слияние
	 * @param array массив, который сливается с предыдущим
	 * @return array слитый массив (исходные массивы остаются без изменений)
	 * @see mergeWith
	 */
	public static function mergeArray($a,$b)
	{
		foreach($b as $k=>$v)
		{
			if(is_integer($k))
				$a[]=$v;
			else if(is_array($v) && isset($a[$k]) && is_array($a[$k]))
				$a[$k]=self::mergeArray($a[$k],$v);
			else
				$a[$k]=$v;
		}
		return $a;
	}

	/**
	 * Находится ли элемент на данном смещении (ключе).
	 * Метод требуется интерфейсом ArrayAccess.
	 * @param mixed смещение для проверки
	 * @return boolean
	 */
	public function offsetExists($offset)
	{
		return $this->contains($offset);
	}

	/**
	 * Возвращает элемент на данном смещении (ключе).
	 * Метод требуется интерфейсом ArrayAccess.
	 * @param integer смещение для получения элемента.
	 * @return mixed элемент на данном смещении (ключе); null, если элемента нет
	 */
	public function offsetGet($offset)
	{
		return $this->itemAt($offset);
	}

	/**
	 * Устанавливает элемент в определенное смещение.
	 * Метод требуется интерфейсом ArrayAccess.
	 * @param integer смещение для установки элемента
	 * @param mixed элемент
	 */
	public function offsetSet($offset,$item)
	{
		$this->add($offset,$item);
	}

	/**
	 * Удаляет элемент на определенном смещении.
	 * Метод требуется интерфейсом ArrayAccess.
	 * @param mixed смещение для удаления элемента
	 */
	public function offsetUnset($offset)
	{
		$this->remove($offset);
	}
}
