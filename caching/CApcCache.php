<?php
/**
 * Файл класса CApcCache
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Класс CApcCache реализует кэш-модуль приложения, основанный на {@link http://www.php.net/apc APC}.
 * Для использования этого компонента приложения должно быть загружено расширение PHP APC.
 *
 * Обратитесь к документации {@link CCache} за информацией об обычных операциях кэша, поддерживаемых компонентом CApcCache.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CApcCache.php 1295 2009-08-06 20:00:34Z qiang.xue $
 * @package system.caching
 * @since 1.0
 */
class CApcCache extends CCache
{
	/**
	 * Инициализирует данный компонент приложения.
	 * Метод требуется интерфейсом {@link IApplicationComponent}.
	 * Проверяет доступность APC.
	 * @throws CException вызывается, если расширение APC не загружено или отключено
	 */
	public function init()
	{
		parent::init();
		if(!extension_loaded('apc'))
			throw new CException(Yii::t('yii','CApcCache requires PHP apc extension to be loaded.'));
	}

	/**
	 * Получает значение из кэша по определенному ключу.
	 * Метод переопределяет реализацию класса-родителя.
	 * @param string уникальный ключ, идентифицирующий кэшированное значение
	 * @return string хранимое в кэше значение; false, если значения в кэше нет или его срок годности истек
	 */
	protected function getValue($key)
	{
		return apc_fetch($key);
	}

	/**
	 * Получает из кэша несколько значений с определенными ключами.
	 * @param array список ключей, идентифицирующих кэшированные значения
	 * @return array список кэшированных значений, индексированный по ключам
	 * @since 1.0.8
	 */
	protected function getValues($keys)
	{
		return array_combine($keys,apc_fetch($keys));
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
		return apc_store($key,$value,$expire);
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
		return apc_add($key,$value,$expire);
	}

	/**
	 * Удаляет из кеша значение по определенному ключу.
	 * Метод переопределяет реализацию класса-родителя.
	 * @param string ключ удаляемого значения
	 * @return boolean true, если в процессе удаления не произошло ошибок
	 */
	protected function deleteValue($key)
	{
		return apc_delete($key);
	}

	/**
	 * Удаляет все значения из кэша.
	 * Будьте осторожны при выполнении данной операции, если кэш доступен в нескольких приложениях.
	 */
	public function flush()
	{
		return apc_clear_cache('user');
	}
}
