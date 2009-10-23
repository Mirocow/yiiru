<?php
/**
 * Файл класса CEAcceleratorCache
 *
 * @author Steffen Dietz <steffo.dietz[at]googlemail[dot]com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Класс CEAcceleratorCache реализует кэш-модуль приложения, основанный на {@link http://eaccelerator.net/ eaccelerator}.
 *
 * Для использования этого компонента приложения должно быть загружено расширение PHP eAccelerator.
 *
 * Обратитесь к документации {@link CCache} за информацией об обычных операциях кэша, поддерживаемых компонентом CEAccelerator.
 *
 * @author Steffen Dietz <steffo.dietz[at]googlemail[dot]com>
 * @version $Id: CEAcceleratorCache.php 814 2009-03-10 18:00:11Z qiang.xue $
 * @package system.caching
 * @since 1.0.4
 */
class CEAcceleratorCache extends CCache
{
	/**
	 * Инициализирует данный компонент приложения.
	 * Метод требуется интерфейсом {@link IApplicationComponent}.
	 * Проверяет доступность memcache.
	 * @throws CException вызывается, если расширение eAccelerator не загружено, отключено или функции кэша не скомпилированы
	 */
	public function init()
	{
		parent::init();
		if(!function_exists('eaccelerator_get'))
			throw new CException(Yii::t('yii','CEAcceleratorCache requires PHP eAccelerator extension to be loaded, enabled or compiled with the "--with-eaccelerator-shared-memory" option.'));
	}

	/**
	 * Получает значение из кэша по определенному ключу.
	 * Метод переопределяет реализацию класса-родителя.
	 * @param string уникальный ключ, идентифицирующий кэшированное значение
	 * @return string хранимое в кэше значение; false, если значения в кэше нет или его срок годности истек
	 */
	protected function getValue($key)
	{
		$result = eaccelerator_get($key);
		return $result !== NULL ? $result : false;
	}

	/**
	 * Сохраняет в кэше значение, идентифицируемое ключом.
	 * Метод переопределяет реализацию класса-родителя.
	 * @param string ключ, идентифицирующий кэшируемое значение
	 * @param string кэшируемое значение
	 * @param integer количество секунд срока годности кэшируемого значения. 0 - без срока годности
	 * @return boolean true, если значение успешно сохранено в кэше, иначе false
	 */
	protected function setValue($key,$value,$expire)
	{
		return eaccelerator_put($key,$value,$expire);
	}

	/**
	 * Сохраняет в кэше значение, идентифицируемое ключом, если кэш не содержит данный ключ.
	 * Метод переопределяет реализацию класса-родителя.
	 * @param string ключ, идентифицирующий кэшируемое значение
	 * @param string кэшируемое значение
	 * @param integer количество секунд срока годности кэшируемого значения. 0 - без срока годности
	 * @return boolean true, если значение успешно сохранено в кэше, иначе false
	 */
	protected function addValue($key,$value,$expire)
	{
		return (NULL === eaccelerator_get($key)) ? $this->setValue($key,$value,$expire) : false;
	}

	/**
	 * Удаляет из кеша значение по определенному ключу.
	 * Метод переопределяет реализацию класса-родителя.
	 * @param string ключ удаляемого значения
	 * @return boolean true, если в процессе удаления не произошло ошибок
	 */
	protected function deleteValue($key)
	{
		return eaccelerator_rm($key);
	}

	/**
	 * Удаляет все значения из кэша.
	 * Будьте осторожны при выполнении данной операции, если кэш доступен в нескольких приложениях.
	 */
	public function flush()
	{
		// first, remove expired content from cache
		eaccelerator_gc();

		// now, remove leftover cache-keys
		$keys = eaccelerator_list_keys();
		foreach($keys as $key)
			$this->deleteValue(substr($key['name'], 1));
	}
}

