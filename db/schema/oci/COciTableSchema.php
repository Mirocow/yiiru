<?php
/**
 * Файл класса COciTableSchema.
 *
 * @author Ricardo Grana <rickgrana@yahoo.com.br>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Класс COciTableSchema представляет метаданные таблицы базы данных Oracle.
 *
 * @author Ricardo Grana <rickgrana@yahoo.com.br>
 * @version $Id: COciTableSchema.php 2799 2011-01-01 19:31:13Z qiang.xue $
 * @package system.db.schema.oci
 * @since 1.0.5
 */
class COciTableSchema extends CDbTableSchema
{
	/**
	 * @var string имя схемы (базы данных), к которой относится данная таблица.
	 * По умолчанию - null, т.е., схемы нет (текущая база данных)
	 */
	public $schemaName;
}
