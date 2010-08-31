<?php
/**
 * Файл класса CConsoleCommand.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CConsoleCommand представляет собой выполняемую пользователем команду.
 *
 * Метод {@link run} должен быть переопределен с реальной логикой выполнения команды.
 * Вы можете переопределить метод {@link getHelp} для получения более детализированного описания команды.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CConsoleCommand.php 1832 2010-02-20 03:22:45Z qiang.xue $
 * @package system.console
 * @since 1.0
 */
abstract class CConsoleCommand extends CComponent
{
	private $_name;
	private $_runner;

	/**
	 * Выполняет команду.
	 * @param array параметры командной строки для данной команды
	 */
	public abstract function run($args);

	/**
	 * Конструктор.
	 * @param string имя команды
	 * @param CConsoleCommandRunner исполнитель (runner) команды
	 */
	public function __construct($name,$runner)
	{
		$this->_name=$name;
		$this->_runner=$runner;
	}

	/**
	 * @return string имя команды
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * @return CConsoleCommandRunner экземпляр исполнителя (runner) команды
	 */
	public function getCommandRunner()
	{
		return $this->_runner;
	}

	/**
	 * Предоставляет описание команды.
	 * Метод может быть переопределен для вывода расширенного описания команды.
	 * @return string описание команды. По умолчанию выводится строка 'Usage: php файл-скрипта.php имя-команды'.
	 */
	public function getHelp()
	{
		return 'Usage: '.$this->getCommandRunner()->getScriptName().' '.$this->getName();
	}

	/**
	 * Отображает ошибки использования.
	 * Метода прерывает выполнение текущего приложения.
	 * @param string сообщение ошибки
	 */
	public function usageError($message)
	{
		die("Error: $message\n\n".$this->getHelp()."\n");
	}

	/**
	 * Копирует список файлов из одного места в другое.
	 * @param array список копируемых файлов (имя => параметры).
	 * Ключи массива - имена, отображаемые во время процесса копирования, а его значения - параметры
	 * копируемых файлов. Каждое значение массива должно быть массивом следующей структуры:
	 * <ul>
	 * <li>source: обязательно, полный путь копируемого файла/директории;</li>
	 * <li>target: обязательно, полный путь до места назначения;</li>
	 * <li>callback: опционально, обратный вызов, выполняемый при копировании файла. Функция обратного вызова
	 *   должна определяться так:
	 *   <pre>
	 *   function foo($source,$params)
	 *   </pre>
	 *   где параметр $source - исходный путь до файла, возвращаемые функцией данные
	 *   будут сохранены в целевой файл;</li>
	 * <li>params: опционально, параметры, передаваемые в обратный вызов</li>
	 * </ul>
	 * @see buildFileList
	 */
	public function copyFiles($fileList)
	{
		$overwriteAll=false;
		foreach($fileList as $name=>$file)
		{
			$source=strtr($file['source'],'/\\',DIRECTORY_SEPARATOR);
			$target=strtr($file['target'],'/\\',DIRECTORY_SEPARATOR);
			$callback=isset($file['callback']) ? $file['callback'] : null;
			$params=isset($file['params']) ? $file['params'] : null;

			if(is_dir($source))
			{
				$this->ensureDirectory($target);
				continue;
			}

			if($callback!==null)
				$content=call_user_func($callback,$source,$params);
			else
				$content=file_get_contents($source);
			if(is_file($target))
			{
				if($content===file_get_contents($target))
				{
					echo "  unchanged $name\n";
					continue;
				}
				if($overwriteAll)
					echo "  overwrite $name\n";
				else
				{
					echo "      exist $name\n";
					echo "            ...overwrite? [Yes|No|All|Quit] ";
					$answer=trim(fgets(STDIN));
					if(!strncasecmp($answer,'q',1))
						return;
					else if(!strncasecmp($answer,'y',1))
						echo "  overwrite $name\n";
					else if(!strncasecmp($answer,'a',1))
					{
						echo "  overwrite $name\n";
						$overwriteAll=true;
					}
					else
					{
						echo "       skip $name\n";
						continue;
					}
				}
			}
			else
			{
				$this->ensureDirectory(dirname($target));
				echo "   generate $name\n";
			}
			file_put_contents($target,$content);
		}
	}

	/**
	 * Строит список файлов в директории.
	 * Метод просматривает переданную в параметре директорию и строит список файлов
	 * и поддиректорий, содержащихся в данной директории.
	 * Результат данной функции может быть передан в метод {@link copyFiles}.
	 * @param string исходная директория
	 * @param string целевая директория
	 * @param string базовая директория
	 * @return array список файлов (см. {@link copyFiles})
	 */
	public function buildFileList($sourceDir, $targetDir, $baseDir='')
	{
		$list=array();
		$handle=opendir($sourceDir);
		while(($file=readdir($handle))!==false)
		{
			if($file==='.' || $file==='..' || $file==='.svn' ||$file==='.yii')
				continue;
			$sourcePath=$sourceDir.DIRECTORY_SEPARATOR.$file;
			$targetPath=$targetDir.DIRECTORY_SEPARATOR.$file;
			$name=$baseDir===''?$file : $baseDir.'/'.$file;
			$list[$name]=array('source'=>$sourcePath, 'target'=>$targetPath);
			if(is_dir($sourcePath))
				$list=array_merge($list,$this->buildFileList($sourcePath,$targetPath,$name));
		}
		closedir($handle);
		return $list;
	}

	/**
	 * Создает все родительские директории, если они не существуют.
	 * @param string проверяемая директория
	 */
	public function ensureDirectory($directory)
	{
		if(!is_dir($directory))
		{
			$this->ensureDirectory(dirname($directory));
			echo "      mkdir ".strtr($directory,'\\','/')."\n";
			mkdir($directory);
		}
	}

	/**
	 * Рендерит файл представления.
	 * @param string путь до файла представления
	 * @param array опциональные данные, распаковываемые в виде локальных переменных представления
	 * @param boolean возвратить ли результат рендера вместо его отображения на экран
	 * @return mixed результат рендера по требованию, иначе null
	 */
	public function renderFile($_viewFile_,$_data_=null,$_return_=false)
	{
		if(is_array($_data_))
			extract($_data_,EXTR_PREFIX_SAME,'data');
		else
			$data=$_data_;
		if($_return_)
		{
			ob_start();
			ob_implicit_flush(false);
			require($_viewFile_);
			return ob_get_clean();
		}
		else
			require($_viewFile_);
	}

	/**
	 * Конвертирует слово во множественную форму (плюрализация). Только английские слова.
	 * @param string плюрализуемое слово
	 * @return string плюрализованное слово
	 */
	public function pluralize($name)
	{
		$rules=array(
			'/(x|ch|ss|sh|us|as|is|os)$/i' => '\1es',
			'/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
			'/(m)an$/i' => '\1en',
			'/(child)$/i' => '\1ren',
			'/(r)y$/i' => '\1ies',
			'/s$/' => 's',
		);
		foreach($rules as $rule=>$replacement)
		{
			if(preg_match($rule,$name))
				return preg_replace($rule,$replacement,$name);
		}
		return $name.'s';
	}
}