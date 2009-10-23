<?php
/**
 * Файл класса CXCache
 *
 * @author Wei Zhuo <weizhuo[at]gmail[dot]com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Компонент CXCache реализует кэш-модуль приложения, основанный на {@link http://xcache.lighttpd.net/ xcache}.
 *
 * Для использования этого компонента приложения должно быть загружено расширение PHP XCache.
 *
 * Обратитесь к документации {@link CCache} за информацией об обычных операциях кэша, поддерживаемых компонентом CXCache.
 *
 * @author Wei Zhuo <weizhuo[at]gmail[dot]com>
 * @version $Id: CXCache.php 1093 2009-06-05 13:09:17Z qiang.xue $
 * @package system.caching
 * @since 1.0.1
 */
class CXCache extends CCache
{
	/**
	 * Инициализирует данный компонент приложения.
	 * Метод требуется интерфейсом {@link IApplicationComponent}.
	 * Проверяет доступность xcache.
	 * @throws CException вызывается, если расширение xcache не загружено или отключено
	 */
	public function init()
	{
		parent::init();
		if(!function_exists('xcache_isset'))
			throw new CException(Yii::t('yii','CXCache requires PHP XCache extension to be loaded.'));
	}

	/**
	 * Получает значение из кэша по определенному ключу.
	 * Метод переопределяет реализацию класса-родителя.
	 * @param string уникальный ключ, идентифицирующий кэшированное значение
	 * @return string хранимое в кэше значение; false, если значения в кэше нет или его срок годности истек
	 */
	protected function getValue($key)
	{
		return xcache_isset($key) ? xcache_get($key) : false;
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
		return xcache_set($key,$value,$expire);
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
		return !xcache_isset($key) ? $this->setValue($key,$value,$expire) : false;
	}

	/**
	 * Удаляет из кеша значение по определенному ключу.
	 * Метод переопределяет реализацию класса-родителя.
	 * @param string ключ удаляемого значения
	 * @return boolean true, если в процессе удаления не произошло ошибок
	 */
	protected function deleteValue($key)
	{
		return xcache_unset($key);
	}

	/**
	 * Удаляет все значения из кэша.
	 * Будьте осторожны при выполнении данной операции, если кэш доступен в нескольких приложениях.
	 */
	public function flush()
	{
		return xcache_clear_cache();
	}
}

