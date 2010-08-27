<?php
/**
 * Файл класса CZendDataCache.
 *
 * @author Steffen Dietz <steffo.dietz[at]googlemail[dot]com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Класс CZendDataCache реализует кэш-компонент приложения, основанный на Zend Data Cache и
 * поставляемый с {@link http://www.zend.com/en/products/server/ ZendServer}.
 *
 * Для использования данного компонента приложения должно быть загружено расширение PHP Zend Data Cache.
 *
 * Обратитесь к документации {@link CCache} за информацией об обычных операциях кэша, поддерживаемых компонентом CZendDataCache.
 *
 * @author Steffen Dietz <steffo.dietz[at]googlemail[dot]com>
 * @version $Id: CZendDataCache.php 1678 2010-01-07 21:02:00Z qiang.xue $
 * @package system.caching
 * @since 1.0.4
 */
class CZendDataCache extends CCache
{
	/**
	 * Инициализирует данный компонент приложения.
	 * Метод требуется интерфейсом {@link IApplicationComponent}.
	 * Проверяет доступность Zend Data Cache.
	 * @throws CException если расширение Zend Data Cache не загружено.
	 */
	public function init()
	{
		parent::init();
		if(!function_exists('zend_shm_cache_store'))
			throw new CException(Yii::t('yii','CZendDataCache requires PHP Zend Data Cache extension to be loaded.'));
	}

	/**
	 * Получает значение из кэша по определенному ключу.
	 * Метод переопределяет реализацию класса-родителя.
	 * @param string уникальный ключ, идентифицирующий кэшированное значение
	 * @return string хранимое в кэше значение; false, если значения в кэше нет или его срок годности истек
	 */
	protected function getValue($key)
	{
		$result = zend_shm_cache_fetch($key);
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
		return zend_shm_cache_store($key,$value,$expire);
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
		return (NULL === zend_shm_cache_fetch($key)) ? $this->setValue($key,$value,$expire) : false;
	}

	/**
	 * Удаляет из кеша значение по определенному ключу.
	 * Метод переопределяет реализацию класса-родителя.
	 * @param string ключ удаляемого значения
	 * @return boolean true, если в процессе удаления не произошло ошибок
	 */
	protected function deleteValue($key)
	{
		return zend_shm_cache_delete($key);
	}

	/**
	 * Удаляет все значения из кэша.
	 * Будьте осторожны при выполнении данной операции, если кэш доступен в нескольких приложениях.
	 */
	public function flush()
	{
		zend_shm_cache_clear();
	}
}
