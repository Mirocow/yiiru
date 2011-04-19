<?php
/**
 * Файл класса CDbCommandBuilder.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Класс CDbCommandBuilder предоставляет базовые методы для создания команд запросов для таблиц.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CDbCommandBuilder.php 3001 2011-02-24 16:42:44Z alexander.makarow $
 * @package system.db.schema
 * @since 1.0
 */
class CDbCommandBuilder extends CComponent
{
	const PARAM_PREFIX=':yp';

	private $_schema;
	private $_connection;

	/**
	 * @param CDbSchema $schema схема БД для данного построителя команд
	 */
	public function __construct($schema)
	{
		$this->_schema=$schema;
		$this->_connection=$schema->getDbConnection();
	}

	/**
	 * @return CDbConnection соединение БД
	 */
	public function getDbConnection()
	{
		return $this->_connection;
	}

	/**
	 * @return CDbSchema схема БД для данного построителя команд
	 */
	public function getSchema()
	{
		return $this->_schema;
	}

	/**
	 * Возвращает идентификатор (ID) последней вставленной строки переданной таблицы
	 * @param mixed $table схема таблицы ({@link CDbTableSchema}) или имя таблицы (строка)
	 * @return mixed идентификатор (ID) последней вставленной строки. Если нет имени
	 * последовательности (sequence), возвращается значение null
	 */
	public function getLastInsertID($table)
	{
		$this->ensureTable($table);
		if($table->sequenceName!==null)
			return $this->_connection->getLastInsertID($table->sequenceName);
		else
			return null;
	}

	/**
	 * Создает команду SELECT для отдельной таблицы
	 * @param mixed $table схема таблицы ({@link CDbTableSchema}) или имя таблицы (строка)
	 * @param CDbCriteria $criteria критерий запроса
	 * @param string $alias псевдоним первичной таблицы. По умолчанию - 't'
	 * @return CDbCommand команда запроса
	 */
	public function createFindCommand($table,$criteria,$alias='t')
	{
		$this->ensureTable($table);
		$select=is_array($criteria->select) ? implode(', ',$criteria->select) : $criteria->select;
		if($criteria->alias!='')
			$alias=$criteria->alias;
		$alias=$this->_schema->quoteTableName($alias);

		// issue 1432: need to expand * when SQL has JOIN
		if($select==='*' && !empty($criteria->join))
		{
			$prefix=$alias.'.';
			$select=array();
			foreach($table->getColumnNames() as $name)
				$select[]=$prefix.$this->_schema->quoteColumnName($name);
			$select=implode(', ',$select);
		}

		$sql=($criteria->distinct ? 'SELECT DISTINCT':'SELECT')." {$select} FROM {$table->rawName} $alias";
		$sql=$this->applyJoin($sql,$criteria->join);
		$sql=$this->applyCondition($sql,$criteria->condition);
		$sql=$this->applyGroup($sql,$criteria->group);
		$sql=$this->applyHaving($sql,$criteria->having);
		$sql=$this->applyOrder($sql,$criteria->order);
		$sql=$this->applyLimit($sql,$criteria->limit,$criteria->offset);
		$command=$this->_connection->createCommand($sql);
		$this->bindValues($command,$criteria->params);
		return $command;
	}

