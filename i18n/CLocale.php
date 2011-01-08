<?php
/**
 * Файл класса CLocale.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Класс CLocale представляет данные, относящиеся к локали.
 *
 * Данные включают информацию форматирования чисел и дат.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CLocale.php 2798 2011-01-01 19:29:03Z qiang.xue $
 * @package system.i18n
 * @since 1.0
 */
class CLocale extends CComponent
{
	/**
	 * @var string директория, содержащая данные локали. Если свойство не установлено,
	 * данные локали будут загружены из директории 'framework/i18n/data'.
	 * @since 1.1.0
	 */
	public static $dataPath;

	private $_id;
	private $_data;
	private $_dateFormatter;
	private $_numberFormatter;

	/**
	 * Возвращает экземпляр определенной локали.
	 * Т.к. конструктор объектов класса CLocale защищен, для получения экземпляра
	 * определенной локали вы можете использовать только данный метод
	 * @param string $id идентификатор локали (например, en_US)
	 * @return CLocale экземпляр локали
	 */
	public static function getInstance($id)
	{
		static $locales=array();
		if(isset($locales[$id]))
			return $locales[$id];
		else
			return $locales[$id]=new CLocale($id);
	}

	/**
	 * @return array идентификаторы локалей, которые фреймворк может распознать
	 */
	public static function getLocaleIDs()
	{
		static $locales;
		if($locales===null)
		{
			$locales=array();
			$dataPath=self::$dataPath===null ? dirname(__FILE__).DIRECTORY_SEPARATOR.'data' : self::$dataPath;
			$folder=@opendir($dataPath);
			while(($file=@readdir($folder))!==false)
			{
				$fullPath=$dataPath.DIRECTORY_SEPARATOR.$file;
				if(substr($file,-4)==='.php' && is_file($fullPath))
					$locales[]=substr($file,0,-4);
			}
			closedir($folder);
			sort($locales);
		}
		return $locales;
	}

	/**
	 * Конструктор.
	 * Т.к. конструктор защищен, для получения экземпляра определенной локали
	 * используйте метод {@link getInstance}
	 * @param string $id идентификатор локали (например, en_US)
	 */
	protected function __construct($id)
	{
		$this->_id=self::getCanonicalID($id);
		$dataPath=self::$dataPath===null ? dirname(__FILE__).DIRECTORY_SEPARATOR.'data' : self::$dataPath;
		$dataFile=$dataPath.DIRECTORY_SEPARATOR.$this->_id.'.php';
		if(is_file($dataFile))
			$this->_data=require($dataFile);
		else
			throw new CException(Yii::t('yii','Unrecognized locale "{locale}".',array('{locale}'=>$id)));
	}

	/**
	 * Конвертирует идентификатор локали в его каноническую форму.
	 * В канонической форме идентификатор содержит только знак подчеркивания и строчные буквы
	 * @param string $id конвертируемый идентификатор локали
	 * @return string идентификатор локали в канонической форме
	 */
	public static function getCanonicalID($id)
	{
		return strtolower(str_replace('-','_',$id));
	}

	/**
	 * @return string идентификатор локали (в канонической форме)
	 */
	public function getId()
	{
		return $this->_id;
	}

	/**
	 * @return CNumberFormatter форматер чисел для данной локали
	 */
	public function getNumberFormatter()
	{
		if($this->_numberFormatter===null)
			$this->_numberFormatter=new CNumberFormatter($this);
		return $this->_numberFormatter;
	}

	/**
	 * @return CDateFormatter форматер дат для данной локали
	 */
	public function getDateFormatter()
	{
		if($this->_dateFormatter===null)
			$this->_dateFormatter=new CDateFormatter($this);
		return $this->_dateFormatter;
	}

	/**
	 * @param string $currency трёхсимвольный код валюты в ISO 4217. Например, код "USD" представляет доллар США, а "EUR" - евро
	 * @return string локализованный символ валюты. Если символа нет, возвращается значение null
	 */
	public function getCurrencySymbol($currency)
	{
		return isset($this->_data['currencySymbols'][$currency]) ? $this->_data['currencySymbols'][$currency] : null;
	}

	/**
	 * Возвращает числовой символ по имени
	 * @param string $name имя символа
	 * @return string символ
	 */
	public function getNumberSymbol($name)
	{
		return isset($this->_data['numberSymbols'][$name]) ? $this->_data['numberSymbols'][$name] : null;
	}

	/**
	 * @return string десятичный формат
	 */
	public function getDecimalFormat()
	{
		return $this->_data['decimalFormat'];
	}

	/**
	 * @return string денежный формат
	 */
	public function getCurrencyFormat()
	{
		return $this->_data['currencyFormat'];
	}

	/**
	 * @return string формат процентов
	 */
	public function getPercentFormat()
	{
		return $this->_data['percentFormat'];
	}

