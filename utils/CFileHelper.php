<?php
/**
 * Файл класса CFileHelper.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Класс CFileHelper предоставляет набор вспомогательных методов для обычных операций файловой системы.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CFileHelper.php 2297 2010-08-02 14:41:08Z qiang.xue $
 * @package system.utils
 * @since 1.0
 */
class CFileHelper
{
	/**
	 * Возвращает расширение файла по его пути.
	 * Например, путь "path/to/something.php" вернет "php".
	 * @param string путь к файлу
	 * @return string расширение файла без точки
	 * @since 1.1.2
	 */
	public static function getExtension($path)
	{
		if(($pos=strrpos($path,'.'))!==false)
			return substr($path,$pos+1);
		else
			return '';
	}
	/**
	 * Рекурсивно копирует директорию.
	 * Если директория назначения не задана, она создается.
	 * @param string копируемая директория
	 * @param string директория назначения
	 * @param array настройки для копирования директории. Допустимы следующие настройки:
	 * <ul>
	 * <li>fileTypes: массив, список расширений файлов (без точки). Только файлы с этими расширениями будут скопированы.</li>
	 * <li>exclude: массив, список исключений файлов и директорий. Каждое исключение
	 * может быть именем или путем (к файлу или директории).
	 * Если имя файла или директории соответствует исключению, этот файл или директория не копируются. Например,
	 * исключение '.svn' запрещает копирование файлов и директорий с именем '.svn', а исключение '/a/b' -
	 * файлы или папки, находящиеся по пути '$src/a/b'. Помните, что надо использовать в качестве разделителя знак '/'
	 * вместо константы DIRECTORY_SEPARATOR.</li>
	 * <li>level: целое число, глубина рекурсии, по умолчанию равно -1.
	 * Уровень -1 означает, что будут скопированы все файлы и директории;
	 * Уровень 0 означает, что будут скопированы только файлы, находящиеся НЕПОСРЕДСТВЕННО в данной директории;
	 * Уровень N означает, что будут скопированы директории вплоть до уровня N.
 	 * </li>
	 * </ul>
	 */
	public static function copyDirectory($src,$dst,$options=array())
	{
		$fileTypes=array();
		$exclude=array();
		$level=-1;
		extract($options);
		self::copyDirectoryRecursive($src,$dst,'',$fileTypes,$exclude,$level);
	}

	/**
	 * Возвращает имена (с путем, если необходимо) файлов, найденных в переданной методу директории и поддиректориях.
	 * @param string директория, в которой происходит поиск
	 * @param array настройки для поиска файлов. Допустимы следующие настройки:
	 * <ul>
	 * <li>fileTypes: массив, список расширений файлов (без точки). Только файлы с этими расширениями будут возвращены.</li>
	 * <li>exclude: массив, список исключений файлов и директорий. Каждое исключение
	 * может быть именем или путем (к файлу или директории).
	 * Если имя файла или директории соответствует исключению, этот файл или директория не копируются. Например,
	 * исключение '.svn' запрещает копирование файлов и директорий с именем '.svn', а исключение '/a/b' -
	 * файлы или папки, находящиеся по пути '$src/a/b'. Помните, что надо использовать в качестве разделителя знак '/'
	 * вместо константы DIRECTORY_SEPARATOR.</li>
	 * <li>level: целое число, глубина рекурсии, по умолчанию равно -1.
	 * Уровень -1 означает, что будут искаться все файлы и директории;
	 * Уровень 0 означает, что будут искаться только файлы, находящиеся НЕПОСРЕДСТВЕННО в данной директории;
	 * Уровень N означает, что будут искаться директории вплоть до уровня N.
 	 * </li>
	 * </ul>
	 * @return array отсортированный список имен (с путем, если необходимо) файлов, найденных в переданной методу директории и поддиректориях
	 */
	public static function findFiles($dir,$options=array())
	{
		$fileTypes=array();
		$exclude=array();
		$level=-1;
		extract($options);
		$list=self::findFilesRecursive($dir,'',$fileTypes,$exclude,$level);
		sort($list);
		return $list;
	}

