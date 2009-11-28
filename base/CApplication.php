<?php
/**
 * Файл класса CApplication.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CApplication - это базовый класс для всех классов приложения.
 *
 * Приложение служит в качестве глобального контекста, в котором выполняется запрос пользователя.
 * Оно управляет набором компонентов приложения, предоставляющих
 * специальные функции для всего приложения.
 *
 * Список компонентов ядра приложения, предоставляемых классом CApplication:
 * <ul>
 * <li>{@link getErrorHandler errorHandler}: обрабатывает ошибки PHP и
 *   неперехваченные исключения. Данный компонент приложения загружается динамически при необходимости;</li>
 * <li>{@link getSecurityManager securityManager}: предоставляет функции безопасности
 *   такие, как хэширование, шифрование. Данный компонент приложения загружается динамически при необходимости;</li>
 * <li>{@link getStatePersister statePersister}: предоставляет функцию постоянного глобального состояния.
 *   Данный компонент приложения загружается динамически при необходимости;</li>
 * <li>{@link getCache cache}: предоставляет функции кэширования. Данный компонент по умолчанию отключен;</li>
 * <li>{@link getMessages messages}: предоставляет источник сообщений для перевода сообщений
 *   приложения. Данный компонент приложения загружается динамически при необходимости;</li>
 * <li>{@link getCoreMessages coreMessages}: предоставляет источник сообщений для перевода сообщений
 *   фреймворка Yii. Данный компонент приложения загружается динамически при необходимости.</li>
 * </ul>
 *
 * CApplication работает по следующему жизненному циклу при обработке пользовательского запроса:
 * <ol>
 * <li>загружает конфигурацию приложения;</li>
 * <li>устанавливает класс автозагрузчика и обработчика ошибок;</li>
 * <li>загружает статические компоненты приложения;</li>
 * <li>{@link onBeginRequest}: выполняет действия перед выполнением пользовательского запроса;</li>
 * <li>{@link processRequest}: выполняет пользовательский запрос;</li>
 * <li>{@link onEndRequest}: выполняет действия после выполнения пользовательского запроса;</li>
 * </ol>
 *
 * Начиная с пункта 3, при возникновении ошибки PHP или неперехваченного исключения,
 * приложение переключается на его обработчик ошибок и после переходит к шагу 6.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CApplication.php 1489 2009-10-27 21:13:26Z qiang.xue $
 * @package system.base
 * @since 1.0
 */
abstract class CApplication extends CModule
{
	/**
	 * @var string имя приложения. По умолчанию - 'My Application'.
	 */
	public $name='My Application';
	/**
	 * @var string кодировка, используемая приложением. По умолчанию - 'UTF-8'.
	 */
	public $charset='UTF-8';
	/**
	 * @var string язык приложения. В основном это язык сообщений и представлений.
	 * По умолчанию - 'en_us' (US English).
	 */
	public $sourceLanguage='en_us';

	private $_id;
	private $_basePath;
	private $_runtimePath;
	private $_extensionPath;
	private $_globalState;
	private $_stateChanged;
	private $_ended=false;
	private $_language;

	/**
	 * Выполняет запрос.
	 * Это то место, где выполняется основная работа по запросу.
	 * Классы-наследники должны переопределить данный метод.
	 */
	abstract public function processRequest();

	/**
	 * Конструктор.
	 * @param mixed конфигурация приложения.
	 * Если передана строка, она считается путем к файлу, содержащему конфигурацию;
	 * если передан массив, он считается реальной информацией конфигурации.
	 * Убедитесь, что свойство {@link getBasePath basePath} определено в конфигурации и
	 * указывает на директорию, содержащую всю логику приложения, шаблоны и данные.
	 * Если это свойство не указано, по умолчанию будет использована директория 'protected'.
	 */
	public function __construct($config=null)
	{
		Yii::setApplication($this);

		// set basePath at early as possible to avoid trouble
		if(is_string($config))
			$config=require($config);
		if(isset($config['basePath']))
		{
			$this->setBasePath($config['basePath']);
			unset($config['basePath']);
		}
		else
			$this->setBasePath('protected');
		Yii::setPathOfAlias('application',$this->getBasePath());
		Yii::setPathOfAlias('webroot',dirname($_SERVER['SCRIPT_FILENAME']));
		Yii::setPathOfAlias('ext',$this->getBasePath().DIRECTORY_SEPARATOR.'extensions');

		$this->preinit();

		$this->initSystemHandlers();
		$this->registerCoreComponents();

		$this->configure($config);
		$this->attachBehaviors($this->behaviors);
		$this->preloadComponents();

		$this->init();
	}


