<?php
/**
 * Файл класса CEmailLogRoute.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Компонент CEmailLogRoute отправляет выбранные сообщения журнала на email-адреса.
 *
 * Целевые email-адреса могут быть установлены свойством {@link setEmails emails}.
 * Опционально, вы можете установить свойство {@link setSubject subject} (тема письма) и
 * свойство {@link setSentFrom sentFrom} (адрес отправителя).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CEmailLogRoute.php 434 2008-12-30 23:14:31Z qiang.xue $
 * @package system.logging
 * @since 1.0
 */
class CEmailLogRoute extends CLogRoute
{
	/**
	 * Тема письма по умолчанию.
	 */
	const DEFAULT_SUBJECT='Application Log';
	/**
	 * @var array список адресов назначения
	 */
	private $_email=array();
	/**
	 * @var string тема письма
	 */
	private $_subject='';
	/**
	 * @var string адрес отправителя
	 */
	private $_from='';

	/**
	 * Отправляет сообщения журнала по определенным адресам.
	 * @param array список сообщений журнала
	 */
	protected function processLogs($logs)
	{
		$message='';
		foreach($logs as $log)
			$message.=$this->formatLogMessage($log[0],$log[1],$log[2],$log[3]);
		$message=wordwrap($message,70);
		foreach($this->getEmails() as $email)
			$this->sendEmail($email,$this->getSubject(),$message);
	}

	/**
	 * Отправляет письмо.
	 * @param string отдельный email-адрес
	 * @param string тема письма
	 * @param string содержимое письма
	 */
	protected function sendEmail($email,$subject,$message)
	{
		if(($from=$this->getSentFrom())!=='')
			mail($email,$subject,$message,"From:{$from}\r\n");
		else
			mail($email,$subject,$message);
	}

	/**
	 * @return array список установленных адресов назначения
	 */
	public function getEmails()
	{
		return $this->_email;
	}

	/**
	 * @return array|string устанавливаемый список адресов назначения. Если передается строка, предполагается,
	 * что адреса разделены запятой.
	 */
	public function setEmails($value)
	{
		if(is_array($value))
			$this->_email=$value;
		else
			$this->_email=preg_split('/[\s,]+/',$value,-1,PREG_SPLIT_NO_EMPTY);
	}

	/**
	 * @return string установленная тема письма. По умолчанию равно значению CEmailLogRoute::DEFAULT_SUBJECT
	 */
	public function getSubject()
	{
		return $this->_subject;
	}

	/**
	 * @param string устанавливаемая тема письма.
	 */
	public function setSubject($value)
	{
		$this->_subject=$value;
	}

	/**
	 * @return string установленный адрес отправителя
	 */
	public function getSentFrom()
	{
		return $this->_from;
	}

	/**
	 * @param string устанавливаемый адрес отправителя
	 */
	public function setSentFrom($value)
	{
		$this->_from=$value;
	}
}

