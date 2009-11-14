<?php
/**
 * Файл класса CExceptionEvent.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Класс CExceptionEvent представляет параметр для события {@link CApplication::onException onException}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CExceptionEvent.php 878 2009-03-23 15:31:21Z qiang.xue $
 * @package system.base
 * @since 1.0
 */
class CExceptionEvent extends CEvent
{
	/**
	 * @var CException исключение для данного события.
	 */
	public $exception;

	/**
	 * Конструктор.
	 * @param mixed отправитель события
	 * @param CException исключение
	 */
	public function __construct($sender,$exception)
	{
		$this->exception=$exception;
		parent::__construct($sender);
	}
}