	/**
	 * Запускает приложение.
	 * Метод загружает статические компоненты приложения. Классы-наследники обычно переопределяют
	 * данны метод для выполнения более специфичных задач приложения.
	 * Не забудьте вызвать метод родителя для загрузки статических компонентов приложения.
	 */
	public function run()
	{
		if($this->hasEventHandler('onBeginRequest'))
			$this->onBeginRequest(new CEvent($this));
		$this->processRequest();
		if($this->hasEventHandler('onEndRequest'))
			$this->onEndRequest(new CEvent($this));
	}

	/**
	 * Завершает приложение.
	 * Метод заменяет PHP функцию exit() вызовом метода
	 * {@link onEndRequest} перед выходом.
	 * @param integer статус выхода (значение 0 означает нормальный выход, другое значение означает выход с ошибкой)
	 */
	public function end($status=0)
	{
		if($this->hasEventHandler('onEndRequest'))
			$this->onEndRequest(new CEvent($this));
		exit($status);
	}

	/**
	 * Выполняется прямо ПЕРЕД обработкой запроса приложением.
	 * @param CEvent параметр события
	 */
	public function onBeginRequest($event)
	{
		$this->raiseEvent('onBeginRequest',$event);
	}

	/**
	 * Выполняется сразу ПОСЛЕ обработки запроса приложением.
	 * @param CEvent параметр события
	 */
	public function onEndRequest($event)
	{
		if(!$this->_ended)
		{
			$this->_ended=true;
			$this->raiseEvent('onEndRequest',$event);
		}
	}

	/**
	 * @return string уникальный идентификатор приложения
	 */
	public function getId()
	{
		if($this->_id!==null)
			return $this->_id;
		else
			return $this->_id=sprintf('%x',crc32($this->getBasePath().$this->name));
	}

	/**
	 * @param string уникальный идентификатор приложения
	 */
	public function setId($id)
	{
		$this->_id=$id;
	}

	/**
	 * @return string корневая директория приложения. По умолчанию - 'protected'.
	 */
	public function getBasePath()
	{
		return $this->_basePath;
	}

	/**
	 * Устанавливает корневую директорию приложения.
	 * Метод может быть вызван только в начале конструктора.
	 * @param string корневая директорию приложения
	 * @throws CException вызывается, если директория не существует
	 */
	public function setBasePath($path)
	{
		if(($this->_basePath=realpath($path))===false || !is_dir($this->_basePath))
			throw new CException(Yii::t('yii','Application base path "{path}" is not a valid directory.',
				array('{path}'=>$path)));
	}

	/**
	 * @return string директория, хранящая рабочие файлы. По умолчанию - 'protected/runtime'.
	 */
	public function getRuntimePath()
	{
		if($this->_runtimePath!==null)
			return $this->_runtimePath;
		else
		{
			$this->setRuntimePath($this->getBasePath().DIRECTORY_SEPARATOR.'runtime');
			return $this->_runtimePath;
		}
	}

	/**
	 * @param string директория, хранящая рабочие файлы
	 * @throws CException вызывается, если директория не существует или недоступна для записи
	 */
	public function setRuntimePath($path)
	{
		if(($runtimePath=realpath($path))===false || !is_dir($runtimePath) || !is_writable($runtimePath))
			throw new CException(Yii::t('yii','Application runtime path "{path}" is not valid. Please make sure it is a directory writable by the Web server process.',
				array('{path}'=>$path)));
		$this->_runtimePath=$runtimePath;
	}

	/**
	 * Возвращает корневую директорию, хранящую все сторонние расширения.
	 * @return string директория, содержащая все расширения. По умолчанию - директория 'extensions' в директории 'protected'
	 */
	public function getExtensionPath()
	{
		return Yii::getPathOfAlias('ext');
	}

