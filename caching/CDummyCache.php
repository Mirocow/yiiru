<?php
/**
 * Файл класса CDummyCache.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Класс CDummyCache - это заглушка компонента кэша.
 *
 * Компонент CDummyCache ничего не делает и не кэширует. Предназначен для того, чтобы можно было настроить
 * компонент приложения 'cache' и не проводить проверку Yii::app()->cache на null.
 * Заменой CDummyCache другим кэширующим компонентом можно быстро переключиться с
 * режима без кэша на режим с кэшем.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CDummyCache.php 2799 2011-01-01 19:31:13Z qiang.xue $
 * @package system.caching
 * @since 1.0
 */
class CDummyCache extends CApplicationComponent implements ICache, ArrayAccess
{
	/**
	 * @var string строка, представляющая собой префикс ключа для обеспечения уникальности. По умолчанию -
	 * {@link CApplication::getId() идентификатор приложения}
	 */
	public $keyPrefix;

	/**
	 * Инициализирует компонент приложения.
	 * Метод переопределяет родительскую реализацию установкой префикса ключа.
	 */
	public function init()
	{
		parent::init();
		if($this->keyPrefix===null)
			$this->keyPrefix=Yii::app()->getId();
	}

	/**
	 * Получает значение из кэша по определенному ключу
	 * @param string $id ключ, идентифицирующий значение кэша
	 * @return mixed значение, сохраненное в кэше; false, если значения нет в кэше, срок годности истек или зависимость изменена
	 */
	public function get($id)
	{
		return false;
	}

	/**
	 * Получает несколько значений из кэша по определенным ключам.
	 * Некоторые кэши (такие как memcache, apc) позволяют одновременно получать несколько значений из кэша,
	 * что может увеличить производительность из-за снижения количества соединений.
	 * Если кэш не поддерживает данную функцию, данный метод симулирует её.
	 * @param array $ids список ключей, идентифицирующих кэшированные значения
	 * @return array список кэшированных значений, соответствующих переданным ключам.
	 * Возвращается массив пар (ключ, значение). Если значения нет в кэше или его срок
	 * годности истек, соответствующее значение массива будет равно значению false
	 * @since 1.0.8
	 */
	public function mget($ids)
	{
		$results=array();
		foreach($ids as $id)
			$results[$id]=false;
		return $results;
	}

	/**
	 * Сохраняет значение, идентифицируемое по ключу, в кэше.
	 * Если кэш уже содержит такой ключ, существующее значение и срок годности будут заменены на новые.
	 *
	 * @param string $id ключ, идентифицирующий кэшируемое значение
	 * @param mixed $value кэшируемое значение
	 * @param integer $expire количество секунд, через которое истечет срок годности кэшируемого значения. 0 означает бесконечный срок годности
	 * @param ICacheDependency $dependency зависимость кэшируемого элемента. Если зависимость изменяется, элемент помечается как недействительный
	 * @return boolean true, если значение успешно сохранено в кэше, иначе - false
	 */
	public function set($id,$value,$expire=0,$dependency=null)
	{
		return true;
	}

	/**
	 * Сохраняет в кэш значение, идентифицируемое ключом, если кэш не содержит данный ключ.
	 * Если такой ключ уже содержится в кэше, ничего не будет выполнено.
	 * @param string $id ключ, идентифицирующий кэшируемое значение
	 * @param mixed $value кэшируемое значение
	 * @param integer $expire количество секунд, через которое истечет срок годности кэшируемого значения. 0 означает бесконечный срок годности
	 * @param ICacheDependency $dependency зависимость кэшируемого элемента. Если зависимость изменяется, элемент помечается как недействительный
	 * @return boolean true, если значение успешно сохранено в кэше, иначе - false
	 */
	public function add($id,$value,$expire=0,$dependency=null)
	{
		return true;
	}

	/**
	 * Удаляет из кеша значение по определенному ключу.
	 * @param string $id ключ удаляемого значения
	 * @return boolean true, если в процессе удаления не произошло ошибок
	 */
	public function delete($id)
	{
		return true;
	}

	/**
	 * Удаляет все значения из кэша.
	 * Будьте осторожны при выполнении данной операции, если кэш является общим для нескольких приложений.
	 * @return boolean успешно ли выполнилась операция очистки
	 * @throws CException вызывается, если метод не переопределен классом-наследником
	 */
	public function flush()
	{
		return true;
	}

	/**
	 * Существует ли запись в кэше с заданным ключом.
	 * Метод требуется интерфейсом ArrayAccess.
	 * @param string $id ключ, идентифицирующий кэшированное значение
	 * @return boolean
	 */
	public function offsetExists($id)
	{
		return false;
	}

	/**
	 * Получает значение из кэша по определенному ключу.
	 * Метод требуется интерфейсом ArrayAccess.
	 * @param string $id ключ, идентифицирующий кэшированное значение
	 * @return mixed кэшированное значение; false, если значения в кэше нет или его срок годности истек
	 */
	public function offsetGet($id)
	{
		return false;
	}

	/**
	 * Сохраняет в кэше значение, идентифицируемое ключом.
	 * Если кэш уже содержит значение с таким ключом, существующее значение будет
	 * заменено новым. Для добавления срока годности и зависимостей, используйте метод set().
	 * Метод требуется интерфейсом ArrayAccess.
	 * @param string $id ключ, идентифицирующий кэшируемое значение
	 * @param mixed $value кэшируемое значение
	 */
	public function offsetSet($id, $value)
	{
	}

	/**
	 * Удаляет из кеша значение по определенному ключу.
	 * Метод требуется интерфейсом ArrayAccess.
	 * @param string $id ключ, идентифицирующий удаляемое значение
	 * @return boolean true, если в процессе удаления не произошло ошибок
	 */
	public function offsetUnset($id)
	{
	}
}
