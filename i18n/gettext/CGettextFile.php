<?php
/**
 * Файл класса CGettextFile.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CGettextFile - это базовый класс, представляющий файлы сообщений Gettext.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CGettextFile.php 1678 2010-01-07 21:02:00Z qiang.xue $
 * @package system.i18n.gettext
 * @since 1.0
 */
abstract class CGettextFile extends CComponent
{
	/**
	 * Загружает сообщение из файла.
	 * @param string путь к файлу
	 * @param string контектс сообщения
	 * @return array перевод сообщения (исходное сообщение => переведенное сообщение)
	 */
	abstract public function load($file,$context);
	/**
	 * Сохраняет сообщения в файл.
	 * @param string путь к файлу
	 * @param array перевод сообщений (идентификатор сообщения => переведенное сообщение).
	 * Примечание: если сообщение имеет контекст, то идентификатор сообщения должен быть с префиксом
	 * в виде контекста и символом-разделителем - chr(4)
	 */
	abstract public function save($file,$messages);
}