	/**
	 * @param string директория, содержащая все сторонние расширения
	 */
	public function setExtensionPath($path)
	{
		if(($extensionPath=realpath($path))===false || !is_dir($extensionPath))
			throw new CException(Yii::t('yii','Extension path "{path}" does not exist.',
				array('{path}'=>$path)));
		Yii::setPathOfAlias('ext',$extensionPath);
	}

	/**
	 * @return string язык, используемый пользователем и приложением.
	 * По умолчанию задан свойством {@link sourceLanguage}.
	 */
	public function getLanguage()
	{
		return $this->_language===null ? $this->sourceLanguage : $this->_language;
	}

	/**
	 * Определяет язык, используемый приложением.
	 *
	 * Это язык, отображаемый приложением конечным пользователям.
	 * Если null, будет использован язык, заданный свойством {@link sourceLanguage}.
	 *
	 * Если ваше приложение должно поддерживать несколько языков, вы должны всегда
	 * устанавливать данный язык в null для улучшения производительности приложения.
	 * @param string язык пользователя (например, 'en_US', 'zh_CN').
	 * Если null, будет использован язык, заданный свойством {@link sourceLanguage}.
	 */
	public function setLanguage($language)
	{
		$this->_language=$language;
	}

	/**
	 * Возвращает временную зону, используемую приложением.
	 * Это простая обертка PHP-функции date_default_timezone_get().
	 * @return string временная зона, используемая приложением
	 * @see http://php.net/manual/en/function.date-default-timezone-get.php
	 * @since 1.0.9
	 */
	public function getTimeZone()
	{
		return date_default_timezone_get();
	}

	/**
	 * Устанавливает временную зону, используемую приложением.
	 * Это простая обертка PHP-функции date_default_timezone_set().
	 * @param string временная зона, используемая приложением
	 * @see http://php.net/manual/en/function.date-default-timezone-set.php
	 * @since 1.0.9
	 */
	public function setTimeZone($value)
	{
		date_default_timezone_set($value);
	}

	/**
	 * Возвращает локализованную версию определенного файла.
	 *
	 * Поиск идет по коду определенного языка. В частности,
	 * файл с таким же именем будет искаться в поддиректории с именем,
	 * равным иеднтификатору локали. Например, если переданы файл "path/to/view.php"
	 * и локаль "zh_cn", то путёт поиска локализованнного файла будет
	 * "path/to/zh_cn/view.php". Если файл не найден, будет возвращен оригинальный файл.
	 *
	 * Для согласованности рекомендуется передавать идентификатор локали
	 * в нижнем регистре и в формате идентификаторЯзыка_идентификаторРегиона (например, "en_us").
	 *
	 * @param string оригинальный файл
	 * @param string язык оригинального файла. Если null, используется язык, заданный свойством {@link sourceLanguage}
	 * @param string желаемый язык, локализованная версия файла которого требуется. Если null, используется {@link getLanguage язык приложения}
	 * @return string соответствующий локализованный файл. Если локализованныя версия не найдена или исходный язык равен желаемомоу, возвращается оригинальный файл
	 */
	public function findLocalizedFile($srcFile,$srcLanguage=null,$language=null)
	{
		if($srcLanguage===null)
			$srcLanguage=$this->sourceLanguage;
		if($language===null)
			$language=$this->getLanguage();
		if($language===$srcLanguage)
			return $srcFile;
		$desiredFile=dirname($srcFile).DIRECTORY_SEPARATOR.$language.DIRECTORY_SEPARATOR.basename($srcFile);
		return is_file($desiredFile) ? $desiredFile : $srcFile;
	}

	/**
	 * @param string идентификатор локали (например, en_US). Если null, используется идентификатор {@link getLanguage языка приложения}
	 * @return CLocale экземпляр локали
	 */
	public function getLocale($localeID=null)
	{
		return CLocale::getInstance($localeID===null?$this->getLanguage():$localeID);
	}

	/**
	 * @return string директория, содержащая данные локали. По умолчанию - 'framework/i18n/data'
	 * @since 1.1.0
	 */
	public function getLocaleDataPath()
	{
		return CLocale::$dataPath===null ? Yii::getPathOfAlias('system.i18n.data') : CLocale::$dataPath;
	}

