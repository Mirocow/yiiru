<?php
/**
 * ���� ������ CDbLogRoute.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */


/**
 * ��������� CDbLogRoute ��������� ��������� ������� � ������� ���� ������.
 *
 * ��� ����������� ������� �� ��� �������� ��������� �������, ���������� � �������� {@link logTableName}
 * ��� ������� � � �������� {@link connectionID} ������������� ���������� ���������� {@link CDbConnection}.
 * ���� ��� �� �����������, ����� ������� ���� ������ SQLite3 'log-YiiVersion.db'
 * � ���������� ������� ���������� ����������.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CDbLogRoute.php 1180 2009-06-26 21:16:33Z qiang.xue $
 * @package system.logging
 * @since 1.0
 */
class CDbLogRoute extends CLogRoute
{
	/**
	 * @var string ������������� ���������� ���������� CDbConnection. ���� �� ����������, ����� �������������
	 * ������� � ����� �������������� ���� SQLite. ���� ���� ������ SQLite -
	 * <code>protected/runtime/log-YiiVersion.db</code>.
	 */
	public $connectionID;
	/**
	 * @var string ��� ������� ��, � ������� �������� ��������� �������. �� ��������� - 'YiiLog'.
	 * ���� �������� {@link autoCreateLogTable} ����������� � �������� false � �� ������ ������� �������
	 * �������, �� ������ ���� �������, ��� ������� ����� ��������� ���������:
	 * <pre>
	 *  (
	 *		id       INTEGER NOT NULL PRIMARY KEY,
	 *		level    VARCHAR(128),
	 *		category VARCHAR(128),
	 *		logtime  INTEGER,
	 *		message  TEXT
	 *   )
	 * </pre>
	 * �������, ��� ������� 'id' ������ ���� ������ ��� ����������������.
	 * � MySQL ������ ���� <code>id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY</code>;
	 * � PostgreSQL - <code>id SERIAL PRIMARY KEY</code>.
	 * @see autoCreateLogTable
	 */
	public $logTableName='YiiLog';
	/**
	 * @var boolean ������ �� ������� �� ��� �������� ��������� ������� ����������� �������������. �� ��������� - true.
	 * @see logTableName
	 */
	public $autoCreateLogTable=true;
	/**
	 * @var CDbConnection ��������� ���������� ��
	 */
	private $_db;

	/**
	 * ����������.
	 * ����������� �� ��.
	 */
	public function __destruct()
	{
		if($this->_db!==null)
			$this->_db->setActive(false);
	}

	/**
	 * �������������� �������.
	 * ����� ���������� ����� �������� �������� ���������� ���������.
	 */
	public function init()
	{
		parent::init();

		$db=$this->getDbConnection();
		$db->setActive(true);

		if($this->autoCreateLogTable)
		{
			$sql="DELETE FROM {$this->logTableName} WHERE 0=1";
			try
			{
				$db->createCommand($sql)->execute();
			}
			catch(Exception $e)
			{
				$this->createLogTable($db,$this->logTableName);
			}
		}
	}

	/**
	 * ������� � �� ������� ��� �������� ��������� �������.
	 * @param CDbConnection ���������� ��
	 * @param string ��� ����������� �������
	 */
	protected function createLogTable($db,$tableName)
	{
		$driver=$db->getDriverName();
		if($driver==='mysql')
			$logID='id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY';
		else if($driver==='pgsql')
			$logID='id SERIAL PRIMARY KEY';
		else
			$logID='id INTEGER NOT NULL PRIMARY KEY';

		$sql="
CREATE TABLE $tableName
(
	$logID,
	level VARCHAR(128),
	category VARCHAR(128),
	logtime INTEGER,
	message TEXT
)";
		$db->createCommand($sql)->execute();
	}

	/**
	 * @return CDbConnection ��������� ���������� ��
	 * @throws CException ����������, ���� {@link connectionID} �� ��������� �� ���������� ��������� ����������.
	 */
	protected function getDbConnection()
	{
		if($this->_db!==null)
			return $this->_db;
		else if(($id=$this->connectionID)!==null)
		{
			if(($this->_db=Yii::app()->getComponent($id)) instanceof CDbConnection)
				return $this->_db;
			else
				throw new CException(Yii::t('yii','CDbLogRoute.connectionID "{id}" does not point to a valid CDbConnection application component.',
					array('{id}'=>$id)));
		}
		else
		{
			$dbFile=Yii::app()->getRuntimePath().DIRECTORY_SEPARATOR.'log-'.Yii::getVersion().'.db';
			return $this->_db=new CDbConnection('sqlite:'.$dbFile);
		}
	}

	/**
	 * ��������� ��������� ������� � ��.
	 * @param array ������ ��������� �������
	 */
	protected function processLogs($logs)
	{
		$sql="
INSERT INTO {$this->logTableName}
(level, category, logtime, message) VALUES
(:level, :category, :logtime, :message)
";
		$command=$this->getDbConnection()->createCommand($sql);
		foreach($logs as $log)
		{
			$command->bindValue(':level',$log[1]);
			$command->bindValue(':category',$log[2]);
			$command->bindValue(':logtime',(int)$log[3]);
			$command->bindValue(':message',$log[0]);
			$command->execute();
		}
	}
}
