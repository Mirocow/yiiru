<?php
/**
 * Файл класса CErrorEvent.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Класс CErrorEvent представляет параметр для события {@link CApplication::onError onError}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CErrorEvent.php 1678 2010-01-07 21:02:00Z qiang.xue $
 * @package system.base
 * @since 1.0
 */
class CErrorEvent extends CEvent
{
	/**
	 * @var string код ошибки
	 */
	public $code;
	/**
	 * @var string сообщение об ошибке
	 */
	public $message;
	/**
	 * @var string файл, в котором произошла ошибка
	 */
	public $file;
	/**
	 * @var string строка, в которой произошла ошибка
	 */
	public $line;

	/**
	 * Конструктор.
	 * @param mixed отправитель события
	 * @param string код ошибки
	 * @param string сообщение об ошибке
	 * @param string файл, в котором произошла ошибка
	 * @param integer строка, в которой произошла ошибка
	 */
	public function __construct($sender,$code,$message,$file,$line)
	{
		$this->code=$code;
		$this->message=$message;
		$this->file=$file;
		$this->line=$line;
		parent::__construct($sender);
	}
}