	/**
	 * @param string директория, содержащая данные локали
	 * @since 1.1.0
	 */
	public function setLocaleDataPath($value)
	{
		CLocale::$dataPath=$value;
	}

	/**
	 * @return CNumberFormatter локалезависимый менеджер форматирования чисел.
	 * Используется текущая {@link getLocale локаль приложения}.
	 */
	public function getNumberFormatter()
	{
		return $this->getLocale()->getNumberFormatter();
	}

	/**
	 * @return CDateFormatter локалезависимый менеджер форматирования дат.
	 * Используется текущая {@link getLocale локаль приложения}.
	 */
	public function getDateFormatter()
	{
		return $this->getLocale()->getDateFormatter();
	}

	/**
	 * @return CDbConnection компонент соединения с базой
	 */
	public function getDb()
	{
		return $this->getComponent('db');
	}

	/**
	 * @return CErrorHandler комопонент приложения, отвечающий за обработку ошибок
	 */
	public function getErrorHandler()
	{
		return $this->getComponent('errorHandler');
	}

	/**
	 * @return CSecurityManager компонент приложения, отвечающий за безопасность
	 */
	public function getSecurityManager()
	{
		return $this->getComponent('securityManager');
	}

	/**
	 * @return CStatePersister компонент приложения, представляющий постоянное состояние (state persister)
	 */
	public function getStatePersister()
	{
		return $this->getComponent('statePersister');
	}

	/**
	 * @return CCache компонент приложения кэша. Null, если компонент не включен.
	 */
	public function getCache()
	{
		return $this->getComponent('cache');
	}

	/**
	 * @return CPhpMessageSource компонент приложения, отвечающий за перевод сообщений ядра
	 */
	public function getCoreMessages()
	{
		return $this->getComponent('coreMessages');
	}

	/**
	 * @return CMessageSource компонент приложения, отвечающий за перевод сообщений приложения
	 */
	public function getMessages()
	{
		return $this->getComponent('messages');
	}

	/**
	 * @return CHttpRequest компонент запроса
	 */
	public function getRequest()
	{
		return $this->getComponent('request');
	}

	/**
	 * @return CUrlManager менеджер URL маршрутов
	 */
	public function getUrlManager()
	{
		return $this->getComponent('urlManager');
	}

	/**
	 * Возвращает глобальное значение.
	 *
	 * Глобальное значение - это постоянное для пользовательских сессий и запросов значение.
	 * @param string имя возвращаемого значения
	 * @param mixed значение по умолчанию. Возвращается, если именованное глобальное значение не было найдено.
	 * @return mixed именованное глобальное значение
	 * @see setGlobalState
	 */
	public function getGlobalState($key,$defaultValue=null)
	{
		if($this->_globalState===null)
			$this->loadGlobalState();
		if(isset($this->_globalState[$key]))
			return $this->_globalState[$key];
		else
			return $defaultValue;
	}

	/**
	 * Устанавливает глобальное значение.
	 *
	 * Глобальное значение - это постоянное для пользовательских сессий и запросов значение.
	 * Убедитесь, что значение сериализуемо и десереализуемо.
	 * @param string имя сохраняемого значения
	 * @param mixed соххраняемое значение. Должно быть сериализуемо
	 * @param mixed значение по умолчанию. Если именованое глобальное значение такое же как и данное, оно будет удалено из текущего хранилища
	 * @see getGlobalState
	 */
	public function setGlobalState($key,$value,$defaultValue=null)
	{
		if($this->_globalState===null)
			$this->loadGlobalState();
		$this->_stateChanged=true;
		if($value===$defaultValue)
			unset($this->_globalState[$key]);
		else
			$this->_globalState[$key]=$value;
	}

	/**
	 * Очищает глобальное значение.
	 *
	 * Очищенное значение больше не будет доступно ни в данном запросе ни в последующих.
	 * @param string имя очищаемого значения
	 */
	public function clearGlobalState($key)
	{
		if($this->_globalState===null)
			$this->loadGlobalState();
		if(isset($this->_globalState[$key]))
		{
			$this->_stateChanged=true;
			unset($this->_globalState[$key]);
		}
	}