	/**
	 * Копирует директорию.
	 * В основном, используется методом {@link copyDirectory}.
	 * @param string копируемая директория
	 * @param string директория назначения
	 * @param string относительный путь к исходной директории
	 * @param array список расширений файлов (без точки). Только файлы с этими расширениями будут скопированы.
	 * @param array массив, список исключений файлов и директорий. Каждое исключение
	 * может быть именем или путем (к файлу или директории).
	 * Если имя файла или директории соответствует исключению, этот файл или директория не копируются. Например,
	 * исключение '.svn' запрещает копирование файлов и директорий с именем '.svn', а исключение '/a/b' -
	 * файлы или папки, находящиеся по пути '$src/a/b'. Помните, что надо использовать в качестве разделителя знак '/'
	 * вместо константы DIRECTORY_SEPARATOR.
	 * @param integer глубина рекурсии, по умолчанию равно -1.
	 * Уровень -1 означает, что будут скопированы все файлы и директории;
	 * Уровень 0 означает, что будут скопированы только файлы, находящиеся НЕПОСРЕДСТВЕННО в данной директории;
	 * Уровень N означает, что будут скопированы директории вплоть до уровня N.
	 */
	protected static function copyDirectoryRecursive($src,$dst,$base,$fileTypes,$exclude,$level)
	{
		@mkdir($dst);
		@chmod($dst,0777);
		$folder=opendir($src);
		while(($file=readdir($folder))!==false)
		{
			if($file==='.' || $file==='..')
				continue;
			$path=$src.DIRECTORY_SEPARATOR.$file;
			$isFile=is_file($path);
			if(self::validatePath($base,$file,$isFile,$fileTypes,$exclude))
			{
				if($isFile)
					copy($path,$dst.DIRECTORY_SEPARATOR.$file);
				else if($level)
					self::copyDirectoryRecursive($path,$dst.DIRECTORY_SEPARATOR.$file,$base.'/'.$file,$fileTypes,$exclude,$level-1);
			}
		}
		closedir($folder);
	}

	/**
	 * Возвращает имена (с путем, если необходимо) файлов, найденных в переданной методу директории и поддиректориях.
	 * В основном, метод используется методом {@link findFiles}.
	 * @param string директория, в которой происходит поиск
	 * @param string относительный путь к исходной директории
	 * @param array список расширений файлов (без точки). Только файлы с этими расширениями будут возвращены.
	 * @param array список исключений файлов и директорий. Каждое исключение
	 * может быть именем или путем (к файлу или директории).
	 * Если имя файла или директории соответствует исключению, этот файл или директория не копируются. Например,
	 * исключение '.svn' запрещает копирование файлов и директорий с именем '.svn', а исключение '/a/b' -
	 * файлы или папки, находящиеся по пути '$src/a/b'. Помните, что надо использовать в качестве разделителя знак '/'
	 * вместо константы DIRECTORY_SEPARATOR.
	 * @param integer глубина рекурсии, по умолчанию равно -1.
	 * Уровень -1 означает, что будут искаться все файлы и директории;
	 * Уровень 0 означает, что будут искаться только файлы, находящиеся НЕПОСРЕДСТВЕННО в данной директории;
	 * Уровень N означает, что будут искаться директории вплоть до уровня N.
	 * @return array список имен (с путем, если необходимо) файлов, найденных в переданной методу директории и поддиректориях
	 */
	protected static function findFilesRecursive($dir,$base,$fileTypes,$exclude,$level)
	{
		$list=array();
		$handle=opendir($dir);
		while(($file=readdir($handle))!==false)
		{
			if($file==='.' || $file==='..')
				continue;
			$path=$dir.DIRECTORY_SEPARATOR.$file;
			$isFile=is_file($path);
			if(self::validatePath($base,$file,$isFile,$fileTypes,$exclude))
			{
				if($isFile)
					$list[]=$path;
				else if($level)
					$list=array_merge($list,self::findFilesRecursive($path,$base.'/'.$file,$fileTypes,$exclude,$level-1));
			}
		}
		closedir($handle);
		return $list;
	}

