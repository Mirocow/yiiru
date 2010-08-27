<?php
/**
 * Файл содержит класс, реализующий функции конфигурации.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */


/**
 * Экземпляр класса CConfiguration представляет собой массив конфигурации.
 *
 * Может быть использован для инициализации свойств объектов.
 *
 * Данные конфигурации могут быть получены из PHP-скрипта. Например,
 * <pre>
 * &lt;?php
 * return array
 * (
 *     'name'=>'Моё приложение',
 *     'defaultController'=>'index',
 * );
 * ?&gt;
 * </pre>
 * Используйте следующий код для загрузки данных конфигурации, написанных выше:
 * <pre>
 * $config=new CConfiguration('путь/к/config.php');
 * </pre>
 *
 * Для применения конфигурации к объекту вызовите метод {@link applyTo()}.
 * Каждая пара значений (key,value) в данных конфигурации применяется
 * к объекту как: $object->$key=$value.
 *
 * Поскольку класс CConfiguration наследует класс {@link CMap}, он может 
 * быть использован в качестве ассоциативного массива. За подробностями обращайтесь к классу {@link CMap}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CConfiguration.php 1678 2010-01-07 21:02:00Z qiang.xue $
 * @package system.collections
 * @since 1.0
 */
class CConfiguration extends CMap
{
	/**
	 * Конструктор.
	 * @param mixed если передается строка, то она представляет собой
	 * путь к файлу конфигурации (PHP-скрипт, возвращающий конфигурацию как массив);
	 * если передается массив, то это данные конфигурации.
	 */
	public function __construct($data=null)
	{
		if(is_string($data))
			parent::__construct(require($data));
		else
			parent::__construct($data);
	}

	/**
	 * Загружает данные конфигурации из файла и сливает их с существующими данными.
	 *
	 * Файл конфигурации должен быть PHP-скриптом, возвращающим массив конфигурации (например, код ниже)
	 * <pre>
	 * return array
	 * (
	 *     'name'=>'Моё приложение',
	 *     'defaultController'=>'index',
	 * );
	 * </pre>
	 *
	 * @param string путь к файлу конфигурации (при использовании относительного пути,
	 * будьте уверены в правильности текущего)
	 * @see mergeWith
	 */
	public function loadFromFile($configFile)
	{
		$data=require($configFile);
		if($this->getCount()>0)
			$this->mergeWith($data);
		else
			$this->copyFrom($data);
	}

	/**
	 * Сохраняет конфигурацию в строку.
	 * Данная строка является правильным PHP-выражением, представляющим собой данные конфигурации в виде массива.
	 * @return string строка, представляющая конфигурацию
	 */
	public function saveAsString()
	{
		return str_replace("\r",'',var_export($this->toArray(),true));
	}

	/**
	 * Применяет конфигурацию к объекту.
	 * Каждая пара (key,value) в данных конфигурации применяется к объекту как: $object->$key=$value.
	 * @param object объект, к которому применяется конфигурация
	 */
	public function applyTo($object)
	{
		foreach($this->toArray() as $key=>$value)
			$object->$key=$value;
	}

	/**
	 * Создает объект и инициализирует его переданной конфигурацией.
	 *
	 * МЕТОД ЯВЛЯЕТСЯ УСТАРЕВШИМ С ВЕРСИИ 1.0.1.
	 * ПИспользуйте вместо него метод {@link YiiBase::createComponent Yii::createComponent}.
	 *
	 * @param mixed конфигурация. Может быть либо строкой либо массивом.
	 * @return mixed созданный объект
	 * @throws CException вызывается, если конфигурация не содержит значения с ключом 'class'
	 */
	public static function createObject($config)
	{
		if($config instanceof self)
			$config=$config->toArray();
		return Yii::createComponent($config);
	}
}