	/**
	 * Создает команду COUNT(*) для отдельной таблицы
	 * @param mixed $table схема таблицы ({@link CDbTableSchema}) или имя таблицы (строка)
	 * @param CDbCriteria $criteria критерий запроса
	 * @param string $alias псевдоним первичной таблицы. По умолчанию - 't'
	 * @return CDbCommand команда запроса
	 */
	public function createCountCommand($table,$criteria,$alias='t')
	{
		$this->ensureTable($table);
		if($criteria->alias!='')
			$alias=$criteria->alias;
		$alias=$this->_schema->quoteTableName($alias);

		if(!empty($criteria->group) || !empty($criteria->having))
		{
			$select=is_array($criteria->select) ? implode(', ',$criteria->select) : $criteria->select;
			if($criteria->alias!='')
				$alias=$criteria->alias;
			$sql=($criteria->distinct ? 'SELECT DISTINCT':'SELECT')." {$select} FROM {$table->rawName} $alias";
			$sql=$this->applyJoin($sql,$criteria->join);
			$sql=$this->applyCondition($sql,$criteria->condition);
			$sql=$this->applyGroup($sql,$criteria->group);
			$sql=$this->applyHaving($sql,$criteria->having);
			$sql="SELECT COUNT(*) FROM ($sql) sq";
		}
		else
		{
			if(is_string($criteria->select) && stripos($criteria->select,'count')===0)
				$sql="SELECT ".$criteria->select;
			else if($criteria->distinct)
			{
				if(is_array($table->primaryKey))
				{
					$pk=array();
					foreach($table->primaryKey as $key)
						$pk[]=$alias.'.'.$key;
					$pk=implode(', ',$pk);
				}
				else
					$pk=$alias.'.'.$table->primaryKey;
				$sql="SELECT COUNT(DISTINCT $pk)";
			}
			else
				$sql="SELECT COUNT(*)";
			$sql.=" FROM {$table->rawName} $alias";
			$sql=$this->applyJoin($sql,$criteria->join);
			$sql=$this->applyCondition($sql,$criteria->condition);
		}

		$command=$this->_connection->createCommand($sql);
		$this->bindValues($command,$criteria->params);
		return $command;
	}

	/**
	 * Создает команду DELETE для отдельной таблицы
	 * @param mixed $table схема таблицы ({@link CDbTableSchema}) или имя таблицы (строка)
	 * @param CDbCriteria $criteria критерий запроса
	 * @return CDbCommand команда удаления
	 */
	public function createDeleteCommand($table,$criteria)
	{
		$this->ensureTable($table);
		$sql="DELETE FROM {$table->rawName}";
		$sql=$this->applyJoin($sql,$criteria->join);
		$sql=$this->applyCondition($sql,$criteria->condition);
		$sql=$this->applyGroup($sql,$criteria->group);
		$sql=$this->applyHaving($sql,$criteria->having);
		$sql=$this->applyOrder($sql,$criteria->order);
		$sql=$this->applyLimit($sql,$criteria->limit,$criteria->offset);
		$command=$this->_connection->createCommand($sql);
		$this->bindValues($command,$criteria->params);
		return $command;
	}

	/**
	 * Создает команду INSERT для отдельной таблицы
	 * @param mixed $table схема таблицы ({@link CDbTableSchema}) или имя таблицы (строка)
	 * @param array $data вставляемые данные (имя столбца => значение столбца). Если ключ не является верным
	 * именем столбца, соответствующее значение игнорируется
	 * @return CDbCommand команда вставки
	 */
	public function createInsertCommand($table,$data)
	{
		$this->ensureTable($table);
		$fields=array();
		$values=array();
		$placeholders=array();
		$i=0;
		foreach($data as $name=>$value)
		{
			if(($column=$table->getColumn($name))!==null && ($value!==null || $column->allowNull))
			{
				$fields[]=$column->rawName;
				if($value instanceof CDbExpression)
				{
					$placeholders[]=$value->expression;
					foreach($value->params as $n=>$v)
						$values[$n]=$v;
				}
				else
				{
					$placeholders[]=self::PARAM_PREFIX.$i;
					$values[self::PARAM_PREFIX.$i]=$column->typecast($value);
					$i++;
				}
			}
		}
		if($fields===array())
		{
			$pks=is_array($table->primaryKey) ? $table->primaryKey : array($table->primaryKey);
			foreach($pks as $pk)
			{
				$fields[]=$table->getColumn($pk)->rawName;
				$placeholders[]='NULL';
			}
		}
		$sql="INSERT INTO {$table->rawName} (".implode(', ',$fields).') VALUES ('.implode(', ',$placeholders).')';
		$command=$this->_connection->createCommand($sql);

		foreach($values as $name=>$value)
			$command->bindValue($name,$value);

		return $command;
	}

