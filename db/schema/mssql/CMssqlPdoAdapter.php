<?php
/**
 * Файл класса CMssqlPdo
 *
 * @author Christophe Boulain <Christophe.Boulain@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Данный класс является расширением стандартного класса PDO для драйвера mssql.
 * Он предоставляет некоторый функционал pdo драйвера, отсутствующий в драйвере mssql
 * @author Christophe Boulain <Christophe.Boulain@gmail.com>
 * @version $Id: CMssqlPdoAdapter.php 3204 2011-05-05 21:36:32Z alexander.makarow $
 * @package system.db.schema.mssql
 * @since 1.0.4
 */
class CMssqlPdoAdapter extends PDO
{
	/**
	 * Получает идентификатор последней вставленной строки.
	 * MSSQL не поддерживает последовательности, поэтому аргумент игнорируется
	 *
	 * @param string|null имя последовательности. По умолчанию - null
	 * @return integer идентификатор последней вставленной строки
	 */
	public function lastInsertId ($sequence=NULL)
	{
		return $this->query('SELECT SCOPE_IDENTITY()')->fetchColumn();
	}

	/**
	 * Открывает транзакцию
	 *
	 * Необходимо переопределить метод драйвера pdo, т.к.
	 * драйвер mssql не поддерживает транзакции
	 *
	 * @return boolean
	 */
	public function beginTransaction ()
	{
		$this->exec('BEGIN TRANSACTION');
		return true;
	}

	/**
	 * Подтверждает транзакцию
	 *
	 * Необходимо переопределить метод драйвера pdo, т.к.
	 * драйвер mssql не поддерживает транзакции
	 *
	 * @return boolean
	 */
	public function commit ()
	{
		$this->exec('COMMIT TRANSACTION');
		return true;
	}

	/**
	 * Отменяет транзакцию
	 *
	 * Необходимо переопределить метод драйвера pdo, т.к.
	 * драйвер mssql не поддерживает транзакции
	 *
	 * @return boolean
	 */
	public function rollBack ()
	{
		$this->exec('ROLLBACK TRANSACTION');
		return true;
	}
}
