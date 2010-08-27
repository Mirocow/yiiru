<?php
/**
 * Файл класса CDirectoryCacheDependency.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Компонент CDirectoryCacheDependency представляет собой зависимость, основанную на изменении директории.
 *
 * Компонент CDirectoryCacheDependency выполняет проверку зависимости, основанную на
 * времени модификации файлов, находящихся в определенной директории.
 * Директория для проверки определяется свойством {@link directory}.
 *
 * По умолчанию будут проверены все файлы в определенной директории и поддиректориях.
 * Если время модификации любого из них изменено или в директории содержится другое
 * количество файлов, зависимость считается измененной.
 * Указав свойство {@link recursiveLevel}, можно ограничить проверку на определенную глубину в директории.
 *
 * Примечание: проверка зависимости для директорий ресурсоёмкая операция, потому что
 * включает в себя доступ ко времени модификации многих файлов в директории.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CDirectoryCacheDependency.php 1678 2010-01-07 21:02:00Z qiang.xue $
 * @package system.caching.dependencies
 * @since 1.0
 */
class CDirectoryCacheDependency extends CCacheDependency
{
	/**
	 * @var string директория, изменение которой используется для определения изменения зависимости.
	 * Если изменен любой файл директории, зависимость считается измененной.
	 */
	public $directory;
	/**
	 * @var integer глубина поддиректорий для рекурсивной проверки.
	 * Значение, меньшее 0, означает неограниченную глубину.
	 * Значение 0 означает, что проверяются только файлы в данной директории.
	 */
	public $recursiveLevel=-1;
	/**
	 * @var string регулярное выражение для проверки правильности имён файла/директории.
	 * Только соответствующие регулярному выражению файлы или директории будут проверены на изменения.
	 * По умолчанию - null, что значит, все файлы и директории будут проверены.
	 */
	public $namePattern;

	/**
	 * Конструктор.
	 * @param string директория для проверки
	 */
	public function __construct($directory=null)
	{
		$this->directory=$directory;
	}

	/**
	 * Генерирует данные, необходимые для определения изменения зависимости.
	 * Метод возвращает массив времени последней модификации файлов в директории.
	 * @return mixed данные, необходимые для определения изменения зависимости
	 */
	protected function generateDependentData()
	{
		if($this->directory!==null)
			return $this->generateTimestamps($this->directory);
		else
			throw new CException(Yii::t('yii','CDirectoryCacheDependency.directory cannot be empty.'));
	}

	/**
	 * Определяет время последней модификации файлов в директории.
	 * Метод может пройти рекурсивно в поддиректории, если свойство {@link recursiveLevel} не равно 0.
	 * @param string имя директории
	 * @param int уровень рекурсии
	 * @return array список времени модификации файлов, индексированный по пути файла
	 */
	protected function generateTimestamps($directory,$level=0)
	{
		if(($dir=@opendir($directory))===false)
			throw new CException(Yii::t('yii','"{path}" is not a valid directory.',
				array('{path}'=>$directory)));
		$timestamps=array();
		while(($file=readdir($dir))!==false)
		{
			$path=$directory.DIRECTORY_SEPARATOR.$file;
			if($file==='.' || $file==='..')
				continue;
			if($this->namePattern!==null && !preg_match($this->namePattern,$file))
				continue;
			if(is_file($path))
			{
				if($this->validateFile($path))
					$timestamps[$path]=filemtime($path);
			}
			else
			{
				if(($this->recursiveLevel<0 || $level<$this->recursiveLevel) && $this->validateDirectory($path))
					$timestamps=array_merge($this->generateTimestamps($path,$level+1));
			}
		}
		closedir($dir);
		return $timestamps;
	}

	/**
	 * Checks to see if the file should be checked for dependency.
	 * This method is invoked when dependency of the whole directory is being checked.
	 * By default, it always returns true, meaning the file should be checked.
	 * You may override this method to check only certain files.
	 * @param string the name of the file that may be checked for dependency.
	 * @return boolean whether this file should be checked.
	 */
	protected function validateFile($fileName)
	{
		return true;
	}

	/**
	 * Checks to see if the specified subdirectory should be checked for dependency.
	 * This method is invoked when dependency of the whole directory is being checked.
	 * By default, it always returns true, meaning the subdirectory should be checked.
	 * You may override this method to check only certain subdirectories.
	 * @param string the name of the subdirectory that may be checked for dependency.
	 * @return boolean whether this subdirectory should be checked.
	 */
	protected function validateDirectory($directory)
	{
		return true;
	}
}