	/**
	 * Создает команду UPDATE для отдельной таблицы
	 * @param mixed $table схема таблицы ({@link CDbTableSchema}) или имя таблицы (строка)
	 * @param array $data список обновляемых столбцов (имя => значение)
	 * @param CDbCriteria $criteria критерий запроса
	 * @return CDbCommand команда обновления
	 */
	public function createUpdateCommand($table,$data,$criteria)
	{
		$this->ensureTable($table);
		$fields=array();
		$values=array();
		$bindByPosition=isset($criteria->params[0]);
		$i=0;
		foreach($data as $name=>$value)
		{
			if(($column=$table->getColumn($name))!==null)
			{
				if($value instanceof CDbExpression)
				{
					$fields[]=$column->rawName.'='.$value->expression;
					foreach($value->params as $n=>$v)
						$values[$n]=$v;
				}
				else if($bindByPosition)
				{
					$fields[]=$column->rawName.'=?';
					$values[]=$column->typecast($value);
				}
				else
				{
					$fields[]=$column->rawName.'='.self::PARAM_PREFIX.$i;
					$values[self::PARAM_PREFIX.$i]=$column->typecast($value);
					$i++;
				}
			}
		}
		if($fields===array())
			throw new CDbException(Yii::t('yii','No columns are being updated for table "{table}".',
				array('{table}'=>$table->name)));
		$sql="UPDATE {$table->rawName} SET ".implode(', ',$fields);
		$sql=$this->applyJoin($sql,$criteria->join);
		$sql=$this->applyCondition($sql,$criteria->condition);
		$sql=$this->applyOrder($sql,$criteria->order);
		$sql=$this->applyLimit($sql,$criteria->limit,$criteria->offset);

		$command=$this->_connection->createCommand($sql);
		$this->bindValues($command,array_merge($values,$criteria->params));

		return $command;
	}

	/**
	 * Создает команду UPDATE, инкрементирующую/декрементирующую некоторые столбцы.
	 * @param mixed $table схема таблицы ({@link CDbTableSchema}) или имя таблицы (строка)
	 * @param array $counters обновляемые счетчики (индексированные по именам столбцов счетчики инкремента/декремента)
	 * @param CDbCriteria $criteria критерий запроса
	 * @return CDbCommand команда инкремента/декремента
	 * @throws CException вызывается, если счетчик не определен
	 */
	public function createUpdateCounterCommand($table,$counters,$criteria)
	{
		$this->ensureTable($table);
		$fields=array();
		foreach($counters as $name=>$value)
		{
			if(($column=$table->getColumn($name))!==null)
			{
				$value=(int)$value;
				if($value<0)
					$fields[]="{$column->rawName}={$column->rawName}-".(-$value);
				else
					$fields[]="{$column->rawName}={$column->rawName}+".$value;
			}
		}
		if($fields!==array())
		{
			$sql="UPDATE {$table->rawName} SET ".implode(', ',$fields);
			$sql=$this->applyJoin($sql,$criteria->join);
			$sql=$this->applyCondition($sql,$criteria->condition);
			$sql=$this->applyOrder($sql,$criteria->order);
			$sql=$this->applyLimit($sql,$criteria->limit,$criteria->offset);
			$command=$this->_connection->createCommand($sql);
			$this->bindValues($command,$criteria->params);
			return $command;
		}
		else
			throw new CDbException(Yii::t('yii','No counter columns are being updated for table "{table}".',
				array('{table}'=>$table->name)));
	}

	/**
	 * Создает команду на основе переданного SQL-выражения
	 * @param string $sql явно определенное SQL-выражение
	 * @param array $params параметры, связываемые с SQL-выражением
	 * @return CDbCommand созданная команда
	 */
	public function createSqlCommand($sql,$params=array())
	{
		$command=$this->_connection->createCommand($sql);
		$this->bindValues($command,$params);
		return $command;
	}