	/**
	 * Загружает данные глобального значения из постоянного хранилища.
	 * @see getStatePersister
	 * @throws CException вызывается, если менеджер постоянного состояния недоступен
	 */
	protected function loadGlobalState()
	{
		$persister=$this->getStatePersister();
		if(($this->_globalState=$persister->load())===null)
			$this->_globalState=array();
		$this->_stateChanged=false;
		$this->attachEventHandler('onEndRequest',array($this,'saveGlobalState'));
	}

	/**
	 * Сохраняет данные глобального состояния в постоянное хранилище.
	 * @see getStatePersister
	 * @throws CException вызывается, если менеджер постоянного состояния недоступен
	 */
	protected function saveGlobalState()
	{
		if($this->_stateChanged)
		{
			$persister=$this->getStatePersister();
			$this->_stateChanged=false;
			$persister->save($this->_globalState);
		}
	}

	/**
	 * Обрабатывает неперехваченные исключения PHP.
	 *
	 * Метод реализован как обработчик исключений PHP. Он требует, чтобы
	 * константа YII_ENABLE_EXCEPTION_HANDLER была установлена в значение true.
	 *
	 * Сначала метод вызывает событие {@link onException}.
	 * Если исключение не обработано каким-либо другим обработчиком, для его
	 * обработки будет вызван {@link getErrorHandler errorHandler}.
	 *
	 * При вызове данного метода приложение завершается.
	 *
	 * @param Exception неперехваченное исключение
	 */
	public function handleException($exception)
	{
		// disable error capturing to avoid recursive errors
		restore_error_handler();
		restore_exception_handler();

		$category='exception.'.get_class($exception);
		if($exception instanceof CHttpException)
			$category.='.'.$exception->statusCode;
		// php <5.2 doesn't support string conversion auto-magically
		$message=$exception->__toString();
		if(isset($_SERVER['REQUEST_URI']))
			$message.=' REQUEST_URI='.$_SERVER['REQUEST_URI'];
		Yii::log($message,CLogger::LEVEL_ERROR,$category);

		try
		{
			$event=new CExceptionEvent($this,$exception);
			$this->onException($event);
			if(!$event->handled)
			{
				// try an error handler
				if(($handler=$this->getErrorHandler())!==null)
					$handler->handle($event);
				else
					$this->displayException($exception);
			}
		}
		catch(Exception $e)
		{
			$this->displayException($e);
		}
		$this->end(1);
	}

	/**
	 * Обрабатывает ошибки выполнения PHP такие, как предупреждения (warnings), замечания (notices).
	 *
	 * Метод реализован как обработчик ошибок PHP. Он требует, чтобы
	 * константа YII_ENABLE_ERROR_HANDLER была установлена в значение true.
	 *
	 * Сначала метод вызывает событие {@link onError}.
	 * Если ошибка не обработана каким-либо другим обработчиком, для ее
	 * обработки будет вызван {@link getErrorHandler errorHandler}.
	 *
	 * При вызове данного метода приложение завершается.
	 *
	 * @param integer уровень ошибки
	 * @param string сообщение ошибки
	 * @param string файл, в котором произошла ошибка
	 * @param string строка кода, в которой произошла ошибка
	 */
	public function handleError($code,$message,$file,$line)
	{
		if($code & error_reporting())
		{
			// disable error capturing to avoid recursive errors
			restore_error_handler();
			restore_exception_handler();

			$log="$message ($file:$line)\nStack trace:\n";
			$trace=debug_backtrace();
			// skip the first 3 stacks as they do not tell the error position
			if(count($trace)>3)
				$trace=array_slice($trace,3);
			foreach($trace as $i=>$t)
			{
				if(!isset($t['file']))
					$t['file']='unknown';
				if(!isset($t['line']))
					$t['line']=0;
				if(!isset($t['function']))
					$t['function']='unknown';
				$log.="#$i {$t['file']}({$t['line']}): ";
				if(isset($t['object']) && is_object($t['object']))
					$log.=get_class($t['object']).'->';
				$log.="{$t['function']}()\n";
			}
			if(isset($_SERVER['REQUEST_URI']))
				$log.='REQUEST_URI='.$_SERVER['REQUEST_URI'];
			Yii::log($log,CLogger::LEVEL_ERROR,'php');

			try
			{
				$event=new CErrorEvent($this,$code,$message,$file,$line);
				$this->onError($event);
				if(!$event->handled)
				{
					// try an error handler
					if(($handler=$this->getErrorHandler())!==null)
						$handler->handle($event);
					else
						$this->displayError($code,$message,$file,$line);
				}
			}
			catch(Exception $e)
			{
				$this->displayException($e);
			}
			$this->end(1);
		}
	}