	/**
	 * Определяет правильность файла или директории.
	 * @param string относительный путь к исходной директории
	 * @param string имя файла или директории
	 * @param boolean файл ли это
	 * @param array список расширений файлов (без точки). Только файлы с такими расширениями будут считаться правильными.
	 * @param array список исключений файлов и директорий. Каждое исключение
	 * может быть именем или путем (к файлу или директории).
	 * Если имя файла или директории соответствует исключению, этот файл или директория считается не допустимым. Например,
	 * исключение '.svn' говорит, что файлы и директории с именем '.svn' недопустимы, а исключение '/a/b' - что недопустимы
	 * файлы или папки, находящиеся по пути '$src/a/b'. Помните, что надо использовать в качестве разделителя знак '/'
	 * вместо константы DIRECTORY_SEPARATOR.
	 * @return boolean является ли файл или папка допустимыми
	 */
	protected static function validatePath($base,$file,$isFile,$fileTypes,$exclude)
	{
		foreach($exclude as $e)
		{
			if($file===$e || strpos($base.'/'.$file,$e)===0)
				return false;
		}
		if(!$isFile || empty($fileTypes))
			return true;
		if(($pos=strrpos($file,'.'))!==false)
		{
			$type=substr($file,$pos+1);
			return in_array($type,$fileTypes);
		}
		else
			return false;
	}

	/**
	 * Определяет MIME-тип файла.
	 * Метод будет пытаться определить расширение в следующем порядке:
	 * <ol>
	 * <li>finfo</li>
	 * <li>mime_content_type</li>
	 * <li>{@link getMimeTypeByExtension}</li>
	 * </ol>
	 * @param string имя файла
	 * @param string имя "магического" файла данных MIME типов, обычно что-то вроде /path/to/magic.mime.
	 * Передается вторым параметром в функцию {@link http://php.net/manual/en/function.finfo-open.php finfo_open}.
	 * Параметр доступен с версии 1.1.3.
	 * @return string MIME-тип. Null, если MIME-тип не может быть определен
	 */
	public static function getMimeType($file,$magicFile=null)
	{
		if(function_exists('finfo_open'))
		{
			$info=$magicFile===null ? finfo_open(FILEINFO_MIME_TYPE) : finfo_open(FILEINFO_MIME_TYPE,$magicFile);
			if($info && ($result=finfo_file($info,$file))!==false)
				return $result;
		}

		if(function_exists('mime_content_type') && ($result=mime_content_type($file))!==false)
			return $result;

		return self::getMimeTypeByExtension($file);
	}

	/**
	 * Определяет MIME-тип, основываясь на расширении имени определенного файла.
	 * Метод будет использовать встроенную карту 'расширение' => 'MIME-тип'.
	 * @param string имя файла
	 * @param string путь к файлу, содержащему информацию о всех доступных MIME типах.
	 * Если не установлен, используется файл по умолчанию - 'system.utils.mimeTypes'.
	 * Параметр доступен с версии 1.1.3.
	 * @return string MIME-тип. Null, если MIME-тип не может быть определен
	 */
	public static function getMimeTypeByExtension($file,$magicFile=null)
	{
		static $extensions;
		if($extensions===null)
			$extensions=$magicFile===null ? require(Yii::getPathOfAlias('system.utils.mimeTypes').'.php') : $magicFile;
		if(($pos=strrpos($file,'.'))!==false)
		{
			$ext=strtolower(substr($file,$pos+1));
			if(isset($extensions[$ext]))
				return $extensions[$ext];
		}
		return null;
	}
}