	/**
	 * Изменяет SQL-выражение, добавляя оператор JOIN
	 * @param string $sql изменяемое SQL-выражение
	 * @param string $join оператор JOIN (начинается с типа объединения, например, INNER JOIN)
	 * @return string измененнное SQL-выражение
	 */
	public function applyJoin($sql,$join)
	{
		if($join!='')
			return $sql.' '.$join;
		else
			return $sql;
	}

	/**
	 * Изменяет SQL-выражение, добавляя оператор WHERE
	 * @param string $sql SQL-выражение без оператора WHERE
	 * @param string $condition оператор WHERE (без ключевого слова WHERE)
	 * @return string измененнное SQL-выражение
	 */
	public function applyCondition($sql,$condition)
	{
		if($condition!='')
			return $sql.' WHERE '.$condition;
		else
			return $sql;
	}

	/**
	 * Изменяет SQL-выражение, добавляя оператор ORDER BY
	 * @param string $sql SQL-выражение без оператора ORDER BY
	 * @param string $orderBy столбцы сортировки
	 * @return string измененное SQL-выражение
	 */
	public function applyOrder($sql,$orderBy)
	{
		if($orderBy!='')
			return $sql.' ORDER BY '.$orderBy;
		else
			return $sql;
	}

	/**
	 * Изменяет SQL-выражение, добавляя операторы LIMIT и OFFSET (при необходимости).
	 * Реализация по умолчанию применима к СУБД PostgreSQL, MySQL и SQLite
	 * @param string $sql SQL-выражение без операторов LIMIT и OFFSET
	 * @param integer $limit максимальное количество строк, -1 для игнорирования ограничения
	 * @param integer $offset смещение строк, -1 для игнорирования смещения (т.е., брать строки с первой)
	 * @return string измененное SQL-выражение
	 */
	public function applyLimit($sql,$limit,$offset)
	{
		if($limit>=0)
			$sql.=' LIMIT '.(int)$limit;
		if($offset>0)
			$sql.=' OFFSET '.(int)$offset;
		return $sql;
	}

	/**
	 * Изменяет SQL-выражение, добавляя оператор GROUP BY
	 * @param string $sql SQL-выражение без оператора GROUP BY
	 * @param string $group параметр для оператора GROUP BY 
	 * @return string измененное SQL-выражение
	 */
	public function applyGroup($sql,$group)
	{
		if($group!='')
			return $sql.' GROUP BY '.$group;
		else
			return $sql;
	}

	/**
	 * Изменяет SQL-выражение, добавляя оператор HAVING
	 * @param string $sql SQL-выражение без оператора HAVING
	 * @param string $having параметр для оператора HAVING
	 * @return string измененное SQL-выражение
	 * @since 1.0.1
	 */
	public function applyHaving($sql,$having)
	{
		if($having!='')
			return $sql.' HAVING '.$having;
		else
			return $sql;
	}

	/**
	 * Связывает значения параметров с SQL-выражением
	 * @param CDbCommand $command команда БД
	 * @param array $values связываемые значения (целочисленно-индексированный массив для
	 * последовательного связывания меток, строкоиндексированный массив - для именованных меток)
	 */
	public function bindValues($command, $values)
	{
		if(($n=count($values))===0)
			return;
		if(isset($values[0])) // question mark placeholders
		{
			for($i=0;$i<$n;++$i)
				$command->bindValue($i+1,$values[$i]);
		}
		else // named placeholders
		{
			foreach($values as $name=>$value)
			{
				if($name[0]!==':')
					$name=':'.$name;
				$command->bindValue($name,$value);
			}
		}
	}