	/**
	 * Выполняется при возникновении неперехваченного исключения PHP.
	 *
	 * Обработчик события может установить свойство {@link CErrorEvent::handled handled}
	 * параметра события в значение true для индикации того, что дальнейшая обработка ошибок не
	 * требуется. В ином случае, компонент приложения {@link getErrorHandler errorHandler}
	 * будет продолжать обрабатывать ошибки.
	 *
	 * @param CErrorEvent параметр события
	 */
	public function onException($event)
	{
		$this->raiseEvent('onException',$event);
	}

	/**
	 * Выполняется при возникновении ошибки исполнения скрипта PHP.
	 *
	 * Обработчик события может установить свойство {@link CErrorEvent::handled handled}
	 * параметра события в значение true для индикации того, что дальнейшая обработка ошибок не
	 * требуется. В ином случае, компонент приложения {@link getErrorHandler errorHandler}
	 * будет продолжать обрабатывать ошибки.
	 *
	 * @param CErrorEvent параметр события
	 */
	public function onError($event)
	{
		$this->raiseEvent('onError',$event);
	}

	/**
	 * Отображает перехваченную ошибку PHP.
	 * Метод отображает ошибку в коде HTML, если
	 * для нее нет обработчика.
	 * @param integer код ошибки
	 * @param string сообщение об ошибке
	 * @param string файл, в котором произошла ошибка
	 * @param string строка кода, в которой произошла ошибка
	 */
	public function displayError($code,$message,$file,$line)
	{
		if(YII_DEBUG)
		{
			echo "<h1>PHP Error [$code]</h1>\n";
			echo "<p>$message ($file:$line)</p>\n";
			echo '<pre>';
			debug_print_backtrace();
			echo '</pre>';
		}
		else
		{
			echo "<h1>PHP Error [$code]</h1>\n";
			echo "<p>$message</p>\n";
		}
	}

	/**
	 * Отображает неперехваченные исключения PHP.
	 * Метод отображает исключения в HTML, когда нет активного обработчика ошибок.
	 * @param Exception неперехваченное исключение
	 */
	public function displayException($exception)
	{
		if(YII_DEBUG)
		{
			echo '<h1>'.get_class($exception)."</h1>\n";
			echo '<p>'.$exception->getMessage().' ('.$exception->getFile().':'.$exception->getLine().')</p>';
			echo '<pre>'.$exception->getTraceAsString().'</pre>';
		}
		else
		{
			echo '<h1>'.get_class($exception)."</h1>\n";
			echo '<p>'.$exception->getMessage().'</p>';
		}
	}

	/**
	 * Инициализирует обработчики исключений и ошибок.
	 */
	protected function initSystemHandlers()
	{
		if(YII_ENABLE_EXCEPTION_HANDLER)
			set_exception_handler(array($this,'handleException'));
		if(YII_ENABLE_ERROR_HANDLER)
			set_error_handler(array($this,'handleError'),error_reporting());
	}

	/**
	 * Регистрирует компоненты ядра приложения.
	 * @see setComponents
	 */
	protected function registerCoreComponents()
	{
		$components=array(
			'coreMessages'=>array(
				'class'=>'CPhpMessageSource',
				'language'=>'en_us',
				'basePath'=>YII_PATH.DIRECTORY_SEPARATOR.'messages',
			),
			'db'=>array(
				'class'=>'CDbConnection',
			),
			'messages'=>array(
				'class'=>'CPhpMessageSource',
			),
			'errorHandler'=>array(
				'class'=>'CErrorHandler',
			),
			'securityManager'=>array(
				'class'=>'CSecurityManager',
			),
			'statePersister'=>array(
				'class'=>'CStatePersister',
			),
			'urlManager'=>array(
				'class'=>'CUrlManager',
			),
			'request'=>array(
				'class'=>'CHttpRequest',
			),
		);

		$this->setComponents($components);
	}
}