	/**
	 * @return string научный формат
	 */
	public function getScientificFormat()
	{
		return $this->_data['scientificFormat'];
	}

	/**
	 * @param integer $month номер месяца (1-12)
	 * @param string $width вид названия месяца. Может принимать значения 'wide', 'abbreviated' или 'narrow'
	 * @param boolean $standAlone возвращать ли название месяца в формате stand-alone
	 * @return string название месяца
	 */
	public function getMonthName($month,$width='wide',$standAlone=false)
	{
		if($standAlone)
			return isset($this->_data['monthNamesSA'][$width][$month]) ? $this->_data['monthNamesSA'][$width][$month] : $this->_data['monthNames'][$width][$month];
		else
			return isset($this->_data['monthNames'][$width][$month]) ? $this->_data['monthNames'][$width][$month] : $this->_data['monthNamesSA'][$width][$month];
	}

	/**
	 * Возвращает названия месяцев в определенном формате
	 * @param string $width вид названий месяцев. Может принимать значения 'wide', 'abbreviated' или 'narrow'
	 * @param boolean $standAlone возвращать ли названия месяцев в формате stand-alone
	 * @return array названия месяцев, индексированные по номеру месяца (1-12)
	 * @since 1.0.9
	 */
	public function getMonthNames($width='wide',$standAlone=false)
	{
		if($standAlone)
			return isset($this->_data['monthNamesSA'][$width]) ? $this->_data['monthNamesSA'][$width] : $this->_data['monthNames'][$width];
		else
			return isset($this->_data['monthNames'][$width]) ? $this->_data['monthNames'][$width] : $this->_data['monthNamesSA'][$width];
	}

	/**
	 * @param integer $day номер дня недели (0-6, 0 - воскресенье)
	 * @param string $width вид названия дня недели. Модет принимать значения 'wide', 'abbreviated' или 'narrow'
	 * @param boolean $standAlone возвращать ли название дня недели в формате stand-alone
	 * @return string название дня недели
	 */
	public function getWeekDayName($day,$width='wide',$standAlone=false)
	{
		if($standAlone)
			return isset($this->_data['weekDayNamesSA'][$width][$day]) ? $this->_data['weekDayNamesSA'][$width][$day] : $this->_data['weekDayNames'][$width][$day];
		else
			return isset($this->_data['weekDayNames'][$width][$day]) ? $this->_data['weekDayNames'][$width][$day] : $this->_data['weekDayNamesSA'][$width][$day];
	}

	/**
	 * Возвращает названия дней недели в определенном формате
	 * @param string $width вид названий дней недели. Может принимать значения 'wide', 'abbreviated' или 'narrow'
	 * @param boolean $standAlone возвращать ли названия дней недели в формате stand-alone
	 * @return array названия дней недели, индексированные по номеру дня недели (0-6, 0 - воскресенье, 1 - понедельник и т.д.)
	 * @since 1.0.9
	 */
	public function getWeekDayNames($width='wide',$standAlone=false)
	{
		if($standAlone)
			return isset($this->_data['weekDayNamesSA'][$width]) ? $this->_data['weekDayNamesSA'][$width] : $this->_data['weekDayNames'][$width];
		else
			return isset($this->_data['weekDayNames'][$width]) ? $this->_data['weekDayNames'][$width] : $this->_data['weekDayNamesSA'][$width];
	}

	/**
	 * @param integer $era номер эры (0,1)
	 * @param string $width вид названия эры. Может принимать значения 'wide', 'abbreviated' или 'narrow'
	 * @return string название эры
	 */
	public function getEraName($era,$width='wide')
	{
		return $this->_data['eraNames'][$width][$era];
	}

	/**
	 * @return string наименование AM
	 */
	public function getAMName()
	{
		return $this->_data['amName'];
	}

	/**
	 * @return string наименование PM
	 */
	public function getPMName()
	{
		return $this->_data['pmName'];
	}

	/**
	 * @param string $width вид формата даты. Может принимать значения 'full', 'long', 'medium' или 'short'
	 * @return string формат даты
	 */
	public function getDateFormat($width='medium')
	{
		return $this->_data['dateFormats'][$width];
	}

	/**
	 * @param string $width вид формата времени. Может принимать значения 'full', 'long', 'medium' или 'short'
	 * @return string формат времени
	 */
	public function getTimeFormat($width='medium')
	{
		return $this->_data['timeFormats'][$width];
	}

	/**
	 * @return string формат даты и времени - порядок, в котором идут дата и время
	 */
	public function getDateTimeFormat()
	{
		return $this->_data['dateTimeFormat'];
	}

	/**
	 * @return string направление текста, может быть либо 'ltr' (слева направо) либо 'rtl' (справа налево)
	 * @since 1.1.2
	 */
	public function getOrientation()
	{
		return isset($this->_data['orientation']) ? $this->_data['orientation'] : 'ltr';
	}
}