	/**
	 * Создает критерий запроса
	 * @param mixed $condition условие запроса или критерий.
	 * Если является строкой, то считатеся условием запроса (выражением оператора WHERE);
	 * если является массивом, то считается инициализирующими данными для конструирования объекта {@link CDbCriteria критерия};
	 * иначе параметр должен быть экземпляром класса {@link CDbCriteria}
	 * @param array $params параметры, передаваемые в SQL-выражение.
	 * Используется только если первый параметр является строкой (условием запроса).
	 * В других случаях для задания параметров используется свойство {@link CDbCriteria::params}
	 * @return CDbCriteria созданный критерий запроса
	 * @throws CException вызывается, если условие не является строкой, массивом или экземпляром класса CDbCriteria
	 */
	public function createCriteria($condition='',$params=array())
	{
		if(is_array($condition))
			$criteria=new CDbCriteria($condition);
		else if($condition instanceof CDbCriteria)
			$criteria=clone $condition;
		else
		{
			$criteria=new CDbCriteria;
			$criteria->condition=$condition;
			$criteria->params=$params;
		}
		return $criteria;
	}

	/**
	 * Создает критерий запроса с определенным первичным ключом
	 * @param mixed $table схема таблицы (объект класса {@link CDbTableSchema}) или имя таблицы (строка)
	 * @param mixed $pk значение(я) первичного ключа. Для задания нескольких первичных ключей используется массив.
	 * Для составного ключа каждое значение ключа должно быть массивом (имя столбца => значение столбца)
	 * @param mixed $condition условие запроса или критерий.
	 * Строка считается условием запроса, массив - начальными значениями для создания
	 * объекта {@link CDbCriteria критерия}, другое значение должно быть экземпляром
	 * класса {@link CDbCriteria}
	 * @param array $params параметры, связываемые с SQL-выражением. Используется только
	 * если второй параметр является строкой (условие запроса). Иначе надо использовать
	 * свойство {@link CDbCriteria::params} для установки необходимых параметров
	 * @param string $prefix префикс столбца (с точкой на конце). Если значение равно null, то префикс будет именем таблицы
	 * @return CDbCriteria созданный критерий запроса
	 */
	public function createPkCriteria($table,$pk,$condition='',$params=array(),$prefix=null)
	{
		$this->ensureTable($table);
		$criteria=$this->createCriteria($condition,$params);
		if($criteria->alias!='')
			$prefix=$this->_schema->quoteTableName($criteria->alias).'.';
		if(!is_array($pk)) // single key
			$pk=array($pk);
		if(is_array($table->primaryKey) && !isset($pk[0]) && $pk!==array()) // single composite key
			$pk=array($pk);
		$condition=$this->createInCondition($table,$table->primaryKey,$pk,$prefix);
		if($criteria->condition!='')
			$criteria->condition=$condition.' AND ('.$criteria->condition.')';
		else
			$criteria->condition=$condition;

		return $criteria;
	}

	/**
	 * Generates the expression for selecting rows of specified primary key values.
	 * @param mixed $table the table schema ({@link CDbTableSchema}) or the table name (string).
	 * @param array $values list of primary key values to be selected within
	 * @param string $prefix column prefix (ended with dot). If null, it will be the table name
	 * @return string the expression for selection
	 */
	public function createPkCondition($table,$values,$prefix=null)
	{
		$this->ensureTable($table);
		return $this->createInCondition($table,$table->primaryKey,$values,$prefix);
	}

	/**
	 * Creates a query criteria with the specified column values.
	 * @param mixed $table the table schema ({@link CDbTableSchema}) or the table name (string).
	 * @param array $columns column values that should be matched in the query (name=>value)
	 * @param mixed $condition query condition or criteria.
	 * If a string, it is treated as query condition;
	 * If an array, it is treated as the initial values for constructing a {@link CDbCriteria};
	 * Otherwise, it should be an instance of {@link CDbCriteria}.
	 * @param array $params parameters to be bound to an SQL statement.
	 * This is only used when the third parameter is a string (query condition).
	 * In other cases, please use {@link CDbCriteria::params} to set parameters.
	 * @param string $prefix column prefix (ended with dot). If null, it will be the table name
	 * @return CDbCriteria the created query criteria
	 */
	public function createColumnCriteria($table,$columns,$condition='',$params=array(),$prefix=null)
	{
		$this->ensureTable($table);
		$criteria=$this->createCriteria($condition,$params);
		if($criteria->alias!='')
			$prefix=$this->_schema->quoteTableName($criteria->alias).'.';
		$bindByPosition=isset($criteria->params[0]);
		$conditions=array();
		$values=array();
		$i=0;
		if($prefix===null)
			$prefix=$table->rawName.'.';
		foreach($columns as $name=>$value)
		{
			if(($column=$table->getColumn($name))!==null)
			{
				if(is_array($value))
					$conditions[]=$this->createInCondition($table,$name,$value,$prefix);
				else if($value!==null)
				{
					if($bindByPosition)
					{
						$conditions[]=$prefix.$column->rawName.'=?';
						$values[]=$value;
					}
					else
					{
						$conditions[]=$prefix.$column->rawName.'='.self::PARAM_PREFIX.$i;
						$values[self::PARAM_PREFIX.$i]=$value;
						$i++;
					}
				}
				else
					$conditions[]=$prefix.$column->rawName.' IS NULL';
			}
			else
				throw new CDbException(Yii::t('yii','Table "{table}" does not have a column named "{column}".',
					array('{table}'=>$table->name,'{column}'=>$name)));
		}
		$criteria->params=array_merge($values,$criteria->params);
		if(isset($conditions[0]))
		{
			if($criteria->condition!='')
				$criteria->condition=implode(' AND ',$conditions).' AND ('.$criteria->condition.')';
			else
				$criteria->condition=implode(' AND ',$conditions);
		}
		return $criteria;
	}

	/**
	 * Generates the expression for searching the specified keywords within a list of columns.
	 * The search expression is generated using the 'LIKE' SQL syntax.
	 * Every word in the keywords must be present and appear in at least one of the columns.
	 * @param mixed $table the table schema ({@link CDbTableSchema}) or the table name (string).
	 * @param array $columns list of column names for potential search condition.
	 * @param mixed $keywords search keywords. This can be either a string with space-separated keywords or an array of keywords.
	 * @param string $prefix optional column prefix (with dot at the end). If null, the table name will be used as the prefix.
	 * @param boolean $caseSensitive whether the search is case-sensitive. Defaults to true. This parameter
	 * has been available since version 1.0.4.
	 * @return string SQL search condition matching on a set of columns. An empty string is returned
	 * if either the column array or the keywords are empty.
	 */
	public function createSearchCondition($table,$columns,$keywords,$prefix=null,$caseSensitive=true)
	{
		$this->ensureTable($table);
		if(!is_array($keywords))
			$keywords=preg_split('/\s+/u',$keywords,-1,PREG_SPLIT_NO_EMPTY);
		if(empty($keywords))
			return '';
		if($prefix===null)
			$prefix=$table->rawName.'.';
		$conditions=array();
		foreach($columns as $name)
		{
			if(($column=$table->getColumn($name))===null)
				throw new CDbException(Yii::t('yii','Table "{table}" does not have a column named "{column}".',
					array('{table}'=>$table->name,'{column}'=>$name)));
			$condition=array();
			foreach($keywords as $keyword)
			{
				$keyword='%'.strtr($keyword,array('%'=>'\%', '_'=>'\_')).'%';
				if($caseSensitive)
					$condition[]=$prefix.$column->rawName.' LIKE '.$this->_connection->quoteValue('%'.$keyword.'%');
				else
					$condition[]='LOWER('.$prefix.$column->rawName.') LIKE LOWER('.$this->_connection->quoteValue('%'.$keyword.'%').')';
			}
			$conditions[]=implode(' AND ',$condition);
		}
		return '('.implode(' OR ',$conditions).')';
	}

	/**
	 * Generates the expression for selecting rows of specified primary key values.
	 * @param mixed $table the table schema ({@link CDbTableSchema}) or the table name (string).
	 * @param mixed $columnName the column name(s). It can be either a string indicating a single column
	 * or an array of column names. If the latter, it stands for a composite key.
	 * @param array $values list of key values to be selected within
	 * @param string $prefix column prefix (ended with dot). If null, it will be the table name
	 * @return string the expression for selection
	 * @since 1.0.4
	 */
	public function createInCondition($table,$columnName,$values,$prefix=null)
	{
		if(($n=count($values))<1)
			return '0=1';

		$this->ensureTable($table);

		if($prefix===null)
			$prefix=$table->rawName.'.';

		$db=$this->_connection;

		if(is_array($columnName) && count($columnName)===1)
			$columnName=reset($columnName);

		if(is_string($columnName)) // simple key
		{
			if(!isset($table->columns[$columnName]))
				throw new CDbException(Yii::t('yii','Table "{table}" does not have a column named "{column}".',
				array('{table}'=>$table->name, '{column}'=>$columnName)));
			$column=$table->columns[$columnName];

			foreach($values as &$value)
			{
				$value=$column->typecast($value);
				if(is_string($value))
					$value=$db->quoteValue($value);
			}
			if($n===1)
				return $prefix.$column->rawName.($values[0]===null?' IS NULL':'='.$values[0]);
			else
				return $prefix.$column->rawName.' IN ('.implode(', ',$values).')';
		}
		else if(is_array($columnName)) // composite key: $values=array(array('pk1'=>'v1','pk2'=>'v2'),array(...))
		{
			foreach($columnName as $name)
			{
				if(!isset($table->columns[$name]))
					throw new CDbException(Yii::t('yii','Table "{table}" does not have a column named "{column}".',
					array('{table}'=>$table->name, '{column}'=>$name)));

				for($i=0;$i<$n;++$i)
				{
					if(isset($values[$i][$name]))
					{
						$value=$table->columns[$name]->typecast($values[$i][$name]);
						if(is_string($value))
							$values[$i][$name]=$db->quoteValue($value);
						else
							$values[$i][$name]=$value;
					}
					else
						throw new CDbException(Yii::t('yii','The value for the column "{column}" is not supplied when querying the table "{table}".',
							array('{table}'=>$table->name,'{column}'=>$name)));
				}
			}
			if(count($values)===1)
			{
				$entries=array();
				foreach($values[0] as $name=>$value)
					$entries[]=$prefix.$table->columns[$name]->rawName.($value===null?' IS NULL':'='.$value);
				return implode(' AND ',$entries);
			}

			return $this->createCompositeInCondition($table,$values,$prefix);
		}
		else
			throw new CDbException(Yii::t('yii','Column name must be either a string or an array.'));
	}

	/**
	 * Generates the expression for selecting rows with specified composite key values.
	 * @param CDbTableSchema $table the table schema
	 * @param array $values list of primary key values to be selected within
	 * @param string $prefix column prefix (ended with dot)
	 * @return string the expression for selection
	 * @since 1.0.4
	 */
	protected function createCompositeInCondition($table,$values,$prefix)
	{
		$keyNames=array();
		foreach(array_keys($values[0]) as $name)
			$keyNames[]=$prefix.$table->columns[$name]->rawName;
		$vs=array();
		foreach($values as $value)
			$vs[]='('.implode(', ',$value).')';
		return '('.implode(', ',$keyNames).') IN ('.implode(', ',$vs).')';
	}

	/**
	 * Checks if the parameter is a valid table schema.
	 * If it is a string, the corresponding table schema will be retrieved.
	 * @param mixed $table table schema ({@link CDbTableSchema}) or table name (string).
	 * If this refers to a valid table name, this parameter will be returned with the corresponding table schema.
	 * @throws CDbException if the table name is not valid
	 * @since 1.0.4
	 */
	protected function ensureTable(&$table)
	{
		if(is_string($table) && ($table=$this->_schema->getTable($tableName=$table))===null)
			throw new CDbException(Yii::t('yii','Table "{table}" does not exist.',
				array('{table}'=>$tableName)));
	}
}